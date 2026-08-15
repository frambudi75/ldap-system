<?php
/**
 * View: LDAP Audit Logs Viewer (Tactile Precision Edition)
 */
$pdo = Database::getConnection();

$statusFilter = $_GET['status'] ?? '';
$opFilter = $_GET['op'] ?? '';

$sql = "SELECT * FROM ldap_logs WHERE 1=1";
$params = [];

if ($statusFilter) {
    $sql .= " AND status = :status";
    $params['status'] = $statusFilter;
}
if ($opFilter) {
    $sql .= " AND operation LIKE :op";
    $params['op'] = "%{$opFilter}%";
}

$sql .= " ORDER BY id DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<main class="flex-1 bg-zinc-950 p-6 md:p-8 overflow-y-auto bg-grid-pattern">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-6 border-b border-zinc-850">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h1 class="text-xl font-extrabold text-white tracking-tight">Audit Stream Logs</h1>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-850 text-zinc-300 border border-zinc-750">IMMUTABLE LOGS</span>
            </div>
            <p class="text-xs text-zinc-400">Catatan lengkap request autentikasi, kueri direktori, dan performa respon</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="POST" action="<?= BASE_URL ?>/index.php?action=clear_logs" onsubmit="return confirm('Hapus seluruh riwayat log LDAP?');">
                <?= csrf_field() ?>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-850 border border-zinc-800 text-xs font-mono font-bold text-rose-400 hover:text-rose-300 transition-colors cursor-pointer">
                    Clear Log Stream
                </button>
            </form>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="tactile-card rounded-2xl p-3.5 mb-5 shadow-lg">
        <form method="GET" action="<?= BASE_URL ?>/index.php" class="flex flex-wrap items-center gap-4 text-xs font-mono">
            <input type="hidden" name="page" value="logs">
            <div class="flex items-center gap-2">
                <span class="text-zinc-500 font-bold">STATUS:</span>
                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:outline-none focus:border-engine-500">
                    <option value="">ALL_STATUS</option>
                    <option value="SUCCESS" <?= $statusFilter === 'SUCCESS' ? 'selected' : '' ?>>SUCCESS (0x00)</option>
                    <option value="FAILED" <?= $statusFilter === 'FAILED' ? 'selected' : '' ?>>FAILED (0x31/0x30)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-zinc-500 font-bold">OPCODE:</span>
                <select name="op" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:outline-none focus:border-engine-500">
                    <option value="">ALL_OPCODES</option>
                    <option value="BIND" <?= $opFilter === 'BIND' ? 'selected' : '' ?>>BIND (Auth)</option>
                    <option value="SEARCH" <?= $opFilter === 'SEARCH' ? 'selected' : '' ?>>SEARCH (Query)</option>
                    <option value="UNBIND" <?= $opFilter === 'UNBIND' ? 'selected' : '' ?>>UNBIND</option>
                </select>
            </div>

            <?php if ($statusFilter || $opFilter): ?>
                <a href="<?= BASE_URL ?>/index.php?page=logs" class="text-engine-400 hover:text-engine-300 ml-auto text-[11px]">Reset Filters</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="tactile-card rounded-2xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-zinc-300 font-mono">
                <thead class="text-[10px] text-zinc-500 uppercase bg-zinc-950 border-b border-zinc-800">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3">Client IP</th>
                        <th class="px-4 py-3">Opcode</th>
                        <th class="px-4 py-3">Target DN / Filter</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Details</th>
                        <th class="px-4 py-3 text-right">Latency</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-850">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-zinc-600 font-sans text-xs">Tidak ada log aktivitas sesuai kriteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr class="hover:bg-zinc-900/60 transition-colors text-[11px]">
                                <td class="px-4 py-2.5 text-zinc-600">#<?= $l['id'] ?></td>
                                <td class="px-4 py-2.5 text-zinc-400 whitespace-nowrap"><?= htmlspecialchars($l['created_at']) ?></td>
                                <td class="px-4 py-2.5 text-zinc-300"><?= htmlspecialchars($l['client_ip'] ?? '-') ?></td>
                                <td class="px-4 py-2.5">
                                    <span class="px-2 py-0.5 rounded font-bold text-[10px] <?= strpos($l['operation'], 'BIND') !== false ? 'bg-zinc-800 text-engine-400 border border-zinc-700' : 'bg-zinc-800 text-purple-300 border border-zinc-700' ?>">
                                        <?= htmlspecialchars($l['operation']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-zinc-300 truncate max-w-xs" title="<?= htmlspecialchars($l['bind_dn'] ?: $l['search_filter'] ?: '-') ?>">
                                    <?= htmlspecialchars($l['bind_dn'] ?: $l['search_filter'] ?: '-') ?>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold <?= $l['status'] === 'SUCCESS' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' ?>">
                                        <?= $l['status'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-zinc-400 truncate max-w-xs" title="<?= htmlspecialchars($l['error_message'] ?? '-') ?>">
                                    <?= htmlspecialchars($l['error_message'] ?? '-') ?>
                                </td>
                                <td class="px-4 py-2.5 text-right text-zinc-500"><?= $l['response_time_ms'] ?>ms</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
