<?php

define('VG_ACCESS', true);
#Заголовки:
header('Content-Type:text/html;charset=utf-8');
#Начала сессии
session_start();

require_once 'config.php';
require_once 'core/base/settings/internal_settings.php';

use \core\base\exceptions\RouteException;
use \core\base\controllers\RouteController;

try
{
    RouteController::getInstance()->route();
}
//обработка исключения
catch(RouteException $e)
{
    exit($e->getMessage());

}