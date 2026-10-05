<?php
require_once __DIR__ . '/../auth/utilisateur_guard.php';

const AUTH_DUREE_INACTIVITE = 1800;
const AUTH_TENTATIVES_MAX = 8;
const AUTH_FENETRE_MINUTES = 15;
const AUTH_HASH_FACTICE = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

function verifierTransportAuthentification($configurationInitiale = false)
{
    $adresse = $_SERVER['REMOTE_ADDR'] ?? '';
    $estLocal = in_array($adresse, array('127.0.0.1', '::1'), true);
    $estHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    if ($configurationInitiale && !$estLocal) {
        http_response_code(403);
        exit('La creation du premier administrateur est reservee a la machine locale.');
    }
    if (!$estLocal && !$estHttps) {
        http_response_code(403);
        exit('Une connexion HTTPS est requise.');
    }
}

function redirigerVersConnexion($destination = null)
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        unset($_SESSION['utilisateur'], $_SESSION['auth_derniere_activite']);
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        http_response_code(401);
        exit('Authentification requise.');
    }

    header('Location: ' . ($destination ?: '../views/login.php'));
    exit;
}

function effacerSessionAuthentification()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $parametres = session_get_cookie_params();
        setcookie(session_name(), '', array(
            'expires' => time() - 42000,
            'path' => $parametres['path'],
            'domain' => $parametres['domain'],
            'secure' => $parametres['secure'],
            'httponly' => $parametres['httponly'],
            'samesite' => $parametres['samesite'] ?? 'Strict',
        ));
    }
    session_destroy();
}

function normaliserUtilisateurSession($utilisateur)
{
    if (!is_array($utilisateur)) {
        return null;
    }

    return array(
        'id' => (int) ($utilisateur['id'] ?? 0),
        'nom' => (string) ($utilisateur['nom'] ?? ''),
        'prenom' => (string) ($utilisateur['prenom'] ?? ''),
        'email' => (string) ($utilisateur['email'] ?? ''),
        'role' => (string) ($utilisateur['role'] ?? ''),
        'photo' => isset($utilisateur['photo']) ? (string) $utilisateur['photo'] : '',
        'session_version' => isset($utilisateur['session_version']) ? (int) $utilisateur['session_version'] : 0,
    );
}

function authentifierUtilisateurCourant($destination = null)
{
    verifierTransportAuthentification();
    $identite = $_SESSION['utilisateur'] ?? null;
    $derniereActivite = (int) ($_SESSION['auth_derniere_activite'] ?? 0);
    if (!is_array($identite) || empty($identite['id']) || !$derniereActivite
        || time() - $derniereActivite > AUTH_DUREE_INACTIVITE) {
        effacerSessionAuthentification();
        redirigerVersConnexion($destination);
    }

    $req = $GLOBALS['connexion']->prepare(
        'SELECT id, nom, prenom, email, role, actif, photo, session_version
         FROM utilisateur WHERE id = ? AND actif = 1'
    );
    $req->execute(array((int) $identite['id']));
    $utilisateur = $req->fetch(PDO::FETCH_ASSOC);
    if (!$utilisateur) {
        effacerSessionAuthentification();
        redirigerVersConnexion($destination);
    }

    $versionSession = array_key_exists('session_version', $identite) ? (int) $identite['session_version'] : null;
    $versionBase = array_key_exists('session_version', $utilisateur) && $utilisateur['session_version'] !== null
        ? (int) $utilisateur['session_version']
        : null;

    if ($versionSession !== null && $versionBase !== null && $versionSession !== $versionBase) {
        effacerSessionAuthentification();
        redirigerVersConnexion($destination);
    }

    $sessionAuthentifiee = normaliserUtilisateurSession($utilisateur);
    if ($sessionAuthentifiee === null) {
        effacerSessionAuthentification();
        redirigerVersConnexion($destination);
    }

    if ($versionSession !== null && $versionBase === null) {
        $sessionAuthentifiee['session_version'] = $versionSession;
    }

    $_SESSION['utilisateur'] = $sessionAuthentifiee;
    $_SESSION['auth_derniere_activite'] = time();
    return $_SESSION['utilisateur'];
}

function exigerRole($roles, $destination = null)
{
    if (!is_array($roles)) {
        $roles = array($roles);
    }
    $utilisateur = authentifierUtilisateurCourant($destination);
    if (!in_array($utilisateur['role'], $roles, true)) {
        http_response_code(403);
        exit('Vous n avez pas les droits necessaires.');
    }
    return $utilisateur;
}

function enregistrerSessionAuthentifiee($utilisateur)
{
    session_regenerate_id(true);
    $_SESSION = array();
    $sessionAuthentifiee = normaliserUtilisateurSession($utilisateur);
    if ($sessionAuthentifiee === null) {
        throw new InvalidArgumentException('Donnees utilisateur invalides pour la session.');
    }

    $_SESSION['utilisateur'] = $sessionAuthentifiee;
    $_SESSION['auth_derniere_activite'] = time();
}

function validerMotDePasse($motDePasse)
{
    return is_string($motDePasse) && strlen($motDePasse) >= 12 && strlen($motDePasse) <= 72;
}

function verifierLimiteTentatives($email, $adresseIp)
{
    $emailHash = hash('sha256', strtolower(trim($email)));
    $ipHash = hash('sha256', $adresseIp);
    $req = $GLOBALS['connexion']->prepare(
        'SELECT COUNT(*) FROM auth_tentative_connexion
         WHERE tente_le >= DATE_SUB(NOW(), INTERVAL ' . AUTH_FENETRE_MINUTES . ' MINUTE)
           AND (email_hash = ? OR ip_hash = ?)'
    );
    $req->execute(array($emailHash, $ipHash));
    return (int) $req->fetchColumn() >= AUTH_TENTATIVES_MAX;
}

function enregistrerEchecConnexion($email, $adresseIp)
{
    $GLOBALS['connexion']->exec('DELETE FROM auth_tentative_connexion WHERE tente_le < DATE_SUB(NOW(), INTERVAL 1 DAY)');
    $req = $GLOBALS['connexion']->prepare(
        'INSERT INTO auth_tentative_connexion (email_hash, ip_hash) VALUES (?, ?)'
    );
    $req->execute(array(hash('sha256', strtolower(trim($email))), hash('sha256', $adresseIp)));
}

function effacerTentativesConnexion($email, $adresseIp)
{
    $req = $GLOBALS['connexion']->prepare(
        'DELETE FROM auth_tentative_connexion WHERE email_hash = ? AND ip_hash = ?'
    );
    $req->execute(array(hash('sha256', strtolower(trim($email))), hash('sha256', $adresseIp)));
}

function premierAdministrateurConfigure()
{
    $req = $GLOBALS['connexion']->query('SELECT admin_initialise FROM auth_configuration WHERE id = 1');
    $configuration = $req->fetch(PDO::FETCH_ASSOC);
    if (!$configuration) {
        throw new RuntimeException('Configuration de securite absente.');
    }

    if ((int) $configuration['admin_initialise'] === 1) {
        return true;
    }

    $reqAdmin = $GLOBALS['connexion']->query(
        "SELECT COUNT(*) FROM utilisateur WHERE role = 'admin' AND actif = 1 AND mot_de_passe_hash IS NOT NULL"
    );
    return (int) $reqAdmin->fetchColumn() > 0;
}