<?php
$page_title = "Edit Artikel";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

// Get artikel ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get artikel data
$sql = "SELECT * FROM artikel WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: artikel.php");
    exit;
}

$artikel = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CRITICAL FIX: Pastikan content di-capture dengan benar
    error_log("=== EDIT ARTIKEL DEBUG ===");
    error_log("POST Data: " . print_r($_POST, true));
    error_log("Content from POST: " . (isset($_POST['content']) ? substr($_POST['content'], 0, 100) : 'NOT SET'));
    
    // Escape input untuk keamanan
    $title = $conn->real_escape_string(trim($_POST['title']));
    $date = $conn->real_escape_string(trim($_POST['date']));
    $author = $conn->real_escape_string(trim($_POST['author']));
    $category = $conn->real_escape_string(trim($_POST['category']));
    
    // CRITICAL FIX: Pastikan content tidak di-escape berlebihan
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    
    // Validasi content tidak kosong
    if (empty(trim(strip_tags($content)))) {
        $error = "Konten artikel tidak boleh kosong!";
        error_log("ERROR: Content is empty");
    } else {
        // Escape content untuk database
        $content_escaped = $conn->real_escape_string($content);
        
        // Generate excerpt otomatis dari content
        $excerpt = $conn->real_escape_string(substr(strip_tags($content), 0, 200));
        
        error_log("Content length before save: " . strlen($content));
        error_log("Content preview: " . substr($content, 0, 200));
        
        // Check if new cropped image uploaded
        if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image']) && strlen($_POST['cropped_image']) > 100) {
            error_log("Cropped image detected, length: " . strlen($_POST['cropped_image']));
            
            // Delete old image
            $old_image = 'uploads/artikel/' . $artikel['image'];
            if (file_exists($old_image)) {
                unlink($old_image);
                error_log("Old image deleted: " . $old_image);
            }
            
            // Decode base64 image
            $cropped_data = $_POST['cropped_image'];
            
            // Remove data:image/png;base64, prefix
            $image_parts = explode(";base64,", $cropped_data);
            if (count($image_parts) > 1) {
                $image_base64 = base64_decode($image_parts[1]);
                
                // Generate filename
                $filename = 'artikel_' . uniqid() . '_' . time() . '.jpg';
                $filepath = 'uploads/artikel/' . $filename;
                
                // Save cropped image
                if (file_put_contents($filepath, $image_base64)) {
                    error_log("New image saved: " . $filepath);
                    $image = $conn->real_escape_string($filename);
                    
                    // Update dengan gambar baru
                    $update_sql = "UPDATE artikel SET 
                                   title = '$title',
                                   date = '$date',
                                   author = '$author',
                                   category = '$category',
                                   content = '$content_escaped',
                                   excerpt = '$excerpt',
                                   image = '$image'
                                   WHERE id = $id";
                    
                    error_log("SQL with image: " . $update_sql);
                    
                    if ($conn->query($update_sql)) {
                        $success = "Artikel berhasil diupdate dengan gambar baru!";
                        error_log("Article updated successfully with new image");
                        
                        // Refresh data setelah update
                        $result = $conn->query("SELECT * FROM artikel WHERE id = $id");
                        $artikel = $result->fetch_assoc();
                        
                        echo "<script>
                            alert('Artikel berhasil diupdate dengan gambar baru!');
                            setTimeout(function() {
                                window.location.href = 'artikel.php';
                            }, 1000);
                        </script>";
                    } else {
                        $error = "Gagal mengupdate artikel: " . $conn->error;
                        error_log("SQL Error: " . $conn->error);
                    }
                } else {
                    $error = "Gagal menyimpan gambar!";
                    error_log("Failed to save image file");
                }
            } else {
                $error = "Format gambar tidak valid!";
                error_log("Invalid image format");
            }
        } else {
            // CRITICAL FIX: Update tanpa mengubah gambar
            error_log("Updating without image change");
            
            $update_sql = "UPDATE artikel SET 
                           title = '$title',
                           date = '$date',
                           author = '$author',
                           category = '$category',
                           content = '$content_escaped',
                           excerpt = '$excerpt'
                           WHERE id = $id";
            
            error_log("SQL without image: " . substr($update_sql, 0, 200));
            
            if ($conn->query($update_sql)) {
                $success = "Artikel berhasil diupdate!";
                error_log("Article updated successfully without image change");
                
                // Refresh data setelah update
                $result = $conn->query("SELECT * FROM artikel WHERE id = $id");
                $artikel = $result->fetch_assoc();
                
                error_log("Content after refresh from DB: " . substr($artikel['content'], 0, 200));
                
                echo "<script>
                    alert('Artikel berhasil diupdate!');
                    setTimeout(function() {
                        window.location.href = 'artikel.php';
                    }, 1000);
                </script>";
            } else {
                $error = "Gagal mengupdate artikel: " . $conn->error;
                error_log("SQL Error: " . $conn->error);
            }
        }
    }
}
?>

<!-- Include Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">

<style>
    .ql-container {
        min-height: 400px;
        font-size: 16px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .ql-editor {
        min-height: 400px;
        line-height: 1.8;
    }
    
    .ql-toolbar {
        background: #f8f9fa;
        border-radius: 10px 10px 0 0 !important;
        border: 1px solid #ddd !important;
    }
    
    .ql-container {
        border-radius: 0 0 10px 10px !important;
        border: 1px solid #ddd !important;
        border-top: none !important;
    }
    
    .ql-snow .ql-picker {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .ql-editor p {
        margin-bottom: 1rem;
    }
    
    .ql-editor h2 {
        font-size: 2rem;
        font-weight: bold;
        margin: 1.5rem 0 1rem;
        color: #333;
    }
    
    .ql-editor h3 {
        font-size: 1.6rem;
        font-weight: bold;
        margin: 1.2rem 0 0.8rem;
        color: #444;
    }
    
    .ql-editor h4 {
        font-size: 1.3rem;
        font-weight: bold;
        margin: 1rem 0 0.6rem;
        color: #555;
    }
    
    .ql-editor ul, .ql-editor ol {
        margin: 1rem 0;
        padding-left: 2rem;
    }
    
    .ql-editor li {
        margin-bottom: 0.5rem;
    }

    /* Cropper Styles */
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
        <h2><i class="fas fa-edit"></i> Edit Artikel</h2>
        <a href="artikel.php" class="btn btn-secondary">
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
    
    <form method="POST" enctype="multipart/form-data" id="artikelForm">
        <div class="form-group">
            <label for="title"><i class="fas fa-heading"></i> Judul Artikel *</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($artikel['title'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="date"><i class="fas fa-calendar"></i> Tanggal *</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo $artikel['date']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="author"><i class="fas fa-user"></i> Penulis *</label>
                <input type="text" class="form-control" id="author" name="author" value="<?php echo htmlspecialchars($artikel['author'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="category"><i class="fas fa-tag"></i> Kategori *</label>
            <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($artikel['category'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="content"><i class="fas fa-file-alt"></i> Konten Artikel *</label>
            <div id="editor-container"></div>
            <textarea name="content" id="content" style="display:none;"></textarea>
            <small style="color: #666; display: block; margin-top: 0.5rem;">
                <i class="fas fa-info-circle"></i> Gunakan toolbar di atas untuk memformat teks Anda
            </small>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-image"></i> Gambar Artikel</label>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Rekomendasi Ukuran:</strong> Gunakan gambar dengan resolusi <strong>1200x800px</strong> (aspect ratio 3:2) untuk hasil terbaik.
            </div>
            
            <div class="image-preview" style="margin-bottom: 1rem;">
                <p style="margin-bottom: 0.5rem; font-weight: 600;">Gambar Saat Ini:</p>
                <img src="uploads/artikel/<?php echo $artikel['image']; ?>" alt="Current Image" style="max-width: 300px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            </div>
            
            <div class="file-upload-box" onclick="document.getElementById('imageInput').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>Klik untuk mengganti gambar (opsional)</p>
                <small style="color: #999;">Format: JPG, JPEG, PNG, GIF (Rekomendasi: <strong>1200x800px</strong> - 3:2 Ratio)</small>
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
                <label><i class="fas fa-eye"></i> Preview Hasil Crop (1200x800px):</label>
                <div class="crop-preview">
                    <img id="croppedPreview" src="" alt="Cropped Preview" style="width: 100%; display: block;">
                </div>
            </div>
        </div>
        
        <!-- Hidden input untuk menyimpan cropped image -->
        <input type="hidden" id="croppedImage" name="cropped_image">
        
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="fas fa-save"></i> Update Artikel
        </button>
    </form>
</div>

<!-- Include Quill JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
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

// Handle image selection
imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
        console.log('Image selected:', file.name);
        
        // Destroy existing cropper
        if (cropper) {
            cropper.destroy();
        }
        
        // Create image URL
        const reader = new FileReader();
        reader.onload = function(event) {
            imagePreview.src = event.target.result;
            cropperArea.classList.remove('hidden');
            
            console.log('Initializing cropper...');
            
            // Initialize Cropper dengan aspect ratio 3:2 (untuk artikel)
            cropper = new Cropper(imagePreview, {
                aspectRatio: 3 / 2,
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
                    console.log('Cropper ready!');
                    updateCroppedPreview();
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
            width: 1200,
            height: 800,
            minWidth: 1200,
            minHeight: 800,
            maxWidth: 1200,
            maxHeight: 1200,
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
            
            console.log('Cropped image updated, data length:', croppedDataUrl.length);
        }
    }
}

// Initialize Quill editor
var quill = new Quill('#editor-container', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [2, 3, 4, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'align': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            ['blockquote', 'code-block'],
            ['link'],
            ['clean']
        ]
    },
    placeholder: 'Tulis konten artikel di sini...'
});

// CRITICAL FIX: Load konten yang sudah ada dengan benar
var existingContent = <?php echo json_encode($artikel['content'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
console.log('=== LOADING EXISTING CONTENT ===');
console.log('Content length:', existingContent.length);
console.log('Content preview:', existingContent.substring(0, 200));

// Set konten ke Quill editor
quill.root.innerHTML = existingContent;

console.log('Content loaded into Quill');
console.log('Quill delta:', JSON.stringify(quill.getContents()).substring(0, 200));

// CRITICAL FIX: Submit form dengan handling yang benar
document.getElementById('artikelForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default submit
    
    console.log('=== FORM SUBMIT TRIGGERED ===');
    
    // CRITICAL: Get content dari Quill
    var content = quill.root.innerHTML;
    var textContent = quill.getText().trim();
    var croppedImageData = croppedImage.value;
    
    console.log('Content dari Quill:');
    console.log('- HTML length:', content.length);
    console.log('- Text length:', textContent.length);
    console.log('- Preview:', content.substring(0, 300));
    console.log('- Has cropped image:', croppedImageData ? 'YES (' + croppedImageData.length + ' chars)' : 'NO');
    console.log('- Cropper area visible:', !cropperArea.classList.contains('hidden'));
    
    // Validasi konten
    if (textContent.length < 10) {
        alert('Konten artikel terlalu pendek! Minimal 10 karakter.');
        return false;
    }
    
    // CRITICAL: Set content ke textarea hidden SEBELUM validasi apapun
    document.getElementById('content').value = content;
    console.log('✓ Content set to hidden textarea');
    console.log('✓ Textarea value length:', document.getElementById('content').value.length);
    
    // Validasi gambar HANYA jika cropper area terlihat (user upload gambar baru)
    if (!cropperArea.classList.contains('hidden')) {
        if (!croppedImageData || croppedImageData.length < 100) {
            alert('Silakan tunggu hingga gambar selesai di-crop, lalu klik Update Artikel lagi!');
            return false;
        }
        console.log('✓ New image will be uploaded');
    } else {
        console.log('✓ No new image, keeping existing image');
    }
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    
    console.log('=== SUBMITTING FORM NOW ===');
    
    // Submit form
    this.submit();
});

// Track Quill changes untuk debugging
quill.on('text-change', function(delta, oldDelta, source) {
    console.log('Quill content changed, new length:', quill.root.innerHTML.length);
});

// Auto-save draft setiap 30 detik
setInterval(function() {
    var content = quill.root.innerHTML;
    if (content.trim() !== '<p><br></p>' && content.trim() !== '') {
        localStorage.setItem('artikel_draft_<?php echo $id; ?>', content);
        console.log('Draft auto-saved, length:', content.length);
    }
}, 30000);

// Load draft on page load
window.addEventListener('load', function() {
    console.log('Page loaded, checking for draft...');
    
    var draft = localStorage.getItem('artikel_draft_<?php echo $id; ?>');
    if (draft && draft !== existingContent && draft.trim() !== '<p><br></p>') {
        if (confirm('Ditemukan draft yang belum disimpan. Muat draft tersebut?')) {
            quill.root.innerHTML = draft;
            console.log('Draft loaded, length:', draft.length);
        }
    }
});

// Clear draft after successful save
<?php if ($success): ?>
localStorage.removeItem('artikel_draft_<?php echo $id; ?>');
console.log('✓ Draft cleared after successful save');
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>