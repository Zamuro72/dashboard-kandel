<?php
$page_title = "Edit Foto Gallery";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

// Get ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get gallery data
$sql = "SELECT * FROM gallery WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: gallery.php");
    exit;
}

$gallery = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string(trim($_POST['title']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    
    // Check if new image uploaded and cropped
    if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image']) && strlen($_POST['cropped_image']) > 100) {
        // FIXED: Path yang benar
        $upload_dir = __DIR__ . '/uploads/gallery/';
        
        // Check folder writable
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        if (!is_writable($upload_dir)) {
            $error = "Folder uploads/gallery/ tidak dapat ditulis! Hubungi hosting untuk chmod 755.";
        } else {
            // Delete old image
            $old_image = $upload_dir . $gallery['image'];
            if (file_exists($old_image)) {
                unlink($old_image);
            }
            
            // Save new cropped image
            $cropped_data = $_POST['cropped_image'];
            $image_parts = explode(";base64,", $cropped_data);
            
            if (count($image_parts) > 1) {
                $image_base64 = base64_decode($image_parts[1]);
                
                $filename = 'gallery_' . uniqid() . '_' . time() . '.jpg';
                $filepath = $upload_dir . $filename;
                
                if (file_put_contents($filepath, $image_base64)) {
                    chmod($filepath, 0644);
                    
                    $sql = "UPDATE gallery SET 
                            title = '$title',
                            description = '$description',
                            image = '$filename'
                            WHERE id = $id";
                    
                    if ($conn->query($sql)) {
                        $success = "Foto berhasil diupdate dengan gambar baru!";
                        $result = $conn->query("SELECT * FROM gallery WHERE id = $id");
                        $gallery = $result->fetch_assoc();
                        
                        echo "<script>
                            alert('Foto berhasil diupdate dengan gambar baru!');
                            setTimeout(function() {
                                window.location.href = 'gallery.php';
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
        // Update without changing image
        $sql = "UPDATE gallery SET 
                title = '$title',
                description = '$description'
                WHERE id = $id";
        
        if ($conn->query($sql)) {
            $success = "Foto berhasil diupdate!";
            $result = $conn->query("SELECT * FROM gallery WHERE id = $id");
            $gallery = $result->fetch_assoc();
            
            echo "<script>
                alert('Foto berhasil diupdate!');
                setTimeout(function() {
                    window.location.href = 'gallery.php';
                }, 1000);
            </script>";
        } else {
            $error = "Gagal mengupdate foto: " . $conn->error;
        }
    }
}
?>

<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">

<style>
.crop-container { max-width: 100%; max-height: 500px; background: #f0f0f0; border-radius: 10px; overflow: hidden; margin: 1rem 0; }
.crop-preview { margin-top: 1rem; border: 2px solid #ddd; border-radius: 10px; overflow: hidden; max-width: 400px; }
.crop-controls { display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap; }
#imagePreview { max-width: 100%; display: block; }
.hidden { display: none; }
.current-image { max-width: 400px; border-radius: 10px; margin-bottom: 1rem; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
.alert-info { background: #d1ecf1; color: #0c5460; padding: 1rem; border-radius: 5px; border-left: 4px solid #17a2b8; margin-bottom: 1rem; }
</style>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-edit"></i> Edit Foto Gallery</h2>
        <a href="gallery.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Rekomendasi Ukuran:</strong> Gunakan gambar dengan resolusi <strong>800x600px</strong> (aspect ratio 4:3) untuk hasil terbaik.
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
                   value="<?php echo htmlspecialchars($gallery['title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description"><i class="fas fa-align-left"></i> Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($gallery['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-image"></i> Gambar Gallery</label>
            <p style="margin-bottom: 1rem; font-weight: 600;">Gambar Saat Ini:</p>
            
            <?php
            $current_image = 'uploads/gallery/' . $gallery['image'];
            if (file_exists($current_image)):
            ?>
                <img src="<?php echo $current_image; ?>" 
                     alt="Current Image" 
                     class="current-image">
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    File gambar tidak ditemukan: <code><?php echo htmlspecialchars($gallery['image']); ?></code>
                </div>
            <?php endif; ?>
            
            <div class="file-upload-box" onclick="document.getElementById('imageInput').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>Klik untuk mengganti gambar (opsional)</p>
                <small style="color: #999;">Format: JPG, PNG, GIF (Rekomendasi: <strong>800x600px</strong> - 4:3 Ratio)</small>
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
            </div>
            
            <div class="form-group" style="margin-top: 1.5rem;">
                <label><i class="fas fa-eye"></i> Preview Hasil Crop (800x600px):</label>
                <div class="crop-preview">
                    <img id="croppedPreview" src="" alt="Cropped Preview" style="width: 100%; display: block;">
                </div>
            </div>
        </div>
        
        <input type="hidden" id="croppedImage" name="cropped_image">
        
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="fas fa-save"></i> Update Foto
        </button>
    </form>
</div>

<!-- Cropper.js -->
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
                aspectRatio: 4 / 3,
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
            width: 800, height: 600,
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

document.getElementById('galleryForm').addEventListener('submit', function() {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
});
</script>

<?php include 'includes/footer.php'; ?>