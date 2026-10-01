CREATE DATABASE IF NOT EXISTS absensi_galon CHARACTER SET utf8mb4;
USE absensi_galon;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password CHAR(64) NOT NULL,
  role ENUM('warga','penjaga') NOT NULL
);
INSERT INTO users (username,password,role) VALUES
('SMKN1PROBOLINGGO', SHA2('SMKN1PROBOLINGGO',256), 'penjaga'),
('SMEXA', SHA2('SMEXA',256), 'warga');

CREATE TABLE siswa (
  nisn VARCHAR(20) PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  kelas VARCHAR(30) NOT NULL
);
INSERT INTO siswa VALUES
('0051234501','Ahmad Fauzi','X TKJ 1'),
('0051234502','Dewi Lestari','X AKL 2'),
('0051234503','Rizky Pratama','XI RPL 1'),
('0051234504','Siti Nurhaliza','XI TKJ 2'),
('0051234505','Bagas Saputra','XII TKR 1'),
('0051234506','Putri Ayu','XII OTKP 1');

CREATE TABLE pengaturan (id INT PRIMARY KEY, stok INT NOT NULL DEFAULT 0);
INSERT INTO pengaturan VALUES (1, 20);

CREATE TABLE absensi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nisn VARCHAR(20) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  kelas VARCHAR(30) NOT NULL,
  tanggal DATE NOT NULL,
  jam TIME NOT NULL,
  INDEX (tanggal)
);
