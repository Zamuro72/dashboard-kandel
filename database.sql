-- Database untuk Dashboard Admin KSI
CREATE DATABASE IF NOT EXISTS ksi_dashboard;
USE ksi_dashboard;

-- Tabel Admin
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin default
INSERT INTO admin (username, password) VALUES 
('kandel', '$2y$10$YourHashedPasswordHere'); -- Password: kandelsekeco1702

-- Tabel Artikel
CREATE TABLE artikel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    author VARCHAR(100) DEFAULT 'Kandel Sekeco',
    category VARCHAR(100) DEFAULT 'Safety K3',
    image VARCHAR(255) NOT NULL,
    excerpt TEXT,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Pelatihan Kemnaker
CREATE TABLE pelatihan_kemnaker (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_training VARCHAR(255) NOT NULL,
    tanggal VARCHAR(100) NOT NULL,
    durasi VARCHAR(50) NOT NULL,
    lokasi VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Pelatihan BNSP
CREATE TABLE pelatihan_bnsp (
    id INT PRIMARY KEY AUTO_INCREMENT,
    skema_sertifikasi VARCHAR(255) NOT NULL,
    tanggal VARCHAR(100) NOT NULL,
    durasi VARCHAR(50) NOT NULL,
    lokasi VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Gallery
CREATE TABLE gallery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);