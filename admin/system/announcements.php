<?php
require_once dirname(__DIR__, 2) . '/config.php';
Auth::requirePermission('manage_announcements');

$pageTitle = 'Announcements';
$pageSubtitle = 'Publish news to the website or send it straight to portal users';
$activeMenu = 'announcements';

$announcements = Database::all('SELECT a.*, u.full_name AS author FROM announcements a LEFT JOIN users u ON u.id = a.created_by ORDER BY a.id DESC LIMIT 50');
require_once dirname(__DIR__, 2) . '/backend/includes/layout/dash-header.php';
?>
<div class="panel">
    <div class="panel-head"><h2>New announcement</h2></div>
    <form method="post" action="<?= url('backend/api/announcements/save.php') ?>">
        <div class="form-grid">
            <div class="field field-full"><label for="title">Title</label><input id="title" name="title" required></div>
            <div class="field">
                <label for="audience">Audience</label>
                <select id="audience" name="audience">
                    <option value="public">Public website</option>
                    <option value="students">Students</option>
                    <option value="parents">Parents</option>
                    <option value="staff">Staff</option>
                    <option value="all">Everyone</option>
                </select>
            </div>
            <div class="field field-full"><label for="body">Message</label><textarea id="body" name="body" rows="5" required></textarea></div>
        </div>
        <button class="btn btn-primary" type="submit">Publish</button>
    </form>
</div>

<div class="panel mt-3">
    <div class="panel-head"><h2>Published</h2></div>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>Date</th><th>Title</th><th>Audience</th><th>By</th></tr></thead>
            <tbody>
            <?php foreach ($announcements as $item): ?>
                <tr>
                    <td><?= pretty_date($item['created_at']) ?></td>
                    <td><?= e($item['title']) ?><br><small class="muted"><?= e(mb_substr($item['body'], 0, 120)) ?></small></td>
                    <td><span class="badge badge-purple"><?= e(ucfirst($item['audience'])) ?></span></td>
                    <td><?= e($item['author'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$announcements): ?><tr><td colspan="4" class="muted">Nothing published yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__, 2) . '/backend/includes/layout/dash-footer.php'; ?>
