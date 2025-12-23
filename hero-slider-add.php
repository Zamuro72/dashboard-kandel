<?php
$page_title = "Tambah Hero Slide";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = clean_input($_POST['title']);
    $description = clean_input($_POST['description']);
    $urutan = (int)$_POST['urutan'];
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    
    // Handle cropped image
    if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image'])) {
        // Decode base64 image
        $cropped_data = $_POST['cropped_image'];
        
        // Remove data:image/png;base64, prefix
        $image_parts = explode(";base64,", $cropped_data);
        $image_base64 = base64_decode($image_parts[1]);
        
        // Generate filename
        $filename = 'hero_' . uniqid() . '_' . time() . '.jpg';
        $filepath = 'uploads/hero/' . $filename;
        
        // Save cropped image
        if (file_put_contents($filepath, $image_base64)) {
            $sql = "INSERT INTO hero_slider (image, title, description, urutan, aktif) 
                    VALUES ('$filename', '$title', '$description', $urutan, $aktif)";
            
            if ($conn->query($sql)) {
                $success = "Hero slide berhasil ditambahkan!";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'hero-slider.php';
                    }, 1500);
                </script>";
            } else {
                $error = "Gagal menyimpan ke database: " . $conn->error;
            }
        } else {
            $error = "Gagal menyimpan gambar!";
        }
    } else {
        $error = "Gambar harus diupload dan di-crop!";
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
    max-width: 800px;
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
        <h2><i class="fas fa-plus-circle"></i> Tambah Hero Slide</h2>
        <a href="hero-slider.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Ukuran Ideal Hero Slider:</strong> Gunakan gambar dengan resolusi <strong>1920x450px</strong> (aspect ratio 4.27:1) sesuai tinggi hero slider di website.
    </div>
    
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>Penting:</strong> Maksimal 5 slide aktif. Pastikan untuk mengatur urutan dengan benar agar slide tampil sesuai keinginan.
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
    
    <form method="POST" id="heroForm">
        <div class="form-group">
            <label for="title"><i class="fas fa-heading"></i> Judul Slide (Opsional)</label>
            <input type="text" class="form-control" id="title" name="title" 
                   placeholder="Contoh: Pelatihan K3 Terbaik Indonesia">
            <small style="color: #666; display: block; margin-top: 0.5rem;">
                <i class="fas fa-lightbulb"></i> Judul akan ditampilkan di slide (opsional)
            </small>
        </div>
        
        <div class="form-group">
            <label for="description"><i class="fas fa-align-left"></i> Deskripsi (Opsional)</label>
            <textarea class="form-control" id="description" name="description" rows="3" 
                      placeholder="Deskripsi singkat tentang slide ini..."></textarea>
            <small style="color: #666; display: block; margin-top: 0.5rem;">
                <i class="fas fa-lightbulb"></i> Deskripsi tambahan untuk keperluan internal
            </small>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="urutan"><i class="fas fa-sort-numeric-down"></i> Urutan *</label>
                <input type="number" class="form-control" id="urutan" name="urutan" 
                       value="1" min="1" max="10" required>
                <small style="color: #666; display: block; margin-top: 0.5rem;">
                    Urutan tampilan slide (1 = pertama, 2 = kedua, dst.)
                </small>
            </div>
            
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem;">
                    <i class="fas fa-toggle-on"></i> Status Slide
                </label>
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="aktif" checked style="margin-right: 0.5rem; width: 20px; height: 20px;">
                    <span><i class="fas fa-eye"></i> Aktifkan Slide</span>
                </label>
                <small style="color: #666; display: block; margin-top: 0.5rem;">
                    Centang untuk menampilkan slide di website
                </small>
            </div>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-image"></i> Upload & Crop Gambar Hero Slider *</label>
            
            <div class="file-upload-box" onclick="document.getElementById('imageInput').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Klik untuk upload gambar</p>
                <small style="color: #999; display: block;">
                    Format: JPG, PNG, GIF<br>
                    <strong style="color: var(--primary-blue);">Ukuran Ideal: 1920x450px (Aspect Ratio 4.27:1)</strong>
                </small>
                <input type="file" id="imageInput" accept="image/*" style="display: none;">
            </div>
        </div>
        
        <!-- Cropper Area -->
        <div id="cropperArea" class="hidden">
            <h3 style="color: var(--dark-bg); margin-bottom: 1rem;">
                <i class="fas fa-crop-alt"></i> Crop Gambar Hero Slider
            </h3>
            
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
                    <i class="fas fa-redo"></i> Rotate 90°
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="cropper.reset()">
                    <i class="fas fa-undo"></i> Reset
                </button>
                <button type="button" class="btn btn-warning btn-sm" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-image"></i> Ganti Gambar
                </button>
            </div>
            
            <div class="form-group" style="margin-top: 1.5rem;">
                <label style="font-weight: 600; color: var(--dark-bg);">
                    <i class="fas fa-eye"></i> Preview Hasil Crop (1920x450px - Hero Slider):
                </label>
                <div class="crop-preview">
                    <img id="croppedPreview" src="" alt="Cropped Preview" style="width: 100%; display: block;">
                </div>
                <small style="color: #666; display: block; margin-top: 0.5rem;">
                    <i class="fas fa-check-circle" style="color: var(--primary-green);"></i> 
                    Gambar akan disimpan dengan ukuran 1920x450px
                </small>
            </div>
        </div>
        
        <!-- Hidden input untuk menyimpan cropped image -->
        <input type="hidden" id="croppedImage" name="cropped_image">
        
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                <i class="fas fa-save"></i> Simpan Hero Slide
            </button>
            <a href="hero-slider.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </a>
        </div>
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
const heroForm = document.getElementById('heroForm');

// Handle image selection
imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
        console.log('Image selected for hero slider:', file.name);
        console.log('File size:', (file.size / 1024 / 1024).toFixed(2) + 'MB');
        
        // Destroy existing cropper
        if (cropper) {
            cropper.destroy();
        }
        
        // Create image URL
        const reader = new FileReader();
        reader.onload = function(event) {
            imagePreview.src = event.target.result;
            cropperArea.classList.remove('hidden');
            
            console.log('Initializing cropper with 1920x450 (4.27:1 ratio)...');
            
            // FIXED: Initialize Cropper dengan aspect ratio 1920/450 untuk hero slider
            cropper = new Cropper(imagePreview, {
                aspectRatio: 1920 / 450, // 4.266666:1 ratio - sesuai CSS hero slider
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
                    console.log('âœ" Cropper ready with 1920x450 dimensions!');
                    updateCroppedPreview();
                    submitBtn.disabled = false;
                    
                    // Scroll to cropper
                    cropperArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        };
        reader.readAsDataURL(file);
    }
});

// Update preview hasil crop
function updateCroppedPreview() {
    if (cropper) {
        // FIXED: Output 1920x450px untuk hero slider
        const canvas = cropper.getCroppedCanvas({
            width: 1920,
            height: 450,
            minWidth: 1920,
            minHeight: 450,
            maxWidth: 1920,
            maxHeight: 450,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });
        
        if (canvas) {
            // Update preview
            const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.95);
            croppedPreview.src = croppedDataUrl;
            
            // Save cropped image data
            croppedImage.value = croppedDataUrl;
            
            console.log('âœ" Hero slider image cropped to 1920x450px');
            console.log('Data size:', (croppedDataUrl.length / 1024).toFixed(2) + 'KB');
        }
    }
}

// Validate before submit
heroForm.addEventListener('submit', function(e) {
    const croppedImageData = croppedImage.value;
    
    console.log('=== FORM VALIDATION ===');
    console.log('Has cropped image:', croppedImageData ? 'YES' : 'NO');
    
    if (!croppedImageData) {
        e.preventDefault();
        alert('❌ Silakan upload dan crop gambar terlebih dahulu!');
        return false;
    }
    
    console.log('âœ" Validation passed, submitting form...');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan Hero Slide...';
    
    return true;
});

// Prevent accidental form leave
window.addEventListener('beforeunload', function(e) {
    if (croppedImage.value && !heroForm.submitted) {
        e.preventDefault();
        e.returnValue = '';
        return 'Anda memiliki gambar yang belum disimpan. Yakin ingin meninggalkan halaman?';
    }
});

heroForm.addEventListener('submit', function() {
    heroForm.submitted = true;
});

console.log('âœ" Hero Slider Add Form initialized');
console.log('Aspect Ratio: 1920x450 (4.27:1)');
</script>

<?php include 'includes/footer.php'; ?>