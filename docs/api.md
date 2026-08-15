# Dokumentasi API & Protokol Interface (API Specification)

Dokumen ini mendefinisikan antarmuka protokol LDAP (RFC 4511) yang didukung oleh Daemon Server serta Internal JSON API yang disediakan oleh Web Admin Dashboard.

---

## 1. Spesifikasi Protokol LDAP (RFC 4511)

### 1.1 Endpoint Koneksi
- **Host**: `127.0.0.1` / `localhost` / `[IP-Server-LAN]`
- **Port**: `389` (Standard) / `1389` (Alternative)
- **Protocol**: LDAP v3 (TCP Socket)
- **Default Base DN**: `dc=internal,dc=net`

---

### 1.2 Operasi Protokol yang Didukung

#### A. Bind Operation (`BindRequest` -> `BindResponse`)
Digunakan untuk autentikasi awal (kredensial admin/service) maupun autentikasi akhir kredensial pengguna.

1. **Anonymous Bind**:
   - `dn`: `""` (String kosong)
   - `password`: `""` (String kosong)
   - *Hasil*: `0 (success)` jika `allow_anonymous_bind = 1`, atau `48 (inappropriateAuthentication)` jika dinonaktifkan.

2. **Admin / Service Account Bind**:
   - `dn`: `cn=admin,dc=internal,dc=net`
   - `password`: `[admin_password]`
   - *Hasil*: `0 (success)` jika kredensial cocok, `49 (invalidCredentials)` jika salah.

3. **User Authentication Bind**:
   - `dn`: `uid=john,ou=users,dc=internal,dc=net` (atau format full email `john@internal.net`)
   - `password`: `[user_password]`
   - *Hasil*: `0 (success)` jika user aktif dan hash password cocok, `49 (invalidCredentials)` jika user tidak ditemukan, dinonaktifkan, atau password salah.

---

#### B. Search Operation (`SearchRequest` -> `SearchResultEntry` + `SearchResultDone`)
Digunakan untuk mencari entitas user atau grup dalam direktori.

- **Parameter Search**:
  - `baseObject`: misal `dc=internal,dc=net` atau `ou=users,dc=internal,dc=net`
  - `scope`:
    - `0` (`baseObject`): Mencari entitas spesifik pada base DN.
    - `1` (`singleLevel`): Mencari satu tingkat di bawah base DN.
    - `2` (`wholeSubtree`): Mencari seluruh cabang hierarki di bawah base DN.
  - `filter`: Ekspresi filter LDAP standar.

- **Filter yang Didukung**:
  | Filter Syntax | Penjelasan | Contoh Penggunaan |
  | :--- | :--- | :--- |
  | `(uid=...)` | Pencarian tepat username | `(uid=john)` |
  | `(mail=...)` | Pencarian email | `(mail=john@internal.net)` |
  | `(sAMAccountName=...)` | Alias username Windows | `(sAMAccountName=john)` |
  | `(cn=...)` | Pencarian nama lengkap | `(cn=John Doe)` |
  | `(objectClass=...)` | Filter kelas objek | `(objectClass=inetOrgPerson)` |
  | `(&(attr1=val1)(attr2=val2))` | Operasi logika AND | `(&(objectClass=inetOrgPerson)(uid=john))` |
  | `(\|(attr1=val1)(attr2=val2))` | Operasi logika OR | `(\|(uid=john)(mail=john@internal.net))` |

- **Atribut yang Dikembalikan (Attributes in `SearchResultEntry`)**:
  ```json
  {
    "dn": "uid=john,ou=users,dc=internal,dc=net",
    "objectClass": ["top", "person", "organizationalPerson", "inetOrgPerson", "posixAccount"],
    "uid": "john",
    "cn": "John Doe",
    "sn": "Doe",
    "givenName": "John",
    "mail": "john@internal.net",
    "telephoneNumber": "+628123456789",
    "departmentNumber": "Engineering",
    "ou": "users",
    "memberOf": ["cn=Developers,ou=groups,dc=internal,dc=net", "cn=Staff,ou=groups,dc=internal,dc=net"]
  }
  ```

---

## 2. Internal Web Dashboard JSON API

Web dashboard menyediakan endpoint internal untuk interaktivitas real-time (AJAX/Fetch):

### 2.1 Cek Status Server Daemon
- **Endpoint**: `GET /api/status.php`
- **Response**:
  ```json
  {
    "status": "online",
    "port": 389,
    "uptime_seconds": 3600,
    "active_connections": 2,
    "last_check": "2026-08-15 20:30:00"
  }
  ```

---

### 2.2 Web LDAP Tester
- **Endpoint**: `POST /api/test-ldap.php`
- **Payload**:
  ```json
  {
    "host": "127.0.0.1",
    "port": 389,
    "bind_dn": "uid=john,ou=users,dc=internal,dc=net",
    "password": "password123",
    "search_base": "dc=internal,dc=net",
    "search_filter": "(uid=john)"
  }
  ```
- **Response (Success)**:
  ```json
  {
    "success": true,
    "message": "Autentikasi Bind berhasil!",
    "search_results": [
      {
        "dn": "uid=john,ou=users,dc=internal,dc=net",
        "cn": "John Doe",
        "mail": "john@internal.net",
        "status": "active"
      }
    ]
  }
  ```

---

### 2.3 Real-Time Live Logs
- **Endpoint**: `GET /api/logs.php?limit=20`
- **Response**:
  ```json
  {
    "logs": [
      {
        "id": 102,
        "client_ip": "192.168.1.50",
        "operation": "BIND",
        "bind_dn": "uid=john,ou=users,dc=internal,dc=net",
        "status": "SUCCESS",
        "response_time_ms": 12,
        "created_at": "2026-08-15 20:31:10"
      }
    ]
  }
  ```

---

## 3. Contoh Integrasi Client

### 3.1 Integrasi PHP Native (`ldap_connect`)
```php
<?php
$ldap_host = "ldap://127.0.0.1:389";
$ldap_conn = ldap_connect($ldap_host);
ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

$user_dn  = "uid=john,ou=users,dc=internal,dc=net";
$password = "password123";

// Lakukan Bind
$bind = @ldap_bind($ldap_conn, $user_dn, $password);

if ($bind) {
    echo "Login LDAP Berhasil!\n";
    // Cari data profil
    $result = ldap_search($ldap_conn, "dc=internal,dc=net", "(uid=john)");
    $entries = ldap_get_entries($ldap_conn, $result);
    echo "Nama Lengkap: " . $entries[0]['cn'][0] . "\n";
    echo "Email: " . $entries[0]['mail'][0] . "\n";
} else {
    echo "Login LDAP Gagal: " . ldap_error($ldap_conn) . "\n";
}

ldap_unbind($ldap_conn);
```

### 3.2 Integrasi pGina (Windows Client)
- **Plugin**: LDAP Authentication Plugin
- **LDAP Server**: `192.168.1.100` (IP Server Anda), Port: `389`
- **Search DN**: `cn=admin,dc=internal,dc=net`
- **Search Password**: `admin123`
- **Search Base**: `ou=users,dc=internal,dc=net`
- **User Filter**: `(&(objectClass=inetOrgPerson)(uid=%u))`
