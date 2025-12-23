<?php
// ==================== HERO-SLIDER-TOGGLE.PHP ====================
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Toggle aktif status
    $sql = "UPDATE hero_slider SET aktif = NOT aktif WHERE id = $id";
    $conn->query($sql);
}

header("Location: hero-slider.php");
exit;
?>