## 1. Kebutuhan Sistem & Pilihan Instalasi (Prerequisites)

Sistem ini dapat dijalankan dengan **2 Cara**:
1. **Opsi A: Menggunakan Docker & Docker Compose (Sangat Praktis & Terisolasi)**.
2. **Opsi B: Menggunakan XAMPP Standalone (Tanpa Docker)**.

---

## 2. Opsi A: Penerapan dengan Docker Compose (Direkomendasikan)

Setup Docker telah disiapkan lengkap dalam file `docker-compose.yml` yang mengorkestrasi 3 kontainer ringan:
- **`mysql`**: Database MariaDB 10.11 (dengan inisialisasi skema & seed otomatis).
- **`ldap-daemon`**: Python 3.11-slim LDAP RFC 4511 TCP daemon (Port 389).
- **`web`**: PHP 8.2-Apache Web Admin Console (Port 8080).

### Langkah-langkah Menjalankan di Docker:

1. **Siapkan File Konfigurasi Environment**:
   Salin file `.env.example` menjadi `.env` (atau gunakan nilai default yang sudah ada):
   ```bash
   cp .env.example .env
   ```

2. **Build dan Jalankan Kontainer**:
   Jalankan perintah berikut di root folder project:
   ```bash
   docker-compose up -d --build
   ```

3. **Cek Status Kontainer**:
   ```bash
   docker-compose ps
   ```

4. **Akses Antarmuka & Layanan**:
   - **Web Admin Dashboard**: `http://localhost:8080` (Login: `admin` / `adminpassword`)
   - **LDAP Server Port**: `127.0.0.1:389` (TCP)
   - **Database Port**: `127.0.0.1:3307`

5. **Menghentikan Kontainer**:
   ```bash
   docker-compose down
   ```
   *(Gunakan `docker-compose down -v` jika ingin membersihkan seluruh volume database)*.

---

## 3. Opsi B: Penerapan di Lingkungan Standalone (Windows XAMPP)

### Langkah 1: Inisialisasi Database MySQL
1. Pastikan modul **Apache** dan **MySQL** di XAMPP Control Panel dalam status **Running**.
2. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`) atau terminal MySQL.
3. Eksekusi file skrip `database/schema.sql` untuk membuat database `ldap_server_db` dan tabel-tabelnya:
   ```bash
   e:\xampp\mysql\bin\mysql.exe -u root -p < e:\xampp\htdocs\ldap\database\schema.sql
   ```
   *(Jika MySQL tidak menggunakan password root, tekan Enter saat diminta password)*.

---

### Langkah 2: Konfigurasi Web Admin Dashboard
1. Buka file `config/database.php`.
2. Sesuaikan kredensial database jika diperlukan:
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_PORT', 3306);
   define('DB_NAME', 'ldap_server_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
3. Buka browser dan akses: `http://localhost/ldap`.
4. Login menggunakan akun bawaan:
   - **Username**: `admin`
   - **Password**: `adminpassword`

---

### Langkah 3: Menjalankan LDAP Server Daemon
1. Buka folder `daemon/` di terminal atau Command Prompt:
   ```cmd
   cd e:\xampp\htdocs\ldap\daemon
   ```
2. Instal dependensi Python:
   ```cmd
   pip install -r requirements.txt
   ```
3. Jalankan server daemon LDAP:
   - **Cara 1**: Klik ganda file `daemon/run_daemon.bat`.
   - **Cara 2**: Jalankan perintah terminal:
     ```cmd
     python server.py
     ```
4. Daemon akan mulai mendengarkan koneksi di `0.0.0.0:389` dan terhubung ke MySQL.

---

## 3. Konfigurasi Windows Firewall & Izin Jaringan

Agar software client di komputer lain (misalnya pGina pada laptop karyawan) dapat terhubung ke LDAP Server:

1. Buka **Windows Defender Firewall with Advanced Security**.
2. Pilih **Inbound Rules** -> **New Rule...**
3. Pilih **Port** -> **TCP** -> Masukkan port `389` (atau `1389`).
4. Pilih **Allow the connection**.
5. Beri nama rule: `Custom LDAP Server (Port 389)`.
6. Simpan konfigurasi.

---

## 4. Menjalankan Daemon Sebagai Windows Service (Opsional / Background)

Agar daemon LDAP otomatis berjalan di latar belakang saat Windows dinyalakan tanpa perlu membuka terminal:

### Menggunakan NSSM (Non-Sucking Service Manager)
1. Unduh `nssm.exe` (ringan, ~300 KB).
2. Buka Command Prompt (Administrator) dan jalankan:
   ```cmd
   nssm install CustomLDAPServer "C:\Python314\python.exe" "e:\xampp\htdocs\ldap\daemon\server.py"
   nssm set CustomLDAPServer AppDirectory "e:\xampp\htdocs\ldap\daemon"
   nssm start CustomLDAPServer
   ```
3. Layanan LDAP kini berjalan otomatis di latar belakang sebagai Windows Service.

---

## 5. Pemecahan Masalah (Troubleshooting)

| Masalah | Kemungkinan Penyebab | Solusi |
| :--- | :--- | :--- |
| **`Port 389 is already in use`** | Port 389 sedang dipakai oleh Active Directory LDS atau service Windows lainnya. | Ubah port ke `1389` di file `daemon/config.json` dan di Web Dashboard, atau matikan service AD LDS di `services.msc`. |
| **`Access denied for user 'root'@'localhost'`** | Password MySQL XAMPP tidak sesuai. | Sesuaikan `DB_PASS` pada `config/database.php` dan `daemon/config.json`. |
| **`Client connection timeout` dari pGina** | Firewall memblokir port TCP 389. | Tambahkan Inbound Rule di Windows Firewall seperti pada Bagian 3. |
| **`Invalid Credentials (49)` pada pGina** | Password salah atau akun user berstatus `disabled`. | Cek status user di Web Admin Dashboard (`http://localhost/ldap`), pastikan berstatus `active` dan reset password jika perlu. |
