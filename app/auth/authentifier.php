<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';

verifierTransportAuthentification();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login.php');
    exit;
}
verifierCsrf('authentification');

$emailSaisi = isset($_POST['email']) && is_string($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
$motDePasse = isset($_POST['mot_de_passe']) && is_string($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';
$adresseIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$emailValide = filter_var($emailSaisi, FILTER_VALIDATE_EMAIL) && strlen($emailSaisi) <= 254;

if (verifierLimiteTentatives($emailSaisi, $adresseIp)) {
    $_SESSION['message'] = ['text' => 'Connexion temporairement indisponible. Reessayez dans quelques minutes.', 'type' => 'danger'];
    header('Location: ../views/login.php');
    exit;
}

$utilisateur = false;
if ($emailValide) {
    $req = $connexion->prepare(
        'SELECT id, nom, prenom, email, role, actif, photo, session_version, mot_de_passe_hash
         FROM utilisateur WHERE email = ? LIMIT 1'
    );
    $req->execute(array($emailSaisi));
    $utilisateur = $req->fetch(PDO::FETCH_ASSOC);
}

$hash = $utilisateur && is_string($utilisateur['mot_de_passe_hash']) && $utilisateur['mot_de_passe_hash'] !== ''
    ? $utilisateur['mot_de_passe_hash']
    : AUTH_HASH_FACTICE;
$motDePasseValide = password_verify($motDePasse, $hash);

if (!$emailValide || !$utilisateur || (int) $utilisateur['actif'] !== 1
    || empty($utilisateur['mot_de_passe_hash']) || !$motDePasseValide) {
    enregistrerEchecConnexion($emailSaisi, $adresseIp);
    $_SESSION['message'] = ['text' => 'Adresse courriel ou mot de passe incorrect.', 'type' => 'danger'];
    header('Location: ../views/login.php');
    exit;
}

if (password_needs_rehash($utilisateur['mot_de_passe_hash'], PASSWORD_DEFAULT)) {
    $nouveauHash = password_hash($motDePasse, PASSWORD_DEFAULT);
    $reqRehash = $connexion->prepare(
        'UPDATE utilisateur SET mot_de_passe_hash = ?, session_version = session_version + 1 WHERE id = ?'
    );
    $reqRehash->execute(array($nouveauHash, $utilisateur['id']));
    $utilisateur['session_version'] = (int) $utilisateur['session_version'] + 1;
}

effacerTentativesConnexion($emailSaisi, $adresseIp);
enregistrerSessionAuthentifiee($utilisateur);
header('Location: ../views/dashboard.php');
exit;