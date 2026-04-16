<?php
$c = file_get_contents('src/Controller/IaController.php');
$c = str_replace('`n', "\n", $c);
file_put_contents('src/Controller/IaController.php', $c);
echo "Fixed IaController.php literals.\n";
