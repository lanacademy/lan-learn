<?php
/**
 * Tracks outbound URLs
 *
 * @author Timothy Su
 * @link http://www.timofeo.com/
 * @license http://opensource.org/licenses/MIT
 */
    if (isset($_GET['url']) && isset($_GET['user'])) {
        $dest = $_GET['url'];
        $user = $_GET['user'];
        $data = "[OUT] - ";
        $data = $data . date('Y/m/d H:i:s');
        $data = $data . " - " . $user . " - " . $dest . "\n";
        file_put_contents('./log/' . $user . '.log', $data);
    }
    else {
        $dest = '/';
    }
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
    header('Location: ' . $dest);
    exit;
?>