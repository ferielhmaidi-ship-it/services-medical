<?php

require __DIR__ . '/vendor/autoload.php';

use App\Entity\Patient;

if (class_exists(Patient::class)) {
    echo "Class App\Entity\Patient exists!\n";
    $p = new Patient();
    echo "Instantiated Patient successfully.\n";
} else {
    echo "Class App\Entity\Patient does NOT exist.\n";
}
