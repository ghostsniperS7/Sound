<?php
$users = ['admin' => 'Admin@12345', 'demo1' => 'Demo@12345', 'demo2' => 'Demo@12345'];
foreach ($users as $u => $p) {
    echo "UPDATE users SET password_hash='" . password_hash($p, PASSWORD_DEFAULT) . "' WHERE userid='$u';<br><br>";
}