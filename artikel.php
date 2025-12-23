<?php
$page_title = "Kelola Artikel";
include 'includes/header.php';
include 'includes/sidebar.php';

// Get all articles
$sql = "SELECT * FROM artikel ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-newspaper"></i> Daftar Artikel</h2>
        <a href="artikel-add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Artikel
        </a>
    </div>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Judul</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = $result->fetch_assoc()): 
                        // Generate excerpt dari content untuk preview
                        $preview = substr(strip_tags($row['content']), 0, 80);
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <img src="uploads/artikel/<?php echo $row['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?>" 
                                 style="width: 80px; height: 60px; object-fit: cover; border-radius: 5px;">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <br>
                            <small style="color: #666;">
                                <?php echo $preview; ?>...
                            </small>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($row['date'])); ?></td>
                        <td>
                            <span style="background: #e3f2fd; padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem;">
                                <?php echo htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="artikel-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="artikel-delete.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirmDelete('Yakin ingin menghapus artikel ini?')"
                                   title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Belum ada artikel. Klik tombol "Tambah Artikel" untuk menambahkan.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>