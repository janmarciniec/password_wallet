<?php

$con = mysqli_connect("localhost","root","","password_wallet");

// checking connection
if (mysqli_connect_errno()){
    echo "Failed to connect to the database: " . mysqli_connect_error();
}