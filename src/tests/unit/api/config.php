<?php
$config = require(__DIR__ . '/../../../config/web.php');

// 追加ここから
foreach ($config['components']['log']['targets'] as &$target) {
    $target['enabled'] = false;
}

return $config;
