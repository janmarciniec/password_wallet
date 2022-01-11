<?php
    include("auth_session.php");
    require('database.php');
    $login = $_SESSION['login'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Change password</title>
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
                            <?php echo $login?>
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
        <?php
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

            // check if the form has been submitted or the user just entered the form to change the password
            if (isset($_POST['submit'])) {
                $current_password = stripslashes($_REQUEST['current_password']);
                $current_password = mysqli_real_escape_string($con, $current_password);
                $new_password = stripslashes($_REQUEST['new_password']);
                $new_password = mysqli_real_escape_string($con, $new_password);

                // fetch the password salt from logged user
                $query = $con->query("SELECT salt FROM `user` WHERE login='$login'");
                if ($query->num_rows > 0) {
                    $row = $query->fetch_assoc();
                    $salt = stripslashes($row['salt']);
                    $salt = mysqli_real_escape_string($con, $salt);
                }
                
                $pepper = 'vEJp@K$#p%*SAqb';

                // check if the given password is correct
                $query = "SELECT * FROM `user` WHERE login='$login' AND ("
                                                                        ."(password_hash='".hash('sha512', $pepper.$salt.$current_password)."' AND isPasswordKeptAsHash='1') "
                                                                        ."OR "
                                                                        ."(password_hash='".hash_hmac('sha512', $current_password, $salt)."' AND isPasswordKeptAsHash='0')"
                                                                    .")";
                $result = mysqli_query($con, $query) or die(mysql_error());
                $rows = mysqli_num_rows($result);

                // check if the current password is correct
                if ($rows == 1) {
                    
                    // check if new password's confirmation is correct
                    if ($_REQUEST['new_password'] == $_REQUEST['repeat_new_password']) {
                        
                        // fetch the hashed current password
                        $query = $con->query("SELECT password_hash FROM `user` WHERE login='$login'");
                        if ($query->num_rows > 0) {
                            $row = $query->fetch_assoc();
                            $current_password_hash = stripslashes($row['password_hash']);
                            $current_password_hash = mysqli_real_escape_string($con, $current_password_hash);
                        }

                        // generate new salt for the password
                        $new_salt = stripslashes(generateString(15));
                        $new_salt = mysqli_real_escape_string($con, $new_salt);

                        // check if the password is stored in the database using SHA512 or HMAC
                        $query = "SELECT isPasswordKeptAsHash FROM `user` WHERE login='$login'";
                        $result = $con->query($query);   
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $isPasswordKeptAsHash = stripslashes($row['isPasswordKeptAsHash']);
                            $isPasswordKeptAsHash = mysqli_real_escape_string($con, $isPasswordKeptAsHash);
                        }

                        if($isPasswordKeptAsHash === "1") {
                            // new password will be stored in the database using SHA512 with salt and pepper
                            $new_password_hash = hash('sha512', $pepper.$new_salt.$new_password);
                            $query = "UPDATE `user` SET password_hash='$new_password_hash', salt='$new_salt' WHERE login='$login'";
                        } else {
                            // new password will be stored in the database using HMAC
                            $new_password_hash = hash_hmac('sha512', $new_password, $new_salt);
                            $query = "UPDATE `user` SET password_hash='$new_password_hash', salt='$new_salt' WHERE login='$login'";
                        }     
                        mysqli_query($con, $query);

                        // decrypt every password from the wallet and then encrypt it with the new master password
                        $query = "SELECT id, password FROM `password` WHERE id_user='$id_user'";
                        $result = $con->query($query);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $password_id = $row["id"];
                                $decrypted_password = openssl_decrypt($row["password"], "aes-128-cbc", md5($current_password_hash));
                                $encrypted_password = openssl_encrypt($decrypted_password, "aes-128-cbc", md5($new_password_hash));
                                $encrypted_password = stripslashes($encrypted_password);
                                $encrypted_password = mysqli_real_escape_string($con, $encrypted_password);
                                $query = "UPDATE `password` SET password='$encrypted_password' WHERE id='$password_id'";
                                mysqli_query($con, $query);
                            }
                        }
                        header("Location: dashboard.php");
                    } else {
                        // new password's confirmation is incorrect
                        echo "<div class='row'>"
                            ."<div class='col-12'>"
                                ."<p class='h2'>New password does not match the confirmation.</p><br/>"
                                ."<a href='change_password.php' class='h3'>Try again</a>"
                            ."</div>"
                    ."</div>";
                    }
                } else {
                    // incorrect current password
                    echo "<div class='row'>"
                        ."<div class='col-6'>"
                            ."<p class='h2'>Incorrect master password.</p><br/>"
                            ."<a href='change_password.php' class='h3'>Try again</a>"
                        ."</div>"
                    ."</div>";
                }
            } else {
        ?>
        <div class="row">
            <div class="col-6">
                <h2 class="mb-4">Change your master password</h2>
            </div>
        </div>
                
        <form action="#" method="POST">
            <div class="row">
                <div class="col-6">  
                    <input type="password" class="form-control mb-2" name="current_password" placeholder="Current password" required>
                    <input type="password" class="form-control mb-2" name="new_password" placeholder="New password" required>
                    <input type="password" class="form-control mb-2" name="repeat_new_password" placeholder="Repeat new password" required>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-3">
                    <input type="submit" name="submit" value="Save" class="btn btn-block btn-primary">
                </div>
                <div class="col-3">
                    <a href="javascript:history.go(-1)" name="cancel" class="btn btn-block btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/0f8cf82757.js" crossorigin="anonymous"></script>
    <?php
        }
        
        function generateString($n) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_{}<>?[]/';
            $randomString = '';

            for ($i = 0; $i < $n; $i++) {
                $index = rand(0, strlen($characters) - 1);
                $randomString .= $characters[$index];
            }

            return $randomString;
        }
    ?>
</body>
</html>