<?php
$page_title = "Tambah Foto Gallery";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

// Get max urutan
$max_urutan_result = $conn->query("SELECT MAX(urutan) as max_urutan FROM gallery");
$max_urutan = 1;
if ($max_urutan_result && $row = $max_urutan_result->fetch_assoc()) {
    $max_urutan = ($row['max_urutan'] ?? 0) + 1;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string(trim($_POST['title']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    $urutan = isset($_POST['urutan']) ? (int)$_POST['urutan'] : $max_urutan;
    
    // Handle cropped image
    if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image'])) {
        $cropped_data = $_POST['cropped_image'];
        $image_parts = explode(";base64,", $cropped_data);
        
        if (count($image_parts) > 1) {
            $image_base64 = base64_decode($image_parts[1]);
            
            // Generate filename
            $filename = 'gallery_' . uniqid() . '_' . time() . '.jpg';
            
            // FIXED: Path yang benar
            $upload_dir = __DIR__ . '/uploads/gallery/';
            $filepath = $upload_dir . $filename;
            
            // Create folder if not exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Save image
            if (file_put_contents($filepath, $image_base64)) {
                chmod($filepath, 0644);
                
                // Insert to database (tanpa BLOB)
                $stmt = $conn->prepare("INSERT INTO gallery (title, description, urutan, image, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssis", $title, $description, $urutan, $filename);
                
                if ($stmt->execute()) {
                    $success = "Foto berhasil ditambahkan!";
                    echo "<script>
                        alert('Foto berhasil ditambahkan!');
                        setTimeout(function() {
                            window.location.href = 'gallery.php';
                        }, 1000);
                    </script>";
                } else {
                    $error = "Gagal menyimpan ke database: " . $stmt->error;
                    // Delete file if database insert failed
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                }
                
                $stmt->close();
            } else {
                $error = "Gagal menyimpan gambar! Cek permission folder uploads/gallery/";
            }
        } else {
            $error = "Format gambar tidak valid!";
        }
    } else {
        $error = "Gambar harus diupload dan di-crop!";
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">

<style>
.crop-container { max-width: 100%; max-height: 500px; background: #f0f0f0; border-radius: 10px; overflow: hidden; margin: 1rem 0; }
.crop-preview { margin-top: 1rem; border: 2px solid #ddd; border-radius: 10px; overflow: hidden; max-width: 400px; }
.crop-controls { display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap; }
#imagePreview { max-width: 100%; display: block; }
.hidden { display: none; }
.alert-info { background: #d1ecf1; color: #0c5460; padding: 1rem; border-radius: 5px; border-left: 4px solid #17a2b8; margin-bottom: 1rem; }
</style>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-plus-circle"></i> Tambah Foto Gallery</h2>
        <a href="gallery.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Rekomendasi:</strong> Upload gambar <strong>800x600px</strong> (4:3 ratio) untuk hasil terbaik.
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
    
    <form method="POST" id="galleryForm">
        <div class="form-group">
            <label for="title"><i class="fas fa-heading"></i> Judul Foto *</label>
            <input type="text" class="form-control" id="title" name="title" 
                   placeholder="Contoh: Pelatihan K3 Gondola" required>
        </div>
        
        <div class="form-group">
            <label for="description"><i class="fas fa-align-left"></i> Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="3" 
                      placeholder="Deskripsi singkat tentang foto (opsional)"></textarea>
        </div>
        
        <div class="form-group">
            <label for="urutan"><i class="fas fa-sort-numeric-down"></i> Urutan Tampilan *</label>
            <input type="number" class="form-control" id="urutan" name="urutan" 
                   value="<?php echo $max_urutan; ?>" 
                   min="1" max="999" required>
            <small style="color: #666; display: block; margin-top: 0.5rem;">
                <i class="fas fa-info-circle"></i> Urutan 1 = tampil paling atas. Default: <?php echo $max_urutan; ?>
            </small>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-image"></i> Upload & Crop Foto *</label>
            <div class="file-upload-box" onclick="document.getElementById('imageInput').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>Klik untuk upload foto</p>
                <small style="color: #999;">Format: JPG, PNG, GIF | Rekomendasi: <strong>800x600px</strong></small>
                <input type="file" id="imageInput" accept="image/*" style="display: none;">
            </div>
        </div>
        
        <!-- Cropper Area -->
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
                <button type="button" class="btn btn-warning btn-sm" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-image"></i> Ganti Gambar
                </button>
            </div>
            
            <div class="form-group" style="margin-top: 1.5rem;">
                <label><i class="fas fa-eye"></i> Preview Hasil Crop (800x600px):</label>
                <div class="crop-preview">
                    <img id="croppedPreview" src="" alt="Cropped Preview" style="width: 100%; display: block;">
                </div>
            </div>
        </div>
        
        <input type="hidden" id="croppedImage" name="cropped_image">
        
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class="fas fa-save"></i> Simpan Foto
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
                aspectRatio: 4 / 3, // 800x600
                viewMode: 2,
                autoCropArea: 1,
                responsive: true,
                crop: updateCroppedPreview,
                ready: function() {
                    updateCroppedPreview();
                    submitBtn.disabled = false;
                }
            });
        };
        reader.readAsDataURL(file);
    }
});

function updateCroppedPreview() {
    if (cropper) {
        const canvas = cropper.getCroppedCanvas({
            width: 800,
            height: 600,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });
        
        if (canvas) {
            croppedPreview.src = canvas.toDataURL('image/jpeg', 0.90);
            croppedImage.value = canvas.toDataURL('image/jpeg', 0.90);
        }
    }
}

document.getElementById('galleryForm').addEventListener('submit', function(e) {
    if (!croppedImage.value) {
        e.preventDefault();
        alert('Silakan upload dan crop gambar terlebih dahulu!');
        return false;
    }
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
});
</script>

<?php include 'includes/footer.php'; ?>