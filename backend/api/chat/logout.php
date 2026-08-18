<?php
require_once dirname(__DIR__, 3) . '/config.php';

$me = ChatSystem::user();
if ($me) {
    ChatSystem::setStatus((int) $me['id'], 'offline');
}
unset($_SESSION['chat_user']);
redirect('/chat/login.php');
