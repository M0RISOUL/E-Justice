<?php
// C:\xampp\htdocs\e-justice\logout.php

require_once __DIR__ . '/config/db.php';

start_session();          // helper in db.php
session_unset();          // clear all session data
session_destroy();        // destroy the session
setcookie(session_name(), '', time() - 3600, '/'); // expire cookie

header('Location: ' . BASE_URL . '/login.php');  // always correct
exit;
