<?php
/** Only the super admin grants permissions to sub-admins, cashiers and teachers. */
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requireRole('superadmin');

$pageTitle = 'Permissions';
$pageSubtitle = 'Grant or withdraw what each staff account can do';
$activeMenu = 'permissions';

$staff = Database::all("SELECT * FROM users WHERE role IN ('subadmin','cashier','teacher') ORDER BY role, full_name");
$selectedId = (int) ($_GET['user_id'] ?? ($staff[0]['id'] ?? 0));
$selected = $selectedId ? Database::one('SELECT * FROM users WHERE id = ?', [$selectedId]) : null;
$granted = $selected ? Permissions::forUser($selected) : [];

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="grid" style="grid-template-columns:300px 1fr;gap:20px">
    <div class="panel">
        <div class="panel-head"><h2>Staff</h2></div>
        <ul class="plain-list">
            <?php foreach ($staff as $person): ?>
                <li>
                    <a class="side-link <?= $selectedId === (int) $person['id'] ? 'active' : '' ?>" href="<?= url('admin/users/permissions.php?user_id=' . (int) $person['id']) ?>">
                        <span><?= e($person['full_name']) ?><br><small class="muted"><?= e(ucfirst($person['role'])) ?></small></span>
                    </a>
                </li>
            <?php endforeach; ?>
            <?php if (!$staff): ?><li class="muted">No staff accounts yet.</li><?php endif; ?>
        </ul>
    </div>

    <div class="panel">
        <?php if ($selected): ?>
            <div class="panel-head">
                <h2><?= e($selected['full_name']) ?></h2>
                <span class="badge badge-purple"><?= e(ucfirst($selected['role'])) ?></span>
            </div>
            <form method="post" action="<?= url('backend/api/users/permissions.php') ?>">
                <input type="hidden" name="user_id" value="<?= (int) $selected['id'] ?>">
                <div class="grid grid-2">
                    <?php foreach (Permissions::ALL as $key => $label): ?>
                        <label class="check">
                            <input type="checkbox" name="permissions[]" value="<?= e($key) ?>" <?= in_array($key, $granted, true) ? 'checked' : '' ?>>
                            <span><strong><?= e($label) ?></strong><br><small class="muted"><?= e($key) ?></small></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <button class="btn btn-primary mt-2" type="submit">Save permissions</button>
            </form>
        <?php else: ?>
            <div class="empty-state">Select a staff account to manage its permissions.</div>
        <?php endif; ?>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
