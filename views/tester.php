<?php
/**
 * View: Interactive LDAP Connection & Query Tester (Tactile Precision Edition)
 */
$baseDn = get_base_dn();
$adminDn = get_admin_bind_dn();
$ldapPort = get_ldap_port();
?>

<main class="flex-1 bg-zinc-950 p-6 md:p-8 overflow-y-auto bg-grid-pattern">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-6 border-b border-zinc-850">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h1 class="text-xl font-extrabold text-white tracking-tight">LDAP Protocol Engine Tester</h1>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-850 text-zinc-300 border border-zinc-750">SOCKET DEBUGGER</span>
            </div>
            <p class="text-xs text-zinc-400">Simulasikan proses `BindRequest` dan evaluasi kueri `SearchRequest` secara live</p>
        </div>
        <!-- Preset Shortcuts -->
        <div class="flex items-center gap-2">
            <span class="text-xs text-zinc-500 font-mono">Quick Preset:</span>
            <button onclick="fillPreset('admin')" class="px-2.5 py-1 rounded-lg bg-zinc-900 hover:bg-zinc-800 border border-zinc-750 text-[11px] font-mono text-zinc-300 transition-colors">Admin Root Bind</button>
            <button onclick="fillPreset('user')" class="px-2.5 py-1 rounded-lg bg-zinc-900 hover:bg-zinc-800 border border-zinc-750 text-[11px] font-mono text-engine-400 transition-colors">User Auth (budi)</button>
            <button onclick="fillPreset('anon')" class="px-2.5 py-1 rounded-lg bg-zinc-900 hover:bg-zinc-800 border border-zinc-750 text-[11px] font-mono text-emerald-400 transition-colors">Anonymous Search</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Input Form (Left 5 cols) -->
        <div class="lg:col-span-5 tactile-card rounded-2xl p-5 shadow-2xl">
            <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono mb-4 flex items-center justify-between">
                <span>Packet Parameters</span>
                <span class="text-[10px] text-zinc-500">RFC 4511 BER</span>
            </h2>

            <form id="tester-form" class="space-y-3.5" onsubmit="executeLdapTest(event)">
                <div class="grid grid-cols-3 gap-2.5">
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-zinc-400 uppercase font-mono mb-1">Host Target (IP / Container / Domain)</label>
                        <input type="text" id="t-host" value="<?= htmlspecialchars(getenv('LDAP_HOST') ?: '127.0.0.1') ?>" required
                               class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-zinc-400 uppercase font-mono mb-1">Port</label>
                        <input type="number" id="t-port" value="<?= $ldapPort ?>" required
                               class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-zinc-400 uppercase font-mono mb-1">Bind DN (Authentication Entity)</label>
                    <input type="text" id="t-bind-dn" value="<?= htmlspecialchars($adminDn) ?>"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                    <span class="block text-[10px] text-zinc-600 font-mono mt-0.5">Empty = Anonymous Bind</span>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-zinc-400 uppercase font-mono mb-1">Password</label>
                    <input type="password" id="t-password" value="admin123"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                </div>

                <div class="pt-2 border-t border-zinc-850">
                    <label class="block text-[10px] font-bold text-zinc-400 uppercase font-mono mb-1">Search Base DN</label>
                    <input type="text" id="t-search-base" value="<?= htmlspecialchars($baseDn) ?>"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-zinc-400 uppercase font-mono mb-1">Search Filter Expression</label>
                    <input type="text" id="t-search-filter" value="(uid=budi)"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                </div>

                <div class="pt-2">
                    <button type="submit" id="btn-submit-test"
                            class="w-full py-2.5 px-4 rounded-xl bg-white hover:bg-zinc-200 text-xs font-bold text-zinc-950 shadow transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Dispatch Test Packets</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Output / Response Panel (Right 7 cols) -->
        <div class="lg:col-span-7 tactile-card rounded-2xl p-5 flex flex-col justify-between shadow-2xl">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-zinc-850 mb-4">
                    <h2 class="text-xs font-bold text-zinc-300 uppercase tracking-wider font-mono flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>Protocol Output Inspector</span>
                    </h2>
                    <span id="test-badge" class="hidden px-2 py-0.5 rounded text-[10px] font-mono font-bold"></span>
                </div>

                <div id="test-output-container" class="space-y-3">
                    <div class="p-8 text-center text-zinc-600 text-xs font-mono">
                        Tekan tombol <strong>"Dispatch Test Packets"</strong> untuk mengevaluasi respons soket LDAP secara real-time.
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-850 text-[11px] font-mono text-zinc-500 flex justify-between">
                <span>Protocol: LDAPv3 RFC4511</span>
                <span id="test-latency">Latency: -</span>
            </div>
        </div>
    </div>
</main>

<script>
function fillPreset(type) {
    const baseDn = "<?= addslashes($baseDn) ?>";
    const adminDn = "<?= addslashes($adminDn) ?>";

    if (type === 'admin') {
        document.getElementById('t-bind-dn').value = adminDn;
        document.getElementById('t-password').value = 'admin123';
        document.getElementById('t-search-base').value = baseDn;
        document.getElementById('t-search-filter').value = '(objectClass=*)';
    } else if (type === 'user') {
        document.getElementById('t-bind-dn').value = 'uid=budi,ou=users,' + baseDn;
        document.getElementById('t-password').value = 'password123';
        document.getElementById('t-search-base').value = 'ou=users,' + baseDn;
        document.getElementById('t-search-filter').value = '(uid=budi)';
    } else if (type === 'anon') {
        document.getElementById('t-bind-dn').value = '';
        document.getElementById('t-password').value = '';
        document.getElementById('t-search-base').value = baseDn;
        document.getElementById('t-search-filter').value = '(uid=siti)';
    }
}

function executeLdapTest(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-test');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin text-xs">⏳</span> Evaluating Socket...';

    const payload = {
        host: document.getElementById('t-host').value,
        port: document.getElementById('t-port').value,
        bind_dn: document.getElementById('t-bind-dn').value,
        password: document.getElementById('t-password').value,
        search_base: document.getElementById('t-search-base').value,
        search_filter: document.getElementById('t-search-filter').value
    };

    fetch('<?= BASE_URL ?>/api/test_ldap.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span>Dispatch Test Packets</span>';

        const badge = document.getElementById('test-badge');
        const output = document.getElementById('test-output-container');
        const latency = document.getElementById('test-latency');

        badge.classList.remove('hidden');
        latency.innerText = 'Latency: ' + (data.duration_ms || 0) + ' ms';

        if (data.success) {
            badge.className = 'px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30';
            badge.innerText = '0x00 (SUCCESS)';

            let entriesHtml = '';
            if (data.entries && data.entries.length > 0) {
                entriesHtml = `
                    <div class="mt-3">
                        <div class="text-[10px] font-mono font-bold text-zinc-400 uppercase tracking-wider mb-2">Search Entries (${data.entries.length}):</div>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            ${data.entries.map(ent => `
                                <div class="p-3 rounded-xl bg-zinc-950 border border-zinc-850 font-mono text-[11px]">
                                    <div class="text-engine-400 font-bold mb-1 select-all">${ent.dn}</div>
                                    <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-zinc-400">
                                        ${Object.entries(ent).map(([k, v]) => k !== 'dn' ? `<div><span class="text-zinc-500">${k}:</span> ${v}</div>` : '').join('')}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            output.innerHTML = `
                <div class="p-3.5 rounded-xl bg-zinc-950 border border-emerald-500/30 font-mono text-xs">
                    <div class="text-emerald-400 font-bold mb-0.5">✓ ${data.message}</div>
                    <div class="text-[10px] text-zinc-500">Stage: ${data.stage}</div>
                </div>
                ${entriesHtml}
            `;
        } else {
            badge.className = 'px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30';
            badge.innerText = 'FAILED';

            output.innerHTML = `
                <div class="p-3.5 rounded-xl bg-zinc-950 border border-rose-500/30 font-mono text-xs">
                    <div class="text-rose-400 font-bold mb-0.5">❌ Execution halted at [${data.stage}]</div>
                    <div class="text-[11px] text-zinc-400">${data.message}</div>
                </div>
            `;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = 'Dispatch Test Packets';
        alert('Socket connection error: ' + err.message);
    });
}
</script>
