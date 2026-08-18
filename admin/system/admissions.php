<?php
/** Admission applications submitted from the website, plus website enquiries. */
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requireRole('superadmin', 'subadmin');

$pageTitle = 'Admission applications';
$activeMenu = 'admissions';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['pending', 'shortlisted', 'admitted', 'rejected'], true) ? $_POST['status'] : 'pending';
    Database::run('UPDATE admission_applications SET status = ? WHERE id = ?', [$status, $id]);
    AuditLogger::log('admission.status', 'application', (string) $id, $status);
    flash('Application marked as ' . $status . '.');
    redirect('/admin/system/admissions.php');
}

$applications = Database::all('SELECT * FROM admission_applications ORDER BY id DESC LIMIT 200');
$messages = Database::all('SELECT * FROM contact_messages ORDER BY id DESC LIMIT 50');

require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>Applications (<?= count($applications) ?>)</h2><input class="table-search" data-table-search="#applicationsTable" placeholder="Search applications"></div>
    <div class="table-wrap">
        <table class="data" id="applicationsTable">
            <thead><tr><th>Reference</th><th>Child</th><th>Class</th><th>Parent</th><th>Contact</th><th>Status</th><th>Update</th></tr></thead>
            <tbody>
            <?php foreach ($applications as $application): ?>
                <tr>
                    <td><?= e($application['reference']) ?></td>
                    <td><?= e($application['child_name']) ?><br><small class="muted"><?= pretty_date($application['date_of_birth']) ?></small></td>
                    <td><?= e($application['class_applied']) ?></td>
                    <td><?= e($application['parent_name']) ?></td>
                    <td><?= e($application['email']) ?><br><small class="muted"><?= e($application['phone']) ?></small></td>
                    <td><span class="badge badge-<?= $application['status'] === 'admitted' ? 'green' : ($application['status'] === 'rejected' ? 'red' : 'gold') ?>"><?= e(ucfirst($application['status'])) ?></span></td>
                    <td>
                        <form method="post" class="flex gap-1">
                            <input type="hidden" name="id" value="<?= (int) $application['id'] ?>">
                            <select name="status">
                                <?php foreach (['pending', 'shortlisted', 'admitted', 'rejected'] as $option): ?>
                                    <option value="<?= $option ?>" <?= $application['status'] === $option ? 'selected' : '' ?>><?= ucfirst($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-ghost btn-sm" type="submit">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$applications): ?><tr><td colspan="7" class="muted">No applications yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>Website enquiries</h2></div>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>Date</th><th>Name</th><th>Contact</th><th>Subject</th><th>Message</th></tr></thead>
            <tbody>
            <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?= pretty_date($message['created_at']) ?></td>
                    <td><?= e($message['name']) ?></td>
                    <td><?= e($message['email']) ?><br><small class="muted"><?= e($message['phone'] ?? '') ?></small></td>
                    <td><?= e($message['subject']) ?></td>
                    <td class="muted"><?= e($message['message']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$messages): ?><tr><td colspan="5" class="muted">No enquiries yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
