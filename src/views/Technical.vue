<template>
	<top-nav/>

	<side-nav/>

	<!--	Technical Deduction Sheet	-->
	<v-main
		v-if="$store.getters['auth/getUser'] !== null"
	>
        <v-table
            v-if="$route.params.eventSlug && event"
            density="comfortable"
            fixed-header
            :height="scoreSheetHeight"
        >
            <thead>
                <tr>
                    <th
                        colspan="3"
                        class="text-uppercase text-center font-weight-bold text-grey-darken-4 py-3"
                        :class="$vuetify.display.mdAndDown ? 'text-h5' : 'text-h4'"
                    >
                        {{ event.title }}
                    </th>
                    <th
                        style="width: 1rem;"
                        class="text-uppercase text-center text-grey-darken-3 font-weight-bold py-3"
                        :class="$vuetify.display.mdAndDown ? 'text-body-1' : 'text-h6'"
                    >
                        Deduction
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(team, teamIndex) in teams"
                    :key="team.id"
                    :class="{ 'bg-grey-lighten-4': coordinates.y == teamIndex && !scoreSheetDisabled }"
                >
                    <td
                        class="text-uppercase text-right text-h5 font-weight-bold text-grey-darken-2"
                        :class="{ 'text-grey-darken-4': coordinates.y == teamIndex && !scoreSheetDisabled }"
                        style="width: 0.2rem;"
                    >
                        {{ team.number }}
                    </td>
                    <td style="width: 72px;">
                        <v-avatar size="72">
                            <v-img
                                cover
                                :src="`${$store.getters.appURL}/crud/uploads/${team.avatar}`"
                            />
                        </v-avatar>
                    </td>
                    <td
                        class="px-0 text-grey-darken-2"
                        :class="{ 'text-grey-darken-4': coordinates.y == teamIndex && !scoreSheetDisabled }"
                    >
                        <p class="mt-0 me-0 mb-1 ms-0 text-subtitle-2 text-uppercase font-weight-bold" style="line-height: 1.2">{{ team.name }}</p>
                        <p class="mb-0" v-if="team.location.trim() !== ''" style="line-height: 1; opacity: 0.8"><small><b>{{ team.location }}</b></small></p>
                        <p class="mb-0" v-if="team.meta.trim() !== ''" style="line-height: 1; opacity: 0.85"><small>{{ team.meta }}</small></p>
                    </td>
                    <td>
                        <v-text-field
                            type="number"
                            variant="outlined"
                            align="center"
                            justify="center"
                            hide-details
                            single-line
                            :min="$store.state.deduction.min"
                            :max="$store.state.deduction.max"
                            :loading="deductions[`${event.slug}_${team.id}`].loading"
                            v-model="deductions[`${event.slug}_${team.id}`].value"
                            @change="saveDeduction(deductions[`${event.slug}_${team.id}`], team.id)"
                            :class="{
                                'text-error font-weight-bold': (
                                    deductions[`${event.slug}_${team.id}`].value < $store.state.deduction.min ||
                                    deductions[`${event.slug}_${team.id}`].value > $store.state.deduction.max
                                ),
                                'text-grey-darken-2': deductions[`${event.slug}_${team.id}`].value === 0
                            }"
                            :error="(
                                  deductions[`${event.slug}_${team.id}`].value.toString().trim() === ''
                               || deductions[`${event.slug}_${team.id}`].value < 0
                               || deductions[`${event.slug}_${team.id}`].value > 100
                           )"
                            :disabled="team.disabled || deductions[`${event.slug}_${team.id}`].is_locked"
                            :id="`input_${teamIndex}`"
                            @keydown.down.prevent="moveDown(teamIndex)"
                            @keydown.enter="moveDown(teamIndex)"
                            @keydown.up.prevent="moveUp(teamIndex)"
                            @focus.passive="updateCoordinates(teamIndex)"
                        />
                    </td>
                </tr>
            </tbody>
            <!--	Dialog	  -->
            <tfoot>
                <tr>
                    <td colspan="12">
                        <v-col align="center" justify="end">
                            <v-btn
                                class="py-7 bg-grey-lighten-1 text-grey-darken-3"
                                @click="submitDialog = true"
                                :disabled="scoreSheetDisabled"
                                block
                                flat
                            >
                                <p style="font-size: 1.2rem;">Submit Deductions</p>
                            </v-btn>
                            <v-dialog
                                v-model="submitDialog"
                                persistent
                                max-width="400"
                            >
                                <v-card>
                                    <v-card-title class="bg-black">
                                        <v-icon id="remind">mdi-information</v-icon>
                                        Submit Deductions
                                    </v-card-title>
                                    <v-card-text>
                                        Please confirm that you wish to finalize the deductions for <b>{{ event.title }}</b>.
                                        This action cannot be undone.
                                    </v-card-text>
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn
                                            class="text-red-darken-1"
                                            :disabled="submitLoading"
                                            @click="submitDialog = false"
                                        >
                                            Go Back
                                        </v-btn>
                                        <v-btn
                                            class="text-green-darken-1"
                                            :loading="submitLoading"
                                            @click="submitDeductions"
                                        >
                                            Submit
                                        </v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-dialog>
                        </v-col>
                    </td>
                </tr>
            </tfoot>
        </v-table>

		<!-- loader -->
		<div v-else-if="this.$route.params.eventSlug" class="d-flex justify-center align-center" style="height: 100vh;">
			<v-progress-circular
				:size="80"
				color="black"
				class="mb-16"
				indeterminate
			/>
		</div>
	</v-main>
</template>


<script>
    import topNav from "../components/nav/TopNav.vue";
    import sideNav from "../components/nav/SideNav.vue";
    import $ from "jquery";

    export default {
        name: 'Technical',
        emits: ['startPing'],
        components: {
            topNav,
            sideNav
        },
        data() {
            return {
                dialog: false,
                submitDialog: false,
                submitLoading: false,
                event: null,
                timer: null,
                teams: [],
                deductions: {},
                submitDeduction: {},
                coordinates: {
                    x: -1,
                    y: -1
                }
            }
        },
        computed: {
            scoreSheetHeight() {
                return this.$store.getters.windowHeight - 64;
            },
            scoreSheetDisabled() {
                // initialize disable
                let disabled = true;
                // get deduction.is_locked and pass value to disabled variable
                for (let i = 0; i < this.teams.length; i++) {
                    const team = this.teams[i];
                    let deduction = this.deductions[`${this.event.slug}_${team.id}`];
                    if (!deduction.is_locked) {
                        disabled = false;
                        break;
                    }
                }
                // return value
                return disabled;
            }
        },
        watch: {
            $route: {
                immediate: true,
                handler(to, from) {
                    this.event = null;
                    if (this.timer)
                        clearTimeout(this.timer)
                    this.fetchDeductionSheet();
                }
            }
        },
        methods: {
            fetchDeductionSheet() {
                if (this.$route.params.eventSlug) {
                    $.ajax({
                        url: `${this.$store.getters.appURL}/${this.$store.getters['auth/getUser'].userType}.php`,
                        type: 'GET',
                        xhrFields: {
                            withCredentials: true
                        },
                        data: {
                            getDeductionSheet: this.$route.params.eventSlug
                        },
                        success: (data) => {
                            data = JSON.parse(data);
                            this.deductions = data.deductions;
                            this.event = data.event;
                            this.teams = data.teams;
                            this.submitDeduction = {};

                            for (let i = 0; i < this.teams.length; i++) {
                                const team = this.teams[i];
                                let deduction = this.deductions[`${this.event.slug}_${team.id}`];
                                deduction.loading = false;
                            }

                        },
                        error: (error) => {
                            alert(`ERROR ${error.status}: ${error.statusText}`);
                        },
                    });
                }
            },
            saveDeduction(deduction, teamId) {
                deduction.loading = true;

                if (deduction.value < 0 || deduction.value === '') {
                    deduction.value = 0;
                }
                else if (deduction.value > 100) {
                    deduction.value = 100;
                }
                $.ajax({
                    url: `${this.$store.getters.appURL}/${this.$store.getters['auth/getUser'].userType}.php`,
                    type: 'POST',
                    xhrFields: {
                        withCredentials: true
                    },
                    data: {
                        deduction: deduction
                    },
                    success: (data, textStatus, jqXHR) => {
                        if (deduction.loading) {
                            setTimeout(() => {
                                deduction.loading = false;
                            }, 1000);
                        }
                    },
                    error: (error) => {
                        deduction.loading = false;
                        alert(`ERROR ${error.status}: ${error.statusText}`);
                    },
                });
            },
            submitDeductions() {
                this.submitLoading = true;

                // prepare deductions
                let deductions = [];
                for (let i = 0; i < this.teams.length; i++) {
                    const team = this.teams[i];
                    const deduction = this.deductions[`${this.event.slug}_${team.id}`]
                    deductions.push(deduction);
                }

                // submit deductions
                $.ajax({
                    url: `${this.$store.getters.appURL}/${this.$store.getters['auth/getUser'].userType}.php`,
                    type: 'POST',
                    xhrFields: {
                        withCredentials: true
                    },
                    data: {
                        deductions,
                        locking: true
                    },
                    success: (data, textStatus, jqXHR) => {
                        if (this.submitLoading) {
                            setTimeout(() => {
                                this.submitLoading = false
                                this.submitDialog = false;
                                // locks deductions of current event
                                for (let i = 0; i < deductions.length; i++) {
                                    deductions[i].is_locked = true;
                                }
                            }, 600);
                        }

                        this.submitDeduction['is_locked'] = true;
                    },
                    error: (error) => {
                        this.submitLoading = false;
                        alert(`ERROR ${error.status}: ${error.statusText}`);
                    }
                });
            },
            move(y, callback, focus = true) {
                // Move to input
                const nextInput = document.querySelector(`#input_${y}`);
                if (nextInput) {
                    if(nextInput.disabled) {
                        if(callback)
                            callback(y);
                    }
                    else {
                        if (focus)
                            nextInput.focus();
                        if (Number(nextInput.value) <= 0)
                            nextInput.select();
                    }
                }
            },
            moveDown (y) {
                // Move to input below
                y += 1;
                if(y < this.teams.length)
                    this.move(y, this.moveDown);
            },
            moveUp (y)  {
                // Move to input above
                y -= 1;
                if(y >= 0)
                    this.move(y, this.moveUp);
            },
            updateCoordinates (y) {
                this.coordinates.y = y;
                this.move(y, null, false);
            }
        },

        mounted() {
            this.$emit('startPing');
        }
    }
</script>


<style scoped>
    tbody td, th {
        height: 64px !important;
    }

    tbody td {
        border-bottom: 1px solid #ddd;
        padding-top: 9px !important;
        padding-bottom: 9px !important;
    }

    #submit {
        background: linear-gradient(-45deg, #e73c7e, #23a6d5, #23d5ab, #e8af45);
        background-size: 200% 200%;

        text-fill-color: transparent;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;

        animation: shine 10s ease infinite;
    }

    #remind {
        animation: tilt-shaking 1s linear infinite;
    }

    @keyframes tilt-shaking {
        0% {
            transform: rotate(0deg);
        }
        25% {
            transform: rotate(6deg);
        }
        50% {
            transform: rotate(0deg);
        }
        75% {
            transform: rotate(-6deg);
        }
        100% {
            transform: rotate(0deg);
        }
    }

    @keyframes shine {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }
</style>
