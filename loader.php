<?php

define('ROOT_DIR', __DIR__ . DIRECTORY_SEPARATOR);
define('GV_APP_DIR', ROOT_DIR . 'app' . DIRECTORY_SEPARATOR);
define('GV_STARTUP_DIR', ROOT_DIR . 'boot' . DIRECTORY_SEPARATOR);
define('GV_CORE_DIR', ROOT_DIR . 'core' . DIRECTORY_SEPARATOR);
define('GV_STORAGE_DIR', ROOT_DIR . 'storage' . DIRECTORY_SEPARATOR);

// load system
require GV_CORE_DIR . 'system/Base.php';
require GV_CORE_DIR . 'system/Guavas.php';

$gv = new Guavas();
$gv->initialize();
$gv->run(WEB_MODE);