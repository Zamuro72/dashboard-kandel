<?php
$page_title = "Kelola Training Kemnaker";
include 'includes/header.php';
include 'includes/sidebar.php';

// Get all training
$sql = "SELECT * FROM pelatihan_kemnaker ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-graduation-cap"></i> Daftar Training Kemnaker</h2>
        <a href="pelatihan-kemnaker-add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Training
        </a>
    </div>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Training</th>
                        <th>Tanggal</th>
                        <th>Durasi</th>
                        <th>Lokasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = $result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo $row['nama_training']; ?></strong></td>
                        <td><?php echo $row['tanggal']; ?></td>
                        <td><span style="background: #e8f5e9; padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem;"><?php echo $row['durasi']; ?></span></td>
                        <td><?php echo $row['lokasi']; ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="pelatihan-kemnaker-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="pelatihan-kemnaker-delete.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirmDelete('Yakin ingin menghapus training ini?')">
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
            <p>Belum ada jadwal training. Klik tombol "Tambah Training" untuk menambahkan.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>