<?php
$page_title = "Tambah Artikel";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug: Tampilkan data POST
    error_log("POST Data: " . print_r($_POST, true));
    
    // Escape input untuk keamanan
    $title = $conn->real_escape_string(trim($_POST['title']));
    $date = $conn->real_escape_string(trim($_POST['date']));
    $author = $conn->real_escape_string(trim($_POST['author']));
    $category = $conn->real_escape_string(trim($_POST['category']));
    $content = $conn->real_escape_string($_POST['content']);
    
    // Handle cropped image
    if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image'])) {
        // Decode base64 image
        $cropped_data = $_POST['cropped_image'];
        
        // Remove data:image/png;base64, prefix
        $image_parts = explode(";base64,", $cropped_data);
        $image_base64 = base64_decode($image_parts[1]);
        
        // Generate filename
        $filename = 'artikel_' . uniqid() . '_' . time() . '.jpg';
        $filepath = 'uploads/artikel/' . $filename;
        
        // Save cropped image
        if (file_put_contents($filepath, $image_base64)) {
            $image = $conn->real_escape_string($filename);
            
            // Generate excerpt otomatis dari content
            $excerpt = $conn->real_escape_string(substr(strip_tags($content), 0, 200));
            
            // Gunakan prepared statement
            $stmt = $conn->prepare("INSERT INTO artikel (title, date, author, category, image, excerpt, content) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $title, $date, $author, $category, $image, $excerpt, $content);
            
            if ($stmt->execute()) {
                $success = "Artikel berhasil ditambahkan!";
                echo "<script>
                    localStorage.removeItem('artikel_draft_new');
                    setTimeout(function() {
                        window.location.href = 'artikel.php';
                    }, 1500);
                </script>";
            } else {
                $error = "Gagal menyimpan artikel: " . $stmt->error;
                error_log("SQL Error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $error = "Gagal menyimpan gambar!";
        }
    } else {
        $error = "Gambar harus diupload dan di-crop!";
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
        <h2><i class="fas fa-plus-circle"></i> Tambah Artikel Baru</h2>
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
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="date"><i class="fas fa-calendar"></i> Tanggal *</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="author"><i class="fas fa-user"></i> Penulis *</label>
                <input type="text" class="form-control" id="author" name="author" value="Kandel Sekeco" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="category"><i class="fas fa-tag"></i> Kategori *</label>
            <input type="text" class="form-control" id="category" name="category" value="Safety K3" required>
        </div>
        
        <div class="form-group">
            <label for="content"><i class="fas fa-file-alt"></i> Konten Artikel *</label>
            <div id="editor-container"></div>
            <textarea name="content" id="content" style="display:none;"></textarea>
            <small style="color: #666; display: block; margin-top: 0.5rem;">
                <i class="fas fa-info-circle"></i> Gunakan toolbar di atas untuk memformat teks Anda. Ringkasan akan dibuat otomatis dari konten.
            </small>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-image"></i> Upload & Crop Gambar Artikel *</label>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Rekomendasi Ukuran:</strong> Gunakan gambar dengan resolusi <strong>1200x800px</strong> (aspect ratio 3:2) untuk hasil terbaik.
            </div>
            
            <div class="file-upload-box" onclick="document.getElementById('imageInput').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>Klik untuk upload gambar</p>
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
        
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class="fas fa-save"></i> Simpan Artikel
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
const artikelForm = document.getElementById('artikelForm');

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
            width: 1200,
            height: 800,
            minWidth: 1200,
            minHeight: 800,
            maxWidth: 1200,
            maxHeight: 800,
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

// Submit form dengan validasi lengkap
artikelForm.onsubmit = function(e) {
    console.log('Form submit triggered');
    
    // Get semua field values
    var title = document.getElementById('title').value.trim();
    var date = document.getElementById('date').value;
    var author = document.getElementById('author').value.trim();
    var category = document.getElementById('category').value.trim();
    var content = quill.root.innerHTML;
    var croppedImageData = croppedImage.value;
    
    // Debug log
    console.log('Title:', title);
    console.log('Date:', date);
    console.log('Author:', author);
    console.log('Category:', category);
    console.log('Content length:', content.length);
    console.log('Has cropped image:', croppedImageData ? 'Yes' : 'No');
    
    // Validasi judul
    if (!title) {
        alert('Judul artikel harus diisi!');
        document.getElementById('title').focus();
        e.preventDefault();
        return false;
    }
    
    // Validasi tanggal
    if (!date) {
        alert('Tanggal harus diisi!');
        document.getElementById('date').focus();
        e.preventDefault();
        return false;
    }
    
    // Validasi konten
    var textContent = quill.getText().trim();
    if (textContent.length < 50) {
        alert('Konten artikel tidak boleh kosong dan minimal 50 karakter!');
        quill.focus();
        e.preventDefault();
        return false;
    }
    
    // Validasi gambar yang sudah di-crop
    if (!croppedImageData) {
        alert('Gambar artikel harus diupload dan di-crop terlebih dahulu!');
        e.preventDefault();
        return false;
    }
    
    // Set content ke textarea hidden
    document.getElementById('content').value = content;
    console.log('Content set to textarea, length:', content.length);
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    
    console.log('Form validation passed, submitting...');
    return true;
};

// Auto-save draft
setInterval(function() {
    var content = quill.root.innerHTML;
    if (content.trim() !== '<p><br></p>' && content.trim() !== '') {
        localStorage.setItem('artikel_draft_new', content);
        console.log('Draft auto-saved');
    }
}, 30000);

// Load draft
window.onload = function() {
    var draft = localStorage.getItem('artikel_draft_new');
    if (draft && draft.trim() !== '<p><br></p>' && draft.trim() !== '') {
        if (confirm('Ditemukan draft yang belum disimpan. Muat draft tersebut?')) {
            quill.root.innerHTML = draft;
        }
    }
};
</script>

<?php include 'includes/footer.php'; ?>