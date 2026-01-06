<?php
$page_title = "Kelola Gallery";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

// Handle update urutan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_urutan'])) {
    if (!empty($_POST['urutan'])) {
        foreach ($_POST['urutan'] as $id => $urutan) {
            $id = (int)$id;
            $urutan = (int)$urutan;
            $conn->query("UPDATE gallery SET urutan = $urutan WHERE id = $id");
        }
        $success = "Urutan foto berhasil diupdate!";
    }
}

// Get all gallery - ORDER BY urutan
$sql = "SELECT * FROM gallery ORDER BY urutan ASC, created_at DESC";
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

.urutan-input {
    width: 60px;
    padding: 0.3rem 0.5rem;
    border: 2px solid #ddd;
    border-radius: 5px;
    text-align: center;
    font-weight: 600;
}

.sortir-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
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
        <h2><i class="fas fa-images"></i> Gallery Foto</h2>
        <a href="gallery-add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Foto
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Sistem Simple:</strong> Gambar disimpan sebagai file di folder uploads/gallery/. 
        Atur urutan tampilan dengan mengubah nomor urutan.
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <form method="POST" id="urutanForm">
            <!-- Sortir Section -->
            <div class="sortir-section">
                <h3 style="color: var(--dark-bg); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-sort-numeric-down"></i> Atur Urutan Tampilan
                </h3>
                <p style="color: #666; margin-bottom: 1rem;">
                    Ubah nomor urutan di bawah setiap foto, lalu klik <strong>"Simpan Urutan"</strong>. 
                    Foto dengan urutan lebih kecil akan ditampilkan lebih dulu.
                </p>
                <button type="submit" name="update_urutan" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Urutan
                </button>
            </div>
            
            <div class="gallery-grid">
                <?php while ($row = $result->fetch_assoc()): 
                    $image_path = 'uploads/gallery/' . $row['image'];
                    $image_exists = file_exists($image_path);
                ?>
                    <div class="gallery-card">
                        <div class="gallery-image-wrapper">
                            <?php if ($image_exists): ?>
                                <img src="<?php echo $image_path; ?>?v=<?php echo time(); ?>" 
                                     alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                     class="gallery-image"
                                     onerror="this.src='https://via.placeholder.com/800x600?text=Image+Not+Found';">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f8d7da; color: #721c24;">
                                    <div style="text-align: center;">
                                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                                        <p>File tidak ditemukan</p>
                                        <small><?php echo htmlspecialchars($row['image']); ?></small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="gallery-info">
                            <!-- Urutan Input -->
                            <div style="margin-bottom: 1rem; text-align: center;">
                                <label style="font-size: 0.85rem; color: #666; display: block; margin-bottom: 0.3rem;">
                                    <i class="fas fa-sort"></i> Urutan:
                                </label>
                                <input type="number" 
                                       name="urutan[<?php echo $row['id']; ?>]" 
                                       value="<?php echo $row['urutan']; ?>" 
                                       min="1" 
                                       max="999" 
                                       class="urutan-input">
                            </div>
                            
                            <h3 class="gallery-title">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3>
                            
                            <?php if ($row['description']): ?>
                                <p class="gallery-description">
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div style="font-size: 0.85rem; color: #999; margin-bottom: 1rem;">
                                <i class="far fa-clock"></i> 
                                <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
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
        </form>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Belum ada foto di gallery. Klik tombol "Tambah Foto" untuk menambahkan.</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Highlight changed inputs
document.querySelectorAll('.urutan-input').forEach(input => {
    input.addEventListener('change', function() {
        this.style.borderColor = '#ffc107';
        this.style.background = '#fff3cd';
    });
});

console.log('✓ Gallery (Simple File System) loaded');
</script>

<?php include 'includes/footer.php'; ?>