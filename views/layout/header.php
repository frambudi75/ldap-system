<?php
/**
 * Layout: Header & Global Design System
 * Anti-AI-Slop Craftsmanship: Crisp Zinc Obsidian Theme with Precision Typography & Tactile Accents
 */
$currentPage = $_GET['page'] ?? 'dashboard';
$adminUser = Auth::user();
$ldapPort = get_ldap_port();
$daemonStatus = check_ldap_daemon_status();
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> - LDAP Engine</title>
    <!-- Modern Typography: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        zinc: {
                            850: '#1e1e24',
                            925: '#111116',
                            950: '#09090c',
                        },
                        accent: {
                            50: '#f0fdf4',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                        },
                        engine: {
                            50: '#eff6ff',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background-color: #09090c;
            color: #f4f4f5;
        }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        /* Subtle Grid Pattern Background */
        .bg-grid-pattern {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
        
        /* Tactile Card Styling */
        .tactile-card {
            background: #111116;
            border: 1px solid #27272a;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.5), inset 0 1px 0 0 rgba(255, 255, 255, 0.05);
        }
        
        .tactile-card-hover:hover {
            border-color: #3f3f46;
            transform: translateY(-1px);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Keyboard badge */
        .kbd-badge {
            background: #18181b;
            border: 1px solid #3f3f46;
            box-shadow: 0 1px 0 1px #09090b;
            border-radius: 4px;
            padding: 1px 5px;
            font-size: 10px;
            font-family: 'JetBrains Mono', monospace;
            color: #a1a1aa;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #09090c; }
        ::-webkit-scrollbar-thumb { background: #27272a; border-radius: 2px; }
        ::-webkit-scrollbar-thumb:hover { background: #3f3f46; }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen flex flex-col antialiased selection:bg-engine-500 selection:text-white">
    <!-- Flash Notification Banner -->
    <?php $flash = get_flash(); if ($flash): ?>
        <div id="flash-banner" class="fixed top-5 right-5 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl border transition-all duration-300 <?= $flash['type'] === 'success' ? 'bg-zinc-900 border-emerald-500/60 text-emerald-300' : ($flash['type'] === 'danger' ? 'bg-zinc-900 border-rose-500/60 text-rose-300' : 'bg-zinc-900 border-engine-500/60 text-engine-300') ?>">
            <div class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold shrink-0 <?= $flash['type'] === 'success' ? 'bg-emerald-500/20 text-emerald-400' : ($flash['type'] === 'danger' ? 'bg-rose-500/20 text-rose-400' : 'bg-engine-500/20 text-engine-400') ?>">
                <?= $flash['type'] === 'success' ? '✓' : '!' ?>
            </div>
            <div class="text-xs font-medium"><?= htmlspecialchars($flash['message']) ?></div>
            <button onclick="document.getElementById('flash-banner').remove()" class="text-zinc-500 hover:text-zinc-300 text-base leading-none ml-2">&times;</button>
        </div>
        <script>setTimeout(() => { const el = document.getElementById('flash-banner'); if(el) el.remove(); }, 4000);</script>
    <?php endif; ?>

    <div class="flex flex-1 min-h-screen">
