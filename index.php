<?php

define('VG_ACCESS', true);
#Заголовки:
header('Content-Type:text/html;charset=utf-8');
#Начала сессии
session_start();

require_once 'config.php';
require_once 'core/base/settings/internal_settings.php';
