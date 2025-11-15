<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    echo json_encode([
        'loggedin' => true,
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role']
    ]);
} else {
    echo json_encode(['loggedin' => false]);
}
