<?php
/**
 * Final fix: BookingController + CalendarController
 */

$baseDir = __DIR__;

// === Fix BookingController.php ===
$path = "$baseDir/src/Controller/BookingController.php";
$c = file_get_contents($path);

// Replace imports
$c = str_replace("use App\\Entity\\RendezVous;\n", "use App\\Entity\\Appointment;\n", $c);
$c = str_replace("use App\\Entity\\RendezVous;\r\n", "use App\\Entity\\Appointment;\r\n", $c);
$c = str_replace("use App\\Repository\\RendezVousRepository;\n", "use App\\Repository\\AppointmentRepository;\n", $c);
$c = str_replace("use App\\Repository\\RendezVousRepository;\r\n", "use App\\Repository\\AppointmentRepository;\r\n", $c);

// Replace type hint
$c = str_replace('RendezVousRepository $apptRepo', 'AppointmentRepository $apptRepo', $c);

// Replace class instantiation
$c = str_replace('new RendezVous()', 'new Appointment()', $c);

// Fix method calls - the old RendezVous entity used setAppointmentDate + setStatut
// The Appointment entity uses setDate + setStartTime + setStatus
$c = preg_replace(
    '/\$appointment->setAppointmentDate\(new \\\\DateTime\(\$dateStr \. \' \' \. \$startTimeStr\)\);/',
    "\$appointment->setDate(new \\DateTime(\$dateStr));\n            \$appointment->setStartTime(new \\DateTime(\$startTimeStr));",
    $c
);
$c = str_replace("\$appointment->setStatut('en_attente');", "\$appointment->setStatus('pending');", $c);

// Check for duplicate Appointment import
if (substr_count($c, "use App\\Entity\\Appointment;") > 1) {
    $c = preg_replace("/(use App\\\\Entity\\\\Appointment;\r?\n)([\s\S]*?)(use App\\\\Entity\\\\Appointment;\r?\n)/", "$1$2", $c);
}

// Check for duplicate AppointmentRepository import
if (substr_count($c, "use App\\Repository\\AppointmentRepository;") > 1) {
    $first = strpos($c, "use App\\Repository\\AppointmentRepository;");
    $second = strpos($c, "use App\\Repository\\AppointmentRepository;", $first + 1);
    if ($second !== false) {
        // Remove the second occurrence and its newline
        $lineEnd = strpos($c, "\n", $second);
        if ($lineEnd !== false) {
            $c = substr($c, 0, $second) . substr($c, $lineEnd + 1);
        }
    }
}

file_put_contents($path, $c);
echo "BookingController: " . (strpos($c, 'RendezVous') === false ? "CLEAN" : "STILL HAS REFS") . "\n";

// === Fix CalendarController.php ===
$path2 = "$baseDir/src/Controller/CalendarController.php";
$c2 = file_get_contents($path2);

// Remove unused RendezVousRepository import
$c2 = str_replace("use App\\Repository\\RendezVousRepository;\r\n", "", $c2);
$c2 = str_replace("use App\\Repository\\RendezVousRepository;\n", "", $c2);

// Remove RendezVousRepository parameter from selectAppointment
// The line is:        RendezVousRepository $rendezVousRepository
$c2 = preg_replace('/,\s*\n\s*RendezVousRepository\s+\$rendezVousRepository/', '', $c2);
$c2 = preg_replace('/RendezVousRepository\s+\$rendezVousRepository,?\s*\n/', '', $c2);

file_put_contents($path2, $c2);
echo "CalendarController: " . (strpos($c2, 'RendezVous') === false ? "CLEAN" : "STILL HAS REFS") . "\n";

// === FINAL VERIFICATION ===
echo "\n=== FINAL VERIFICATION (src/) ===\n";
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$baseDir/src"));
$found = false;
foreach ($iterator as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php')
        continue;
    $content = file_get_contents($file->getPathname());
    if (preg_match('/\bRendezVous\b/', $content)) {
        $lines = explode("\n", $content);
        foreach ($lines as $n => $line) {
            if (preg_match('/\bRendezVous\b/', $line)) {
                echo basename($file->getPathname()) . ':' . ($n + 1) . ': ' . trim($line) . "\n";
                $found = true;
            }
        }
    }
}
if (!$found)
    echo "ALL CLEAN! No RendezVous references in src/\n";

echo "\nDone!\n";
