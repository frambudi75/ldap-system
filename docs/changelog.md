# Catatan Perubahan (Changelog)

Semua perubahan dan perkembangan penting pada proyek **Custom LDAP Server & Web Management System** akan dicatat dalam dokumen ini. Format pencatatan mengacu pada [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) dan menganut [Semantic Versioning](https://semver.org/).

---

## [Unreleased] - Perencanaan Mendatang
### Added
- Dukungan enkripsi **LDAPS (LDAP over SSL/TLS Port 636)** dengan sertifikat X.509.
- Modul sinkronisasi berkala (Import/Export LDIF).
- Dukungan Active Directory Object Extension (misal atribut `userAccountControl`, `sAMAccountType`).
- Dukungan Multi-Factor Authentication (MFA / TOTP) pada Web Admin Dashboard.

---

## [1.0.0] - 2026-08-15 (Initial Design & Specification)
### Added
- **Dokumentasi Proyek Lengkap**:
  - `docs/prd.md`: Product Requirements Document dan spesifikasi kebutuhan fungsional/non-fungsional.
  - `docs/architecture.md`: Diagram arsitektur sistem tingkat tinggi, diagram sekuens LDAP, DIT mapping, dan detail modul.
  - `docs/rules.md`: Pedoman pengembangan, standar keamanan, konvensi penamaan kode dan database.
  - `docs/database.md`: Skema database MySQL (`ldap_server_db`), Entity Relationship Diagram (ERD), kamus data, dan seed data default.
  - `docs/api.md`: Spesifikasi protokol LDAP (RFC 4511), internal Web Admin JSON API, dan contoh kode integrasi client (pGina, PHP, Laravel).
  - `docs/ui-ux.md`: Panduan desain antarmuka, palet warna Tailwind CSS, tipografi, dan komponen dashboard.
  - `docs/deployment.md`: Petunjuk instalasi, panduan XAMPP, konfigurasi firewall Windows, troubleshooting, dan Windows Service setup.
  - `docs/changelog.md`: Riwayat perubahan dan roadmap versi sistem.
- **Arsitektur Hybrid**:
  - Core LDAP Daemon berbasis Python TCP Socket (RFC 4511 ASN.1 / BER).
  - Web Admin Dashboard berbasis PHP Native + Tailwind CSS.
  - Database terpusat berbasis MySQL / MariaDB XAMPP.
