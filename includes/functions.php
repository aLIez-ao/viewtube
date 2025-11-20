<?php
/* includes/functions.php */

function formatDuration($seconds) {
    if ($seconds < 3600) {
        // Minutos:Segundos (ej. 05:30)
        return gmdate("i:s", $seconds);
    } else {
        // Horas:Minutos:Segundos (ej. 01:05:30)
        return gmdate("H:i:s", $seconds);
    }
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return "hace unos segundos";
    if ($diff < 3600) return "hace " . floor($diff/60) . " min";
    if ($diff < 86400) return "hace " . floor($diff/3600) . " h";
    if ($diff < 604800) return "hace " . floor($diff/86400) . " días";
    return date("d M Y", $time);
}
?>