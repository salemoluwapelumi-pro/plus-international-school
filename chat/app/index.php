<?php
require_once dirname(__DIR__, 2) . '/config.php';

$me = ChatSystem::requireLogin();
ChatSystem::setStatus((int) $me['id'], 'online');

$peerId = (int) ($_GET['peer'] ?? 0);
$peer = $peerId ? Database::one('SELECT cu.*, c.name AS class_name FROM chat_users cu LEFT JOIN school_classes c ON c.id = cu.class_id WHERE cu.id = ?', [$peerId]) : null;
$contacts = ChatSystem::contacts((int) $me['id']);
$messages = $peer ? ChatSystem::conversation((int) $me['id'], $peerId) : [];
if ($peer) {
    ChatSystem::markRead((int) $me['id'], $peerId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>School chat · <?= e(SCHOOL_NAME) ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/dashboard.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/purple-theme.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/mobile-responsive.css') ?>">
<script>window.APP_URL = <?= json_encode(APP_URL) ?>;</script>
</head>
<body class="dash-body">
<header class="dash-topbar" style="left:0">
    <div class="flex-between" style="width:100%">
        <a class="brand" href="<?= url('index.php') ?>">
            <span class="brand-mark">PIS</span>
            <span class="brand-text"><strong>School chat</strong><small><?= e($me['full_name']) ?> · <?= e(ucfirst($me['role'])) ?></small></span>
        </a>
        <div class="topbar-actions">
            <a class="btn btn-ghost btn-sm" href="<?= url('index.php') ?>">School website</a>
            <a class="btn btn-gold btn-sm" href="<?= url('backend/api/chat/logout.php') ?>">Sign out</a>
        </div>
    </div>
</header>

<main class="dash-main" style="margin-left:0;padding:16px">
    <div id="chatApp" class="chat-app" data-me="<?= (int) $me['id'] ?>" data-peer="<?= (int) $peerId ?>">
        <aside class="chat-list">
            <div class="search"><input id="contactSearch" placeholder="Search teachers and students"></div>
            <?php foreach ($contacts as $contact): ?>
                <a class="contact <?= $peerId === (int) $contact['id'] ? 'active' : '' ?>" href="<?= url('chat/app/index.php?peer=' . (int) $contact['id']) ?>">
                    <span class="avatar"><?= e(strtoupper(substr($contact['full_name'], 0, 1))) ?></span>
                    <span class="who">
                        <strong><?= e($contact['full_name']) ?></strong>
                        <small><?= e(ucfirst($contact['role'])) ?><?= $contact['class_name'] ? ' · ' . e($contact['class_name']) : '' ?><?= $contact['last_message'] ? ' — ' . e($contact['last_message']) : '' ?></small>
                    </span>
                    <?php if ((int) $contact['unread'] > 0): ?><span class="badge badge-gold"><?= (int) $contact['unread'] ?></span><?php endif; ?>
                    <span class="status <?= $contact['chat_status'] === 'online' ? 'online' : '' ?>"></span>
                </a>
            <?php endforeach; ?>
            <?php if (!$contacts): ?>
                <div class="empty-state"><div class="ico">💬</div>No contacts yet. Teachers and students appear here once they register for chat.</div>
            <?php endif; ?>
        </aside>

        <section class="chat-thread">
            <?php if ($peer): ?>
                <div class="chat-head">
                    <button class="btn btn-ghost btn-sm hide-desktop" id="chatBack" type="button">←</button>
                    <span class="avatar"><?= e(strtoupper(substr($peer['full_name'], 0, 1))) ?></span>
                    <div>
                        <strong><?= e($peer['full_name']) ?></strong><br>
                        <small class="muted"><?= e(ucfirst($peer['role'])) ?> · <?= $peer['chat_status'] === 'online' ? 'online' : 'last seen ' . pretty_date($peer['last_seen'], 'd M, H:i') ?></small>
                    </div>
                </div>
                <div id="chatScroll" class="chat-scroll">
                    <?php foreach ($messages as $message): ?>
                        <div class="bubble <?= (int) $message['sender_id'] === (int) $me['id'] ? 'me' : 'them' ?>" data-id="<?= (int) $message['id'] ?>">
                            <div><?= nl2br(e($message['body'])) ?></div>
                            <time><?= date('H:i', strtotime($message['created_at'])) ?></time>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$messages): ?>
                        <div class="empty-state">Say hello to start the conversation.</div>
                    <?php endif; ?>
                </div>
                <form id="chatComposer" class="chat-compose" autocomplete="off">
                    <input name="body" placeholder="Type a message…" required>
                    <button class="btn btn-primary" type="submit">Send</button>
                </form>
            <?php else: ?>
                <div class="empty-state"><div class="ico">👋</div><h3>Select a contact</h3><p>Choose a teacher or student on the left to start chatting.</p></div>
            <?php endif; ?>
        </section>
    </div>
</main>

<script src="<?= url('assets/js/main.js') ?>"></script>
<script src="<?= url('assets/js/chat-system.js') ?>"></script>
</body>
</html>
