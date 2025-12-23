<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $sql = "SELECT image FROM flyer_pelatihan WHERE id = $id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $flyer = $result->fetch_assoc();
        
        $image_path = '../uploads/flyer/' . $flyer['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        
        $sql = "DELETE FROM flyer_pelatihan WHERE id = $id";
        $conn->query($sql);
    }
}

header("Location: flyer-pelatihan.php");
exit;
?>