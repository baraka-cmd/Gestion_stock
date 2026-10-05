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

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
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

if (!$id || $nom === '' || $longueur($nom) > 80 || $prenom === '' || $longueur($prenom) > 80
    || !filter_var($email, FILTER_VALIDATE_EMAIL) || $longueur($email) > 254
    || $longueur($telephone) > 30 || ($telephone !== '' && !preg_match('/^[0-9+().\- ]+$/', $telephone))
    || !validerRoleUtilisateur($role)
    || (($motDePasse !== '' || $confirmation !== '')
        && (!validerMotDePasse($motDePasse) || !hash_equals($motDePasse, $confirmation)))) {
    $_SESSION['message'] = ['text' => 'Verifiez les champs, le role et la confirmation du mot de passe.', 'type' => 'danger'];
    header('Location: ../views/utilisateur.php');
    exit;
}

$nouvellePhoto = null;
$anciennePhoto = null;
try {
    $nouvellePhoto = stockerPhotoUtilisateur($_FILES['photo'] ?? null, false);
    $connexion->beginTransaction();
    $actuel = verifierDernierAdministrateur($connexion, $id, $role, 1);
    $anciennePhoto = $actuel['photo'];
    $photo = $nouvellePhoto ?? $anciennePhoto;

    if ($motDePasse !== '') {
        $req = $connexion->prepare(
            'UPDATE utilisateur
             SET nom = ?, prenom = ?, email = ?, telephone = ?, role = ?, photo = ?,
                 mot_de_passe_hash = ?, session_version = session_version + 1
             WHERE id = ?'
        );
        $req->execute([
            $nom,
            $prenom,
            $email,
            $telephone === '' ? null : $telephone,
            $role,
            $photo,
            password_hash($motDePasse, PASSWORD_DEFAULT),
            $id,
        ]);
    } else {
        $req = $connexion->prepare(
            'UPDATE utilisateur
             SET nom = ?, prenom = ?, email = ?, telephone = ?, role = ?, photo = ?
             WHERE id = ?'
        );
        $req->execute([$nom, $prenom, $email, $telephone === '' ? null : $telephone, $role, $photo, $id]);
    }
    $connexion->commit();

    if ($motDePasse !== '' && (int) ($_SESSION['utilisateur']['id'] ?? 0) === $id) {
        $_SESSION['utilisateur']['session_version']++;
        session_regenerate_id(true);
    }

    if ($nouvellePhoto !== null && $anciennePhoto !== $nouvellePhoto) {
        supprimerPhotoUtilisateur($anciennePhoto);
    }
    $_SESSION['message'] = ['text' => 'Utilisateur modifie avec succes.', 'type' => 'success'];
} catch (DomainException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    supprimerPhotoUtilisateur($nouvellePhoto);
    $_SESSION['message'] = ['text' => $e->getMessage(), 'type' => 'danger'];
} catch (PDOException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    supprimerPhotoUtilisateur($nouvellePhoto);
    $_SESSION['message'] = [
        'text' => $e->getCode() === '23000'
            ? 'Cette adresse courriel est deja utilisee.'
            : 'Impossible de modifier cet utilisateur.',
        'type' => 'danger',
    ];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    supprimerPhotoUtilisateur($nouvellePhoto);
    $_SESSION['message'] = ['text' => 'Impossible de modifier cet utilisateur.', 'type' => 'danger'];
}

header('Location: ../views/utilisateur.php');
exit;