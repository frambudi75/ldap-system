<?php
/**
 * View: Login Page (Anti-AI-Slop Precision Edition)
 * Inspired by high-end developer tools & infrastructure engineering consoles (Vercel/Linear/Stripe)
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/helpers.php';

$ldapPort = get_ldap_port();
$daemonStatus = check_ldap_daemon_status();
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication - LDAP Engine Console</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        zinc: {
                            850: '#18181f',
                            900: '#121217',
                            925: '#0d0d12',
                            950: '#07070a',
                        },
                        engine: {
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #07070a; color: #f4f4f5; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        .bg-grid {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }

        .tactile-panel {
            background: #0e0e14;
            border: 1px solid #23232c;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.8), inset 0 1px 0 0 rgba(255, 255, 255, 0.06);
        }

        .kbd-key {
            background: #181820;
            border: 1px solid #2e2e3a;
            box-shadow: 0 1px 0 1px #000;
            border-radius: 4px;
            padding: 1px 6px;
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            color: #a1a1aa;
        }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen flex items-center justify-center p-4 md:p-8 bg-grid antialiased selection:bg-engine-500 selection:text-white">
    <div class="w-full max-w-4xl tactile-panel rounded-3xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 relative">
        
        <!-- Left Side: Engine Telemetry & Terminal (7 cols) -->
        <div class="lg:col-span-7 p-8 md:p-10 bg-zinc-900/60 border-b lg:border-b-0 lg:border-r border-zinc-800/80 flex flex-col justify-between relative overflow-hidden">
            <!-- Background Radial Glow -->
            <div class="absolute -top-24 -left-24 w-72 h-72 bg-engine-600/10 rounded-full blur-3xl pointer-events-none"></div>

            <div>
                <!-- Brand Header -->
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-zinc-800 border border-zinc-700 flex items-center justify-center font-mono font-bold text-white shadow-inner">
                        <svg class="w-5 h-5 text-engine-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-white text-base tracking-tight">LDAP ENGINE</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-800 text-zinc-300 border border-zinc-700">RFC 4511</span>
                        </div>
                        <span class="text-xs text-zinc-400 font-medium">Directory & Authentication Daemon</span>
                    </div>
                </div>

                <!-- Telemetry Metrics Grid -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="p-3.5 rounded-xl bg-zinc-950/80 border border-zinc-800">
                        <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-mono mb-1">Daemon Status</div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full <?= $daemonStatus['online'] ? 'bg-emerald-400 animate-ping' : 'bg-rose-400' ?>"></span>
                            <span class="text-xs font-bold font-mono <?= $daemonStatus['online'] ? 'text-emerald-400' : 'text-rose-400' ?>">
                                <?= $daemonStatus['online'] ? "LISTENING ({$ldapPort}/TCP)" : 'DAEMON_OFFLINE' ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-zinc-950/80 border border-zinc-800">
                        <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-mono mb-1">Base Namespace</div>
                        <div class="text-xs font-bold font-mono text-zinc-200 truncate">
                            <?= htmlspecialchars(get_base_dn()) ?>
                        </div>
                    </div>
                </div>

                <!-- Simulated Live Socket Packet Terminal -->
                <div class="rounded-xl bg-zinc-950 border border-zinc-800/90 overflow-hidden font-mono text-xs shadow-2xl">
                    <div class="px-3.5 py-2 bg-zinc-900 border-b border-zinc-800/80 flex items-center justify-between text-[11px] text-zinc-400">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-zinc-700"></span>
                            <span class="w-2 h-2 rounded-full bg-zinc-700"></span>
                            <span class="w-2 h-2 rounded-full bg-zinc-700"></span>
                            <span class="ml-2 font-semibold text-zinc-300">ldap_protocol.stream</span>
                        </div>
                        <span class="text-[10px] text-zinc-500">ASN.1 / BER</span>
                    </div>
                    <div class="p-4 space-y-1.5 text-[11px] text-zinc-300">
                        <div class="text-zinc-500">00:00:01 &gt; TCP SYN [127.0.0.1:<?= $ldapPort ?>] connected</div>
                        <div class="text-emerald-400">&gt; BIND_REQ: dn="cn=admin,<?= htmlspecialchars(get_base_dn()) ?>" [PASS]</div>
                        <div class="text-engine-400">&gt; SEARCH_REQ: base="ou=users,<?= htmlspecialchars(get_base_dn()) ?>" filter="(uid=*)"</div>
                        <div class="text-zinc-400">&gt; SEARCH_RES_ENTRY: found 3 posixAccount entries</div>
                        <div class="text-emerald-400">&gt; RESULT_CODE: 0x00 (SUCCESS)</div>
                    </div>
                </div>
            </div>

            <!-- Footer Specs -->
            <div class="pt-6 mt-6 border-t border-zinc-800/60 flex items-center justify-between text-[11px] text-zinc-400">
                <span>Client Compatibility: <strong class="text-zinc-300">pGina, PAM, Nextcloud</strong></span>
                <span class="font-mono">v<?= APP_VERSION ?></span>
            </div>
        </div>

        <!-- Right Side: Login Form (5 cols) -->
        <div class="lg:col-span-5 p-8 md:p-10 bg-zinc-950 flex flex-col justify-center">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-white tracking-tight">Console Login</h2>
                <p class="text-xs text-zinc-400 mt-1">Masuk dengan kredensial administrator direktori</p>
            </div>

            <?php if (!empty($loginError)): ?>
                <div class="mb-5 p-3.5 rounded-xl bg-rose-950/50 border border-rose-500/40 text-rose-300 text-xs flex items-center gap-2.5">
                    <svg class="w-4 h-4 shrink-0 text-rose-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <span><?= htmlspecialchars($loginError) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/index.php?action=login" class="space-y-4">
                <?= csrf_field() ?>
                
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase tracking-wider mb-1.5">
                        Username
                    </label>
                    <div class="relative">
                        <input type="text" name="username" id="in-username" required autofocus value="" placeholder="Masukkan username" autocomplete="username"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-900 border border-zinc-700 text-white text-xs font-mono focus:outline-none focus:border-engine-500 focus:ring-1 focus:ring-engine-500 transition-all">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-[11px] font-bold text-zinc-300 uppercase tracking-wider">
                            Password
                        </label>
                        <button type="button" onclick="togglePassword()" class="text-[11px] text-zinc-500 hover:text-zinc-300 transition-colors">
                            <span id="pwd-toggle-text">Show</span>
                        </button>
                    </div>
                    <div class="relative">
                        <input type="password" name="password" id="in-password" required value="" placeholder="Masukkan password" autocomplete="current-password"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-900 border border-zinc-700 text-white text-xs font-mono focus:outline-none focus:border-engine-500 focus:ring-1 focus:ring-engine-500 transition-all">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3 px-4 rounded-xl bg-white hover:bg-zinc-200 text-zinc-950 font-bold text-xs shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.98]">
                        <span>Authenticate Session</span>
                        <span class="kbd-key text-[10px] text-zinc-800 bg-zinc-200 border-zinc-300">↵ Enter</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('in-password');
            const txt = document.getElementById('pwd-toggle-text');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                txt.innerText = 'Hide';
            } else {
                pwd.type = 'password';
                txt.innerText = 'Show';
            }
        }
    </script>
</body>
</html>
