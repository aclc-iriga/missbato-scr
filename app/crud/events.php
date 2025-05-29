<?php

    require_once '../config/database.php';
    require_once 'auth.php';
    
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="logo.png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="dist/bootstrap-4.2.1/css/bootstrap.min.css">

    <!-- For Icon -->
    <link rel="stylesheet" href="dist/fontawesome-6.3.0/css/all.min.css">

    <style>tr, td, th { vertical-align: middle !important; }</style>

    <title>Events | CRUD</title>
  </head>
  <body style="background-color: black">
     <!-- Modal -->
     <!-- ADD POP UP FORM (Bootstrap MODAL) -->
     <div class="modal fade" id="addmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="events_operation.php" method="POST">
                    <div class="modal-body">
                        <?php
                            require_once '../models/Category.php';

                            $entity_category = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
                            $categories = Category::all();
                        ?>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category) {
                                    $selected = ($category->getSlug() == $entity_category) ? 'selected' : '';
                                    echo "<option value={$category->getId()} $selected>{$category->getTitle()}</option>";
                                } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" class="form-control" placeholder="Enter Slug" autocomplete="off" required>
                        </div>

                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" placeholder="Enter Title" autocomplete="off" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="submit" name="insertdata" class="btn btn-primary">Save Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT POP UP FORM (Bootstrap MODAL) -->
    <div class="modal fade" id="editmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="events_operation.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="update_id" id="update_id">
                        <?php
                            require_once '../models/Category.php';

                            $entity_category = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
                            $categories = Category::all();
                        ?>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" id="category_id" class="form-control">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category) {
                                    $selected = ($category->getSlug() == $entity_category) ? 'selected' : '';
                                    echo "<option value={$category->getId()} $selected>{$category->getTitle()}</option>";
                                } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control" placeholder="Enter Slug">
                        </div>

                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="Enter Title">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="submit" name="updatedata" class="btn btn-primary">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DELETE POP UP FORM (Bootstrap MODAL) -->
    <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="events_operation.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="delete_id" id="delete_id">
                        <h4>Do you want to Delete this Data ??</h4>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal"> NO </button>
                        <button type="submit" name="deletedata" class="btn btn-danger"> Yes !! Delete it. </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

     <!-- Sign Out POP UP Form (Bootstrap MODAL) -->
     <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered">
             <div class="modal-content">
                 <div class="modal-header">
                     <h5 class="modal-title" id="exampleModalLabel">Sign Out</h5>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                         <span aria-hidden="true">&times;</span>
                     </button>
                 </div>
                 <form action="" method="POST">
                     <div class="modal-body">
                         <input type="hidden" name="signout">
                         <h4> Are you sure you want to Sign Out??</h4>
                     </div>
                 </form>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                     <a class="btn btn-danger" href="logout.php" role="button">Yes</a>
                 </div>
             </div>
         </div>
     </div>

    <div class="container my-3">
        <h1 class="text-center text-light"><b> <u>Events</u> </b></h1>
        <div class="d-flex align-items-center mr-3 my-3">
            <div class="btn-group" role="group" aria-label="Go to">
                <select onchange="window.location.href=this.value" class="btn btn-secondary">
                    <option value="competitions.php">Competitions</option>
                    <option value="categories.php">Categories</option>
                    <option selected value="events.php">Events</option>
                    <option value="criteria.php">Criterion</option>
                    <option value="teams.php">Teams</option>
                    <option value="judges.php">Judges</option>
                    <option value="technicals.php">Technicals</option>
                </select>
            </div>
            <div class="btn-group ml-3" role="group" aria-label="Go to">
                <?php require_once '../models/Category.php'; ?>
                <select onchange="window.location = `${window.location.pathname}${this.value !== '' ? '?category=' + this.value : ''}`" class="btn btn-secondary">
                    <option selected value="">All Categories</option>
                    <?php foreach(Category::rows() as $category) { ?>
                        <option value="<?= $category['slug'] ?>"
                            <?php
                            if(isset($_GET['category'])) {
                                if(strtolower(trim($_GET['category'])) == $category['slug'])
                                    echo " selected";
                            }
                            ?>
                        ><?= $category['title'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="btn-group ml-auto" role="group" aria-label="Go to">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addmodal">ADD DATA</button>
            </div>
            <div class="btn-group ml-3" role="group" aria-label="Go to">
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#exampleModal">
                    Sign out <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </div>
        </div>
        <?php
            require_once '../config/database.php';
            require_once '../models/Event.php';
            require_once '../models/Category.php';

            $events = [];
            if(isset($_GET['category'])) {
                $category = Category::findBySlug($_GET['category']);
                if($category)
                    $events = $category->getAllEvents();
                else
                    $events = Event::all();
            }
            else
                $events = Event::all();
        ?>
        <table id="datatableid" class="table table-info table-striped text-center">
            <thead class="table-dark">
                <tr>
                    <th scope="col" class="d-none">ID</th>
                    <th scope="col" class="d-none">Categories_ID</th>
                    <th scope="col">Slug</th>
                    <th scope="col">Title</th>
                    <th scope="col">Operations</th>
                </tr>
            </thead>
            <tbody class="table-dark">
                <?php foreach ($events as $event) { ?>
                    <tr>
                        <td class="d-none"><?php echo $event->getId(); ?></td>
                        <td class="d-none"><?php echo $event->getCategoryId(); ?></td>
                        <td><?php echo $event->getSlug(); ?></td>
                        <td><?php echo $event->getTitle(); ?></td>
                        <td>
                            <button type="button" class="btn btn-success editbtn"><i class="fa-solid fa-pen-to-square"></i></button>
                            <button type="button" class="btn btn-danger deletebtn"><i class="fa-solid fa-trash-can"></i></button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap Javascript -->
    <script src="dist/jquery-3.6.4/jquery-3.6.4.min.js"></script>
    <script src="dist/bootstrap-4.2.1/js/bootstrap.min.js"></script>

    <script src="main.js"></script>

    <script>
        $(document).ready(function () {

            $('.editbtn').on('click', function () {

                $('#editmodal').modal('show');

                $tr = $(this).closest('tr');

                var data = $tr.children("td").map(function () {
                    return $(this).text();
                }).get();

                console.log(data);

                $('#update_id').val(data[0]);
                $('#category_id').val(data[1]);
                $('#slug').val(data[2]);
                $('#title').val(data[3]);
            });
        });
    </script>
  </body>
</html>
