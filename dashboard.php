<?php
//include auth_session.php file on all user panel pages
include("auth_session.php");
require('database.php');

$login = $_SESSION['login'];

$dates_successful = array();
$dates_failed = array();

$query = "SELECT date_time FROM `login` WHERE login='$login' AND result=1 ORDER BY date_time DESC";

$result = $con->query($query);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        array_push($dates_successful, $row["date_time"]);
    }
}

$query = "SELECT date_time FROM `login` WHERE login='$login' AND result=0 ORDER BY date_time DESC";
$result = mysqli_query($con,$query);

$result = $con->query($query);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        array_push($dates_failed, $row["date_time"]);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password wallet</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-home mr-2"></i><span class=logo>Home</logo>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- right side of navbar -->
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <?php echo $_SESSION['login'];?>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="change_password.php">Change password</a>
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-5"> 
        <div class="row mb-5">
            <div class="col-12">
                Last succesfull login:
                <b>
                    <?php 
                        if(sizeof($dates_successful) < 2) {
                            echo "None";
                        } else {
                            echo $dates_successful[1];  
                        }
                    ?>
                </b>
                <br/>Last failed login: 
                <b>
                    <?php 
                        if(sizeof($dates_failed) < 1) {
                            echo "None";
                        } else {
                            echo $dates_failed[0];
                        }               
                    ?>
                </b>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Your passwords:</h2>
            </div>
        </div>
        
        <div class="row">
            <div class="col-4">
                <?php
                    $login = $_SESSION['login'];
                    
                    // fetch user id and master password hash
                    $query = "SELECT id, password_hash FROM `user` WHERE login='$login'";
                    $result = $con->query($query);
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $id_user = stripslashes($row['id']);
                        $id_user = mysqli_real_escape_string($con, $id_user);
                        $master_password = stripslashes($row['password_hash']);
                        $master_password = mysqli_real_escape_string($con, $master_password);
                    }

                    $query = "SELECT id, password, web_address, description, login FROM `password` WHERE id_user='$id_user' ORDER BY id DESC";
                    $result = $con->query($query);

                    if ($result->num_rows > 0) {
                        // output data of each password
                        while($row = $result->fetch_assoc()) { ?>
                            <p>
                                <?php echo $row["web_address"]; ?><br/>
                                <?php echo $row["password"]; ?>
                                <a href="#" data-toggle="modal" data-target="#showPasswordModal<?php echo $row["id"];?>"><i class="far fa-eye ml-2"></i></a>
                                <hr>
                            </p>
                            <div class="modal fade" id="showPasswordModal<?php echo $row["id"];?>" tabindex="-1" role="dialog" aria-labelledby="showPasswordModalLabel<?php echo $row["id"];?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                  <div class="modal-content">
                                    <div class="modal-header">
                                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                      </button>
                                    </div>
                                    <div class="modal-body">
                                        <table cellpadding="5">
                                            <tr>
                                                <td class="font-weight-bold">Password:</td>
                                                <td>
                                                    <?php 
                                                        $decrypted_password = openssl_decrypt($row["password"], "aes-128-cbc", md5($master_password)); 
                                                        echo $decrypted_password;
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">Web address:</td>
                                                <td><?php echo $row["web_address"];?></td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">Login:</td>
                                                <td><?php echo $row["login"];?></td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">Description:</td>
                                                <td><?php echo $row["description"];?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                  </div>
                                </div>
                            </div>
                        <?php }
                    } else {
                ?>
                        <b>No passwords.</b>
                    <?php } ?>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-12">
                <a href="add_password.php" class="btn btn-primary mt-2"><i class="fas fa-plus mr-1"></i>New password</a>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/0f8cf82757.js" crossorigin="anonymous"></script>
</body>
</html>