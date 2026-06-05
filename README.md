# 🏕️ CampRent - Sistem Penyewaan Peralatan Camping Berbasis Web

## Deskripsi

CampRent adalah sistem penyewaan peralatan camping berbasis web yang dirancang untuk membantu proses pengelolaan penyewaan alat camping secara lebih efektif dan terkomputerisasi. Sistem ini memungkinkan pelanggan melakukan pemesanan alat camping secara online, sedangkan admin dapat mengelola data alat, transaksi penyewaan, pengembalian, serta laporan penyewaan.

---

## Anggota Kelompok
- 2217051159  
  Eriza Trisativa

- 2217051011  
  Ayu Puspitasari

- 2217051134  
  Jefri Raihan Akbar


## Teknologi yang Digunakan

* PHP Native
* MySQL
* Laragon
* GitHub

---

## Fitur Utama

### Login

* Login Admin
* Login Pelanggan
* Manajemen sesi pengguna

### Manajemen Alat Camping

* Menambah data alat camping
* Mengubah data alat camping
* Menghapus data alat camping
* Mengelola stok alat

### Penyewaan

* Pemesanan alat camping
* Pencatatan transaksi penyewaan
* Monitoring status penyewaan

### Pengembalian

* Pengembalian alat camping
* Perubahan status penyewaan

### Laporan

* Laporan transaksi penyewaan
* Monitoring aktivitas penyewaan

---

# Implementasi Materi Praktikum PDT

## 1. Trigger

### Nama Trigger

`trigger_pengembalian_alat`

### Fungsi

Trigger dijalankan secara otomatis ketika status penyewaan berubah menjadi **"Dikembalikan"**.

### Implementasi

* Menambahkan kembali stok alat yang telah dikembalikan.
* Memastikan jumlah stok selalu sesuai dengan kondisi aktual.

### Lokasi

Database MySQL → Trigger `trigger_pengembalian_alat`

---

## 2. Task Scheduler

### Nama Event

`event_cek_keterlambatan`

### Fungsi

Event Scheduler dijalankan otomatis setiap hari untuk memeriksa data penyewaan yang telah melewati tanggal pengembalian.

### Implementasi

* Mengecek penyewaan yang belum dikembalikan.
* Mengubah status menjadi **"Terlambat"** secara otomatis.

### Lokasi

Database MySQL → Event Scheduler

---

## 3. Fragmentasi Data

### Fragmentasi Horizontal

Data penyewaan dipisahkan berdasarkan status transaksi sehingga proses pencarian data aktif menjadi lebih cepat.

Contoh:

* Penyewaan Aktif
* Penyewaan Dikembalikan
* Penyewaan Terlambat

### Fragmentasi Vertikal

Data laporan hanya menampilkan atribut yang diperlukan.

Contoh atribut:

* ID Transaksi
* Nama Pelanggan
* Nama Alat
* Tanggal Sewa
* Status

### Tujuan

* Mengurangi data yang diproses saat query dijalankan.
* Meningkatkan efisiensi akses data.

---

## 4. Backup Database

### Metode

Backup otomatis menggunakan:

* mysqldump
* Windows Task Scheduler
* File Batch (.bat)

### Jadwal

Setiap pukul 00.00 WIB

### Tujuan

Mengurangi risiko kehilangan data akibat kerusakan sistem atau kesalahan pengguna.

---

## Struktur Database

Database: `camprent`

Tabel utama:

* pengguna
* alat_camping
* penyewaan
* detail_penyewaan
* pengembalian

---

## Cara Menjalankan Sistem

### 1. Clone Repository

```bash
git clone https://github.com/username/uap-pdt-camprent.git
```

### 2. Import Database

Import file:

```text
database.sql
```

ke MySQL menggunakan phpMyAdmin atau CLI.

### 3. Jalankan Laragon

Aktifkan:

* Apache
* MySQL

### 4. Simpan Project

```text
C:\laragon\www\camprent
```

### 5. Akses Sistem

```text
http://localhost/camprent
```

---

## Screenshot Sistem

### Login

(Tambahkan screenshot)

### Dashboard

(Tambahkan screenshot)

### Penyewaan

(Tambahkan screenshot)

### Pengembalian

(Tambahkan screenshot)

### Laporan

(Tambahkan screenshot)

---

## Lisensi

Project ini dibuat untuk memenuhi Ujian Akhir Praktikum Pengelolaan Data Terdistribusi (PDT).
