<?php
$page_title = "Kelola Gallery";
include 'includes/header.php';
include 'includes/sidebar.php';

// Get all gallery
$sql = "SELECT * FROM gallery ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<style>
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.gallery-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
}

.gallery-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.gallery-image-wrapper {
    position: relative;
    width: 100%;
    height: 220px;
    background: #f0f0f0;
    overflow: hidden;
}

.gallery-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.gallery-card:hover .gallery-image {
    transform: scale(1.05);
}

.gallery-info {
    padding: 1.5rem;
}

.gallery-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark-bg);
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.gallery-description {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.gallery-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.image-error {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    color: #999;
}

.image-error i {
    font-size: 3rem;
    margin-bottom: 0.5rem;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    padding: 1rem;
    border-radius: 5px;
    border-left: 4px solid #17a2b8;
    margin-bottom: 1rem;
}
</style>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-images"></i> Gallery Foto (BLOB System)</h2>
        <a href="gallery-add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Foto
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Sistem Baru:</strong> Gambar disimpan langsung di database. Tidak ada dependency folder uploads lagi!
    </div>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="gallery-grid">
            <?php while ($row = $result->fetch_assoc()): 
                // Check if image exists in BLOB or file
                $has_blob = !empty($row['image_blob']);
                $has_file = file_exists('uploads/gallery/' . $row['image']);
            ?>
                <div class="gallery-card">
                    <div class="gallery-image-wrapper">
                        <?php if ($has_blob): ?>
                            <!-- Display from BLOB -->
                            <img src="display-gallery-image.php?id=<?php echo $row['id']; ?>" 
                                 alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                 class="gallery-image"
                                 onerror="this.parentElement.innerHTML='<div class=\'image-error\'><i class=\'fas fa-image\'></i><small>Error loading image</small></div>'">
                        <?php elseif ($has_file): ?>
                            <!-- Fallback to file system -->
                            <img src="uploads/gallery/<?php echo htmlspecialchars($row['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                 class="gallery-image"
                                 onerror="this.parentElement.innerHTML='<div class=\'image-error\'><i class=\'fas fa-image\'></i><small>Image not found</small></div>'">
                        <?php else: ?>
                            <!-- No image available -->
                            <div class="image-error">
                                <i class="fas fa-exclamation-triangle"></i>
                                <small>Gambar tidak ditemukan</small>
                                <small style="font-size: 0.75rem; margin-top: 0.5rem;">
                                    ID: <?php echo $row['id']; ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="gallery-info">
                        <h3 class="gallery-title">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>
                        
                        <?php if ($row['description']): ?>
                            <p class="gallery-description">
                                <?php echo htmlspecialchars($row['description']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div style="font-size: 0.85rem; color: #999; margin-bottom: 0.5rem;">
                            <i class="far fa-clock"></i> 
                            <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                        </div>
                        
                        <div style="font-size: 0.75rem; color: #999; margin-bottom: 1rem;">
                            <?php if ($has_blob): ?>
                                <span style="background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 10px;">
                                    <i class="fas fa-database"></i> BLOB
                                </span>
                            <?php elseif ($has_file): ?>
                                <span style="background: #fff3cd; color: #856404; padding: 2px 8px; border-radius: 10px;">
                                    <i class="fas fa-file"></i> File
                                </span>
                            <?php else: ?>
                                <span style="background: #f8d7da; color: #721c24; padding: 2px 8px; border-radius: 10px;">
                                    <i class="fas fa-times"></i> Missing
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="gallery-actions">
                            <a href="gallery-edit.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-warning btn-sm" 
                               style="flex: 1; justify-content: center;">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="gallery-delete.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirmDelete('Yakin ingin menghapus foto ini?')" 
                               style="flex: 1; justify-content: center;">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Belum ada foto di gallery. Klik tombol "Tambah Foto" untuk menambahkan.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>