# Arsitektur Sistem (Architecture Document) - Custom LDAP Server

Dokumen ini menguraikan arsitektur teknis, desain modul, alur data protokol, dan topologi jaringan dari **Custom LDAP Server**.

---

## 1. Diagram Arsitektur Tingkat Tinggi (High-Level Architecture)

```mermaid
graph TB
    subgraph "Clients Layer"
        PGINA["pGina (Windows Auth Client)"]
        WEBAPP["Aplikasi Web (PHP, Laravel, Nextcloud)"]
        CLI["LDAP CLI (ldapsearch, ldapwhoami)"]
    end

    subgraph "Network & Protocol Layer (Port 389 / 1389)"
        TCP_SOCK["TCP Socket Server"]
        BER_DEC["ASN.1 / BER Protocol Decoder"]
        BER_ENC["ASN.1 / BER Protocol Encoder"]
    end

    subgraph "LDAP Core Server Daemon (Python)"
        ROUTER["LDAP Message Dispatcher"]
        BIND_H["Bind Handler (Auth)"]
        SEARCH_H["Search Handler (Query & Filter Engine)"]
        UNBIND_H["Unbind / Abandon Handler"]
        AUTH_ENG["Password Verification Engine (Bcrypt / SSHA)"]
        LOGGER["Audit Logger Service"]
    end

    subgraph "Storage Layer"
        MYSQL[("MySQL Database (XAMPP)\n`ldap_server_db`")]
    end

    subgraph "Management & Presentation Layer (Port 80 / 443)"
        APACHE["Apache Web Server (XAMPP)"]
        PHP_CORE["PHP Native Application Engine"]
        TAILWIND["Tailwind CSS + UI Components"]
        ADMIN["System Administrator Browser"]
    end

    %% Client flows
    PGINA -->|TCP Connection| TCP_SOCK
    WEBAPP -->|LDAP Protocol| TCP_SOCK
    CLI -->|LDAP Protocol| TCP_SOCK

    %% Protocol Engine flows
    TCP_SOCK <--> BER_DEC
    BER_DEC --> ROUTER
    ROUTER --> BIND_H
    ROUTER --> SEARCH_H
    ROUTER --> UNBIND_H

    %% Handlers to DB & Engine
    BIND_H --> AUTH_ENG
    AUTH_ENG --> MYSQL
    SEARCH_H --> MYSQL
    LOGGER --> MYSQL
    BIND_H --> LOGGER
    SEARCH_H --> LOGGER

    ROUTER --> BER_ENC
    BER_ENC --> TCP_SOCK

    %% Web Admin flows
    ADMIN <--> TAILWIND
    TAILWIND <--> APACHE
    APACHE <--> PHP_CORE
    PHP_CORE <--> MYSQL
```

---

## 2. Rincian Lapisan Arsitektur (Layer Breakdown)

### 2.1 Protocol & Network Layer (TCP Listener)
- **Protokol Dasar**: TCP RFC 4511.
- **Port Default**: `389` (LDAP standar) dan opsi port fallback `1389` / `3890`.
- **Enkoding Pesan**: **ASN.1** (*Abstract Syntax Notation One*) menggunakan aturan **BER** (*Basic Encoding Rules*).
- **Struktur Pesan LDAP**:
  ```
  LDAPMessage ::= SEQUENCE {
      messageID       MessageID,
      protocolOp      CHOICE {
          bindRequest         BindRequest,
          bindResponse        BindResponse,
          unbindRequest       UnbindRequest,
          searchRequest       SearchRequest,
          searchResEntry      SearchResultEntry,
          searchResDone       SearchResultDone,
          ...
      },
      controls        [0] Controls OPTIONAL
  }
  ```

---

### 2.2 Core Message Dispatcher & Handlers

```mermaid
sequenceDiagram
    autonumber
    actor Client as Client (pGina / Web App)
    participant Daemon as LDAP Daemon (Python)
    participant DB as MySQL Database

    Note over Client,Daemon: Fase 1: Bind (Service Account / Anonymous)
    Client->>Daemon: BindRequest (version=3, dn="cn=admin,dc=example,dc=local", pw="***")
    Daemon->>DB: Query Admin/Service Credentials
    DB-->>Daemon: Return Password Hash & Status
    Daemon->>Daemon: Verifikasi Password Hash
    Daemon-->>Client: BindResponse (resultCode: 0 / success)

    Note over Client,Daemon: Fase 2: Search (Mencari User)
    Client->>Daemon: SearchRequest (base="dc=example,dc=local", filter="(uid=budi)")
    Daemon->>DB: SELECT * FROM ldap_users WHERE username='budi' AND status='active'
    DB-->>Daemon: Return Data User (uid, cn, mail, password_hash, dll)
    Daemon-->>Client: SearchResultEntry (dn="uid=budi,ou=users,dc=example,dc=local", attrs)
    Daemon-->>Client: SearchResultDone (resultCode: 0 / success)

    Note over Client,Daemon: Fase 3: User Authentication Bind
    Client->>Daemon: BindRequest (dn="uid=budi,ou=users,dc=example,dc=local", pw="user_pwd")
    Daemon->>DB: Query User & Password Hash
    DB-->>Daemon: Return Password Hash
    Daemon->>Daemon: Verify Bcrypt/SSHA(user_pwd, password_hash)
    Daemon->>DB: INSERT INTO ldap_logs (user, ip, status="SUCCESS")
    Daemon-->>Client: BindResponse (resultCode: 0 / success)

    Note over Client,Daemon: Fase 4: Selesai
    Client->>Daemon: UnbindRequest
    Daemon->>Daemon: Tutup Sesi TCP Socket
```

---

### 2.3 Directory Information Tree (DIT) Mapping

Struktur DIT LDAP yang diproyeksikan secara virtual dari database relasional:

```
dc=example,dc=local  (Base DN / Root)
│
├── cn=admin  (Root / Service Bind Account)
│
├── ou=users  (Organizational Unit: Pengguna)
│   ├── uid=budi,ou=users,dc=example,dc=local
│   ├── uid=siti,ou=users,dc=example,dc=local
│   └── uid=ahmad,ou=users,dc=example,dc=local
│
└── ou=groups (Organizational Unit: Kelompok/Role)
    ├── cn=administrators,ou=groups,dc=example,dc=local
    ├── cn=staff,ou=groups,dc=example,dc=local
    └── cn=developers,ou=groups,dc=example,dc=local
```

---

### 2.4 Storage Layer (MySQL Relational Schema)
Penyimpanan menggunakan MySQL/MariaDB yang sudah ada di XAMPP:
- **Pemisahan Logis**: Tabel dipisah secara relasional untuk performa kueri cepat dengan indeks pada `username`, `email`, dan `status`.
- **Virtual DIT Projection**: Daemon secara otomatis menyusun Distinguished Name (`DN`) berdasarkan konfigurasi `base_dn` dan nama entitas.

---

### 2.5 Presentation Layer (Web Admin Dashboard)
- **Teknologi**: PHP 8.x Native (OOP & Procedural murni tanpa framework berat) + Tailwind CSS (Modern UI via CDN & Custom Stylesheet).
- **Keamanan Web**:
  - Session-based Authentication dengan token CSRF pada setiap form mutating (POST/PUT/DELETE).
  - Sanitasi input dan Output Encoding untuk mencegah XSS.
  - PDO Prepared Statements untuk mencegah SQL Injection.
  - Middleware otorisasi di setiap route admin.
