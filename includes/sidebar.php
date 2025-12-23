<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <img src="assets/images/logo.png" alt="Logo KSI" style="width: 50px; height: 50px;">
        <h2>Admin Panel</h2>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <a href="index.php">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="<?php echo ($current_page == 'artikel.php' || $current_page == 'artikel-add.php' || $current_page == 'artikel-edit.php') ? 'active' : ''; ?>">
                <a href="artikel.php">
                    <i class="fas fa-newspaper"></i>
                    <span>Artikel</span>
                </a>
            </li>
            
            <li class="<?php echo ($current_page == 'jadwal-kegiatan.php' || $current_page == 'jadwal-kegiatan-add.php' || $current_page == 'jadwal-kegiatan-edit.php') ? 'active' : ''; ?>">
                <a href="jadwal-kegiatan.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Jadwal Kegiatan</span>
                </a>
            </li>
            
            <li class="<?php echo ($current_page == 'hero-slider.php' || $current_page == 'hero-slider-add.php' || $current_page == 'hero-slider-edit.php') ? 'active' : ''; ?>">
                <a href="hero-slider.php">
                    <i class="fas fa-images"></i>
                    <span>Hero Slider</span>
                </a>
            </li>
            
            <!-- MENU BARU: Flyer Pelatihan -->
            <li class="<?php echo ($current_page == 'flyer-pelatihan.php' || $current_page == 'flyer-pelatihan-add.php' || $current_page == 'flyer-pelatihan-edit.php') ? 'active' : ''; ?>">
                <a href="flyer-pelatihan.php">
                    <i class="fas fa-file-image"></i>
                    <span>Flyer Pelatihan</span>
                </a>
            </li>
            
            <li class="<?php echo ($current_page == 'gallery.php' || $current_page == 'gallery-add.php') ? 'active' : ''; ?>">
                <a href="gallery.php">
                    <i class="fas fa-photo-video"></i>
                    <span>Gallery</span>
                </a>
            </li>
            
            <li class="sidebar-divider"></li>
            
            <li>
                <a href="logout.php" onclick="return confirm('Yakin ingin logout?')">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <p>&copy; <?php echo date('Y'); ?> PT KSI</p>
        <small>v1.0.0</small>
    </div>
</aside>

<style>
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

/* Scrollbar styling */
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

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    
    .sidebar-footer {
        position: relative;
    }
}
</style>