-- Database Sistem Menu Kantin
-- Paket E - Praktikum Pemrograman Web
-- Relasi: kategori_menu (1) ke menu (N)
-- Import file ini pada database baru.

CREATE DATABASE IF NOT EXISTS menu_kantin_2401020144;

USE menu_kantin_2401020144;

CREATE TABLE kategori_menu (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE menu (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_menu CHAR(10) NOT NULL UNIQUE,
    nama_menu VARCHAR(100) NOT NULL,
    barcode VARCHAR(30) NOT NULL UNIQUE,
    harga INT UNSIGNED NOT NULL,
    kategori_menu_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT chk_harga
    CHECK (harga BETWEEN 1000 AND 100000),

    CONSTRAINT fk_menu_kategori
    FOREIGN KEY (kategori_menu_id)
    REFERENCES kategori_menu(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

INSERT INTO kategori_menu (nama_kategori)
VALUES
('Makanan'),
('Minuman');

INSERT INTO menu
(kode_menu, nama_menu, barcode, harga, kategori_menu_id)
VALUES
('MN00000001', 'Nasi Goreng', 'BR-MKN-0001', 18000, 1),
('MN00000002', 'Es Teh', 'BR-MNM-0001', 5000, 2),
('MN00000003', 'Mie Rebus', 'BR-MKN-0002', 15000, 1);
