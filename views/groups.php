<?php
/**
 * View: Group Management (Tactile Precision Edition)
 */
$pdo = Database::getConnection();
$baseDn = get_base_dn();

$groups = $pdo->query("
    SELECT g.*, 
           COUNT(gm.user_id) as member_count,
           GROUP_CONCAT(u.username SEPARATOR ', ') as member_usernames,
           GROUP_CONCAT(u.full_name SEPARATOR ', ') as member_names
    FROM ldap_groups g
    LEFT JOIN ldap_group_members gm ON g.id = gm.group_id
    LEFT JOIN ldap_users u ON gm.user_id = u.id
    GROUP BY g.id
    ORDER BY g.id ASC
")->fetchAll();

$allUsers = $pdo->query("SELECT id, username, full_name FROM ldap_users ORDER BY username ASC")->fetchAll();
?>

<main class="flex-1 bg-zinc-950 p-6 md:p-8 overflow-y-auto bg-grid-pattern">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-6 border-b border-zinc-850">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h1 class="text-xl font-extrabold text-white tracking-tight">Directory Groups</h1>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-850 text-zinc-300 border border-zinc-750">posixGroup</span>
            </div>
            <p class="text-xs text-zinc-400">Kelola kelompok pengguna dan role mapping (`groupOfNames`)</p>
        </div>
        <div>
            <button onclick="document.getElementById('modal-add-group').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-zinc-200 text-xs font-bold text-zinc-950 shadow transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                <span>New Group</span>
            </button>
        </div>
    </div>

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($groups as $group): 
            $groupDn = "cn={$group['name']},ou=groups,{$baseDn}";
        ?>
            <div class="tactile-card rounded-2xl p-5 flex flex-col justify-between tactile-card-hover">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-zinc-800 border border-zinc-700 text-purple-400 flex items-center justify-center font-mono font-bold text-xs">
                                #
                            </span>
                            <h3 class="font-bold text-white text-sm font-mono"><?= htmlspecialchars($group['name']) ?></h3>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-950 border border-zinc-800 text-zinc-400">
                            GID: <?= $group['gid_number'] ?: '-' ?>
                        </span>
                    </div>

                    <p class="text-xs text-zinc-400 mb-3 min-h-[32px] line-clamp-2">
                        <?= htmlspecialchars($group['description'] ?: 'Tidak ada deskripsi.') ?>
                    </p>

                    <!-- Group DN -->
                    <div class="p-2 rounded-xl bg-zinc-950 border border-zinc-850 mb-3 text-[10px] font-mono text-zinc-400 truncate select-all" title="<?= htmlspecialchars($groupDn) ?>">
                        <?= htmlspecialchars($groupDn) ?>
                    </div>

                    <!-- Members List -->
                    <div class="mb-4">
                        <div class="text-[10px] font-bold text-zinc-400 uppercase font-mono tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Members (<?= $group['member_count'] ?>)</span>
                        </div>
                        <div class="flex flex-wrap gap-1 max-h-20 overflow-y-auto">
                            <?php if (!empty($group['member_usernames'])): ?>
                                <?php foreach (explode(',', $group['member_usernames']) as $u): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-zinc-800 text-zinc-300 border border-zinc-700">
                                        <?= htmlspecialchars(trim($u)) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-xs text-zinc-600 font-mono italic">no members assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-3 border-t border-zinc-850 flex items-center justify-between">
                    <span class="text-[10px] font-mono text-zinc-500"><?= date('d M Y', strtotime($group['created_at'])) ?></span>
                    <div class="flex items-center gap-1.5">
                        <button onclick='openEditGroupModal(<?= json_encode($group) ?>)'
                                class="p-1.5 rounded-lg text-zinc-400 hover:text-engine-400 hover:bg-zinc-800 transition-colors" title="Edit Group">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <form method="POST" action="<?= BASE_URL ?>/index.php?action=delete_group" onsubmit="return confirm('Hapus kelompok <?= htmlspecialchars($group['name']) ?>?');" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
                            <button type="submit" class="p-1.5 rounded-lg text-zinc-400 hover:text-rose-400 hover:bg-zinc-800 transition-colors" title="Delete Group">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Modal: Add Group -->
<div id="modal-add-group" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden p-4">
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-md shadow-2xl p-6">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-800 mb-5">
            <h3 class="text-sm font-bold text-white font-mono uppercase tracking-wider">New Directory Group</h3>
            <button onclick="document.getElementById('modal-add-group').classList.add('hidden')" class="text-zinc-500 hover:text-white text-xl leading-none">&times;</button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=create_group" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Group Name (cn) *</label>
                <input type="text" name="name" required placeholder="Engineers, Managers"
                       class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">GID Number (posixGroup)</label>
                <input type="number" name="gid_number" placeholder="505"
                       class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Description</label>
                <textarea name="description" rows="2" placeholder="Tujuan kelompok ini..."
                          class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-white focus:outline-none focus:border-engine-500"></textarea>
            </div>

            <div class="pt-4 flex justify-end gap-2 border-t border-zinc-800">
                <button type="button" onclick="document.getElementById('modal-add-group').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-750 text-xs font-semibold text-zinc-300">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-white hover:bg-zinc-200 text-xs font-bold text-zinc-950">Save Group</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Group -->
<div id="modal-edit-group" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden p-4">
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-md shadow-2xl p-6">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-800 mb-5">
            <h3 class="text-sm font-bold text-white font-mono uppercase tracking-wider">Modify Group</h3>
            <button onclick="document.getElementById('modal-edit-group').classList.add('hidden')" class="text-zinc-500 hover:text-white text-xl leading-none">&times;</button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=update_group" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="group_id" id="edit-group-id">

            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Group Name (cn) *</label>
                <input type="text" name="name" id="edit-group-name" required
                       class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">GID Number (posixGroup)</label>
                <input type="number" name="gid_number" id="edit-group-gid"
                       class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Description</label>
                <textarea name="description" id="edit-group-desc" rows="2"
                          class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-white focus:outline-none focus:border-engine-500"></textarea>
            </div>

            <div class="pt-4 flex justify-end gap-2 border-t border-zinc-800">
                <button type="button" onclick="document.getElementById('modal-edit-group').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-750 text-xs font-semibold text-zinc-300">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-white hover:bg-zinc-200 text-xs font-bold text-zinc-950">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditGroupModal(group) {
    document.getElementById('edit-group-id').value = group.id;
    document.getElementById('edit-group-name').value = group.name;
    document.getElementById('edit-group-gid').value = group.gid_number || '';
    document.getElementById('edit-group-desc').value = group.description || '';
    document.getElementById('modal-edit-group').classList.remove('hidden');
}
</script>
