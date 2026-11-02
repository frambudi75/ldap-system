<?php
/**
 * View: Directory & Server Settings (Tactile Precision Edition)
 */
$pdo = Database::getConnection();

$settingsRows = $pdo->query("SELECT setting_key, setting_value FROM ldap_settings")->fetchAll();
$settings = [];
foreach ($settingsRows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$ldapPort = get_ldap_port();
$daemonStatus = check_ldap_daemon_status();
?>

<main class="flex-1 bg-zinc-950 p-6 md:p-8 overflow-y-auto bg-grid-pattern">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-6 border-b border-zinc-850">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h1 class="text-xl font-extrabold text-white tracking-tight">Directory Engine Configuration</h1>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-850 text-zinc-300 border border-zinc-750">SETTINGS</span>
            </div>
            <p class="text-xs text-zinc-400">Konfigurasi struktur pohon direktori (DIT), akun root bind, dan parameter soket</p>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/index.php?action=save_settings" class="space-y-5 max-w-4xl">
        <?= csrf_field() ?>

        <!-- Section 1: Direktori Dasar -->
        <div class="tactile-card rounded-2xl p-5 shadow-xl">
            <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-engine-400"></span>
                <span>1. Namespace & DIT Structure</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Base DN *</label>
                    <input type="text" name="base_dn" required value="<?= htmlspecialchars($settings['base_dn'] ?? 'dc=internal,dc=net') ?>"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                    <span class="block text-[10px] text-zinc-500 font-mono mt-1">contoh: <code>dc=internal,dc=net</code></span>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Organization Label</label>
                    <input type="text" name="org_name" value="<?= htmlspecialchars($settings['org_name'] ?? 'Internal Directory Service') ?>"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-white focus:outline-none focus:border-engine-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">User Organizational Unit (OU)</label>
                    <input type="text" name="user_ou" value="<?= htmlspecialchars($settings['user_ou'] ?? 'ou=users') ?>"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Group Organizational Unit (OU)</label>
                    <input type="text" name="group_ou" value="<?= htmlspecialchars($settings['group_ou'] ?? 'ou=groups') ?>"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                </div>
            </div>
        </div>

        <!-- Section 2: Autentikasi Admin -->
        <div class="tactile-card rounded-2xl p-5 shadow-xl">
            <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span>2. Service / Root Bind Credentials</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Admin Bind DN *</label>
                    <input type="text" name="admin_bind_dn" required value="<?= htmlspecialchars($settings['admin_bind_dn'] ?? 'cn=admin,dc=internal,dc=net') ?>"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Update Admin Password</label>
                    <input type="password" name="admin_password" placeholder="Kosongkan jika tidak diubah"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                </div>
            </div>
        </div>

        <!-- Section 3: Jaringan -->
        <div class="tactile-card rounded-2xl p-5 shadow-xl">
            <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>3. Socket Daemon & Security Policy</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">TCP Port Listener</label>
                    <input type="number" name="ldap_port" required value="<?= htmlspecialchars($settings['ldap_port'] ?? '389') ?>"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                </div>

                <div>
                    <label class="flex items-center gap-3 p-3 rounded-xl bg-zinc-950 border border-zinc-850 cursor-pointer">
                        <input type="checkbox" name="allow_anonymous_bind" value="1" <?= ($settings['allow_anonymous_bind'] ?? '1') === '1' ? 'checked' : '' ?>
                               class="rounded bg-zinc-900 border-zinc-700 text-engine-600 focus:ring-0">
                        <div>
                            <span class="text-xs font-bold text-white block">Allow Anonymous Bind / Search</span>
                            <span class="text-[10px] text-zinc-500">Izinkan kueri direktori tanpa autentikasi</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-white hover:bg-zinc-200 text-xs font-bold text-zinc-950 shadow transition-all cursor-pointer">
                Save Engine Configuration
            </button>
        </div>
    </form>
</main>
