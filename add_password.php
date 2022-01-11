<?php
include("auth_session.php");
require('database.php');

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

// if form has been submited
if (isset($_POST['submit'])) {
    // encrypt new password with the master password
    $encrypted_password = openssl_encrypt($_REQUEST['password'], "aes-128-cbc", md5($master_password));
    $encrypted_password = stripslashes($encrypted_password);
    $encrypted_password = mysqli_real_escape_string($con, $encrypted_password);
    $web_address = stripslashes($_REQUEST['web_address']);
    $web_address = mysqli_real_escape_string($con, $web_address);
    $login = stripslashes($_REQUEST['login']);
    $login = mysqli_real_escape_string($con, $login);
    $description = stripslashes($_REQUEST['description']);
    $description = mysqli_real_escape_string($con, $description);

    $query = "INSERT INTO `password` (password, id_user, web_address, description, login) VALUES ('$encrypted_password', '$id_user', '$web_address', '$description', '$login')";
    mysqli_query($con, $query);

    header("Location: dashboard.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New password</title>
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
                <!-- Right Side Of Navbar -->
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
        <div class="row">
            <div class="col-1">
                <a href="dashboard.php" class="h2"><i class="fas fa-arrow-circle-left"></i></a>
            </div>
            <div class="col-6">
                <h2 class="mb-4">New password</h2>
            </div>
        </div>
                
        <form action="#" method="POST">
            <div class="row">
                <div class="col-6">  
                    <input type="password" class="form-control mb-2" name="password" placeholder="Password" required>
                    <input type="text" class="form-control mb-2" name="web_address" placeholder="Web address">
                    <input type="text" class="form-control mb-2" name="login" placeholder="Login">
                    <input type="text" class="form-control mb-2" name="description" placeholder="Description">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-3">
                    <input type="submit" name="submit" value="Save" class="btn btn-block btn-primary">
                </div>
                <div class="col-3">
                    <a href="dashboard.php" name="cancel" class="btn btn-block btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/0f8cf82757.js" crossorigin="anonymous"></script>
</body>
</html>