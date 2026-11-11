<?php
// NSBM Study Circle - Student Directory Login Forwarder
$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header("Location: ../login.php" . $queryString);
exit();

