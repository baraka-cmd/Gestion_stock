<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');

    $connexionSecurisee = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(array(
        'lifetime' => 0,
        'path' => '/',
        'secure' => $connexionSecurisee,
        'httponly' => true,
        'samesite' => 'Strict',
    ));
    session_start();
}

$nom_serveur = 'localhost';
$nom_base_de_donnees = 'gestion_stock_dclic';
$utilisateur = 'root';
$motpass = '';

try {
    $connexion = new PDO(
        "mysql:host=$nom_serveur;dbname=$nom_base_de_donnees;charset=utf8mb4",
        $utilisateur,
        $motpass,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        )
    );
} catch (Throwable $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(503);
    exit('Le service est temporairement indisponible.');
}
