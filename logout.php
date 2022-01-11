<?php
session_start();
// destroy the session
if(session_destroy()) {
    // redirect to the home page
    header("Location: index.php");
}