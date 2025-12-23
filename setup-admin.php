<?php
require_once 'config/database.php';

// Hash password
$username = 'kandel';
$password = 'kandelsekeco1702';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin exists
$check = $conn->query("SELECT * FROM admin WHERE username = '$username'");

if ($check->num_rows > 0) {
    // Update existing admin
    $sql = "UPDATE admin SET password = '$hashed_password' WHERE username = '$username'";
    if ($conn->query($sql)) {
        echo "<h2 style='color: green;'>✅ Password admin berhasil diupdate!</h2>";
    }
} else {
    // Insert new admin
    $sql = "INSERT INTO admin (username, password) VALUES ('$username', '$hashed_password')";
    if ($conn->query($sql)) {
        echo "<h2 style='color: green;'>✅ Admin berhasil dibuat!</h2>";
    }
}

echo "<div style='font-family: Arial; padding: 20px; line-height: 2;'>";
echo "<h3>📋 Informasi Login:</h3>";
echo "Username: <strong style='color: blue;'>$username</strong><br>";
echo "Password: <strong style='color: blue;'>$password</strong><br><br>";
echo "<a href='login.php' style='background: #2E9FD8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login Sekarang</a><br><br>";
echo "<hr><br>";
echo "<strong style='color: red; font-size: 1.2rem;'>⚠️ PENTING: Hapus file ini (setup-admin.php) setelah selesai setup untuk keamanan!</strong>";
echo "</div>";
?>