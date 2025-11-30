-- Buat Database
CREATE DATABASE petra_airlines;
USE petra_airlines;

-- Tabel Penerbangan
CREATE TABLE penerbangan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_penerbangan VARCHAR(10) NOT NULL,
    asal VARCHAR(50) NOT NULL,
    tujuan VARCHAR(50) NOT NULL,
    tanggal_berangkat DATE NOT NULL,
    jam_berangkat TIME NOT NULL,
    jam_tiba TIME NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    kursi_tersedia INT NOT NULL
);

-- Insert Data Contoh
INSERT INTO penerbangan (kode_penerbangan, asal, tujuan, tanggal_berangkat, jam_berangkat, jam_tiba, harga, kursi_tersedia) VALUES
('PA101', 'Surabaya', 'Jakarta', '2025-12-01', '08:00:00', '10:00:00', 750000, 120),
('PA102', 'Surabaya', 'Bali', '2025-12-01', '09:30:00', '11:00:00', 650000, 150),
('PA103', 'Jakarta', 'Surabaya', '2025-12-01', '12:00:00', '14:00:00', 750000, 100),
('PA104', 'Surabaya', 'Yogyakarta', '2025-12-02', '07:00:00', '08:30:00', 550000, 80),
('PA105', 'Bali', 'Surabaya', '2025-12-02', '15:00:00', '16:30:00', 650000, 140),
('PA106', 'Surabaya', 'Medan', '2025-12-03', '10:00:00', '13:30:00', 1200000, 110),
('PA107', 'Jakarta', 'Bali', '2025-12-03', '14:00:00', '16:30:00', 900000, 130),
('PA108', 'Surabaya', 'Makassar', '2025-12-04', '11:00:00', '14:00:00', 950000, 90);

-- Tabel Users (untuk login)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'member' CHECK (role IN ('member', 'admin'))
);

-- Insert User Contoh (password sebaiknya di-hash dengan PHP)
INSERT INTO users (username, pass, role) VALUES
('damin', '$2y$10$abcdefghijklmnopqrstuv', 'admin'),
('nathan', '$2y$10$abcdefghijklmnopqrstuv', 'member');

-- table seat 
CREATE TABLE seat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_id INT NOT NULL,
    seat_no VARCHAR(5) NOT NULL,
    class VARCHAR(20) DEFAULT 'economy',
    status ENUM('available','booked') DEFAULT 'available',
    FOREIGN KEY (flight_id) REFERENCES penerbangan(id)
);

-- table booking
CREATE TABLE booking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flight_id) REFERENCES penerbangan(id)
);


