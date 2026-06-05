# 🏕️ CampRent - Sistem Penyewaan Peralatan Camping Berbasis Web

## Deskripsi

CampRent adalah sistem penyewaan peralatan camping berbasis web yang dirancang untuk membantu proses pengelolaan penyewaan alat camping secara lebih efektif dan terkomputerisasi. Sistem ini memungkinkan pelanggan melakukan pemesanan alat camping secara online, sedangkan admin dapat mengelola data alat, transaksi penyewaan, pengembalian, serta laporan penyewaan.

Selain itu, admin juga dapat mengelola data alat camping, data pelanggan, transaksi penyewaan, pengembalian alat, hingga laporan penyewaan dalam satu sistem. Dengan adanya sistem ini, proses pendataan menjadi lebih rapi, mengurangi kesalahan pencatatan manual, serta membantu meningkatkan efisiensi dalam pengelolaan penyewaan alat camping.



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



# Implementasi Materi Praktikum PDT

## 1. Trigger

### Nama Trigger

trigger_pengembalian_alat
### Fungsi

Trigger ini digunakan untuk memperbarui data secara otomatis saat alat camping sudah dikembalikan oleh pelanggan.

### Implementasi

* Mengembalikan jumlah stok alat setelah proses pengembalian dilakukan.
* Membantu menjaga data stok agar tetap sesuai dengan kondisi sebenarnya di sistem.
### Lokasi

Database MySQL → Trigger  trigger_pengembalian_alat


## 2. Task Scheduler

### Nama Event

event_cek_keterlambatan

### Fungsi

Event Scheduler ini dipakai untuk melakukan pengecekan otomatis pada data penyewaan yang sudah melewati batas waktu pengembalian alat.

### Implementasi

* Memeriksa data penyewaan yang status pengembaliannya masih belum selesai.
* Mengubah status penyewaan menjadi “Terlambat” secara otomatis apabila melewati tanggal pengembalian.

### Lokasi

Database MySQL → Event Scheduler



## 3. Fragmentasi Data

### Fragmentasi Horizontal

Data penyewaan dikelompokkan berdasarkan status transaksi agar proses pencarian dan pengelolaan data menjadi lebih mudah dan cepat.

Contoh:

* Penyewaan Aktif
* Penyewaan Dikembalikan
* Penyewaan Terlambat

### Fragmentasi Vertikal

Pada bagian laporan, sistem hanya menampilkan data yang diperlukan sehingga informasi lebih ringkas dan mudah dibaca.

Contoh atribut:

* ID Transaksi
* Nama Pelanggan
* Nama Alat
* Tanggal Sewa
* Status

### Tujuan

* Mengurangi jumlah data yang diproses saat menjalankan query.
* Membantu meningkatkan kecepatan dan efisiensi akses data pada sistem.



## 4. Backup Database

### Metode

Proses backup database dilakukan secara otomatis menggunakan beberapa tools,yaitu:

* mysqldump
* Windows Task Scheduler
* File Batch (.bat)

### Jadwal

Backup database dijalankan setiap hari pada pukul 00.00 WIB.

### Tujuan

Backup dilakukan untuk menjaga keamanan data dan mengurangi kemungkinan kehilangan data apabila terjadi error pada sistem atau kesalahan saat penggunaan aplikasi.



## Struktur Database

Database yang digunakan pada project ini bernama camprent.

Beberapa tabel utama yang digunakan, yaitu:

* pengguna
* alat_camping
* penyewaan
* detail_penyewaan
* pengembalian



## Cara Menjalankan Sistem

### 1. Clone Repository

git clone https://github.com/username/uap-pdt-camprent.git

### 2. Import Database

Import file:

text
database.sql

ke MySQL menggunakan phpMyAdmin atau CLI.

### 3. Jalankan Laragon

Aktifkan:

* Apache
* MySQL

### 4. Simpan Project

text
C:\laragon\www\camprent

### 5. Akses Sistem

text
http://localhost/camprent




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



## Lisensi

Project ini dibuat untuk memenuhi Ujian Akhir Praktikum Pengelolaan Data Terdistribusi (PDT).
