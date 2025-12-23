<?php
$page_title = "Edit Jadwal Kegiatan";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

// Get ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: jadwal-kegiatan.php");
    exit;
}

// Get kegiatan data
$stmt = $conn->prepare("SELECT * FROM jadwal_kegiatan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: jadwal-kegiatan.php");
    exit;
}

$kegiatan = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_program = $conn->real_escape_string(trim($_POST['nama_program']));
    $jenis_program = $conn->real_escape_string(trim($_POST['jenis_program']));
    $tanggal_mulai = $conn->real_escape_string(trim($_POST['tanggal_mulai']));
    $tanggal_selesai = $conn->real_escape_string(trim($_POST['tanggal_selesai']));
    
    // Validasi
    if (empty($nama_program)) {
        $error = "Judul program/kegiatan harus diisi!";
    } elseif (empty($jenis_program)) {
        $error = "Jenis program harus dipilih!";
    } elseif (empty($tanggal_mulai) || empty($tanggal_selesai)) {
        $error = "Tanggal mulai dan selesai harus diisi!";
    } else {
        // Validasi tanggal
        try {
            $start = new DateTime($tanggal_mulai);
            $end = new DateTime($tanggal_selesai);
            
            if ($end < $start) {
                $error = "Tanggal selesai tidak boleh lebih awal dari tanggal mulai!";
            } else {
                // Update database
                $stmt = $conn->prepare("UPDATE jadwal_kegiatan SET nama_program = ?, jenis_program = ?, tanggal_mulai = ?, tanggal_selesai = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $nama_program, $jenis_program, $tanggal_mulai, $tanggal_selesai, $id);
                
                if ($stmt->execute()) {
                    $success = "Jadwal kegiatan berhasil diupdate!";
                    // Refresh data
                    $stmt_get = $conn->prepare("SELECT * FROM jadwal_kegiatan WHERE id = ?");
                    $stmt_get->bind_param("i", $id);
                    $stmt_get->execute();
                    $result = $stmt_get->get_result();
                    $kegiatan = $result->fetch_assoc();
                    $stmt_get->close();
                } else {
                    $error = "Gagal mengupdate kegiatan: " . $stmt->error;
                }
                
                $stmt->close();
            }
        } catch (Exception $e) {
            $error = "Format tanggal tidak valid!";
        }
    }
}
?>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-edit"></i> Edit Jadwal Kegiatan</h2>
        <a href="jadwal-kegiatan.php" class="btn btn-secondary">
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
    
    <form method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="nama_program"><i class="fas fa-heading"></i> Judul Program/Kegiatan *</label>
            <input type="text" 
                   class="form-control" 
                   id="nama_program" 
                   name="nama_program" 
                   value="<?php echo htmlspecialchars($kegiatan['nama_program']); ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="jenis_program"><i class="fas fa-certificate"></i> Jenis Program *</label>
            <select class="form-control" id="jenis_program" name="jenis_program" required>
                <option value="">Pilih Jenis Program</option>
                <option value="Sertifikasi Kemnaker RI" <?php echo $kegiatan['jenis_program'] == 'Sertifikasi Kemnaker RI' ? 'selected' : ''; ?>>Sertifikasi Kemnaker RI</option>
                <option value="Sertifikasi BNSP" <?php echo $kegiatan['jenis_program'] == 'Sertifikasi BNSP' ? 'selected' : ''; ?>>Sertifikasi BNSP</option>
                <option value="Sertifikat Keselamatan Kebakaran (SKK)" <?php echo $kegiatan['jenis_program'] == 'Sertifikat Keselamatan Kebakaran (SKK)' ? 'selected' : ''; ?>>Sertifikat Keselamatan Kebakaran (SKK)</option>
                <option value="Sertifikat Laik Operasi (SLO)" <?php echo $kegiatan['jenis_program'] == 'Sertifikat Laik Operasi (SLO)' ? 'selected' : ''; ?>>Sertifikat Laik Operasi (SLO)</option>
                <option value="Sertifikat Laik Fungsi (SLF)" <?php echo $kegiatan['jenis_program'] == 'Sertifikat Laik Fungsi (SLF)' ? 'selected' : ''; ?>>Sertifikat Laik Fungsi (SLF)</option>
                <option value="Sertifikat Badan Usaha (SBU)" <?php echo $kegiatan['jenis_program'] == 'Sertifikat Badan Usaha (SBU)' ? 'selected' : ''; ?>>Sertifikat Badan Usaha (SBU)</option>
                <!-- AMDAL DIHAPUS -->
                <option value="Analisis Dampak Lalu Lintas (ANDALALIN)" <?php echo $kegiatan['jenis_program'] == 'Analisis Dampak Lalu Lintas (ANDALALIN)' ? 'selected' : ''; ?>>Analisis Dampak Lalu Lintas (ANDALALIN)</option>
                <option value="Pemeriksaan dan Pengujian Alat K3 (Riksa Uji Alat K3)" <?php echo $kegiatan['jenis_program'] == 'Pemeriksaan dan Pengujian Alat K3 (Riksa Uji Alat K3)' ? 'selected' : ''; ?>>Pemeriksaan dan Pengujian Alat K3 (Riksa Uji Alat K3)</option>
                <option value="GREENSHIP & EDGE" <?php echo $kegiatan['jenis_program'] == 'GREENSHIP & EDGE' ? 'selected' : ''; ?>>GREENSHIP & EDGE</option>
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="tanggal_mulai"><i class="fas fa-calendar-check"></i> Tanggal Mulai *</label>
                <input type="date" 
                       class="form-control" 
                       id="tanggal_mulai" 
                       name="tanggal_mulai" 
                       value="<?php echo $kegiatan['tanggal_mulai']; ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="tanggal_selesai"><i class="fas fa-calendar-times"></i> Tanggal Selesai *</label>
                <input type="date" 
                       class="form-control" 
                       id="tanggal_selesai" 
                       name="tanggal_selesai" 
                       value="<?php echo $kegiatan['tanggal_selesai']; ?>" 
                       required>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Kegiatan
            </button>
            <a href="jadwal-kegiatan.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </a>
        </div>
    </form>
</div>

<script>
function validateForm() {
    const start = document.getElementById('tanggal_mulai').value;
    const end = document.getElementById('tanggal_selesai').value;
    
    if (!start || !end) {
        alert('Tanggal mulai dan selesai harus diisi!');
        return false;
    }
    
    if (end < start) {
        alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai!');
        return false;
    }
    
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>