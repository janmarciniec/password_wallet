<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Sign in</title>
    <link rel="stylesheet" href="css/style.css"/>
    <script src="block.js"></script>
</head>
<body>
<?php
    require('database.php');

    session_start();
    $ip_addr = $_SERVER['REMOTE_ADDR'];
    $error_msg="";
    $blocked_msg="";
    
    if (isset($_SESSION["locked"]))
    {
        $difference = time() - $_SESSION["locked"];
        
        if ($_SESSION["login_attempts"] == 2) {
            $blocked_for = 5;
        } elseif ($_SESSION["login_attempts"] == 3) {
            $blocked_for = 10;
        } elseif ($_SESSION["login_attempts"] > 3) {
            $blocked_for = 120;
        }
 
        if ($difference > $blocked_for)
        {
            unset($_SESSION["locked"]);
            unset($_SESSION["login_attempts"]);
       }
    }
    
    //check if the login form has been submitted or the user just entered the login form
    if(isset($_POST['submit'])) {
        
        if (isset($_SESSION['login_attempts'])) {
            if ($_SESSION["login_attempts"] == 2) {
                $_SESSION["locked"] = time();
                $blocked_msg = "Login blocked. Please wait for 5 seconds.";
            } elseif ($_SESSION["login_attempts"] == 3) {
                $_SESSION["locked"] = time();
                $blocked_msg = "Login blocked. Please wait for 10 seconds.";
            } elseif ($_SESSION["login_attempts"] > 3) {
                $_SESSION["locked"] = time();
                $blocked_msg = "Login blocked. Please wait for 2 minutes.";
            }
        } else {
            $_SESSION["login_attempts"] = 0;
        }
 
        $login = stripslashes($_REQUEST['login']);
        $login = mysqli_real_escape_string($con, $login);
        $password = stripslashes($_REQUEST['password']);
        $password = mysqli_real_escape_string($con, $password);
        
        // check if the user exists in the database
        $query = "SELECT * FROM `user` WHERE login='$login'";
        $result = mysqli_query($con, $query) or die(mysql_error());
        $rows = mysqli_num_rows($result);
        
        // if user exsists
        if($rows == 1) {
            // fetch user's password salt
            $query = $con->query("SELECT salt FROM `user` WHERE login='$login'");
            if ($query->num_rows > 0) {
                $row = $query->fetch_assoc();
                $salt = stripslashes($row['salt']);
                $salt = mysqli_real_escape_string($con, $salt);
            }

            $pepper = 'vEJp@K$#p%*SAqb';

            // check if the given password is correct
            $query = "SELECT * FROM `user` WHERE login='$login' AND ("
                                                                    ."(password_hash='".hash('sha512', $pepper.$salt.$password)."' AND isPasswordKeptAsHash='1') "
                                                                    ."OR "
                                                                    ."(password_hash='".hash_hmac('sha512', $password, $salt)."' AND isPasswordKeptAsHash='0')"
                                                                .")";
            $result = mysqli_query($con, $query) or die(mysql_error());
            $rows = mysqli_num_rows($result);
            
            // if the password is correct
            if($rows == 1) {
                // add session variable
                $_SESSION['login'] = $login;

                $query = "INSERT INTO `login` (result, ip_address, login) VALUES (1, '$ip_addr', '$login')";
                $result = mysqli_query($con, $query);
                
                if (!isset($_SESSION['locked'])) {
                    // redirect to the user's account
                    header("Location: dashboard.php");
                }
            } else {
                $query = "INSERT INTO `login` (result, ip_address, login) VALUES (0, '$ip_addr', '$login')";
                $result = mysqli_query($con, $query);
                                
                $error_msg="Incorrect password."; 
                
                $_SESSION["login_attempts"] += 1;
            }
        } else {
            $query = "INSERT INTO `login` (result, ip_address) VALUES (0, '$ip_addr')";
            $result = mysqli_query($con, $query);
            
            $_SESSION["login_attempts"] += 1;
            $error_msg="Incorrect login."; 
        }
    }
?>
    <form action="#" method="POST" class="form">
        <h1 class="login-title">SIGN IN</h1>

        <p class="error"><?php echo $error_msg ?></p> 
        <p class="error"><?php echo $blocked_msg ?></p> 
        
        <input type="text" class="login-input" name="login" placeholder="Login" autofocus="true" required>
        <input type="password" class="login-input" name="password" placeholder="Password" required>
        <input type="submit" value="Login" name="submit" class="login-button"/>
        <p class="link"><a href="registration.php">Create account</a></p>
    </form>

</body>
</html>