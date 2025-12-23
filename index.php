<?php
$page_title = "Dashboard";
include 'includes/header.php';
include 'includes/sidebar.php';

// Hitung total data
$total_artikel = $conn->query("SELECT COUNT(*) as total FROM artikel")->fetch_assoc()['total'];
$total_kegiatan = $conn->query("SELECT COUNT(*) as total FROM jadwal_kegiatan")->fetch_assoc()['total'];
$total_gallery = $conn->query("SELECT COUNT(*) as total FROM gallery")->fetch_assoc()['total'];
$total_hero = $conn->query("SELECT COUNT(*) as total FROM hero_slider")->fetch_assoc()['total'];
?>

<div class="dashboard-cards">
    <div class="card">
        <div class="card-icon blue">
            <i class="fas fa-newspaper"></i>
        </div>
        <h3>Total Artikel</h3>
        <div class="count"><?php echo $total_artikel; ?></div>
    </div>
    
    <div class="card">
        <div class="card-icon green">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <h3>Jadwal Kegiatan</h3>
        <div class="count"><?php echo $total_kegiatan; ?></div>
    </div>
    
    <div class="card">
        <div class="card-icon purple">
            <i class="fas fa-images"></i>
        </div>
        <h3>Hero Slider</h3>
        <div class="count"><?php echo $total_hero; ?></div>
    </div>
    
    <div class="card">
        <div class="card-icon orange">
            <i class="fas fa-images"></i>
        </div>
        <h3>Total Gallery</h3>
        <div class="count"><?php echo $total_gallery; ?></div>
    </div>
</div>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-chart-line"></i> Selamat Datang, <?php echo $_SESSION['admin_username']; ?>!</h2>
    </div>
    
    <p style="color: #666; line-height: 1.8;">
        Anda berhasil login ke Dashboard Admin PT Kandel Sekeco Internasional. 
        Gunakan menu di sidebar untuk mengelola konten website.
    </p>
    
    <div style="margin-top: 2rem; padding: 1.5rem; background: linear-gradient(135deg, rgba(46, 159, 216, 0.1), rgba(118, 199, 87, 0.1)); border-radius: 10px;">
        <h3 style="margin-bottom: 1rem; color: var(--dark-bg);">
            <i class="fas fa-info-circle"></i> Quick Guide
        </h3>
        <ul style="list-style: none; padding: 0;">
            <li style="padding: 0.5rem 0; color: #555;">
                <i class="fas fa-check-circle" style="color: var(--primary-green);"></i> 
                Kelola artikel K3 di menu <strong>Artikel</strong>
            </li>
            <li style="padding: 0.5rem 0; color: #555;">
                <i class="fas fa-check-circle" style="color: var(--primary-green);"></i> 
                Atur jadwal kegiatan di menu <strong>Jadwal Kegiatan</strong>
            </li>
            <li style="padding: 0.5rem 0; color: #555;">
                <i class="fas fa-check-circle" style="color: var(--primary-green);"></i> 
                Upload foto kegiatan di menu <strong>Gallery</strong>
            </li>
            <li style="padding: 0.5rem 0; color: #555;">
                <i class="fas fa-check-circle" style="color: var(--primary-green);"></i> 
                Kelola hero slider di menu <strong>Hero Slider</strong>
            </li>
        </ul>
    </div>
</div>

<!-- Recent Jadwal Kegiatan -->
<div class="content-box" style="margin-top: 2rem;">
    <div class="content-box-header">
        <h2><i class="fas fa-calendar-alt"></i> Jadwal Kegiatan Terbaru</h2>
        <a href="jadwal-kegiatan.php" class="btn btn-primary btn-sm">
            <i class="fas fa-eye"></i> Lihat Semua
        </a>
    </div>
    
    <?php
    $recent_kegiatan = $conn->query("SELECT * FROM jadwal_kegiatan ORDER BY created_at DESC LIMIT 5");
    if ($recent_kegiatan && $recent_kegiatan->num_rows > 0):
    ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Judul Program/Kegiatan</th>
                    <th>Jenis Program</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $recent_kegiatan->fetch_assoc()): 
                    $start = new DateTime($row['tanggal_mulai']);
                    $end = new DateTime($row['tanggal_selesai']);
                    $date_range = $start->format('d M') . ' - ' . $end->format('d M Y');
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['nama_program']); ?></strong></td>
                    <td>
                        <span style="background: #e3f2fd; padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem;">
                            <?php echo htmlspecialchars($row['jenis_program']); ?>
                        </span>
                    </td>
                    <td><?php echo $date_range; ?></td>
                    <td>
                        <span style="padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem; <?php echo $row['status'] == 'aktif' ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>Belum ada jadwal kegiatan. Klik "Jadwal Kegiatan" untuk menambahkan.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Recent Artikel -->
<div class="content-box" style="margin-top: 2rem;">
    <div class="content-box-header">
        <h2><i class="fas fa-newspaper"></i> Artikel Terbaru</h2>
        <a href="artikel.php" class="btn btn-primary btn-sm">
            <i class="fas fa-eye"></i> Lihat Semua
        </a>
    </div>
    
    <?php
    $recent_artikel = $conn->query("SELECT * FROM artikel ORDER BY created_at DESC LIMIT 5");
    if ($recent_artikel && $recent_artikel->num_rows > 0):
    ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Tanggal</th>
                    <th>Penulis</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $recent_artikel->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                    <td><?php echo date('d M Y', strtotime($row['date'])); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>Belum ada artikel. Klik "Artikel" untuk menambahkan.</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>