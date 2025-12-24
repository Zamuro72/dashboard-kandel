<?php
// Konfigurasi Database
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_40520125');
define('DB_PASS', 'auza1702');
define('DB_NAME', 'if0_40520125_zamuro');

// Koneksi Database
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Fungsi untuk mencegah SQL Injection
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Fungsi Upload Gambar
function upload_image($file, $target_dir) {
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    $max_size = 5 * 1024 * 1024; // 5MB
    
    $file_name = basename($file["name"]);
    $file_tmp = $file["tmp_name"];
    $file_size = $file["size"];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Generate unique filename
    $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
    $target_file = $target_dir . $new_filename;
    
    // Validasi
    if (!in_array($file_ext, $allowed_types)) {
        return array('success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.');
    }
    
    if ($file_size > $max_size) {
        return array('success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB.');
    }
    
    // Upload file
    if (move_uploaded_file($file_tmp, $target_file)) {
        return array('success' => true, 'filename' => $new_filename);
    } else {
        return array('success' => false, 'message' => 'Gagal upload file.');
    }
}

// Fungsi Delete File
function delete_file($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}
?>