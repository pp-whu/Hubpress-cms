<?php
// One-time file fixer — delete after use
$src  = __DIR__ . '/../app/Views/admin/menus/index_new.php';
$dest = __DIR__ . '/../app/Views/admin/menus/index.php';
if (file_exists($src)) {
    file_put_contents($dest, file_get_contents($src));
    unlink($src);
    unlink(__FILE__);
    echo "OK";
} else {
    echo "src not found";
}
