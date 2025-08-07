<?php
require_once 'includes/functions.php';

startSession();

// Destroy all session data
session_destroy();

// Redirect to home page
header('Location: index.php');
exit();
?>