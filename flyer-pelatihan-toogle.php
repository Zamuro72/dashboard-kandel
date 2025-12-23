<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $sql = "UPDATE flyer_pelatihan SET aktif = NOT aktif WHERE id = $id";
    $conn->query($sql);
}

header("Location: flyer-pelatihan.php");
exit;
?>