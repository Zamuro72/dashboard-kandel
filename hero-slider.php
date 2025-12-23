<?php
$page_title = "Kelola Hero Slider";
include 'includes/header.php';
include 'includes/sidebar.php';

// Get all hero slides
$sql = "SELECT * FROM hero_slider ORDER BY urutan ASC";
$result = $conn->query($sql);
?>

<style>
.slider-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.slider-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s;
}

.slider-card:hover {
    transform: translateY(-5px);
}

.slider-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background: #f0f0f0;
}

.slider-info {
    padding: 1.5rem;
}

.slider-badge {
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

.slider-actions {
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
        <h2><i class="fas fa-images"></i> Hero Slider</h2>
        <a href="hero-slider-add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Slide
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Tips:</strong> Gunakan gambar dengan resolusi <strong>1920x1080px</strong> (aspect ratio 16:9) untuk hasil terbaik. Maksimal 5 slide aktif.
    </div>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="slider-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="slider-card">
                    <img src="uploads/hero/<?php echo $row['image']; ?>" 
                         alt="Slide <?php echo $row['urutan']; ?>" 
                         class="slider-image"
                         onerror="this.src='https://via.placeholder.com/1920x1080?text=Image+Not+Found'">
                    
                    <div class="slider-info">
                        <span class="slider-badge <?php echo $row['aktif'] ? 'badge-active' : 'badge-inactive'; ?>">
                            <?php echo $row['aktif'] ? '✓ Aktif' : '✕ Tidak Aktif'; ?>
                        </span>
                        
                        <h3 style="font-size: 1.1rem; color: var(--dark-bg); margin-bottom: 0.5rem;">
                            Slide #<?php echo $row['urutan']; ?>
                            <?php if ($row['title']): ?>
                                - <?php echo htmlspecialchars($row['title']); ?>
                            <?php endif; ?>
                        </h3>
                        
                        <?php if ($row['description']): ?>
                            <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">
                                <?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...
                            </p>
                        <?php endif; ?>
                        
                        <div style="font-size: 0.85rem; color: #999; margin-bottom: 1rem;">
                            <i class="far fa-clock"></i> 
                            <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                        </div>
                        
                        <div class="slider-actions">
                            <a href="hero-slider-edit.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-warning btn-sm" style="flex: 1;">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="hero-slider-toggle.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-<?php echo $row['aktif'] ? 'secondary' : 'success'; ?> btn-sm"
                               style="flex: 1;">
                                <i class="fas fa-<?php echo $row['aktif'] ? 'eye-slash' : 'eye'; ?>"></i>
                                <?php echo $row['aktif'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
                            </a>
                            <a href="hero-slider-delete.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirmDelete('Yakin ingin menghapus slide ini?')">
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
            <p>Belum ada slide di hero section. Klik tombol "Tambah Slide" untuk menambahkan.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>