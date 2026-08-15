# Product Requirements Document (PRD) - Custom LDAP Server & Management System

## 1. Ringkasan Eksekutif (Executive Summary)
**Custom LDAP Server** adalah solusi *directory service* berbasis protokol standar LDAP (RFC 4511) yang dirancang untuk menyediakan autentikasi dan pencarian direktori terpusat bagi berbagai klien, seperti **pGina** (Windows Login Authentication), aplikasi web (Nextcloud, WordPress, Laravel, PHP native `ldap_connect`), dan sistem enterprise lainnya.

Sistem ini menggabungkan:
1. **Core LDAP Daemon**: Server TCP (Port 389/1389) persisten yang memproses paket biner ASN.1/BER protokol LDAP.
2. **Database MySQL**: Penyimpanan terpusat data kredensial, entitas user, group/OU, pengaturan direktori, dan audit logs.
3. **Web Admin Dashboard (PHP Native + Tailwind CSS)**: Antarmuka berbasis web modern dan intuitif untuk manajemen pengguna, grup, monitoring aktivitas, dan pengujian koneksi.

---

## 2. Tujuan & Masalah yang Diselesaikan (Goals & Problem Statement)
- **Kemandirian Infrastruktur**: Menghindari ketergantungan pada Active Directory atau OpenLDAP yang kompleks dalam setup dan pemeliharaan untuk lingkungan skala kecil hingga menengah.
- **Kemudahan Integrasi**: Memberikan endpoint LDAP standar yang kompatibel dengan aplikasi pihak ketiga dan software pGina.
- **Manajemen User Terpusat**: Memberikan antarmuka web modern bagi administrator untuk mengelola akun (CRUD), reset password, assign group, dan mengaudit log login tanpa perlu keahlian command-line LDAP (ldif).
- **Ringan & Tanpa Beban Docker**: Menggunakan arsitektur hybrid yang berjalan langsung di atas XAMPP (Apache + MySQL) dan daemon Python yang ringan.

---

## 3. Pengguna Sasaran (Target Audience / Personas)
1. **System Administrator (SysAdmin)**:
   - Mengelola akun pengguna, hak akses, dan grup.
   - Memantau log autentikasi dan status kesehatan server LDAP.
   - Melakukan konfigurasi Base DN, Root DN, dan kebijakan keamanan.
2. **End User / Karyawan**:
   - Melakukan login pada workstation Windows melalui pGina menggunakan username dan password terdaftar.
   - Melakukan single-credential login pada aplikasi web terintegrasi.
3. **Software Developer / IT Integrator**:
   - Menghubungkan aplikasi web internal (PHP, Laravel, Node.js, Python, Java) ke server LDAP melalui fungsi standar LDAP client.

---

## 4. Fitur Utama & Kebutuhan Fungsional (Functional Requirements)

### 4.1 Core LDAP Server Daemon
- **FR-01 (Protokol RFC 4511)**: Menerima koneksi TCP pada port 389 (default) atau port kustom (misal: 1389).
- **FR-02 (LDAP Simple Bind)**:
  - Verifikasi Admin/Service Bind (misal: `cn=admin,dc=example,dc=local`).
  - Verifikasi User Authentication Bind (misal: `uid=budi,ou=users,dc=example,dc=local`) dengan pencocokan hash password di MySQL.
  - Opsi Anonymous Bind untuk pencarian publik jika diaktifkan.
- **FR-03 (LDAP Search)**:
  - Mendukung scope: `BaseObject`, `SingleLevel`, `WholeSubtree`.
  - Mendukung filter: equality `(uid=budi)`, `(mail=...)`, `(cn=...)`, `(sAMAccountName=...)`, wildcard `(uid=bu*)`, `(objectClass=...)`, dan operasi logika `(&...)`, `(|...)`, `(!...)`.
  - Mengembalikan atribut standar: `dn`, `objectClass`, `uid`, `cn`, `sn`, `givenName`, `mail`, `memberOf`, `telephoneNumber`, `userPassword`.
- **FR-04 (Multi-Hash Password Support)**: Mendukung verifikasi password dengan format:
  - Bcrypt (`$2y$...` / `$2a$...`)
  - SSHA (`{SSHA}...` - standar OpenLDAP)
  - SHA-256 (`{SHA256}...`)
  - Plaintext / Legacy MD5 (opsional fallback)
- **FR-05 (Real-Time Audit Logging)**: Mencatat setiap request LDAP (IP, waktu, operasi, target DN, status hasil) ke tabel database.

### 4.2 Web Admin Dashboard (PHP Native + Tailwind CSS)
- **FR-06 (Autentikasi Admin)**: Login aman administrator web dengan session timeout.
- **FR-07 (Dashboard Overview)**:
  - Indikator status LDAP Server Daemon (Aktif / Nonaktif / Port).
  - Statistik ringkasan (Total Pengguna, Total Grup, Pengguna Aktif vs Nonaktif, Login Sukses & Gagal hari ini).
  - Widget log autentikasi real-time / auto-refresh.
- **FR-08 (Manajemen Pengguna - User Management)**:
  - Daftar pengguna dengan pagination, pencarian nama/username/email, dan filter status/grup.
  - Tambah, Edit, Hapus (Soft/Hard delete) Pengguna.
  - Reset / Ubah Password dengan auto-hash.
  - Toggle status akun: *Active* atau *Suspended/Disabled* (akun nonaktif langsung ditolak oleh daemon LDAP).
- **FR-09 (Manajemen Grup - Group Management)**:
  - Tambah, Edit, Hapus Grup (`posixGroup` / `groupOfNames`).
  - Manajemen anggota grup (Assign/Remove user).
- **FR-10 (Konfigurasi Server & Direktori)**:
  - Pengaturan Base DN (misal: `dc=company,dc=lan`), Organization Name, Root Bind DN, Admin Password.
  - Toggle izin Anonymous Bind.
  - Pengaturan Default User OU (`ou=users`) dan Group OU (`ou=groups`).
- **FR-11 (Web LDAP Connection Tester)**:
  - Alat pengujian langsung di browser untuk mensimulasikan Bind & Search tanpa perlu install command `ldapsearch`.
  - Validasi kredensial user langsung dari UI.
- **FR-12 (Dokumentasi & Panduan Integrasi)**:
  - Panduan interaktif konfigurasi pGina (Windows Client).
  - Contoh kode integrasi untuk PHP (`ldap_connect`), Laravel, WordPress, dan Nextcloud.
- **FR-13 (Audit Log Viewer)**:
  - Riwayat lengkap request LDAP dengan filter tanggal, status sukses/gagal, dan IP address.
  - Fitur export log ke format CSV / JSON.

---

## 5. Kebutuhan Non-Fungsional (Non-Functional Requirements)
- **NFR-01 (Performa & Efisiensi)**: Respon query autentikasi LDAP rata-rata < 50ms untuk koneksi lokal/LAN. Daemon menggunakan memori rendah (< 30 MB RAM).
- **NFR-02 (Keamanan)**:
  - Proteksi SQL Injection menggunakan PDO Prepared Statements pada Web Admin dan Daemon.
  - Proteksi CSRF pada formulir Web Admin.
  - Penyimpanan password wajib berformat hash aman (Bcrypt / SSHA).
- **NFR-03 (Kompatibilitas Sistem Operasi)**: Berjalan secara optimal di Windows (lingkungan XAMPP) dan Linux (Apache/Nginx + MySQL + Python).
- **NFR-04 (Keandalan)**: Daemon LDAP mampu menangani error koneksi database sementara (auto-reconnect) tanpa crash.

---

## 6. Metrik Keberhasilan (Success Metrics)
1. **pGina Compatibility**: Klien Windows dengan pGina dapat melakukan login workstation secara lancar menggunakan kredensial yang dibuat di Web Admin.
2. **Web App Interoperability**: Aplikasi web dengan modul LDAP (misal: PHP, Nextcloud) dapat melakukan bind dan retrieve atribut user dengan sukses.
3. **Zero Configuration Complexity**: Admin dapat menginstal dan mengoperasikan sistem dalam waktu kurang dari 5 menit menggunakan XAMPP yang sudah ada.
