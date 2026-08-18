<?php
require_once dirname(__DIR__, 3) . '/config.php';

Auth::logout();
redirect('/portal/login.php');
