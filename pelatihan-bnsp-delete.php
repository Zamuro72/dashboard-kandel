<?php
session_start();
require_once 'config/database.php';

// Cek login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $sql = "DELETE FROM pelatihan_bnsp WHERE id = $id";
    $conn->query($sql);
}

header("Location: pelatihan-bnsp.php");
exit;
?>