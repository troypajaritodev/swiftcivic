<?php
// Logout without requiring database connection
session_start();
session_destroy();
header('Location: index.php');
exit();
