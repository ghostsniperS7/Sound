<?php
// Transparent forwarder to videos.php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    header("Location: /sound/videos.php?id={$id}", true, 301);
} else {
    header("Location: /sound/videos.php", true, 301);
}
exit;
