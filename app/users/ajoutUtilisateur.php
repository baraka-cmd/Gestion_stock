<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/utilisateur_guard.php';
require_once __DIR__ . '/../core/photoUtilisateur.php';
require_once __DIR__ . '/../auth/auth.php';

exigerRole('admin', '../views/login.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/utilisateur.php');
    exit;
}
verifierCsrfUtilisateurs();

$nom = isset($_POST['nom']) && is_string($_POST['nom']) ? trim($_POST['nom']) : '';
$prenom = isset($_POST['prenom']) && is_string($_POST['prenom']) ? trim($_POST['prenom']) : '';
$email = isset($_POST['email']) && is_string($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
$telephone = isset($_POST['telephone']) && is_string($_POST['telephone']) ? trim($_POST['telephone']) : '';
$role = $_POST['role'] ?? '';
$motDePasse = isset($_POST['mot_de_passe']) && is_string($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';
$confirmation = isset($_POST['confirmation']) && is_string($_POST['confirmation']) ? $_POST['confirmation'] : '';
$longueur = static function ($valeur) {
    return function_exists('mb_strlen') ? mb_strlen($valeur, 'UTF-8') : strlen($valeur);
};

if ($nom === '' || $longueur($nom) > 80 || $prenom === '' || $longueur($prenom) > 80
    || !filter_var($email, FILTER_VALIDATE_EMAIL) || $longueur($email) > 254
    || $longueur($telephone) > 30 || ($telephone !== '' && !preg_match('/^[0-9+().\- ]+$/', $telephone))
    || !validerRoleUtilisateur($role) || !validerMotDePasse($motDePasse)
    || !hash_equals($motDePasse, $confirmation)) {
    $_SESSION['message'] = ['text' => 'Verifiez les champs, le role et le mot de passe confirme (12 a 72 caracteres).', 'type' => 'danger'];
    header('Location: ../views/utilisateur.php');
    exit;
}

$photo = null;
try {
    $photo = stockerPhotoUtilisateur($_FILES['photo'] ?? null, true);
    $req = $connexion->prepare(
        'INSERT INTO utilisateur (nom, prenom, email, mot_de_passe_hash, telephone, role, photo)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $req->execute([
        $nom,
        $prenom,
        $email,
        password_hash($motDePasse, PASSWORD_DEFAULT),
        $telephone === '' ? null : $telephone,
        $role,
        $photo,
    ]);
    $_SESSION['message'] = ['text' => 'Utilisateur ajoute avec succes.', 'type' => 'success'];
} catch (DomainException $e) {
    supprimerPhotoUtilisateur($photo);
    $_SESSION['message'] = ['text' => $e->getMessage(), 'type' => 'danger'];
} catch (PDOException $e) {
    supprimerPhotoUtilisateur($photo);
    $_SESSION['message'] = [
        'text' => $e->getCode() === '23000'
            ? 'Cette adresse courriel est deja utilisee.'
            : 'Impossible d enregistrer cet utilisateur.',
        'type' => 'danger',
    ];
} catch (Throwable $e) {
    supprimerPhotoUtilisateur($photo);
    $_SESSION['message'] = ['text' => 'Impossible d enregistrer cet utilisateur.', 'type' => 'danger'];
}

header('Location: ../views/utilisateur.php');
exit;