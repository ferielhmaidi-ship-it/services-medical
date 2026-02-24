<?php
// diag.php
require 'vendor/autoload.php';
use Symfony\Component\ErrorHandler\DebugClassLoader;

DebugClassLoader::enable();

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('src'));
foreach ($it as $file) {
    if ($file->getExtension() === 'php') {
        $class = str_replace(['src\\', '.php', '/'], ['App\\', '', '\\'], $file->getPathname());
        try {
            class_exists($class);
            echo "OK: $class\n";
        }
        catch (\Throwable $e) {
            echo "FAIL: $class - " . $e->getMessage() . "\n";
        }
    }
}
