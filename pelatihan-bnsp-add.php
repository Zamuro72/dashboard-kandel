<?php
$page_title = "Tambah Training BNSP";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $skema_sertifikasi = clean_input($_POST['skema_sertifikasi']);
    $tanggal = clean_input($_POST['tanggal']);
    $durasi = clean_input($_POST['durasi']);
    $lokasi = clean_input($_POST['lokasi']);
    
    $sql = "INSERT INTO pelatihan_bnsp (skema_sertifikasi, tanggal, durasi, lokasi) 
            VALUES ('$skema_sertifikasi', '$tanggal', '$durasi', '$lokasi')";
    
    if ($conn->query($sql)) {
        $success = "Training berhasil ditambahkan!";
        echo "<script>
            setTimeout(function() {
                window.location.href = 'pelatihan-bnsp.php';
            }, 2000);
        </script>";
    } else {
        $error = "Gagal menyimpan training: " . $conn->error;
    }
}
?>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-plus-circle"></i> Tambah Training BNSP</h2>
        <a href="pelatihan-bnsp.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
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
    
    <form method="POST">
        <div class="form-group">
            <label for="skema_sertifikasi"><i class="fas fa-certificate"></i> Skema Sertifikasi *</label>
            <input type="text" class="form-control" id="skema_sertifikasi" name="skema_sertifikasi" placeholder="Contoh: Manajer K3" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="tanggal"><i class="fas fa-calendar"></i> Tanggal *</label>
                <input type="text" class="form-control" id="tanggal" name="tanggal" placeholder="Contoh: 18-22 November 2025" required>
            </div>
            
            <div class="form-group">
                <label for="durasi"><i class="fas fa-clock"></i> Durasi *</label>
                <input type="text" class="form-control" id="durasi" name="durasi" placeholder="Contoh: 5 Hari" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="lokasi"><i class="fas fa-map-marker-alt"></i> Lokasi *</label>
            <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Contoh: Jakarta" required>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan Training
        </button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>