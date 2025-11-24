<?php
session_start();
include 'class/user.php';
include 'connection.php';

if(isset($_POST['username']) && isset($_POST['password'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user= User::find($conn, $username, $password);

    if($user){
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error']="Invalid username or password";
        header("Location: login.php");
        exit;
    }
}else{
    header("Location: login.php");
    exit;
}

?>