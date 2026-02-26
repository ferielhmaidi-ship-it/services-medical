<?php
// diag_validate.php
$output = shell_exec('php bin/console doctrine:schema:validate 2>&1');
file_put_contents('diag_validate_output.txt', $output);
echo $output;
