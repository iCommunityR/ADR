<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
  $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
  session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
  session_start();
}
define('ROOT', dirname(__DIR__));
$config = require ROOT . '/config/config.php';
date_default_timezone_set($config['app']['timezone']);
require_once ROOT . '/includes/data.php';
require_once ROOT . '/includes/i18n.php';
require_once ROOT . '/includes/functions.php';
current_language();
