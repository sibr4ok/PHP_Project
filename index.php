<?php

define('VG_ACCESS', true);
#Заголовки:
header('Content-Type:text/html;charset=utf-8');
#Начала сессии
session_start();

require_once 'config.php';
require_once 'core/base/settings/internal_settings.php';

function load1($class_name){
    $class_name = str_replace('\\', '/', $class_name);
    include  $class_name.'.php';
}

spl_autoload_register('load1');

new \n1\A();