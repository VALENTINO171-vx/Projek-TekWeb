<?php 
    include 'user.php';
    include 'connection.php';
    $users= new User(0,"","",'');
    $users->username="admins";
    $users->password="admin";
    $users->save($conn);
    // ini bisa di ganti sesuai kebutuhan kalian
?> 
