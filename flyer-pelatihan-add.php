<?php
$page_title = "Tambah Flyer Pelatihan";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("=== FLYER ADD DEBUG ===");
    error_log("POST data received");
    
    $title = $conn->real_escape_string(trim($_POST['title']));
    $urutan = (int)$_POST['urutan'];
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    
    error_log("Title: " . $title);
    error_log("Urutan: " . $urutan);
    error_log("Aktif: " . $aktif);
    
    // Handle cropped image
    if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image'])) {
        $cropped_data = $_POST['cropped_image'];
        error_log("Cropped data length: " . strlen($cropped_data));
        
        // Remove data:image/png;base64, prefix
        $image_parts = explode(";base64,", $cropped_data);
        
        if (count($image_parts) > 1) {
            $image_base64 = base64_decode($image_parts[1]);
            error_log("Base64 decoded, size: " . strlen($image_base64));
            
            // Generate filename
            $filename = 'flyer_' . uniqid() . '_' . time() . '.jpg';
            
            // FIXED: Path yang benar untuk InfinityFree
            $upload_dir = __DIR__ . '/uploads/flyer/';
            $filepath = $upload_dir . $filename;
            
            error_log("Upload directory: " . $upload_dir);
            error_log("Full filepath: " . $filepath);
            
            // Check if directory exists and is writable
            if (!is_dir($upload_dir)) {
                error_log("Directory does not exist, creating...");
                if (!mkdir($upload_dir, 0755, true)) {
                    error_log("ERROR: Failed to create directory!");
                    $error = "Gagal membuat folder uploads/flyer/";
                }
            }
            
            if (!is_writable($upload_dir)) {
                error_log("ERROR: Directory is not writable!");
                error_log("Directory permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4));
                $error = "Folder uploads/flyer/ tidak dapat ditulis! Hubungi hosting untuk chmod 755.";
            } else {
                error_log("Directory writable, proceeding with save...");
                
                // Save cropped image
                if (file_put_contents($filepath, $image_base64)) {
                    error_log("Image saved successfully to: " . $filepath);
                    chmod($filepath, 0644);
                    
                    $sql = "INSERT INTO flyer_pelatihan (image, title, urutan, aktif) 
                            VALUES ('$filename', '$title', $urutan, $aktif)";
                    
                    error_log("SQL: " . $sql);
                    
                    if ($conn->query($sql)) {
                        $success = "Flyer pelatihan berhasil ditambahkan!";
                        error_log("Database insert successful!");
                        echo "<script>
                            alert('Flyer berhasil ditambahkan!');
                            setTimeout(function() {
                                window.location.href = 'flyer-pelatihan.php';
                            }, 1000);
                        </script>";
                    } else {
                        $error = "Gagal menyimpan ke database: " . $conn->error;
                        error_log("Database error: " . $conn->error);
                        // Delete uploaded file jika database insert gagal
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                    }
                } else {
                    $error = "Gagal menyimpan gambar! Error: " . error_get_last()['message'];
                    error_log("ERROR: file_put_contents failed!");
                    error_log("PHP Error: " . error_get_last()['message']);
                }
            }
        } else {
            $error = "Format gambar tidak valid!";
            error_log("ERROR: Invalid base64 format");
        }
    } else {
        $error = "Gambar harus diupload dan di-crop!";
        error_log("ERROR: No cropped image data received");
    }
    
    error_log("=== END DEBUG ===");
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">

<style>
.crop-container {
    max-width: 100%;
    max-height: 600px;
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
        <h2><i class="fas fa-plus-circle"></i> Tambah Flyer Pelatihan</h2>
        <a href="flyer-pelatihan.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Ukuran Ideal Flyer/Poster:</strong> Gunakan gambar portrait dengan resolusi <strong>720x1280px</strong> (aspect ratio 9:16 - standar portrait mobile)
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
            <label for="title"><i class="fas fa-heading"></i> Judul Flyer/Poster *</label>
            <input type="text" class="form-control" id="title" name="title" 
                   placeholder="Contoh: Authorized Gas Tester (AGT) Sertifikasi BNSP Online"
                   required>
            <small style="color: #666; display: block; margin-top: 0.5rem;">
                <i class="fas fa-lightbulb"></i> Judul akan ditampilkan di atas gambar flyer
            </small>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="urutan"><i class="fas fa-sort-numeric-down"></i> Urutan *</label>
                <input type="number" class="form-control" id="urutan" name="urutan" 
                       value="1" min="1" max="20" required>
                <small style="color: #666; display: block; margin-top: 0.5rem;">
                    Urutan tampilan flyer (1 = paling kiri)
                </small>
            </div>
            
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem;">
                    <i class="fas fa-toggle-on"></i> Status Flyer
                </label>
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="aktif" checked style="margin-right: 0.5rem; width: 20px; height: 20px;">
                    <span><i class="fas fa-eye"></i> Aktifkan Flyer</span>
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-image"></i> Upload & Crop Flyer/Poster *</label>
            
            <div class="file-upload-box" onclick="document.getElementById('imageInput').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Klik untuk upload gambar flyer</p>
                <small style="color: #999; display: block;">
                    Format: JPG, PNG, GIF<br>
                    <strong style="color: var(--primary-blue);">Ukuran Ideal: 720x1280px (Portrait 9:16 - Standar Mobile)</strong>
                </small>
                <input type="file" id="imageInput" accept="image/*" style="display: none;">
            </div>
        </div>
        
        <div id="cropperArea" class="hidden">
            <h3 style="color: var(--dark-bg); margin-bottom: 1rem;">
                <i class="fas fa-crop-alt"></i> Crop Gambar Flyer
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
                    <i class="fas fa-eye"></i> Preview Hasil Crop (720x1280px - Portrait 9:16):
                </label>
                <div class="crop-preview">
                    <img id="croppedPreview" src="" alt="Cropped Preview" style="width: 100%; display: block;">
                </div>
            </div>
        </div>
        
        <input type="hidden" id="croppedImage" name="cropped_image">
        
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                <i class="fas fa-save"></i> Simpan Flyer
            </button>
            <a href="flyer-pelatihan.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </a>
        </div>
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
const flyerForm = document.getElementById('flyerForm');

imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
        console.log('Flyer image selected:', file.name);
        
        if (cropper) {
            cropper.destroy();
        }
        
        const reader = new FileReader();
        reader.onload = function(event) {
            imagePreview.src = event.target.result;
            cropperArea.classList.remove('hidden');
            
            console.log('Initializing cropper with 9:16 ratio (720x1280)...');
            
            // ASPECT RATIO: 9:16 (720x1280 - Portrait Mobile)
            cropper = new Cropper(imagePreview, {
                aspectRatio: 9 / 16, // 9:16 portrait ratio
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
        // Output: 720x1280px (9:16 portrait)
        const canvas = cropper.getCroppedCanvas({
            width: 720,
            height: 1280,
            minWidth: 720,
            minHeight: 1280,
            maxWidth: 720,
            maxHeight: 1280,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });
        
        if (canvas) {
            const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.95);
            croppedPreview.src = croppedDataUrl;
            croppedImage.value = croppedDataUrl;
            
            console.log('✓ Flyer cropped to 720x1280px (9:16 portrait)');
        }
    }
}

flyerForm.addEventListener('submit', function(e) {
    if (!croppedImage.value) {
        e.preventDefault();
        alert('✗ Silakan upload dan crop gambar terlebih dahulu!');
        return false;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    return true;
});

console.log('✓ Flyer Form initialized');
console.log('Aspect Ratio: 9:16 (Portrait Mobile)');
console.log('Output Size: 720x1280px');
</script>

<?php include 'includes/footer.php'; ?>