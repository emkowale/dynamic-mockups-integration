<?php
/*
 * File: list-plugin-files.php
 * Description: Outputs a tree-style structure of all files in the plugin directory
 */

function listFiles($dir, $prefix = '') {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        $isLast = $item === end($items);

        echo $prefix . '├── ' . $item . PHP_EOL;

        if (is_dir($path)) {
            listFiles($path, $prefix . '│   ');
        }
    }
}

$pluginPath = __DIR__; // Change this to a specific path if needed
echo basename($pluginPath) . '/' . PHP_EOL;
listFiles($pluginPath);
