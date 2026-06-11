<?php
echo "PHP: " . PHP_VERSION . PHP_EOL;
echo "SAPI: " . php_sapi_name() . PHP_EOL;
echo "INI: " . php_ini_loaded_file() . PHP_EOL;
echo "Ext dir: " . ini_get('extension_dir') . PHP_EOL;
echo "pdo_pgsql: " . (extension_loaded('pdo_pgsql') ? 'yes' : 'no') . PHP_EOL;
echo "pgsql: " . (extension_loaded('pgsql') ? 'yes' : 'no') . PHP_EOL;
