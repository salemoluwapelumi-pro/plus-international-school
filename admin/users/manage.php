<?php
/** Super admin account management: create, edit, suspend and delete users. */
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requirePermission('manage_users');

$pageTitle = 'User accounts';
$pageSubtitle = 'Create and manage teachers, sub-admins, cashiers, students and parents';
$activeMenu = 'users';

$roleFilter = $_GET['role'] ?? '';
$classes = classes_list();
$sql = 'SELECT u.*, c.name AS class_name FROM users u LEFT JOIN school_classes c ON c.id = u.class_id';
$params = [];
if (in_array($roleFilter, ['subadmin', 'cashier', 'teacher', 'student', 'parent'], true)) {
    $sql .= ' WHERE u.role = ?';
    $params[] = $roleFilter;
}
$sql .= ' ORDER BY u.role, u.full_name';
$users = Database::all($sql, $params);

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2>Add an account</h2>
        <span class="muted">Only the super admin can create staff accounts.</span>
    </div>
    <form method="post" action="<?= url('backend/api/users/save.php') ?>">
        <input type="hidden" name="id" value="">
        <div class="form-grid">
            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="teacher">Teacher</option>
                    <option value="subadmin">Sub-admin</option>
                    <option value="cashier">Cashier</option>
                    <option value="student">Student</option>
                    <option value="parent">Parent</option>
                </select>
            </div>
            <div class="field"><label for="full_name">Full name</label><input id="full_name" name="full_name" required></div>
            <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" required></div>
            <div class="field"><label for="phone">Phone number</label><input id="phone" name="phone"></div>
            <div class="field"><label for="admission_no">Admission number (students)</label><input id="admission_no" name="admission_no" placeholder="PIS/2024/0123"></div>
            <div class="field"><label for="staff_no">Staff number (staff)</label><input id="staff_no" name="staff_no" placeholder="PIS/STF/019"></div>
            <div class="field">
                <label for="class_id">Class</label>
                <select id="class_id" name="class_id"><option value="">—</option>
                    <?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>"><?= e($class['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="gender">Gender</label>
                <select id="gender" name="gender"><option value="">—</option><option value="male">Male</option><option value="female">Female</option></select>
            </div>
            <div class="field"><label for="date_of_birth">Date of birth</label><input id="date_of_birth" type="date" name="date_of_birth"></div>
            <div class="field">
                <label for="student_status">Student status</label>
                <select id="student_status" name="student_status"><option value="new">New student</option><option value="returning">Returning student</option></select>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <div class="flex gap-1"><input id="password" name="password" required><button class="btn btn-ghost btn-sm" type="button" data-generate-password="#password">Generate</button></div>
            </div>
            <div class="field field-full"><label for="address">Address</label><input id="address" name="address"></div>
        </div>
        <button class="btn btn-primary" type="submit">Create account</button>
    </form>
</div>

<div class="panel mt-3">
    <div class="panel-head">
        <h2>All accounts (<?= count($users) ?>)</h2>
        <div class="flex gap-1">
            <input class="table-search" data-table-search="#usersTable" placeholder="Search accounts">
            <form method="get" class="flex gap-1">
                <select name="role" onchange="this.form.submit()">
                    <option value="">All roles</option>
                    <?php foreach (['subadmin' => 'Sub-admins', 'cashier' => 'Cashiers', 'teacher' => 'Teachers', 'student' => 'Students', 'parent' => 'Parents'] as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $roleFilter === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="table-wrap">
        <table class="data" id="usersTable">
            <thead><tr><th>Name</th><th>Role</th><th>Identifier</th><th>Class</th><th>Email</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($users as $row): ?>
                <tr>
                    <td><?= e($row['full_name']) ?></td>
                    <td><span class="badge badge-purple"><?= e(ucfirst($row['role'])) ?></span></td>
                    <td><?= e($row['admission_no'] ?: ($row['staff_no'] ?: '—')) ?></td>
                    <td><?= e($row['class_name'] ?? '—') ?></td>
                    <td><?= e($row['email']) ?></td>
                    <td><span class="badge badge-<?= $row['status'] === 'active' ? 'green' : 'red' ?>"><?= e(ucfirst($row['status'])) ?></span></td>
                    <td class="actions">
                        <?php if (Auth::is('superadmin')): ?>
                            <a class="btn btn-ghost btn-sm" href="<?= url('admin/users/permissions.php?user_id=' . (int) $row['id']) ?>">Permissions</a>
                            <form method="post" action="<?= url('backend/api/users/delete.php') ?>" data-confirm="Delete <?= e($row['full_name']) ?>? This cannot be undone." style="display:inline">
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$users): ?><tr><td colspan="7" class="muted">No accounts yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
