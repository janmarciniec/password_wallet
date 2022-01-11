<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Create account</title>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>
<?php
    // database connection
    require('database.php');
    
    // check if the registration form has been submitted or the user just entered the registration form
    if (isset($_POST['submit'])) {
        
        // check if password's confirmation is correct
        if ($_REQUEST['password'] == $_REQUEST['repeat_password']) {
            // remove backslashes
            $login = stripslashes($_REQUEST['login']);
            // escape special characters in a string
            $login = mysqli_real_escape_string($con, $login);
            $password = stripslashes($_REQUEST['password']);
            $password = mysqli_real_escape_string($con, $password);
            $method = ($_REQUEST['method']);

            if($method === "hash") {
                $isPasswordKeptAsHash = 1;
            }
            else {
                $isPasswordKeptAsHash = 0;
            }

            $isPasswordKeptAsHash = stripslashes($isPasswordKeptAsHash);
            $isPasswordKeptAsHash = mysqli_real_escape_string($con, $isPasswordKeptAsHash);

            // generate the salt as a random string
            $salt = stripslashes(generateString(15));
            $salt = mysqli_real_escape_string($con, $salt);

            // hardcoded pepper
            $pepper = 'vEJp@K$#p%*SAqb';

            // save user's data to the database
            // if $isPasswordKeptAsHash === '1', the password will be stored using SHA512 with salt and pepper
            // if $isPasswordKeptAsHash === '0', the password will be stored using HMAC
            $query = "INSERT INTO `user` (login, password_hash, salt, isPasswordKeptAsHash) VALUES ('$login', '".($isPasswordKeptAsHash === '1' ? hash('sha512', $pepper.$salt.$password) : hash_hmac('sha512', $password, $salt))."', '$salt', '$isPasswordKeptAsHash')";
            $result = mysqli_query($con, $query);

            if($result) {
                echo "<div class='form'>
                        <h3>You are registered successfully.</h3><br/>
                        <p class='link'>Click here to <a href='login.php'>Login</a></p>
                      </div>";
            } else {
                echo "<div class='form'>
                        <h3>Required fields are missing.</h3><br/>
                        <p class='link'><a href='registration.php'>CLICK HERE</a> to register again.</p>
                      </div>";
            }
        } else {
            // password's confirmation is incorrect
             echo "<div class='form'>
                    <h3>Password does not match the confirmation.</h3><br/>
                    <p class='link'><a href='registration.php'>CLICK HERE</a> to register again.</p>
                   </div>";
        }
    } else {
?>
    <form class="form" action="#" method="POST">
        <h1 class="login-title">CREATE YOUR ACCOUNT</h1>
        <input type="text" class="login-input" name="login" placeholder="Login" required>
        <input type="password" class="login-input" name="password" placeholder="Password" required>
        <input type="password" class="login-input" name="repeat_password" placeholder="Repeat password" required>
        
        <h4>Hashing algorithm for the password:</h4>
        <div class="radio-group">
            <input type="radio" id="hash" name="method" value="hash" required>
            <label for="hash">SHA512</label><br/>
            <input type="radio" id="hmac" name="method" value="hmac">
            <label for="hmac">HMAC</label><br/>
        </div>
        
        <input type="submit" name="submit" value="Register" class="login-button">
        <p class="link"><a href="login.php">Sign in</a></p>
    </form>
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