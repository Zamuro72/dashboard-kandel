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
    
    <!-- Font Awesome dari CDN Cloudflare (lebih stabil) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* ==================== RESET & VARIABLES ==================== */
        :root {
            --primary-blue: #2E9FD8;
            --primary-green: #76C757;
            --primary-purple: #6B4C9A;
            --dark-bg: #1a1a2e;
            --sidebar-bg: #2d2d44;
            --light-gray: #f8f9fa;
            --border-color: #e0e0e0;
            --text-dark: #333;
            --text-light: #666;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* ==================== SIDEBAR ==================== */
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #1a1a2e 0%, #2d2d44 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header img {
            width: 50px;
            height: 50px;
            margin-bottom: 1rem;
            border-radius: 50%;
            border: 3px solid rgba(118, 199, 87, 0.3);
        }

        .sidebar-header h2 {
            font-size: 1.3rem;
            margin: 0;
            color: white;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            margin: 0;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav a:hover {
            background: rgba(118, 199, 87, 0.1);
            color: white;
            border-left-color: var(--primary-green);
        }

        .sidebar-nav li.active a {
            background: rgba(46, 159, 216, 0.15);
            color: white;
            border-left-color: var(--primary-blue);
            font-weight: 600;
        }

        .sidebar-nav a i {
            font-size: 1.2rem;
            width: 30px;
            margin-right: 1rem;
        }

        .sidebar-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 1rem 0;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
        }

        .sidebar-footer p {
            margin: 0;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.6);
        }

        .sidebar-footer small {
            color: rgba(255,255,255,0.4);
            font-size: 0.75rem;
        }

        /* Scrollbar sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(118, 199, 87, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(118, 199, 87, 0.5);
        }

        /* ==================== DASHBOARD LAYOUT ==================== */
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 0;
        }

        /* ==================== TOPBAR ==================== */
        .topbar {
            background: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar h1 {
            font-size: 1.8rem;
            color: var(--dark-bg);
            font-weight: 600;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .datetime {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* ==================== CONTENT AREA ==================== */
        .content-area {
            padding: 2rem;
        }

        .content-box {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .content-box-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light-gray);
        }

        .content-box-header h2 {
            font-size: 1.5rem;
            color: var(--dark-bg);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ==================== DASHBOARD CARDS ==================== */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: white;
        }

        .card-icon.blue { background: linear-gradient(135deg, var(--primary-blue), #1e7fb8); }
        .card-icon.green { background: linear-gradient(135deg, var(--primary-green), #5aa742); }
        .card-icon.purple { background: linear-gradient(135deg, var(--primary-purple), #533a79); }
        .card-icon.orange { background: linear-gradient(135deg, #ff9800, #f57c00); }

        .card h3 {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .card .count {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-bg);
        }

        /* ==================== BUTTONS ==================== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), #1e7fb8);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 159, 216, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--primary-green), #5aa742);
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        /* ==================== FORMS ==================== */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(46, 159, 216, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        select.form-control {
            cursor: pointer;
        }

        /* ==================== FILE UPLOAD BOX ==================== */
        .file-upload-box {
            border: 3px dashed var(--border-color);
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: var(--light-gray);
        }

        .file-upload-box:hover {
            border-color: var(--primary-blue);
            background: rgba(46, 159, 216, 0.05);
        }

        /* ==================== TABLES ==================== */
        .table-responsive {
            overflow-x: auto;
            margin-top: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        table thead tr {
            background: var(--light-gray);
        }

        table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark-bg);
            border-bottom: 2px solid var(--border-color);
        }

        table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        table tbody tr:hover {
            background: rgba(46, 159, 216, 0.03);
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* ==================== ALERTS ==================== */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        /* ==================== EMPTY STATE ==================== */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: 1rem;
        }

        /* ==================== BADGE UNTUK JENIS PROGRAM ==================== */
        span[class*="badge-"] {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-sertifikasikemnakerri {
            background: rgba(46, 159, 216, 0.15);
            color: var(--primary-blue);
        }

        .badge-sertifikasibnsp {
            background: rgba(107, 76, 154, 0.15);
            color: var(--primary-purple);
        }

        .badge-sertifikatkeselamatankebakaran,
        .badge-sertifikatkeselamatankebakaranskk,
        .badge-skk {
            background: rgba(244, 67, 54, 0.15);
            color: #f44336;
        }

        .badge-sertifikatlaikoperasi,
        .badge-sertifikatlaikoperasislo,
        .badge-slo {
            background: rgba(255, 152, 0, 0.15);
            color: #ff9800;
        }

        .badge-sertifikatlaikfungsi,
        .badge-sertifikatlaikfungsislf,
        .badge-slf {
            background: rgba(33, 150, 243, 0.15);
            color: #2196f3;
        }

        .badge-sertifikatbadanusaha,
        .badge-sertifikatbadanusahasbu,
        .badge-sbu {
            background: rgba(76, 175, 80, 0.15);
            color: #4caf50;
        }

        .badge-analisisdampaklalulintas,
        .badge-analisisdampaklalulintasandalalin,
        .badge-andalalin {
            background: rgba(156, 39, 176, 0.15);
            color: #9c27b0;
        }

        .badge-pemeriksaandanpengujianÐ°latk3,
        .badge-riksauji,
        .badge-riksaujialatk3 {
            background: rgba(255, 87, 34, 0.15);
            color: #ff5722;
        }

        .badge-greenship,
        .badge-greenshipedge {
            background: rgba(139, 195, 74, 0.15);
            color: #8bc34a;
        }

        /* Badge status */
        .badge-active {
            background: #d4edda;
            color: #155724;
        }

        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        /* ==================== SEARCH & FILTER ==================== */
        .search-filter-box {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .topbar {
                flex-direction: column;
                gap: 1rem;
            }
            
            .content-area {
                padding: 1rem;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .content-box {
                padding: 1.5rem;
            }

            .content-box-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .table-responsive {
                overflow-x: scroll;
            }
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