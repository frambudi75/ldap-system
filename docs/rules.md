# Aturan dan Standar Pengembangan (Rules & Guidelines)

Dokumen ini mendefinisikan pedoman arsitektur, konvensi penamaan, standar keamanan, dan aturan penulisan kode untuk proyek **Custom LDAP Server**.

---

## 1. Aturan Arsitektur & Desain (Architectural Rules)

1. **Pemisahan Peran (Separation of Concerns)**:
   - **PHP Web Admin**: Hanya bertanggung jawab untuk manajemen data via web, otentikasi admin, monitoring, dan visualisasi log. Tidak boleh memblokir thread socket LDAP.
   - **Python Core Daemon**: Berjalan mandiri sebagai background service TCP. Hanya mengakses database secara read/write melalui connection pool yang aman.
   - **MySQL**: Sebagai *single source of truth* untuk seluruh state kredensial dan konfigurasi.

2. **Kompatibilitas Standar LDAP (RFC 4511)**:
   - Respon protokol LDAP wajib mengembalikan kode hasil (*ResultCode*) standar:
     - `0`: `success`
     - `32`: `noSuchObject` (DN tidak ditemukan)
     - `48`: `inappropriateAuthentication` (Metode bind tidak didukung)
     - `49`: `invalidCredentials` (Password salah / user dinonaktifkan)
     - `50`: `insufficientAccessRights` (Akses ditolak)
     - `80`: `other` (Internal error / DB offline)

3. **Virtual DIT Resolution**:
   - Seluruh Distinguished Name (`DN`) harus diurai secara *case-insensitive* pada atribut (misal: `UID=budi` sama dengan `uid=budi`).
   - Format DN default: `uid={username},{user_ou},{base_dn}`.

---

## 2. Standar Keamanan (Security Rules)

1. **Penyimpanan Password**:
   - **DILARANG** menyimpan password dalam bentuk plaintext di database.
   - Algoritma hashing default adalah **Bcrypt** (`PASSWORD_BCRYPT` di PHP / `bcrypt` di Python).
   - Format hash OpenLDAP SSHA (`{SSHA}...`) harus didukung untuk kompatibilitas integrasi software legacy.
   - Akun dengan status `disabled` atau `suspended` **WAJIB** ditolak oleh daemon LDAP saat *BindRequest* meskipun password yang dimasukkan benar.

2. **Proteksi Database**:
   - Wajib menggunakan **PDO Prepared Statements** dengan *parameterized queries* di PHP.
   - Daemon Python wajib menggunakan placeholder parameterized query (misal `%s` pada connector MySQL) untuk mencegah SQL Injection.

3. **Proteksi Web Admin**:
   - Semua form mutating (POST/PUT/DELETE) wajib menyertakan **CSRF Token** valid.
   - Data keluaran HTML wajib di-*sanitize* menggunakan `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` untuk mencegah XSS.
   - Sesi login admin harus di-*regenerate* saat login sukses (`session_regenerate_id(true)`).

---

## 3. Konvensi Penamaan (Naming Conventions)

### 3.1 Basis Data (MySQL)
- Nama tabel: `snake_case` diawali dengan prefiks `ldap_` (contoh: `ldap_users`, `ldap_groups`, `ldap_settings`, `ldap_logs`).
- Kunci utama: `id` (INT UNSIGNED AUTO_INCREMENT).
- Kolom timestamp: `created_at` dan `updated_at` (DATETIME).
- Kunci asing: `{nama_entitas}_id` (contoh: `user_id`, `group_id`).

### 3.2 Kode PHP (Web Admin)
- Variabel & Fungsi: `camelCase` atau `snake_case` yang konsisten (contoh: `getUserById()`, `sanitizeInput()`).
- File tampilan (views): `kebab-case.php` atau `snake_case.php` (contoh: `user-list.php`, `settings.php`).
- File konfigurasi: `config.php`, `database.php`.

### 3.3 Kode Python (Core Daemon)
- Nama File & Modul: `snake_case.py` (contoh: `server.py`, `auth_handler.py`).
- Nama Kelas: `PascalCase` (contoh: `LdapServer`, `DatabaseManager`, `BerDecoder`).
- Nama Fungsi & Metode: `snake_case` (contoh: `handle_bind()`, `verify_password()`).
- Konstanta: `UPPER_SNAKE_CASE` (contoh: `DEFAULT_PORT`, `RESULT_SUCCESS`).

---

## 4. Standar Tampilan Antarmuka (UI/UX Guidelines)

1. **Desain Modern & Responsif**:
   - Menggunakan Tailwind CSS untuk utilitas tata letak, warna harmonis (Slate/Indigo/Emerald), dan tipografi bersih (Inter/System sans).
   - Menyediakan visual status yang jelas (badge hijau untuk server berjalan, merah untuk mati, kuning untuk peringatan).
2. **Umpan Balik Pengguna (Feedback & Alerts)**:
   - Setiap aksi (tambah user, ganti password, simpan konfigurasi) harus memberikan notifikasi *toast* atau *alert banner* yang jelas (sukses/gagal).
3. **Empty States**:
   - Tabel tanpa data harus menampilkan pesan ramah dan tombol aksi pembuatan data baru (*Call to Action*).
