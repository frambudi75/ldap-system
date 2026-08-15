# Custom LDAP Server & Web Management System

Sistem Server Direktori & Autentikasi LDAP mandiri berbasis protokol standar **RFC 4511** yang kompatibel dengan **pGina** (Windows Login), aplikasi web (Nextcloud, WordPress, Laravel, PHP `ldap_connect`), dan dilengkapi **Web Admin Dashboard** modern berbasis **PHP Native + Tailwind CSS**.

---

## 🌟 Fitur Utama
- **Core LDAP Server Daemon (TCP Port 389)**:
  - Protokol biner ASN.1 / BER LDAPv3 standar.
  - Autentikasi Admin/Root Bind & User Bind (`inetOrgPerson`, `posixAccount`).
  - Pencarian Direktori (Search filter equality, substrings, present, and/or/not).
  - Multi-hash password support (Bcrypt, SSHA, SHA256).
  - Logging aktivitas real-time ke MySQL.
- **Web Admin Dashboard (PHP Native + Tailwind CSS)**:
  - Berjalan langsung di Apache XAMPP (`http://localhost/ldap`).
  - Manajemen Pengguna (CRUD, Toggle Active/Disabled, Reset Password).
  - Manajemen Kelompok (Groups & posixGroup mapping).
  - Pengaturan Base DN & Root Bind.
  - **Interactive LDAP Connection Tester** (Uji bind/search langsung dari web).
  - **Audit Logs Viewer** dengan filter & monitoring latensi.
  - **Panduan Integrasi Interaktif** (pGina, PHP, Laravel, WordPress, Nextcloud).

---

## 🚀 Cara Menjalankan

### 1. Pastikan Apache & MySQL di XAMPP Aktif
Database `ldap_server_db` sudah diinisialisasi secara otomatis.

### 2. Jalankan LDAP Server Daemon
Klik ganda file:
```
e:\xampp\htdocs\ldap\daemon\run_daemon.bat
```
Atau via terminal:
```bash
cd e:\xampp\htdocs\ldap\daemon
python server.py
```

### 3. Akses Web Admin Dashboard
Buka browser dan buka alamat:
```
http://localhost/ldap
```
- **Username**: `admin`
- **Password**: `adminpassword`

---

## 📁 Struktur Folder
```
e:/xampp/htdocs/ldap/
├── config/              # Konfigurasi database & app
├── core/                # Auth, helper, LDAP tester logic
├── daemon/              # Core LDAP TCP Server (RFC 4511)
├── database/            # Skema SQL database & seeder
├── docs/                # Dokumentasi arsitektur, PRD, API, UI/UX
├── views/               # Antarmuka PHP & Tailwind CSS
├── index.php            # Master router
└── README.md
```
