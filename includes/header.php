<?php
session_start();

// Cek login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Koneksi database
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?> - Admin KSI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/dashboard.css">
    <style>
/* Force fix layout */
.dashboard-wrapper {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 260px;
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    background: linear-gradient(135deg, #1a1a2e 0%, #2d2d44 100%);
    color: white;
}

.main-content {
    flex: 1;
    margin-left: 260px;
    background: #f5f5f5;
}

.content-area {
    padding: 2rem;
}
</style>
</head>
<body>
    <div class="dashboard-wrapper">
        <main class="main-content">
            <div class="topbar">
                <h1><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                <div class="topbar-right">
                    <span class="datetime" id="datetime"></span>
                    <a href="logout.php" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            <div class="content-area">