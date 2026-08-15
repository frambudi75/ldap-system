<?php
/**
 * View: Connected Clients & Application Monitor
 * Real-time active devices, workstations, and integrated software analytics.
 */
$pdo = Database::getConnection();

// Aggregate unique clients from audit logs
$clientsQuery = $pdo->query("
    SELECT 
        client_ip,
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success_count,
        SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as failed_count,
        MIN(created_at) as first_seen,
        MAX(created_at) as last_seen,
        GROUP_CONCAT(DISTINCT operation ORDER BY id DESC SEPARATOR ', ') as operations_list,
        GROUP_CONCAT(DISTINCT bind_dn ORDER BY id DESC SEPARATOR ', ') as target_accounts,
        GROUP_CONCAT(DISTINCT search_filter ORDER BY id DESC SEPARATOR ' | ') as search_filters
    FROM ldap_logs
    WHERE client_ip IS NOT NULL AND client_ip != ''
    GROUP BY client_ip
    ORDER BY last_seen DESC
");
$clients = $clientsQuery->fetchAll();

// Total stats
$totalUniqueClients = count($clients);
$totalOps = $pdo->query("SELECT COUNT(*) FROM ldap_logs")->fetchColumn();

function detectClientApp(array $client): array {
    $ip = $client['client_ip'];
    $ops = strtolower($client['operations_list'] ?? '');
    $targets = strtolower($client['target_accounts'] ?? '');
    $filters = strtolower($client['search_filters'] ?? '');

    // Internal tester
    if ($ip === '127.0.0.1' || str_starts_with($ip, '172.') || $ip === '::1') {
        return [
            'name' => 'Web Admin / Internal Tester',
            'type' => 'Internal Console',
            'icon' => '🔧',
            'badge' => 'bg-engine-500/10 text-engine-400 border-engine-500/30'
        ];
    }

    // pGina Windows Client detection
    if (str_contains($filters, 'objectclass=inetorgperson') || str_contains($targets, 'ou=users') || str_contains($ops, 'bind_user')) {
        return [
            'name' => 'Windows Workstation (pGina)',
            'type' => 'Windows OS Login',
            'icon' => '💻',
            'badge' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
        ];
    }

    // Nextcloud
    if (str_contains($filters, 'posixgroup') && str_contains($filters, 'memberuid')) {
        return [
            'name' => 'Nextcloud / Cloud Storage',
            'type' => 'Directory Backend',
            'icon' => '☁️',
            'badge' => 'bg-purple-500/10 text-purple-400 border-purple-500/30'
        ];
    }

    // Generic External App
    return [
        'name' => 'External LDAP Client',
        'type' => 'Remote TCP Client',
        'icon' => '🌐',
        'badge' => 'bg-amber-500/10 text-amber-400 border-amber-500/30'
    ];
}
?>

<main class="flex-1 bg-zinc-950 p-6 md:p-8 overflow-y-auto bg-grid-pattern">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-6 border-b border-zinc-850">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h1 class="text-xl font-extrabold text-white tracking-tight">Connected Clients & Devices</h1>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-850 text-zinc-300 border border-zinc-750">LIVE CLIENTS</span>
            </div>
            <p class="text-xs text-zinc-400">Daftar perangkat (laptop pGina, server, web app) yang pernah atau sedang terhubung ke LDAP Server</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="location.reload()" class="px-3 py-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-850 border border-zinc-750 text-xs font-mono font-bold text-zinc-300 transition-colors flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Refresh Stream
            </button>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <div class="tactile-card rounded-2xl p-5">
            <div class="flex items-center justify-between text-xs text-zinc-400 mb-2">
                <span class="font-medium font-mono text-[11px]">DISCOVERED_CLIENTS</span>
                <span class="text-zinc-500 font-mono text-[10px]">Unique IPs</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white tracking-tight"><?= $totalUniqueClients ?></span>
                <span class="text-xs text-emerald-400 font-mono">devices tracked</span>
            </div>
        </div>

        <div class="tactile-card rounded-2xl p-5">
            <div class="flex items-center justify-between text-xs text-zinc-400 mb-2">
                <span class="font-medium font-mono text-[11px]">TOTAL_REQUESTS</span>
                <span class="text-zinc-500 font-mono text-[10px]">All Time</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white tracking-tight"><?= $totalOps ?></span>
                <span class="text-xs text-engine-400 font-mono">packets processed</span>
            </div>
        </div>

        <div class="tactile-card rounded-2xl p-5">
            <div class="flex items-center justify-between text-xs text-zinc-400 mb-2">
                <span class="font-medium font-mono text-[11px]">PROTOCOL_DAEMON</span>
                <span class="text-emerald-400 font-mono text-[10px]">TCP Port 389</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-white tracking-tight">Active Listener</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            </div>
        </div>
    </div>

    <!-- Connected Clients Table -->
    <div class="tactile-card rounded-2xl overflow-hidden shadow-2xl">
        <div class="px-5 py-4 border-b border-zinc-850 flex items-center justify-between">
            <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Active & Historical Client Registry</span>
            </h2>
            <span class="text-[10px] font-mono text-zinc-500"><?= count($clients) ?> Endpoints Detected</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono text-zinc-300">
                <thead class="text-[10px] text-zinc-500 uppercase bg-zinc-950 border-b border-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Client Application / Fingerprint</th>
                        <th class="px-4 py-3">Client IP Address</th>
                        <th class="px-4 py-3">Activity Status</th>
                        <th class="px-4 py-3">Target Accounts</th>
                        <th class="px-4 py-3">Operations</th>
                        <th class="px-4 py-3 text-right">Success / Total</th>
                        <th class="px-4 py-3 text-right">Last Seen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-850 text-[11px]">
                    <?php if (empty($clients)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-zinc-600 font-sans text-xs">
                                Belum ada client yang tercatat terhubung ke server LDAP ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clients as $c): 
                            $app = detectClientApp($c);
                            $lastTime = strtotime($c['last_seen']);
                            $diffSec = time() - $lastTime;
                            
                            $isOnline = $diffSec < 600; // < 10 mins
                            $isToday = $diffSec < 86400; // < 24 hours
                        ?>
                            <tr class="hover:bg-zinc-900/60 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-lg"><?= $app['icon'] ?></span>
                                        <div>
                                            <div class="font-bold text-white font-sans text-xs"><?= htmlspecialchars($app['name']) ?></div>
                                            <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-mono border <?= $app['badge'] ?>">
                                                <?= $app['type'] ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded bg-zinc-950 border border-zinc-800 font-mono text-zinc-200 select-all font-bold">
                                        <?= htmlspecialchars($c['client_ip']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if ($isOnline): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            ACTIVE RECENTLY
                                        </span>
                                    <?php elseif ($isToday): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-800 text-zinc-300 border border-zinc-700">
                                            TODAY
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-900 text-zinc-500 border border-zinc-800">
                                            IDLE
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-zinc-300 truncate max-w-xs" title="<?= htmlspecialchars($c['target_accounts'] ?: '-') ?>">
                                    <span class="text-engine-400"><?= htmlspecialchars($c['target_accounts'] ?: '-') ?></span>
                                </td>
                                <td class="px-4 py-3 text-zinc-400">
                                    <?= htmlspecialchars($c['operations_list']) ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-bold text-emerald-400"><?= $c['success_count'] ?></span>
                                    <span class="text-zinc-500">/ <?= $c['total_requests'] ?></span>
                                </td>
                                <td class="px-4 py-3 text-right text-zinc-400 whitespace-nowrap">
                                    <?= date('d M H:i', $lastTime) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
