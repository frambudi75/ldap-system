<?php
/**
 * View: User Management (Tactile Precision Edition)
 */
$pdo = Database::getConnection();
$baseDn = get_base_dn();

// Search & Filter
$search = trim($_GET['q'] ?? '');
$query = "
    SELECT u.*, 
           GROUP_CONCAT(g.name SEPARATOR ', ') as group_names,
           GROUP_CONCAT(g.id SEPARATOR ',') as group_ids
    FROM ldap_users u
    LEFT JOIN ldap_group_members gm ON u.id = gm.user_id
    LEFT JOIN ldap_groups g ON gm.group_id = g.id
";
$params = [];
if (!empty($search)) {
    $query .= " WHERE u.username LIKE :s1 OR u.full_name LIKE :s2 OR u.email LIKE :s3 ";
    $params['s1'] = "%{$search}%";
    $params['s2'] = "%{$search}%";
    $params['s3'] = "%{$search}%";
}
$query .= " GROUP BY u.id ORDER BY u.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get all groups for assignment
$allGroups = $pdo->query("SELECT id, name FROM ldap_groups ORDER BY name ASC")->fetchAll();
?>

<main class="flex-1 bg-zinc-950 p-6 md:p-8 overflow-y-auto bg-grid-pattern">
    <!-- Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-6 border-b border-zinc-850">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h1 class="text-xl font-extrabold text-white tracking-tight">Directory Accounts</h1>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-850 text-zinc-300 border border-zinc-750"><?= count($users) ?> entries</span>
            </div>
            <p class="text-xs text-zinc-400">Kelola entitas pengguna LDAP (`inetOrgPerson` / `posixAccount`)</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="<?= BASE_URL ?>/index.php" class="relative">
                <input type="hidden" name="page" value="users">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Filter accounts..."
                       class="w-56 pl-8 pr-3 py-2 rounded-xl bg-zinc-900 border border-zinc-750 text-xs font-mono text-white placeholder-zinc-500 focus:outline-none focus:border-engine-500 transition-colors">
                <svg class="w-3.5 h-3.5 text-zinc-500 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </form>
            <button onclick="document.getElementById('modal-add-user').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-zinc-200 text-xs font-bold text-zinc-950 shadow transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                <span>New Account</span>
            </button>
        </div>
    </div>

    <!-- Users Table -->
    <div class="tactile-card rounded-2xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-zinc-300">
                <thead class="text-[10px] text-zinc-400 uppercase font-mono bg-zinc-950 border-b border-zinc-800">
                    <tr>
                        <th class="px-5 py-3.5">Account / DN</th>
                        <th class="px-5 py-3.5">Full Name & Contact</th>
                        <th class="px-5 py-3.5">Assigned Groups</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-850">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-zinc-500 font-sans">
                                Tidak ada data pengguna yang sesuai filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): 
                            $userDn = "uid={$user['username']},ou={$user['ou']},{$baseDn}";
                        ?>
                            <tr class="hover:bg-zinc-900/60 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-zinc-800 border border-zinc-700 flex items-center justify-center font-bold text-xs text-engine-400 shrink-0 font-mono">
                                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <span class="font-bold text-white text-xs font-mono"><?= htmlspecialchars($user['username']) ?></span>
                                            <span class="block text-[10px] text-zinc-400 font-mono select-all truncate max-w-xs" title="<?= htmlspecialchars($userDn) ?>">
                                                <?= htmlspecialchars($userDn) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-zinc-200"><?= htmlspecialchars($user['full_name']) ?></div>
                                    <div class="text-[11px] text-zinc-400 font-mono"><?= htmlspecialchars($user['email'] ?? '-') ?></div>
                                    <?php if ($user['department']): ?>
                                        <div class="text-[10px] text-engine-400 font-medium"><?= htmlspecialchars($user['department']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5">
                                    <?php if (!empty($user['group_names'])): ?>
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach (explode(',', $user['group_names']) as $gn): ?>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-zinc-800 text-zinc-300 border border-zinc-700">
                                                    <?= htmlspecialchars(trim($gn)) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-zinc-600 text-xs font-mono">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5">
                                    <form method="POST" action="<?= BASE_URL ?>/index.php?action=toggle_user_status" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" title="Toggle active status"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded font-mono text-[10px] font-bold cursor-pointer transition-all <?= $user['status'] === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30 hover:bg-rose-500/20' ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $user['status'] === 'active' ? 'bg-emerald-400' : 'bg-rose-400' ?>"></span>
                                            <?= strtoupper($user['status']) ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button onclick='openResetModal(<?= json_encode($user) ?>)'
                                                title="Reset Password"
                                                class="p-1.5 rounded-lg text-zinc-400 hover:text-amber-400 hover:bg-zinc-800 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                        </button>
                                        <button onclick='openEditModal(<?= json_encode($user) ?>)'
                                                title="Edit Account"
                                                class="p-1.5 rounded-lg text-zinc-400 hover:text-engine-400 hover:bg-zinc-800 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <?php if ($user['username'] !== 'admin'): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/index.php?action=delete_user" onsubmit="return confirm('Hapus permanen pengguna <?= htmlspecialchars($user['username']) ?>?');" class="inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" title="Delete Account" class="p-1.5 rounded-lg text-zinc-400 hover:text-rose-400 hover:bg-zinc-800 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal: Add User -->
<div id="modal-add-user" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden p-4">
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-lg shadow-2xl p-6 relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-800 mb-5">
            <h3 class="text-sm font-bold text-white font-mono uppercase tracking-wider">New Directory Account</h3>
            <button onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="text-zinc-500 hover:text-white text-xl leading-none">&times;</button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=create_user" class="space-y-4">
            <?= csrf_field() ?>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Username (uid) *</label>
                    <input type="text" name="username" required placeholder="john"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Password *</label>
                    <input type="password" name="password" required placeholder="Min 6 chars"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Full Name (cn) *</label>
                <input type="text" name="full_name" required placeholder="John Doe"
                       class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-white focus:outline-none focus:border-engine-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Given Name</label>
                    <input type="text" name="first_name" placeholder="John"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-white focus:outline-none focus:border-engine-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Surname (sn)</label>
                    <input type="text" name="last_name" placeholder="Doe"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-white focus:outline-none focus:border-engine-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Email (mail)</label>
                <input type="email" name="email" placeholder="john@internal.net"
                       class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Department</label>
                    <input type="text" name="department" placeholder="Engineering"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-white focus:outline-none focus:border-engine-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Telephone</label>
                    <input type="text" name="telephone" placeholder="+628..."
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Assign Groups</label>
                <div class="grid grid-cols-2 gap-2 p-3 rounded-xl bg-zinc-950 border border-zinc-800 max-h-28 overflow-y-auto">
                    <?php foreach ($allGroups as $grp): ?>
                        <label class="flex items-center gap-2 text-xs text-zinc-300 cursor-pointer">
                            <input type="checkbox" name="group_ids[]" value="<?= $grp['id'] ?>" class="rounded bg-zinc-900 border-zinc-700 text-engine-600 focus:ring-0">
                            <span class="font-mono text-xs"><?= htmlspecialchars($grp['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-2 border-t border-zinc-800">
                <button type="button" onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-750 text-xs font-semibold text-zinc-300">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-white hover:bg-zinc-200 text-xs font-bold text-zinc-950 shadow">Create Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reset Password -->
<div id="modal-reset-password" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden p-4">
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-sm shadow-2xl p-6">
        <div class="flex items-center justify-between pb-3 border-b border-zinc-800 mb-4">
            <h3 class="text-xs font-bold text-white font-mono uppercase tracking-wider">Reset Password</h3>
            <button onclick="document.getElementById('modal-reset-password').classList.add('hidden')" class="text-zinc-500 hover:text-white text-xl leading-none">&times;</button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=reset_user_password" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" id="reset-user-id">
            <div>
                <span class="text-xs text-zinc-400">Account:</span>
                <span id="reset-user-name" class="font-bold text-zinc-100 text-xs ml-1 font-mono"></span>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">New Password *</label>
                <input type="password" name="new_password" required placeholder="Enter new password"
                       class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
            </div>
            <div class="pt-3 flex justify-end gap-2 border-t border-zinc-800">
                <button type="button" onclick="document.getElementById('modal-reset-password').classList.add('hidden')" class="px-3 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-750 text-xs font-semibold text-zinc-300">Cancel</button>
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-white hover:bg-zinc-200 text-xs font-bold text-zinc-950">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit User -->
<div id="modal-edit-user" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden p-4">
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-lg shadow-2xl p-6 relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-800 mb-5">
            <h3 class="text-sm font-bold text-white font-mono uppercase tracking-wider">Modify Account</h3>
            <button onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="text-zinc-500 hover:text-white text-xl leading-none">&times;</button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/index.php?action=update_user" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" id="edit-user-id">

            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Username (uid)</label>
                <input type="text" id="edit-username" disabled
                       class="w-full px-3 py-2 rounded-xl bg-zinc-950/50 border border-zinc-800 text-xs font-mono text-zinc-500 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Full Name (cn) *</label>
                <input type="text" name="full_name" id="edit-full-name" required
                       class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-white focus:outline-none focus:border-engine-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Given Name</label>
                    <input type="text" name="first_name" id="edit-first-name"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-white focus:outline-none focus:border-engine-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Surname (sn)</label>
                    <input type="text" name="last_name" id="edit-last-name"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-white focus:outline-none focus:border-engine-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Email (mail)</label>
                <input type="email" name="email" id="edit-email"
                       class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Department</label>
                    <input type="text" name="department" id="edit-department"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-white focus:outline-none focus:border-engine-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Telephone</label>
                    <input type="text" name="telephone" id="edit-telephone"
                           class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs font-mono text-white focus:outline-none focus:border-engine-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-zinc-300 uppercase font-mono mb-1">Group Membership</label>
                <div class="grid grid-cols-2 gap-2 p-3 rounded-xl bg-zinc-950 border border-zinc-800 max-h-28 overflow-y-auto">
                    <?php foreach ($allGroups as $grp): ?>
                        <label class="flex items-center gap-2 text-xs text-zinc-300 cursor-pointer">
                            <input type="checkbox" name="group_ids[]" value="<?= $grp['id'] ?>" class="edit-grp-check rounded bg-zinc-900 border-zinc-700 text-engine-600 focus:ring-0">
                            <span class="font-mono text-xs"><?= htmlspecialchars($grp['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-2 border-t border-zinc-800">
                <button type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-750 text-xs font-semibold text-zinc-300">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-white hover:bg-zinc-200 text-xs font-bold text-zinc-950 shadow">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openResetModal(user) {
    document.getElementById('reset-user-id').value = user.id;
    document.getElementById('reset-user-name').innerText = user.username + ' (' + user.full_name + ')';
    document.getElementById('modal-reset-password').classList.remove('hidden');
}

function openEditModal(user) {
    document.getElementById('edit-user-id').value = user.id;
    document.getElementById('edit-username').value = user.username;
    document.getElementById('edit-full-name').value = user.full_name;
    document.getElementById('edit-first-name').value = user.first_name || '';
    document.getElementById('edit-last-name').value = user.last_name || '';
    document.getElementById('edit-email').value = user.email || '';
    document.getElementById('edit-department').value = user.department || '';
    document.getElementById('edit-telephone').value = user.telephone || '';

    const assignedIds = (user.group_ids || '').split(',').map(s => s.trim());
    document.querySelectorAll('.edit-grp-check').forEach(cb => {
        cb.checked = assignedIds.includes(cb.value);
    });

    document.getElementById('modal-edit-user').classList.remove('hidden');
}
</script>
