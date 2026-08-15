<?php
/**
 * View: Dashboard Overview (Anti-AI-Slop Command Center Edition)
 */
$pdo = Database::getConnection();

// Metrics
$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM ldap_users")->fetchColumn();
$activeUsers = (int) $pdo->query("SELECT COUNT(*) FROM ldap_users WHERE status = 'active'")->fetchColumn();
$totalGroups = (int) $pdo->query("SELECT COUNT(*) FROM ldap_groups")->fetchColumn();
$todayLogs = (int) $pdo->query("SELECT COUNT(*) FROM ldap_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$todaySuccess = (int) $pdo->query("SELECT COUNT(*) FROM ldap_logs WHERE DATE(created_at) = CURDATE() AND status = 'SUCCESS'")->fetchColumn();

// Recent logs
$recentLogs = $pdo->query("SELECT * FROM ldap_logs ORDER BY id DESC LIMIT 8")->fetchAll();
?>

<!-- Main Content Area -->
<main class="flex-1 bg-zinc-950 p-6 md:p-8 overflow-y-auto bg-grid-pattern">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-6 border-b border-zinc-850">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h1 class="text-xl font-extrabold text-white tracking-tight">Directory Telemetry</h1>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-850 text-zinc-300 border border-zinc-750">LIVE</span>
            </div>
            <p class="text-xs text-zinc-400">Pusat kendali direktori LDAP, pemantauan soket TCP 389, dan audit autentikasi</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="<?= BASE_URL ?>/index.php?page=tester" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-850 border border-zinc-750 text-xs font-bold text-zinc-200 transition-all">
                <svg class="w-3.5 h-3.5 text-engine-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                <span>Protocol Tester</span>
            </a>
            <a href="<?= BASE_URL ?>/index.php?page=users" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-zinc-200 text-xs font-bold text-zinc-950 shadow transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                <span>Add User</span>
            </a>
        </div>
    </div>

    <!-- Telemetry Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card 1: Users -->
        <div class="tactile-card rounded-2xl p-4 md:p-5">
            <div class="flex items-center justify-between text-xs text-zinc-400 mb-3">
                <span class="font-medium font-mono text-[11px]">USERS_POOL</span>
                <span class="text-zinc-500 font-mono text-[10px]">inetOrgPerson</span>
            </div>
            <div class="flex items-baseline gap-2 mb-2">
                <span class="text-3xl font-extrabold text-white tracking-tight"><?= $totalUsers ?></span>
                <span class="text-xs text-emerald-400 font-mono font-medium"><?= $activeUsers ?> active</span>
            </div>
            <div class="w-full bg-zinc-900 rounded-full h-1.5 overflow-hidden">
                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: <?= $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0 ?>%"></div>
            </div>
        </div>

        <!-- Card 2: Groups -->
        <div class="tactile-card rounded-2xl p-4 md:p-5">
            <div class="flex items-center justify-between text-xs text-zinc-400 mb-3">
                <span class="font-medium font-mono text-[11px]">GROUPS_SCHEMA</span>
                <span class="text-zinc-500 font-mono text-[10px]">posixGroup</span>
            </div>
            <div class="flex items-baseline gap-2 mb-2">
                <span class="text-3xl font-extrabold text-white tracking-tight"><?= $totalGroups ?></span>
                <span class="text-xs text-zinc-400 font-mono">roles mapped</span>
            </div>
            <div class="text-[11px] text-zinc-500 truncate">ou=groups,<?= htmlspecialchars(get_base_dn()) ?></div>
        </div>

        <!-- Card 3: Traffic -->
        <div class="tactile-card rounded-2xl p-4 md:p-5">
            <div class="flex items-center justify-between text-xs text-zinc-400 mb-3">
                <span class="font-medium font-mono text-[11px]">24H_OPERATIONS</span>
                <span class="text-zinc-500 font-mono text-[10px]">TCP Port 389</span>
            </div>
            <div class="flex items-baseline gap-2 mb-2">
                <span class="text-3xl font-extrabold text-white tracking-tight"><?= $todayLogs ?></span>
                <span class="text-xs text-emerald-400 font-mono font-medium"><?= $todaySuccess ?> success</span>
            </div>
            <div class="text-[11px] text-zinc-500">Live query & bind audit stream</div>
        </div>

        <!-- Card 4: Daemon Engine -->
        <div class="tactile-card rounded-2xl p-4 md:p-5">
            <div class="flex items-center justify-between text-xs text-zinc-400 mb-3">
                <span class="font-medium font-mono text-[11px]">SERVER_DAEMON</span>
                <span class="w-2 h-2 rounded-full <?= $daemonStatus['online'] ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' ?>"></span>
            </div>
            <div class="flex items-baseline gap-2 mb-2">
                <span class="text-xl font-bold font-mono <?= $daemonStatus['online'] ? 'text-emerald-400' : 'text-rose-400' ?>">
                    <?= $daemonStatus['online'] ? 'RUNNING :389' : 'OFFLINE' ?>
                </span>
            </div>
            <div class="text-[10px] font-mono text-zinc-400 truncate" title="<?= htmlspecialchars(get_base_dn()) ?>">
                Base: <?= htmlspecialchars(get_base_dn()) ?>
            </div>
        </div>
    </div>

    <!-- Middle Row: Directory Configuration & Integration Snippet -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">
        <!-- Telemetry Config Box (5 cols) -->
        <div class="lg:col-span-5 tactile-card rounded-2xl p-5">
            <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono mb-4 flex items-center justify-between">
                <span>Direct Connection Params</span>
                <span class="text-[10px] text-zinc-500">RFC 4511</span>
            </h2>
            <div class="space-y-2.5 text-xs font-mono">
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-zinc-950 border border-zinc-850">
                    <span class="text-zinc-500">URI</span>
                    <?php 
                    $currentHost = !empty($_SERVER['HTTP_HOST']) ? explode(':', $_SERVER['HTTP_HOST'])[0] : '127.0.0.1';
                    ?>
                    <span class="text-emerald-400 font-bold select-all">ldap://<?= htmlspecialchars($currentHost) ?>:<?= $ldapPort ?></span>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-zinc-950 border border-zinc-850">
                    <span class="text-zinc-500">Base DN</span>
                    <span class="text-zinc-200 select-all"><?= htmlspecialchars(get_base_dn()) ?></span>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-zinc-950 border border-zinc-850">
                    <span class="text-zinc-500">Admin DN</span>
                    <span class="text-engine-400 select-all truncate max-w-[200px]"><?= htmlspecialchars(get_admin_bind_dn()) ?></span>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-zinc-950 border border-zinc-850">
                    <span class="text-zinc-500">User Filter</span>
                    <span class="text-zinc-300 select-all">(&(objectClass=inetOrgPerson)(uid=%u))</span>
                </div>
            </div>
        </div>

        <!-- Live Architecture Status (7 cols) -->
        <div class="lg:col-span-7 tactile-card rounded-2xl p-5 flex flex-col justify-between">
            <div>
                <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono mb-4 flex items-center justify-between">
                    <span>Directory Tree Topology (DIT)</span>
                    <a href="<?= BASE_URL ?>/index.php?page=guide" class="text-engine-400 hover:text-engine-300 text-[11px] font-sans">View Full Guide &rarr;</a>
                </h2>
                
                <div class="p-3.5 rounded-xl bg-zinc-950 border border-zinc-850 font-mono text-[11px] space-y-1 text-zinc-300">
                    <div><span class="text-emerald-400 font-bold"><?= htmlspecialchars(get_base_dn()) ?></span> (Root DSE)</div>
                    <div class="pl-4 text-zinc-500">&boxur;&boxh;&boxh; <span class="text-engine-400 font-bold">ou=users</span> &bull; <span class="text-zinc-400"><?= $totalUsers ?> accounts</span></div>
                    <div class="pl-4 text-zinc-500">&boxur;&boxh;&boxh; <span class="text-purple-400 font-bold">ou=groups</span> &bull; <span class="text-zinc-400"><?= $totalGroups ?> posixGroups</span></div>
                    <div class="pl-4 text-zinc-500">&boxur;&boxh;&boxh; <span class="text-zinc-400">cn=admin</span> &bull; <span class="text-zinc-500">Service Bind Account</span></div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-zinc-850 flex items-center justify-between text-xs text-zinc-400">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    Workstation Login (pGina) Ready
                </span>
                <span class="font-mono text-[11px] text-zinc-500">MySQL Backend: OK</span>
            </div>
        </div>
    </div>

    <!-- Recent Audit Stream Table -->
    <div class="tactile-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-engine-400"></span>
                <span>Recent Audit Event Stream</span>
            </h2>
            <a href="<?= BASE_URL ?>/index.php?page=logs" class="text-xs font-medium text-engine-400 hover:text-engine-300 transition-colors">
                View All Logs &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-zinc-300 font-mono">
                <thead class="text-[10px] text-zinc-500 uppercase bg-zinc-950 border-b border-zinc-850">
                    <tr>
                        <th class="px-4 py-2.5">Timestamp</th>
                        <th class="px-4 py-2.5">Client IP</th>
                        <th class="px-4 py-2.5">Opcode</th>
                        <th class="px-4 py-2.5">Target DN / Query</th>
                        <th class="px-4 py-2.5">Result</th>
                        <th class="px-4 py-2.5 text-right">Latency</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-850">
                    <?php if (empty($recentLogs)): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-zinc-600 font-sans text-xs">Belum ada aktivitas tercatat</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <tr class="hover:bg-zinc-900/60 transition-colors text-[11px]">
                                <td class="px-4 py-2.5 text-zinc-400"><?= htmlspecialchars($log['created_at']) ?></td>
                                <td class="px-4 py-2.5 text-zinc-300"><?= htmlspecialchars($log['client_ip'] ?? '-') ?></td>
                                <td class="px-4 py-2.5">
                                    <span class="px-2 py-0.5 rounded font-bold text-[10px] <?= strpos($log['operation'], 'BIND') !== false ? 'bg-zinc-800 text-engine-400 border border-zinc-700' : 'bg-zinc-800 text-purple-300 border border-zinc-700' ?>">
                                        <?= htmlspecialchars($log['operation']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-zinc-300 truncate max-w-xs" title="<?= htmlspecialchars($log['bind_dn'] ?: $log['search_filter'] ?: '-') ?>">
                                    <?= htmlspecialchars($log['bind_dn'] ?: $log['search_filter'] ?: '-') ?>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold <?= $log['status'] === 'SUCCESS' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' ?>">
                                        <?= $log['status'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right text-zinc-500"><?= $log['response_time_ms'] ?>ms</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
