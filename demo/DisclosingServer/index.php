<?php
(@include '../../vendor/autoload.php') or die('Please use composer to install required packages.' . PHP_EOL);

\Loader::registerNew( 'php', NULL, 'classes/' );

new Server();
?>
