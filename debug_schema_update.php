<?php
$output = [];
$return_var = 0;
exec('php bin/console doctrine:schema:update --force -vvv 2>&1', $output, $return_var);
file_put_contents('update_log.txt', implode("\n", $output));
echo "Done with return code: $return_var\n";
