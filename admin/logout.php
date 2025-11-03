<?php
    session_start();
    
    // ทำลาย session
    session_destroy();
    
    // กลับไปหน้า login
    header("location: login.php");
    exit;
?>