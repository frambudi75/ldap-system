# Design System & Philosophy - Anti-AI-Slop Command Center

Dokumen ini menjelaskan pedoman visual, filosofi antarmuka, dan sistem token desain yang diterapkan pada **LDAP Engine Console**.

---

## 1. Filosofi Desain: Anti-AI-Slop & Precision Engineering

Antarmuka ini dirancang dengan standar alat infrastruktur kelas atas (*high-end developer & infrastructure tooling* seperti Vercel, Linear, Stripe, Raycast), menolak gaya template AI generik (warna biru/ungu neon tanpa struktur):

1. **High-Density Telemetry**: Data disajikan dengan kerapatan informasi yang efisien, tipografi monospace untuk objek teknis (DN, IP, Port, Hash), dan hierarki visual yang tegas.
2. **Tactile & Precision Surfaces**: Menggunakan palet Obsidian Zinc (`#07070a` - `#181820`), border mikroskopis berdefinisi tinggi (`#23232c`), dan efek bayangan lembut yang memberikan kedalaman fisik.
3. **Purposeful Accent Accents**: Aksen warna memiliki arti fungsional yang jelas:
   - 🟢 **Emerald (`#10B981`)**: Status protokol `0x00 SUCCESS`, daemon aktif, akun terverifikasi.
   - 🔵 **Electric Engine (`#3B82F6`)**: Navigasi utama, target DN, token query.
   - 🔴 **Rose (`#F43F5E`)**: Galat socket, akun dinonaktifkan, aksi destruktif.
   - 🟡 **Amber (`#F59E0B`)**: Reset password, peringatan kredensial.
4. **Grid Pattern & Monospace Accents**: Background bermotif grid halus (*subtle 32px grid*) dan label keyboard (*kbd*) yang memperkuat estetika terminal teknis.

---

## 2. Tipografi
- **Interface Body**: `Plus Jakarta Sans`, sans-serif (Geometric, modern, highly legible).
- **Code & Telemetry**: `JetBrains Mono`, monospace (Tabular figures, crisp coding font).

---

## 3. Komponen Utama
- **Split Telemetry Login Card**: Sisi kiri menampilkan status protokol aktif dan stream terminal paket LDAP, sisi kanan menampilkan box login yang taktil dengan toggle password dan shortcut key `Enter`.
- **Directory Topology Visualizer**: Visualisasi pohon direktori DIT virtual (Root DSE, ou=users, ou=groups).
- **Interactive Socket Tester**: Alat uji paket BER ASN.1 langsung di browser dengan output payload JSON dan badge latensi real-time.
