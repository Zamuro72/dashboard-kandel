<?php
$page_title = "Tambah Foto Gallery";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("=== GALLERY ADD (BLOB) DEBUG ===");
    
    $title = $conn->real_escape_string(trim($_POST['title']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    
    error_log("Title: " . $title);
    
    // Handle cropped image
    if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image'])) {
        $cropped_data = $_POST['cropped_image'];
        
        // Remove data:image/jpeg;base64, prefix
        $image_parts = explode(";base64,", $cropped_data);
        
        if (count($image_parts) > 1) {
            $image_base64 = base64_decode($image_parts[1]);
            
            // Detect image type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $image_type = finfo_buffer($finfo, $image_base64);
            finfo_close($finfo);
            
            error_log("Image type: " . $image_type);
            error_log("Image size: " . strlen($image_base64) . " bytes");
            
            // Generate filename untuk reference (optional)
            $filename = 'gallery_' . uniqid() . '_' . time() . '.jpg';
            
            // Escape binary data untuk database
            $image_blob_escaped = $conn->real_escape_string($image_base64);
            
            // Insert ke database dengan BLOB
            $stmt = $conn->prepare("INSERT INTO gallery (title, description, image, image_blob, image_type, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssss", $title, $description, $filename, $image_base64, $image_type);
            
            if ($stmt->execute()) {
                $success = "Foto berhasil ditambahkan ke database!";
                error_log("✓ Database insert successful!");
                
                echo "<script>
                    alert('Foto berhasil ditambahkan!');
                    setTimeout(function() {
                        window.location.href = 'gallery.php';
                    }, 1000);
                </script>";
            } else {
                $error = "Gagal menyimpan ke database: " . $stmt->error;
                error_log("✗ Database error: " . $stmt->error);
            }
            
            $stmt->close();
        } else {
            $error = "Format gambar tidak valid!";
            error_log("✗ Invalid base64 format");
        }
    } else {
        $error = "Gambar harus diupload dan di-crop!";
        error_log("✗ No cropped image data received");
    }
    
    error_log("=== END DEBUG ===");
}
?>

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

.alert-warning {
    background: #fff3cd;
    color: #856404;
    padding: 1rem;
    border-radius: 5px;
    border-left: 4px solid #ffc107;
    margin-bottom: 1rem;
}
</style>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-plus-circle"></i> Tambah Foto Gallery (Simpan ke Database)</h2>
        <a href="gallery.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Sistem Baru:</strong> Gambar akan disimpan langsung di database (BLOB). Tidak perlu folder uploads lagi.
    </div>
    
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>Ukuran File:</strong> Maksimal 5MB per gambar. Rekomendasi: <strong>800x600px</strong> (4:3 ratio)
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
                <small style="color: #999;">Format: JPG, PNG, GIF | Max: 5MB | Rekomendasi: <strong>800x600px</strong></small>
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
                <div style="margin-top: 0.5rem; padding: 0.5rem; background: #f8f9fa; border-radius: 5px;">
                    <small><i class="fas fa-info-circle"></i> <strong>File Size:</strong> <span id="fileSize">-</span></small>
                </div>
            </div>
        </div>
        
        <!-- Hidden input untuk menyimpan cropped image -->
        <input type="hidden" id="croppedImage" name="cropped_image">
        
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class="fas fa-save"></i> Simpan ke Database
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
const galleryForm = document.getElementById('galleryForm');
const fileSizeDisplay = document.getElementById('fileSize');

imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
        // Check file size (5MB max)
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            alert('❌ File terlalu besar! Maksimal 5MB.\nUkuran file Anda: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
            imageInput.value = '';
            return;
        }
        
        console.log('✓ Image selected:', file.name, '(' + (file.size/1024/1024).toFixed(2) + 'MB)');
        
        if (cropper) {
            cropper.destroy();
        }
        
        const reader = new FileReader();
        reader.onload = function(event) {
            imagePreview.src = event.target.result;
            cropperArea.classList.remove('hidden');
            
            console.log('✓ Initializing cropper (4:3 ratio)...');
            
            cropper = new Cropper(imagePreview, {
                aspectRatio: 4 / 3,
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
                    console.log('✓ Cropper ready!');
                    updateCroppedPreview();
                    submitBtn.disabled = false;
                    cropperArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
            minWidth: 800,
            minHeight: 600,
            maxWidth: 800,
            maxHeight: 600,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });
        
        if (canvas) {
            const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
            croppedPreview.src = dataUrl;
            croppedImage.value = dataUrl;
            
            // Calculate file size
            const base64Length = dataUrl.split(',')[1].length;
            const sizeInBytes = Math.ceil(base64Length * 3 / 4);
            const sizeInKB = (sizeInBytes / 1024).toFixed(2);
            const sizeInMB = (sizeInBytes / 1024 / 1024).toFixed(2);
            
            fileSizeDisplay.textContent = sizeInKB + ' KB (' + sizeInMB + ' MB)';
            
            // Warn if too large
            if (sizeInBytes > 5 * 1024 * 1024) {
                fileSizeDisplay.style.color = '#dc3545';
                fileSizeDisplay.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + sizeInKB + ' KB - TOO LARGE!';
            } else {
                fileSizeDisplay.style.color = '#28a745';
            }
            
            console.log('✓ Cropped to 800x600px, size:', sizeInKB, 'KB');
        }
    }
}

galleryForm.addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const croppedImageData = croppedImage.value;
    
    if (!title) {
        e.preventDefault();
        alert('❌ Judul foto harus diisi!');
        return false;
    }
    
    if (!croppedImageData) {
        e.preventDefault();
        alert('❌ Silakan upload dan crop gambar terlebih dahulu!');
        return false;
    }
    
    // Check size before submit
    const base64Length = croppedImageData.split(',')[1].length;
    const sizeInBytes = Math.ceil(base64Length * 3 / 4);
    if (sizeInBytes > 5 * 1024 * 1024) {
        e.preventDefault();
        alert('❌ Gambar terlalu besar untuk disimpan! Maksimal 5MB.');
        return false;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan ke Database...';
    
    console.log('✓ Submitting to database...');
});

console.log('✓ Gallery Add (BLOB) initialized');
</script>

<?php include 'includes/footer.php'; ?>