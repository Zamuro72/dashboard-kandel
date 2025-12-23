<?php
$page_title = "Edit Training Kemnaker";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

// Get training ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get training data
$sql = "SELECT * FROM pelatihan_kemnaker WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: pelatihan-kemnaker.php");
    exit;
}

$training = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_training = clean_input($_POST['nama_training']);
    $tanggal = clean_input($_POST['tanggal']);
    $durasi = clean_input($_POST['durasi']);
    $lokasi = clean_input($_POST['lokasi']);
    
    $sql = "UPDATE pelatihan_kemnaker SET 
            nama_training = '$nama_training',
            tanggal = '$tanggal',
            durasi = '$durasi',
            lokasi = '$lokasi'
            WHERE id = $id";
    
    if ($conn->query($sql)) {
        $success = "Training berhasil diupdate!";
        // Refresh data
        $result = $conn->query("SELECT * FROM pelatihan_kemnaker WHERE id = $id");
        $training = $result->fetch_assoc();
    } else {
        $error = "Gagal mengupdate training: " . $conn->error;
    }
}
?>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-edit"></i> Edit Training Kemnaker</h2>
        <a href="pelatihan-kemnaker.php" class="btn btn-secondary">
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
            <label for="nama_training"><i class="fas fa-graduation-cap"></i> Nama Training *</label>
            <input type="text" class="form-control" id="nama_training" name="nama_training" value="<?php echo htmlspecialchars($training['nama_training']); ?>" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="tanggal"><i class="fas fa-calendar"></i> Tanggal *</label>
                <input type="text" class="form-control" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($training['tanggal']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="durasi"><i class="fas fa-clock"></i> Durasi *</label>
                <input type="text" class="form-control" id="durasi" name="durasi" value="<?php echo htmlspecialchars($training['durasi']); ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="lokasi"><i class="fas fa-map-marker-alt"></i> Lokasi *</label>
            <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?php echo htmlspecialchars($training['lokasi']); ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Update Training
        </button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>