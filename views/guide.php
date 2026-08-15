<?php
/**
 * View: Universal LDAP Integration Manual & Config Generator
 * Anti-AI-Slop Tactical Edition
 */
$baseDn = get_base_dn();
$adminDn = get_admin_bind_dn();
$ldapPort = get_ldap_port();
$serverIp = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
if ($serverIp === '::1') $serverIp = '127.0.0.1';
?>

<main class="flex-1 bg-zinc-950 p-6 md:p-8 overflow-y-auto bg-grid-pattern">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-6 border-b border-zinc-850">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h1 class="text-xl font-extrabold text-white tracking-tight">Universal LDAP Integration Hub</h1>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-850 text-zinc-300 border border-zinc-750">UNIVERSAL RFC 4511</span>
            </div>
            <p class="text-xs text-zinc-400">Panduan standar dan generator konfigurasi untuk menghubungkan LDAP server ini ke aplikasi atau sistem apa pun</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-3 py-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-850 border border-zinc-750 text-xs font-mono font-bold text-zinc-300 transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print / PDF
            </button>
        </div>
    </div>

    <!-- Section 1: The Universal Parameter Matrix (Cheat Sheet) -->
    <div class="tactile-card rounded-2xl p-5 mb-6 shadow-xl">
        <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono mb-4 flex items-center justify-between">
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                1. Universal LDAP Parameter Matrix
            </span>
            <span class="text-[10px] text-zinc-500 font-normal">Gunakan nilai berikut di form pengaturan aplikasi pihak ketiga mana pun</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs font-mono">
            <!-- Item 1 -->
            <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-850 relative group">
                <div class="text-[10px] font-sans font-bold text-zinc-500 uppercase tracking-wider mb-1">Server Host / URL</div>
                <div class="text-emerald-400 font-bold text-sm select-all">ldap://<?= $serverIp ?>:<?= $ldapPort ?></div>
                <div class="text-[10px] text-zinc-500 mt-1 font-sans">Port: <code><?= $ldapPort ?></code> (TCP plain)</div>
            </div>

            <!-- Item 2 -->
            <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-850">
                <div class="text-[10px] font-sans font-bold text-zinc-500 uppercase tracking-wider mb-1">Base DN (Root Search)</div>
                <div class="text-white font-bold text-sm select-all"><?= htmlspecialchars($baseDn) ?></div>
                <div class="text-[10px] text-zinc-500 mt-1 font-sans">Akar hierarki direktori</div>
            </div>

            <!-- Item 3 -->
            <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-850">
                <div class="text-[10px] font-sans font-bold text-zinc-500 uppercase tracking-wider mb-1">Admin / Bind DN</div>
                <div class="text-engine-400 font-bold text-sm select-all truncate" title="<?= htmlspecialchars($adminDn) ?>"><?= htmlspecialchars($adminDn) ?></div>
                <div class="text-[10px] text-zinc-500 mt-1 font-sans">Akun pencari (Service Account)</div>
            </div>

            <!-- Item 4 -->
            <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-850">
                <div class="text-[10px] font-sans font-bold text-zinc-500 uppercase tracking-wider mb-1">Bind Password</div>
                <div class="text-amber-400 font-bold text-sm select-all">admin123</div>
                <div class="text-[10px] text-zinc-500 mt-1 font-sans">Password admin bawaan</div>
            </div>

            <!-- Item 5 -->
            <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-850">
                <div class="text-[10px] font-sans font-bold text-zinc-500 uppercase tracking-wider mb-1">User Search Base (OU)</div>
                <div class="text-zinc-200 font-bold text-sm select-all truncate">ou=users,<?= htmlspecialchars($baseDn) ?></div>
                <div class="text-[10px] text-zinc-500 mt-1 font-sans">Tempat akun user disimpan</div>
            </div>

            <!-- Item 6 -->
            <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-850">
                <div class="text-[10px] font-sans font-bold text-zinc-500 uppercase tracking-wider mb-1">User Search Filter</div>
                <div class="text-purple-300 font-bold text-sm select-all">(&(objectClass=inetOrgPerson)(uid=%s))</div>
                <div class="text-[10px] text-zinc-500 mt-1 font-sans">Alias: <code>(sAMAccountName=%s)</code></div>
            </div>
        </div>
    </div>

    <!-- Section 2: Universal Attribute Mapping Standard -->
    <div class="tactile-card rounded-2xl p-5 mb-6 shadow-xl">
        <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono mb-4 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-engine-400"></span>
            <span>2. Standard Attribute Mapping Dictionary</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono text-zinc-300">
                <thead class="text-[10px] text-zinc-500 uppercase bg-zinc-950 border-b border-zinc-800">
                    <tr>
                        <th class="px-4 py-2.5">Field / Atribut Aplikasi</th>
                        <th class="px-4 py-2.5">Atribut Standar LDAP</th>
                        <th class="px-4 py-2.5">ObjectClass Terkait</th>
                        <th class="px-4 py-2.5">Contoh Data Balasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-850 text-[11px]">
                    <tr class="hover:bg-zinc-900/60 transition-colors">
                        <td class="px-4 py-2 font-sans font-semibold text-white">Username / Login ID</td>
                        <td class="px-4 py-2 text-engine-400 font-bold">uid, sAMAccountName</td>
                        <td class="px-4 py-2 text-zinc-500">inetOrgPerson, posixAccount</td>
                        <td class="px-4 py-2 text-zinc-400">budi</td>
                    </tr>
                    <tr class="hover:bg-zinc-900/60 transition-colors">
                        <td class="px-4 py-2 font-sans font-semibold text-white">Full Name / Display Name</td>
                        <td class="px-4 py-2 text-engine-400 font-bold">cn, displayName</td>
                        <td class="px-4 py-2 text-zinc-500">person, inetOrgPerson</td>
                        <td class="px-4 py-2 text-zinc-400">Budi Santoso</td>
                    </tr>
                    <tr class="hover:bg-zinc-900/60 transition-colors">
                        <td class="px-4 py-2 font-sans font-semibold text-white">First Name</td>
                        <td class="px-4 py-2 text-engine-400 font-bold">givenName</td>
                        <td class="px-4 py-2 text-zinc-500">inetOrgPerson</td>
                        <td class="px-4 py-2 text-zinc-400">Budi</td>
                    </tr>
                    <tr class="hover:bg-zinc-900/60 transition-colors">
                        <td class="px-4 py-2 font-sans font-semibold text-white">Last Name / Surname</td>
                        <td class="px-4 py-2 text-engine-400 font-bold">sn</td>
                        <td class="px-4 py-2 text-zinc-500">person</td>
                        <td class="px-4 py-2 text-zinc-400">Santoso</td>
                    </tr>
                    <tr class="hover:bg-zinc-900/60 transition-colors">
                        <td class="px-4 py-2 font-sans font-semibold text-white">Email Address</td>
                        <td class="px-4 py-2 text-engine-400 font-bold">mail</td>
                        <td class="px-4 py-2 text-zinc-500">inetOrgPerson</td>
                        <td class="px-4 py-2 text-zinc-400">budi@internal.net</td>
                    </tr>
                    <tr class="hover:bg-zinc-900/60 transition-colors">
                        <td class="px-4 py-2 font-sans font-semibold text-white">Group Membership (Role)</td>
                        <td class="px-4 py-2 text-purple-400 font-bold">memberOf</td>
                        <td class="px-4 py-2 text-zinc-500">groupOfNames</td>
                        <td class="px-4 py-2 text-zinc-400">cn=Developers,ou=groups,<?= htmlspecialchars($baseDn) ?></td>
                    </tr>
                    <tr class="hover:bg-zinc-900/60 transition-colors">
                        <td class="px-4 py-2 font-sans font-semibold text-white">Telephone / Handphone</td>
                        <td class="px-4 py-2 text-engine-400 font-bold">telephoneNumber</td>
                        <td class="px-4 py-2 text-zinc-500">inetOrgPerson</td>
                        <td class="px-4 py-2 text-zinc-400">+628123456789</td>
                    </tr>
                    <tr class="hover:bg-zinc-900/60 transition-colors">
                        <td class="px-4 py-2 font-sans font-semibold text-white">Department</td>
                        <td class="px-4 py-2 text-engine-400 font-bold">departmentNumber</td>
                        <td class="px-4 py-2 text-zinc-500">inetOrgPerson</td>
                        <td class="px-4 py-2 text-zinc-400">Software Engineering</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Universal Code & Config Generator -->
    <div class="tactile-card rounded-2xl p-5 mb-6 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                <span>3. Code & Config Generator by Target Stack</span>
            </h2>
            <div class="flex items-center gap-2">
                <span class="text-xs text-zinc-500 font-mono">Pilih Target:</span>
                <select id="target-stack" onchange="renderCodeSnippet()" class="px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500 cursor-pointer">
                    <option value="pgina">pGina (Windows Workstation Login)</option>
                    <option value="php">PHP Native (ldap_connect)</option>
                    <option value="laravel">Laravel Framework (.env + config)</option>
                    <option value="python">Python (ldap3 / Django / FastAPI)</option>
                    <option value="nodejs">Node.js (ldapjs / Express)</option>
                    <option value="golang">Go (Golang go-ldap)</option>
                    <option value="nextcloud">Nextcloud / ownCloud</option>
                    <option value="grafana">Grafana / Portainer (ldap.toml)</option>
                    <option value="gitlab">GitLab / Jenkins (YAML Config)</option>
                    <option value="mikrotik">MikroTik RouterOS / Hotspot</option>
                    <option value="cli">CLI Linux / MacOS (ldapsearch 1-Liner)</option>
                </select>
            </div>
        </div>

        <div class="relative">
            <div class="absolute right-3 top-3 z-10">
                <button onclick="copyCode()" id="btn-copy-code" class="px-2.5 py-1 rounded-lg bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-[11px] font-mono text-zinc-300 transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    <span id="copy-text">Copy Code</span>
                </button>
            </div>
            <pre id="code-snippet-box" class="p-4 rounded-xl bg-zinc-950 border border-zinc-850 text-xs font-mono text-zinc-300 overflow-x-auto min-h-[160px]"></pre>
        </div>
    </div>

    <!-- Section 4: Universal 3-Step Integration Flowchart -->
    <div class="tactile-card rounded-2xl p-5 shadow-xl">
        <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono mb-4 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            <span>4. Universal 3-Step LDAP Authentication Lifecycle</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div class="p-4 rounded-xl bg-zinc-950 border border-zinc-850">
                <div class="w-6 h-6 rounded-lg bg-engine-500/10 text-engine-400 flex items-center justify-center font-mono font-bold text-xs mb-2">1</div>
                <div class="font-bold text-white mb-1">Step 1: Admin Bind (Service Auth)</div>
                <p class="text-zinc-400 text-[11px]">
                    Aplikasi klien menghubungkan socket TCP ke port <code><?= $ldapPort ?></code> dan melakukan Bind menggunakan service account <code><?= htmlspecialchars($adminDn) ?></code>.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-zinc-950 border border-zinc-850">
                <div class="w-6 h-6 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center font-mono font-bold text-xs mb-2">2</div>
                <div class="font-bold text-white mb-1">Step 2: Search Entity DN</div>
                <p class="text-zinc-400 text-[11px]">
                    Aplikasi mengirim <code>SearchRequest</code> dengan filter <code>(uid=input_user)</code> untuk mendapatkan objek DN lengkap target (misal: <code>uid=budi,ou=users,<?= htmlspecialchars($baseDn) ?></code>).
                </p>
            </div>

            <div class="p-4 rounded-xl bg-zinc-950 border border-zinc-850">
                <div class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-mono font-bold text-xs mb-2">3</div>
                <div class="font-bold text-white mb-1">Step 3: User Credential Bind</div>
                <p class="text-zinc-400 text-[11px]">
                    Aplikasi melakukan Bind ulang dengan DN target + password yang diinputkan pengguna. Jika server mengembalikan <code>resultCode: 0</code>, autentikasi sukses.
                </p>
            </div>
        </div>
    </div>
</main>

<?php
$snippets = [
    'pgina' => "===============================================================\n" .
        "PANDUAN KONFIGURASI pGina (WINDOWS WORKSTATION LOGIN)\n" .
        "===============================================================\n" .
        "1. Buka pGina Configuration -> Tab 'Plugin Selection'\n" .
        "   -> Centang plugin: [x] LDAP Authentication\n" .
        "   -> Beri checklist pada: [x] Authentication  [x] Authorization  [x] Gateway\n\n" .
        "2. Klik Konfigurasi pada plugin LDAP:\n" .
        "   - LDAP Host / IP    : {$serverIp}\n" .
        "   - LDAP Port         : {$ldapPort}\n" .
        "   - Use SSL / TLS     : False (Disable)\n" .
        "   - Search DN         : {$adminDn}\n" .
        "   - Search Password   : admin123\n" .
        "   - Search Base       : ou=users,{$baseDn}\n" .
        "   - User Filter       : (&(objectClass=inetOrgPerson)(uid=%u))\n" .
        "   - Group Search Base : ou=groups,{$baseDn}\n" .
        "   - Group Filter      : (&(objectClass=posixGroup)(memberUid=%u))",

    'php' => "<?php\n" .
        "// ===============================================================\n" .
        "// UNIVERSAL PHP LDAP AUTHENTICATION SCRIPT\n" .
        "// ===============================================================\n" .
        "\$ldapHost = \"ldap://{$serverIp}:{$ldapPort}\";\n" .
        "\$conn = ldap_connect(\$ldapHost);\n\n" .
        "ldap_set_option(\$conn, LDAP_OPT_PROTOCOL_VERSION, 3);\n" .
        "ldap_set_option(\$conn, LDAP_OPT_REFERRALS, 0);\n\n" .
        "\$username = \"budi\";\n" .
        "\$password = \"password123\";\n\n" .
        "// Step 1 & 2: Search user DN\n" .
        "ldap_bind(\$conn, \"{$adminDn}\", \"admin123\");\n" .
        "\$search = ldap_search(\$conn, \"{$baseDn}\", \"(uid={\$username})\");\n" .
        "\$info = ldap_get_entries(\$conn, \$search);\n\n" .
        "if (\$info['count'] > 0) {\n" .
        "    \$userDn = \$info[0]['dn'];\n" .
        "    \n" .
        "    // Step 3: Verifikasi User Password via Bind\n" .
        "    if (@ldap_bind(\$conn, \$userDn, \$password)) {\n" .
        "        echo \"✓ Login Sukses sebagai: \" . \$info[0]['cn'][0] . \" (\" . \$info[0]['mail'][0] . \")\";\n" .
        "    } else {\n" .
        "        echo \"❌ Password Salah!\";\n" .
        "    }\n" .
        "} else {\n" .
        "    echo \"❌ User Tidak Ditemukan!\";\n" .
        "}\n\n" .
        "ldap_unbind(\$conn);\n" .
        "?>",

    'laravel' => "# ===============================================================\n" .
        "# LARAVEL .env CONFIGURATION (LdapRecord Package)\n" .
        "# composer require directorytree/ldaprecord-laravel\n" .
        "# ===============================================================\n" .
        "LDAP_LOGGING=true\n" .
        "LDAP_CONNECTION=default\n" .
        "LDAP_HOST={$serverIp}\n" .
        "LDAP_PORT={$ldapPort}\n" .
        "LDAP_BASE_DN=\"{$baseDn}\"\n" .
        "LDAP_USERNAME=\"{$adminDn}\"\n" .
        "LDAP_PASSWORD=admin123\n" .
        "LDAP_USE_SSL=false\n" .
        "LDAP_USE_TLS=false\n" .
        "LDAP_TIMEOUT=5\n\n" .
        "# config/ldap.php User Model Mapping:\n" .
        "# 'locate_users_by' => 'uid',\n" .
        "# 'bind_users_by'   => 'distinguishedname',",

    'python' => "# ===============================================================\n" .
        "# PYTHON (ldap3) AUTHENTICATION SCRIPT\n" .
        "# pip install ldap3\n" .
        "# ===============================================================\n" .
        "from ldap3 import Server, Connection, ALL, SUBTREE\n\n" .
        "server = Server('{$serverIp}', port={$ldapPort}, get_info=ALL)\n\n" .
        "# 1. Search User DN via Admin Connection\n" .
        "admin_conn = Connection(server, '{$adminDn}', 'admin123', auto_bind=True)\n" .
        "admin_conn.search('{$baseDn}', '(uid=budi)', search_scope=SUBTREE, attributes=['cn', 'mail', 'memberOf'])\n\n" .
        "if len(admin_conn.entries) > 0:\n" .
        "    user_entry = admin_conn.entries[0]\n" .
        "    user_dn = user_entry.entry_dn\n" .
        "    \n" .
        "    # 2. Verify User Password\n" .
        "    user_conn = Connection(server, user_dn, 'password123')\n" .
        "    if user_conn.bind():\n" .
        "        print(f\"✓ Login Berhasil: {user_entry.cn} ({user_entry.mail})\")\n" .
        "    else:\n" .
        "        print(\"❌ Password Salah!\")\n" .
        "else:\n" .
        "    print(\"❌ User tidak ditemukan!\")",

    'nodejs' => "// ===============================================================\n" .
        "// NODE.JS (ldapjs) AUTHENTICATION SCRIPT\n" .
        "// npm install ldapjs\n" .
        "// ===============================================================\n" .
        "const ldap = require('ldapjs');\n\n" .
        "const client = ldap.createClient({\n" .
        "  url: 'ldap://{$serverIp}:{$ldapPort}'\n" .
        "});\n\n" .
        "const username = 'budi';\n" .
        "const password = 'password123';\n\n" .
        "// Step 1: Admin bind to search DN\n" .
        "client.bind('{$adminDn}', 'admin123', (err) => {\n" .
        "  if (err) return console.error('Admin bind failed:', err);\n\n" .
        "  client.search('{$baseDn}', { filter: `(uid=\${username})`, scope: 'sub' }, (err, res) => {\n" .
        "    let userDn = null;\n" .
        "    res.on('searchEntry', (entry) => { userDn = entry.objectName; });\n" .
        "    res.on('end', () => {\n" .
        "      if (!userDn) return console.log('User not found');\n" .
        "      \n" .
        "      // Step 2: Auth user\n" .
        "      const userClient = ldap.createClient({ url: 'ldap://{$serverIp}:{$ldapPort}' });\n" .
        "      userClient.bind(userDn, password, (authErr) => {\n" .
        "        if (!authErr) console.log('✓ Login Berhasil sebagai:', userDn);\n" .
        "        else console.log('❌ Password salah');\n" .
        "      });\n" .
        "    });\n" .
        "  });\n" .
        "});",

    'golang' => "// ===============================================================\n" .
        "// GO (GOLANG) LDAP AUTHENTICATION\n" .
        "// go get github.com/go-ldap/ldap/v3\n" .
        "// ===============================================================\n" .
        "package main\n\n" .
        "import (\n" .
        "\t\"fmt\"\n" .
        "\t\"log\"\n" .
        "\t\"github.com/go-ldap/ldap/v3\"\n" .
        ")\n\n" .
        "func main() {\n" .
        "\tl, err := ldap.DialURL(\"ldap://{$serverIp}:{$ldapPort}\")\n" .
        "\tif err != nil { log.Fatal(err) }\n" .
        "\tdefer l.Close()\n\n" .
        "\t// 1. Admin Bind & Search\n" .
        "\terr = l.Bind(\"{$adminDn}\", \"admin123\")\n" .
        "\tif err != nil { log.Fatal(\"Admin bind failed:\", err) }\n\n" .
        "\tsr, err := l.Search(ldap.NewSearchRequest(\n" .
        "\t\t\"{$baseDn}\",\n" .
        "\t\tldap.ScopeWholeSubtree, ldap.NeverDerefAliases, 0, 0, false,\n" .
        "\t\t\"(&(objectClass=inetOrgPerson)(uid=budi))\",\n" .
        "\t\t[]string{\"dn\", \"cn\", \"mail\"},\n" .
        "\t\tnil,\n" .
        "\t))\n" .
        "\tif err != nil || len(sr.Entries) == 0 {\n" .
        "\t\tlog.Fatal(\"User not found\")\n" .
        "\t}\n\n" .
        "\tuserDN := sr.Entries[0].DN\n\n" .
        "\t// 2. User Bind Test\n" .
        "\terr = l.Bind(userDN, \"password123\")\n" .
        "\tif err == nil {\n" .
        "\t\tfmt.Printf(\"✓ Login Berhasil: %s\\n\", sr.Entries[0].GetAttributeValue(\"cn\"))\n" .
        "\t} else {\n" .
        "\t\tfmt.Println(\"❌ Password salah\")\n" .
        "\t}\n" .
        "}",

    'nextcloud' => "===============================================================\n" .
        "PENGATURAN NEXTCLOUD / OWNCLOUD (LDAP Backend App)\n" .
        "===============================================================\n" .
        "Tab 1: Server\n" .
        "- Host          : {$serverIp}\n" .
        "- Port          : {$ldapPort}\n" .
        "- User DN       : {$adminDn}\n" .
        "- Password      : admin123\n" .
        "- Base DN       : {$baseDn}\n\n" .
        "Tab 2: Users\n" .
        "- objectClass   : inetOrgPerson\n\n" .
        "Tab 3: Login Attributes\n" .
        "- LDAP Username : uid\n" .
        "- (Atau centang opsi: uid, mail)\n\n" .
        "Tab 4: Groups\n" .
        "- objectClass   : posixGroup",

    'grafana' => "###############################################################\n" .
        "# GRAFANA / PORTAINER (ldap.toml)\n" .
        "###############################################################\n" .
        "[[servers]]\n" .
        "host = \"{$serverIp}\"\n" .
        "port = {$ldapPort}\n" .
        "use_ssl = false\n" .
        "start_tls = false\n" .
        "bind_dn = \"{$adminDn}\"\n" .
        "bind_password = \"admin123\"\n" .
        "search_filter = \"(uid=%s)\"\n" .
        "search_base_dns = [\"ou=users,{$baseDn}\"]\n\n" .
        "[servers.attributes]\n" .
        "name = \"givenName\"\n" .
        "surname = \"sn\"\n" .
        "username = \"uid\"\n" .
        "member_of = \"memberOf\"\n" .
        "email = \"mail\"\n\n" .
        "[[servers.group_mappings]]\n" .
        "group_dn = \"cn=Administrators,ou=groups,{$baseDn}\"\n" .
        "org_role = \"Admin\"",

    'gitlab' => "# ===============================================================\n" .
        "# GITLAB (/etc/gitlab/gitlab.rb)\n" .
        "# ===============================================================\n" .
        "gitlab_rails['ldap_enabled'] = true\n" .
        "gitlab_rails['ldap_servers'] = {\n" .
        "  'main' => {\n" .
        "    'label' => 'Internal LDAP',\n" .
        "    'host' => '{$serverIp}',\n" .
        "    'port' => {$ldapPort},\n" .
        "    'uid' => 'uid',\n" .
        "    'bind_dn' => '{$adminDn}',\n" .
        "    'password' => 'admin123',\n" .
        "    'encryption' => 'plain',\n" .
        "    'base' => 'ou=users,{$baseDn}',\n" .
        "    'user_filter' => '(objectClass=inetOrgPerson)'\n" .
        "  }\n" .
        "}",

    'mikrotik' => "###############################################################\n" .
        "# MIKROTIK ROUTEROS (User Manager / Hotspot / PPP LDAP)\n" .
        "###############################################################\n" .
        "/user-manager/router\n" .
        "add address={$serverIp} port={$ldapPort}\n\n" .
        "/radius\n" .
        "# Jika bridging via RADIUS-to-LDAP atau direct AAA\n" .
        "# Base DN    : ou=users,{$baseDn}\n" .
        "# Bind DN    : {$adminDn}\n" .
        "# Secret     : admin123\n" .
        "# Attribute  : uid",

    'cli' => "# ===============================================================\n" .
        "# COMMAND LINE TESTING (Linux / macOS / Windows bash)\n" .
        "# ===============================================================\n\n" .
        "# 1. Anonymous / Admin Search Query:\n" .
        "ldapsearch -x -H ldap://{$serverIp}:{$ldapPort} -b \"{$baseDn}\" \"(uid=budi)\"\n\n" .
        "# 2. Authenticated Search with Admin Bind:\n" .
        "ldapsearch -x -H ldap://{$serverIp}:{$ldapPort} -D \"{$adminDn}\" -w admin123 -b \"{$baseDn}\" \"(objectClass=*)\"\n\n" .
        "# 3. Test Direct User Password Bind:\n" .
        "ldapwhoami -x -H ldap://{$serverIp}:{$ldapPort} -D \"uid=budi,ou=users,{$baseDn}\" -w password123"
];
?>

<script>
const snippets = <?= json_encode($snippets, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

function renderCodeSnippet() {
    const val = document.getElementById('target-stack').value;
    const box = document.getElementById('code-snippet-box');
    box.textContent = snippets[val] || '';
}

function copyCode() {
    const text = document.getElementById('code-snippet-box').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copy-text');
        btn.innerText = 'Copied!';
        setTimeout(() => { btn.innerText = 'Copy Code'; }, 2000);
    });
}

// Initial render
renderCodeSnippet();
</script>
