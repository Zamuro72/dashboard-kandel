<?php
$page_title = "Kelola Gallery";
include 'includes/header.php';
include 'includes/sidebar.php';

// Get all gallery
$sql = "SELECT * FROM gallery ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-images"></i> Gallery Foto</h2>
        <a href="gallery-add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Foto
        </a>
    </div>
    
    <?php if ($result->num_rows > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 3px 15px rgba(0,0,0,0.1); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <img src="uploads/gallery/<?php echo $row['image']; ?>" 
                         alt="<?php echo htmlspecialchars($row['title']); ?>" 
                         style="width: 100%; height: 200px; object-fit: cover;">
                    <div style="padding: 1rem;">
                        <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--dark-bg);">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>
                        <?php if ($row['description']): ?>
                        <p style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($row['description']); ?>
                        </p>
                        <?php endif; ?>
                        <div style="display: flex; gap: 0.5rem;">
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