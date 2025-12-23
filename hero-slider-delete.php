<?php
// ==================== HERO-SLIDER-DELETE.PHP ====================
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Get image filename
    $sql = "SELECT image FROM hero_slider WHERE id = $id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $slide = $result->fetch_assoc();
        
        // Delete image file
        $image_path = 'uploads/hero/' . $slide['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        
        // Delete from database
        $sql = "DELETE FROM hero_slider WHERE id = $id";
        $conn->query($sql);
    }
}

header("Location: hero-slider.php");
exit;
?>