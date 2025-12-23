<?php
$page_title = "Tambah Foto Gallery";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = clean_input($_POST['title']);
    $description = clean_input($_POST['description']);
    
    // Handle cropped image
    if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image'])) {
        // Decode base64 image
        $cropped_data = $_POST['cropped_image'];
        
        // Remove data:image/png;base64, prefix
        $image_parts = explode(";base64,", $cropped_data);
        $image_base64 = base64_decode($image_parts[1]);
        
        // Generate filename
        $filename = 'gallery_' . uniqid() . '_' . time() . '.jpg';
        $filepath = 'uploads/gallery/' . $filename;
        
        // Save cropped image
        if (file_put_contents($filepath, $image_base64)) {
            $sql = "INSERT INTO gallery (title, description, image) 
                    VALUES ('$title', '$description', '$filename')";
            
            if ($conn->query($sql)) {
                $success = "Foto berhasil ditambahkan!";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'gallery.php';
                    }, 2000);
                </script>";
            } else {
                $error = "Gagal menyimpan foto: " . $conn->error;
            }
        } else {
            $error = "Gagal menyimpan gambar!";
        }
    } else {
        $error = "Foto harus diupload dan di-crop!";
    }
}
?>

<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">

<style>
.crop-container {
    max-width: 100%;
    max-height: 500px;
    background: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    margin: 1rem 0;
}

.crop-preview {
    margin-top: 1rem;
    border: 2px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    max-width: 400px;
}

.crop-controls {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

#imagePreview {
    max-width: 100%;
    display: block;
}

.hidden {
    display: none;
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
        <h2><i class="fas fa-plus-circle"></i> Tambah Foto Gallery</h2>
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
                   placeholder="Contoh: Pelatihan Gondola" required>
        </div>
        
        <div class="form-group">
            <label for="description"><i class="fas fa-align-left"></i> Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="3" 
                      placeholder="Deskripsi singkat tentang foto (opsional)"></textarea>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-image"></i> Upload & Crop Foto *</label>
            <div class="file-upload-box" onclick="document.getElementById('imageInput').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>Klik untuk upload foto</p>
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
        
        <!-- Hidden input untuk menyimpan cropped image -->
        <input type="hidden" id="croppedImage" name="cropped_image">
        
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class="fas fa-save"></i> Simpan Foto
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
const galleryForm = document.getElementById('galleryForm');

// Handle image selection
imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
        // Destroy existing cropper
        if (cropper) {
            cropper.destroy();
        }
        
        // Create image URL
        const reader = new FileReader();
        reader.onload = function(event) {
            imagePreview.src = event.target.result;
            cropperArea.classList.remove('hidden');
            
            // Initialize Cropper dengan aspect ratio 4:3 untuk gallery
            cropper = new Cropper(imagePreview, {
                aspectRatio: 4 / 3, // 4:3 ratio untuk gallery
                viewMode: 2,
                autoCropArea: 1,
                responsive: true,
                guides: true,
                center: true,
                highlight: true,
                background: true,
                movable: true,
                rotatable: true,
                scalable: true,
                zoomable: true,
                zoomOnWheel: true,
                cropBoxResizable: true,
                cropBoxMovable: true,
                crop: updateCroppedPreview,
                cropend: updateCroppedPreview,
                zoom: updateCroppedPreview,
                ready: function() {
                    updateCroppedPreview();
                    submitBtn.disabled = false;
                }
            });
        };
        reader.readAsDataURL(file);
    }
});

// Update preview hasil crop
function updateCroppedPreview() {
    if (cropper) {
        const canvas = cropper.getCroppedCanvas({
            width: 800,
            height: 600, // 4:3 ratio = 800x600px
            minWidth: 800,
            minHeight: 600,
            maxWidth: 800,
            maxHeight: 600,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });
        
        if (canvas) {
            // Update preview
            croppedPreview.src = canvas.toDataURL('image/jpeg', 0.95);
            
            // Save cropped image data
            croppedImage.value = canvas.toDataURL('image/jpeg', 0.95);
        }
    }
}

// Validate before submit
galleryForm.addEventListener('submit', function(e) {
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