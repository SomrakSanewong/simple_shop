<?php
include '../db.php'; // เพื่อเรียก session_start() ด้วย
// ตรวจสอบว่ามี session ของ admin หรือไม่
if (!isset($_SESSION['admin_id'])) {
    // ถ้าไม่มี ให้ redirect ไปหน้า login
    header("location: login.php");
    exit;
}