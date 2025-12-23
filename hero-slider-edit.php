<?php
$page_title = "Edit Hero Slide";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

// Get slide ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get slide data
$sql = "SELECT * FROM hero_slider WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: hero-slider.php");
    exit;
}

$slide = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = clean_input($_POST['title']);
    $description = clean_input($_POST['description']);
    $urutan = (int)$_POST['urutan'];
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    
    // Check if new image uploaded and cropped
    if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image']) && strlen($_POST['cropped_image']) > 100) {
        // Delete old image
        $old_image = 'uploads/hero/' . $slide['image'];
        if (file_exists($old_image)) {
            unlink($old_image);
        }
        
        // Save new cropped image
        $cropped_data = $_POST['cropped_image'];
        $image_parts = explode(";base64,", $cropped_data);
        
        if (count($image_parts) > 1) {
            $image_base64 = base64_decode($image_parts[1]);
            
            $filename = 'hero_' . uniqid() . '_' . time() . '.jpg';
            $filepath = 'uploads/hero/' . $filename;
            
            if (file_put_contents($filepath, $image_base64)) {
                $sql = "UPDATE hero_slider SET 
                        image = '$filename',
                        title = '$title',
                        description = '$description',
                        urutan = $urutan,
                        aktif = $aktif
                        WHERE id = $id";
                
                if ($conn->query($sql)) {
                    $success = "Hero slide berhasil diupdate dengan gambar baru!";
                    // Refresh data
                    $result = $conn->query("SELECT * FROM hero_slider WHERE id = $id");
                    $slide = $result->fetch_assoc();
                    
                    echo "<script>
                        alert('Hero slide berhasil diupdate dengan gambar baru!');
                        setTimeout(function() {
                            window.location.href = 'hero-slider.php';
                        }, 1000);
                    </script>";
                } else {
                    $error = "Gagal mengupdate slide: " . $conn->error;
                }
            } else {
                $error = "Gagal menyimpan gambar!";
            }
        } else {
            $error = "Format gambar tidak valid!";
        }
    } else {
        // Update without changing image
        $sql = "UPDATE hero_slider SET 
                title = '$title',
                description = '$description',
                urutan = $urutan,
                aktif = $aktif
                WHERE id = $id";
        
        if ($conn->query($sql)) {
            $success = "Hero slide berhasil diupdate!";
            // Refresh data
            $result = $conn->query("SELECT * FROM hero_slider WHERE id = $id");
            $slide = $result->fetch_assoc();
            
            echo "<script>
                alert('Hero slide berhasil diupdate!');
                setTimeout(function() {
                    window.location.href = 'hero-slider.php';
                }, 1000);
            </script>";
        } else {
            $error = "Gagal mengupdate slide: " . $conn->error;
        }
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

.current-image {
    max-width: 100%;
    border-radius: 10px;
    margin-bottom: 1rem;
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
        <h2><i class="fas fa-edit"></i> Edit Hero Slide</h2>
        <a href="hero-slider.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Ukuran Ideal Hero Slider:</strong> Gunakan gambar dengan resolusi <strong>1920x450px</strong> (aspect ratio 4.27:1) sesuai tinggi hero slider di website.
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
                   value="<?php echo htmlspecialchars($slide['title']); ?>">
        </div>
        
        <div class="form-group">
            <label for="description"><i class="fas fa-align-left"></i> Deskripsi (Opsional)</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($slide['description']); ?></textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="urutan"><i class="fas fa-sort-numeric-down"></i> Urutan *</label>
                <input type="number" class="form-control" id="urutan" name="urutan" 
                       value="<?php echo $slide['urutan']; ?>" min="1" max="10" required>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="aktif" <?php echo $slide['aktif'] ? 'checked' : ''; ?> style="margin-right: 0.5rem;">
                    <i class="fas fa-eye"></i> Aktifkan Slide
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-image"></i> Gambar Hero Slide</label>
            <p style="margin-bottom: 1rem; font-weight: 600;">Gambar Saat Ini:</p>
            <img src="uploads/hero/<?php echo $slide['image']; ?>" 
                 alt="Current Slide" 
                 class="current-image"
                 style="max-width: 800px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            
            <div class="file-upload-box" onclick="document.getElementById('imageInput').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>Klik untuk mengganti gambar (opsional)</p>
                <small style="color: #999;">Format: JPG, PNG | Ukuran Ideal: <strong>1920x450px</strong> (Aspect Ratio 4.27:1)</small>
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
                <label><i class="fas fa-eye"></i> Preview Hasil Crop (1920x450px - Hero Slider):</label>
                <div class="crop-preview">
                    <img id="croppedPreview" src="" alt="Cropped Preview" style="width: 100%; display: block;">
                </div>
            </div>
        </div>
        
        <input type="hidden" id="croppedImage" name="cropped_image">
        
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="fas fa-save"></i> Update Slide
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
const heroForm = document.getElementById('heroForm');

imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
        console.log('Image selected for hero slider:', file.name);
        
        if (cropper) {
            cropper.destroy();
        }
        
        const reader = new FileReader();
        reader.onload = function(event) {
            imagePreview.src = event.target.result;
            cropperArea.classList.remove('hidden');
            
            console.log('Initializing cropper with 4.27:1 ratio (1920x450)...');
            
            // FIXED: Aspect ratio 4.266666 (1920/450) untuk hero slider
            cropper = new Cropper(imagePreview, {
                aspectRatio: 1920 / 450, // 4.266666:1 ratio untuk hero slider
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
                    console.log('Cropper ready with 1920x450 dimensions!');
                    updateCroppedPreview();
                }
            });
        };
        reader.readAsDataURL(file);
    }
});

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
            const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.95);
            croppedPreview.src = croppedDataUrl;
            croppedImage.value = croppedDataUrl;
            
            console.log('Hero slider image cropped to 1920x450px, data length:', croppedDataUrl.length);
        }
    }
}

heroForm.addEventListener('submit', function(e) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
});
</script>

<?php include 'includes/footer.php'; ?>