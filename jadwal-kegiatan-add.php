<?php
$page_title = "Tambah Jadwal Kegiatan";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

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
                // Insert ke database
                $stmt = $conn->prepare("INSERT INTO jadwal_kegiatan (nama_program, jenis_program, tanggal_mulai, tanggal_selesai, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssss", $nama_program, $jenis_program, $tanggal_mulai, $tanggal_selesai);
                
                if ($stmt->execute()) {
                    $success = "Jadwal kegiatan berhasil ditambahkan!";
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'jadwal-kegiatan.php';
                        }, 1500);
                    </script>";
                } else {
                    $error = "Gagal menyimpan kegiatan: " . $stmt->error;
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
        <h2><i class="fas fa-plus-circle"></i> Tambah Jadwal Kegiatan</h2>
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
                   placeholder="Contoh: Ahli K3 Umum" 
                   value="<?php echo isset($_POST['nama_program']) ? htmlspecialchars($_POST['nama_program']) : ''; ?>"
                   required>
        </div>
        
        <div class="form-group">
            <label for="jenis_program"><i class="fas fa-certificate"></i> Jenis Program *</label>
            <select class="form-control" id="jenis_program" name="jenis_program" required>
                <option value="">Pilih Jenis Program</option>
                <option value="Sertifikasi Kemnaker RI">Sertifikasi Kemnaker RI</option>
                <option value="Sertifikasi BNSP">Sertifikasi BNSP</option>
                <option value="Sertifikat Keselamatan Kebakaran (SKK)">Sertifikat Keselamatan Kebakaran (SKK)</option>
                <option value="Sertifikat Laik Operasi (SLO)">Sertifikat Laik Operasi (SLO)</option>
                <option value="Sertifikat Laik Fungsi (SLF)">Sertifikat Laik Fungsi (SLF)</option>
                <option value="Sertifikat Badan Usaha (SBU)">Sertifikat Badan Usaha (SBU)</option>
                <!-- AMDAL DIHAPUS -->
                <option value="Analisis Dampak Lalu Lintas (ANDALALIN)">Analisis Dampak Lalu Lintas (ANDALALIN)</option>
                <option value="Pemeriksaan dan Pengujian Alat K3 (Riksa Uji Alat K3)">Pemeriksaan dan Pengujian Alat K3 (Riksa Uji Alat K3)</option>
                <option value="GREENSHIP & EDGE">GREENSHIP & EDGE</option>
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="tanggal_mulai"><i class="fas fa-calendar-check"></i> Tanggal Mulai *</label>
                <input type="date" 
                       class="form-control" 
                       id="tanggal_mulai" 
                       name="tanggal_mulai" 
                       value="<?php echo isset($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : ''; ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="tanggal_selesai"><i class="fas fa-calendar-times"></i> Tanggal Selesai *</label>
                <input type="date" 
                       class="form-control" 
                       id="tanggal_selesai" 
                       name="tanggal_selesai" 
                       value="<?php echo isset($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : ''; ?>"
                       required>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Kegiatan
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