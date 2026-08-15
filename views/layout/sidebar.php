<?php
/**
 * Layout: Sidebar Navigation (Anti-AI-Slop Precision Edition)
 */
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<!-- Sidebar Component -->
<aside class="w-64 bg-zinc-925 border-r border-zinc-800 flex flex-col justify-between select-none shrink-0">
    <div>
        <!-- Brand Header -->
        <div class="h-16 flex items-center px-5 border-b border-zinc-800 gap-3">
            <div class="w-8 h-8 rounded-lg bg-zinc-800 border border-zinc-700 flex items-center justify-center font-bold text-white shadow-inner">
                <svg class="w-4 h-4 text-engine-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <div class="truncate">
                <span class="font-extrabold text-white text-xs tracking-wider block">LDAP ENGINE</span>
                <span class="text-[10px] text-zinc-500 font-mono">TCP :<?= $ldapPort ?> &bull; RFC 4511</span>
            </div>
        </div>

        <!-- Daemon Live Status Strip -->
        <div class="px-4 py-3 mx-3 my-3 rounded-xl bg-zinc-950 border border-zinc-850">
            <div class="flex items-center justify-between text-xs">
                <span class="text-zinc-400 font-mono text-[11px]">TCP_DAEMON</span>
                <span id="daemon-status-badge" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold <?= $daemonStatus['online'] ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?= $daemonStatus['online'] ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' ?>"></span>
                    <?= $daemonStatus['online'] ? 'ONLINE' : 'STOPPED' ?>
                </span>
            </div>
            <div class="mt-2 text-[10px] font-mono text-zinc-500 flex justify-between">
                <span>PORT <?= $ldapPort ?></span>
                <span class="truncate max-w-[120px]" title="<?= htmlspecialchars(get_base_dn()) ?>"><?= htmlspecialchars(get_base_dn()) ?></span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="px-3 space-y-1">
            <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all <?= $currentPage === 'dashboard' ? 'bg-zinc-800 text-white shadow-sm border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-850' ?>">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard Overview
            </a>

            <a href="<?= BASE_URL ?>/index.php?page=clients" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all <?= $currentPage === 'clients' ? 'bg-zinc-800 text-white shadow-sm border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-850' ?>">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Klien Terhubung (Devices)
            </a>

            <a href="<?= BASE_URL ?>/index.php?page=users" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all <?= $currentPage === 'users' ? 'bg-zinc-800 text-white shadow-sm border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-850' ?>">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Pengguna (Users)
            </a>

            <a href="<?= BASE_URL ?>/index.php?page=groups" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all <?= $currentPage === 'groups' ? 'bg-zinc-800 text-white shadow-sm border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-850' ?>">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Kelompok (Groups)
            </a>

            <a href="<?= BASE_URL ?>/index.php?page=tester" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all <?= $currentPage === 'tester' ? 'bg-zinc-800 text-white shadow-sm border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-850' ?>">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                LDAP Protocol Tester
            </a>

            <a href="<?= BASE_URL ?>/index.php?page=logs" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all <?= $currentPage === 'logs' ? 'bg-zinc-800 text-white shadow-sm border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-850' ?>">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Audit Stream Logs
            </a>

            <a href="<?= BASE_URL ?>/index.php?page=settings" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all <?= $currentPage === 'settings' ? 'bg-zinc-800 text-white shadow-sm border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-850' ?>">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Direktori Settings
            </a>

            <a href="<?= BASE_URL ?>/index.php?page=guide" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all <?= $currentPage === 'guide' ? 'bg-zinc-800 text-white shadow-sm border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-850' ?>">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Integrasi (pGina / Apps)
            </a>
        </nav>
    </div>

    <!-- Admin User Footer Strip -->
    <div class="p-3.5 border-t border-zinc-800">
        <div class="flex items-center justify-between p-2 rounded-xl bg-zinc-950 border border-zinc-850">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <div class="w-7 h-7 rounded-lg bg-zinc-800 border border-zinc-700 flex items-center justify-center font-mono font-bold text-xs text-white shrink-0">
                    <?= strtoupper(substr($adminUser['username'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="truncate">
                    <div class="text-[11px] font-bold text-zinc-200 truncate"><?= htmlspecialchars($adminUser['full_name'] ?? 'Admin') ?></div>
                    <div class="text-[9px] text-zinc-500 font-mono truncate"><?= htmlspecialchars($adminUser['username'] ?? 'admin') ?></div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/index.php?action=logout" title="End Session" class="text-zinc-500 hover:text-rose-400 p-1.5 rounded-lg hover:bg-zinc-850 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </a>
        </div>
    </div>
</aside>
