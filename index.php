<?php

require_once __DIR__ . '/vendor/autoload.php';

use Core\Application;

$app = Application::make(__DIR__);

require_once __DIR__ . '/bootstrap/routes.php';