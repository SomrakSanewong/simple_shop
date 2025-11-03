<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '1234');
define('DB_NAME', 'simple_shop_db');

// พยายามเชื่อมต่อฐานข้อมูล
$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// ตรวจสอบการเชื่อมต่อ
if ($db === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
} 

//ตั้งค่า charset เป็น utf8mb4
mysqli_set_charset($db, "utf8mb4");

// เริ่ม session ในไฟล์นี้ เพื่อให้ทุกหน้าที่ include ไปใช้งานได้เลย
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}