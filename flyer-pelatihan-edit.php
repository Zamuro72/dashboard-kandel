<?php
$page_title = "Edit Flyer Pelatihan";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT * FROM flyer_pelatihan WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: flyer-pelatihan.php");
    exit;
}

$flyer = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string(trim($_POST['title']));
    $urutan = (int)$_POST['urutan'];
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    
    if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image']) && strlen($_POST['cropped_image']) > 100) {
        // FIXED: Path yang benar
        $upload_dir = __DIR__ . '/uploads/flyer/';
        
        // Check folder writable
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        if (!is_writable($upload_dir)) {
            $error = "Folder uploads/flyer/ tidak dapat ditulis! Hubungi hosting untuk chmod 755.";
        } else {
            $old_image = $upload_dir . $flyer['image'];
            if (file_exists($old_image)) {
                unlink($old_image);
            }
            
            $cropped_data = $_POST['cropped_image'];
            $image_parts = explode(";base64,", $cropped_data);
            
            if (count($image_parts) > 1) {
                $image_base64 = base64_decode($image_parts[1]);
                
                $filename = 'flyer_' . uniqid() . '_' . time() . '.jpg';
                $filepath = $upload_dir . $filename;
                
                if (file_put_contents($filepath, $image_base64)) {
                    chmod($filepath, 0644);
                    
                    $sql = "UPDATE flyer_pelatihan SET 
                            image = '$filename',
                            title = '$title',
                            urutan = $urutan,
                            aktif = $aktif
                            WHERE id = $id";
                    
                    if ($conn->query($sql)) {
                        $success = "Flyer berhasil diupdate dengan gambar baru!";
                        $result = $conn->query("SELECT * FROM flyer_pelatihan WHERE id = $id");
                        $flyer = $result->fetch_assoc();
                        
                        echo "<script>
                            alert('Flyer berhasil diupdate!');
                            setTimeout(function() {
                                window.location.href = 'flyer-pelatihan.php';
                            }, 1000);
                        </script>";
                    } else {
                        $error = "Gagal mengupdate database: " . $conn->error;
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                    }
                } else {
                    $error = "Gagal menyimpan gambar! Error: " . error_get_last()['message'];
                }
            } else {
                $error = "Format gambar tidak valid!";
            }
        }
    } else {
        $sql = "UPDATE flyer_pelatihan SET 
                title = '$title',
                urutan = $urutan,
                aktif = $aktif
                WHERE id = $id";
        
        if ($conn->query($sql)) {
            $success = "Flyer berhasil diupdate!";
            $result = $conn->query("SELECT * FROM flyer_pelatihan WHERE id = $id");
            $flyer = $result->fetch_assoc();
            
            echo "<script>
                alert('Flyer berhasil diupdate!');
                setTimeout(function() {
                    window.location.href = 'flyer-pelatihan.php';
                }, 1000);
            </script>";
        } else {
            $error = "Gagal mengupdate database: " . $conn->error;
        }
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">

<style>
.crop-container { max-width: 100%; max-height: 600px; background: #f0f0f0; border-radius: 10px; overflow: hidden; margin: 1rem 0; }
.crop-preview { margin-top: 1rem; border: 2px solid #ddd; border-radius: 10px; overflow: hidden; max-width: 400px; }
.crop-controls { display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap; }
#imagePreview { max-width: 100%; display: block; }
.hidden { display: none; }
.alert-info { background: #d1ecf1; color: #0c5460; padding: 1rem; border-radius: 5px; border-left: 4px solid #17a2b8; margin-bottom: 1rem; }
</style>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-edit"></i> Edit Flyer Pelatihan</h2>
        <a href="flyer-pelatihan.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Ukuran Ideal:</strong> 720x1280px (Portrait 9:16 - Ratio Mobile Standar)
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
    
    <form method="POST" id="flyerForm">
        <div class="form-group">
            <label for="title"><i class="fas fa-heading"></i> Judul Flyer *</label>
            <input type="text" class="form-control" id="title" name="title" 
                   value="<?php echo htmlspecialchars($flyer['title']); ?>" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="urutan"><i class="fas fa-sort-numeric-down"></i> Urutan *</label>
                <input type="number" class="form-control" id="urutan" name="urutan" 
                       value="<?php echo $flyer['urutan']; ?>" min="1" max="20" required>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="aktif" <?php echo $flyer['aktif'] ? 'checked' : ''; ?> style="margin-right: 0.5rem;">
                    <i class="fas fa-eye"></i> Aktifkan Flyer
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-image"></i> Gambar Flyer</label>
            <p style="margin-bottom: 1rem; font-weight: 600;">Gambar Saat Ini:</p>
            <img src="uploads/flyer/<?php echo htmlspecialchars($flyer['image']); ?>" 
                 alt="Current Flyer" 
                 style="max-width: 400px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 1rem;">
            
            <div class="file-upload-box" onclick="document.getElementById('imageInput').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>Klik untuk mengganti gambar (opsional)</p>
                <small style="color: #999;">Format: JPG, PNG | Ukuran Ideal: 720x1280px (Portrait 9:16)</small>
                <input type="file" id="imageInput" accept="image/*" style="display: none;">
            </div>
        </div>
        
        <div id="cropperArea" class="hidden">
            <div class="crop-container">
                <img id="imagePreview" src="" alt="Preview">
            </div>
            
            <div class="crop-controls">
                <button type="button" class="btn btn-secondary btn-sm" onclick="cropper.zoom(0.1)">
                    <i class="fas fa-search-plus"></i> Zoom In
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="cropper.zoom(-0.1)">
                    <i class="fas fa-search-minus"></i> Zoom Out
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="cropper.rotate(90)">
                    <i class="fas fa-redo"></i> Rotate
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="cropper.reset()">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
            
            <div class="form-group" style="margin-top: 1.5rem;">
                <label><i class="fas fa-eye"></i> Preview Hasil Crop:</label>
                <div class="crop-preview">
                    <img id="croppedPreview" src="" alt="Cropped Preview" style="width: 100%; display: block;">
                </div>
            </div>
        </div>
        
        <input type="hidden" id="croppedImage" name="cropped_image">
        
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="fas fa-save"></i> Update Flyer
        </button>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
let cropper;
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');
const cropperArea = document.getElementById('cropperArea');
const croppedImage = document.getElementById('croppedImage');
const croppedPreview = document.getElementById('croppedPreview');
const submitBtn = document.getElementById('submitBtn');

imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (cropper) cropper.destroy();
        
        const reader = new FileReader();
        reader.onload = function(event) {
            imagePreview.src = event.target.result;
            cropperArea.classList.remove('hidden');
            
            cropper = new Cropper(imagePreview, {
                aspectRatio: 9 / 16,
                viewMode: 2,
                autoCropArea: 1,
                responsive: true,
                crop: updateCroppedPreview,
                ready: updateCroppedPreview
            });
        };
        reader.readAsDataURL(file);
    }
});

function updateCroppedPreview() {
    if (cropper) {
        const canvas = cropper.getCroppedCanvas({
            width: 720, 
            height: 1280,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });
        if (canvas) {
            croppedPreview.src = canvas.toDataURL('image/jpeg', 0.95);
            croppedImage.value = canvas.toDataURL('image/jpeg', 0.95);
        }
    }
}

document.getElementById('flyerForm').addEventListener('submit', function() {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
});
</script>

<?php include 'includes/footer.php'; ?>