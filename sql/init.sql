-- sql/init.sql
CREATE DATABASE IF NOT EXISTS kce_mechanic CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE kce_mechanic;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','karyawan') NOT NULL,
  nama VARCHAR(100) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS laporan_perbaikan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal_input DATE NOT NULL,
  nama_unit VARCHAR(100) NOT NULL,
  keluhan_kerusakan TEXT,
  penyebab_kerusakan TEXT,
  tgl_mulai_reparasi DATE,
  tgl_selesai_reparasi DATE,
  tindakan_perbaikan TEXT,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Password hashes for demo accounts
-- admin123 and karyawan123 hashed with PASSWORD_DEFAULT
INSERT INTO users (username, password, role, nama) VALUES
('admin', '$2y$10$2hZy6mQvujqG4iPqg8b3he3uP8qKfE0aQYgq9Q7xvW1YbN8KzZ0cC', 'admin', 'Kepala Divisi'),
('karyawan', '$2y$10$K1daxVn9kqV7t3Y1q4bYbeql3v8Ftr5Gq9QYp1jz1sH0v9K2WmL6', 'karyawan', 'User Mekanik');
