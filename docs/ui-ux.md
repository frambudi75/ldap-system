# Panduan Desain Antarmuka & Pengalaman Pengguna (UI/UX Specification)

Dokumen ini mendefinisikan sistem desain, palet warna, tipografi, struktur tata letak, dan interaksi antarmuka untuk **Web Admin Dashboard**.

---

## 1. Prinsip Desain (Design Principles)

1. **Modern, Bersih & Profesional**:
   - Tampilan *enterprise-grade* dengan sentuhan aksen modern (Dark/Slate background elegan, aksen Indigo & Emerald).
   - Menghindari tampilan datar konvensional dengan memanfaatkan *glassmorphism*, bayangan halus (*soft elevation*), dan border bertekstur tipis.
2. **Keterbacaan & Hierarki Visual**:
   - Informasi terpenting (status server, jumlah user aktif, riwayat login terakhir) langsung terlihat di layar pertama (*above the fold*).
3. **Responsif & Adaptif**:
   - Nyaman digunakan baik pada layar Desktop resolusi tinggi (1080p / 4K), laptop, maupun tablet.

---

## 2. Palet Warna & Tema (Color Palette)

| Kategori | Nama Warna | Kode Hex / Tailwind | Penggunaan |
| :--- | :--- | :--- | :--- |
| **Primary** | Indigo Vibrant | `#4F46E5` (`indigo-600`) | Tombol utama, link aktif, highlight grafis |
| **Primary Hover**| Indigo Dark | `#4338CA` (`indigo-700`) | Hover state tombol utama |
| **Background Dark**| Slate 900 | `#0F172A` (`slate-900`) | Background aplikasi & sidebar |
| **Surface Dark** | Slate 800 | `#1E293B` (`slate-800`) | Kartu, modal, tabel container |
| **Card Border** | Slate 700 | `#334155` (`slate-700`) | Garis pembatas kartu & input |
| **Success** | Emerald 500 | `#10B981` (`emerald-500`)| Status Server Online, Login Sukses |
| **Danger / Error**| Rose 500 | `#F43F5E` (`rose-500`) | Status Server Offline, Login Gagal, Tombol Hapus |
| **Warning** | Amber 500 | `#F59E0B` (`amber-500`) | Akun Suspended, Peringatan Port |
| **Text Primary** | White / Slate 50| `#F8FAFC` (`slate-50`) | Judul, label utama |
| **Text Secondary**| Slate 400 | `#94A3B8` (`slate-400`) | Deskripsi, timestamp, placeholder |

---

## 3. Tipografi (Typography)
- **Font Utama**: `Inter, system-ui, -apple-system, sans-serif`.
- **Font Monospace**: `JetBrains Mono, Fira Code, Consolas, monospace` (untuk DN, filter LDAP, hash password, dan log query).

---

## 4. Struktur Tata Letak (Layout Architecture)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  SIDEBAR (Kiri - 260px)        │  MAIN CONTENT (Kanan - Flex 1)             │
│                                │                                             │
│  [Logo] Custom LDAP Server     │  [Top Header]                               │
│  ------------------------      │   - Status Badge: 🟢 LDAP Online (Port 389)  │
│  📌 Dashboard                  │   - Quick Action: + Tambah Pengguna         │
│  👥 Pengguna (Users)           │   - Admin Profile & Logout                  │
│  📁 Kelompok (Groups)          │  -----------------------------------------  │
│  ⚙️ Pengaturan Direktori       │  [Dynamic Page View Content]                │
│  🧪 Web LDAP Tester            │   - Metric Cards (Total User, Groups, Logs) │
│  📜 Audit Logs                 │   - Interactive Data Tables                 │
│  📖 Panduan Integrasi          │   - Live Stream Activity Feed               │
│                                │                                             │
│  ------------------------      │  -----------------------------------------  │
│  Daemon Status: RUNNING        │  [Footer] Versi 1.0.0 | Built with PHP Native│
└────────────────────────────────┴─────────────────────────────────────────────┘
```

---

## 5. Komponen Kunci (Key Components)

### 5.1 Kartu Metrik (Metric Cards)
- Menampilkan metrik penting dengan ikon latar berpendar (*glow accent*).
- Menampilkan persentase atau perbandingan status aktif vs nonaktif.

### 5.2 Tabel Data Interaktif (Data Tables)
- Dilengkapi fitur pencarian cepat (*instant search filter*).
- Status pill / badge warna-warni untuk status pengguna (`Active` = Hijau, `Disabled` = Abu/Merah).
- Tombol aksi cepat: *Edit*, *Reset Password*, *Toggle Status*, *Hapus*.

### 5.3 Web LDAP Tester Widget
- Form input terpadu untuk Host, Port, Bind DN, Password, dan Filter.
- Output visual respons berupa JSON biner berwarna dan status badge sukses/gagal yang jelas.

### 5.4 Live Log Stream Feed
- Auto-refresh setiap 3-5 detik tanpa perlu reload halaman penuh.
- Indikator IP, waktu, operasi (BIND / SEARCH), dan hasil kode status.

---

## 6. Pola Interaksi & Notifikasi (Micro-Interactions)
- **Modal Dialog**: Animasi *fade-in* halus untuk form tambah pengguna, edit user, dan reset password.
- **Copy-to-Clipboard**: Tombol salin 1-klik untuk Distinguished Name (DN) dan filter LDAP pada panduan integrasi.
- **Alert Toasts**: Pesan melayang di pojok kanan atas untuk konfirmasi simpan, update, dan hapus data.
