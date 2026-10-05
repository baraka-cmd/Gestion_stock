<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';

verifierTransportAuthentification();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login.php');
    exit;
}
verifierCsrf('authentification');
effacerSessionAuthentification();
header('Location: ../views/login.php');
exit;