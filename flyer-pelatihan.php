<?php
$page_title = "Kelola Flyer Pelatihan";
include 'includes/header.php';
include 'includes/sidebar.php';

// Get all flyers
$sql = "SELECT * FROM flyer_pelatihan ORDER BY urutan ASC, created_at DESC";
$result = $conn->query($sql);
?>

<style>
.flyer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.flyer-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s;
}

.flyer-card:hover {
    transform: translateY(-5px);
}

.flyer-image-wrapper {
    position: relative;
    width: 100%;
    padding-bottom: 177.78%; /* 9:16 aspect ratio */
    background: #f0f0f0;
    overflow: hidden;
}

.flyer-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.flyer-info {
    padding: 1.5rem;
}

.flyer-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.8rem;
}

.badge-active {
    background: #d4edda;
    color: #155724;
}

.badge-inactive {
    background: #f8d7da;
    color: #721c24;
}

.flyer-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border-left: 4px solid #17a2b8;
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1.5rem;
}
</style>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-images"></i> Flyer Pelatihan</h2>
        <a href="flyer-pelatihan-add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Flyer
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Tips:</strong> Gunakan gambar flyer/poster dengan format portrait <strong>720x1280px</strong> (aspect ratio 9:16 - standar mobile). Maksimal 10 flyer aktif ditampilkan di website.
    </div>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="flyer-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="flyer-card">
                    <div class="flyer-image-wrapper">
                        <img src="uploads/flyer/<?php echo htmlspecialchars($row['image']); ?>" 
                             alt="<?php echo htmlspecialchars($row['title']); ?>" 
                             class="flyer-image"
                             onerror="this.src='https://via.placeholder.com/720x1280?text=Flyer+Not+Found&text_color=999'">
                    </div>
                    
                    <div class="flyer-info">
                        <span class="flyer-badge <?php echo $row['aktif'] ? 'badge-active' : 'badge-inactive'; ?>">
                            <?php echo $row['aktif'] ? '✓ Aktif' : '✗ Tidak Aktif'; ?>
                        </span>
                        
                        <h3 style="font-size: 1rem; color: var(--dark-bg); margin-bottom: 0.5rem; line-height: 1.4;">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>
                        
                        <div style="font-size: 0.85rem; color: #999; margin-bottom: 0.5rem;">
                            <i class="fas fa-sort-numeric-up"></i> Urutan: <?php echo $row['urutan']; ?>
                        </div>
                        
                        <div style="font-size: 0.85rem; color: #999;">
                            <i class="far fa-clock"></i> 
                            <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                        </div>
                        
                        <div class="flyer-actions">
                            <a href="flyer-pelatihan-edit.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-warning btn-sm" style="flex: 1;">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="flyer-pelatihan-toggle.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-<?php echo $row['aktif'] ? 'secondary' : 'success'; ?> btn-sm"
                               title="<?php echo $row['aktif'] ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                                <i class="fas fa-<?php echo $row['aktif'] ? 'eye-slash' : 'eye'; ?>"></i>
                            </a>
                            <a href="flyer-pelatihan-delete.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirmDelete('Yakin ingin menghapus flyer ini?')"
                               title="Hapus">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Belum ada flyer pelatihan. Klik tombol "Tambah Flyer" untuk menambahkan.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>