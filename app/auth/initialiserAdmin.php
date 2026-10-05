<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../core/photoUtilisateur.php';
require_once __DIR__ . '/../config/logoConfiguration.php';

verifierTransportAuthentification(true);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login.php');
    exit;
}
verifierCsrf('authentification');

$lire = static function ($cle) {
    return isset($_POST[$cle]) && is_string($_POST[$cle]) ? trim($_POST[$cle]) : '';
};
$nom = $lire('nom');
$prenom = $lire('prenom');
$email = strtolower($lire('email'));
$telephone = $lire('telephone');
$motDePasse = isset($_POST['mot_de_passe']) && is_string($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';
$confirmation = isset($_POST['confirmation']) && is_string($_POST['confirmation']) ? $_POST['confirmation'] : '';
$longueur = static function ($valeur) {
    return function_exists('mb_strlen') ? mb_strlen($valeur, 'UTF-8') : strlen($valeur);
};

if ($nom === '' || $longueur($nom) > 80 || $prenom === '' || $longueur($prenom) > 80
    || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254
    || $longueur($telephone) > 30 || ($telephone !== '' && !preg_match('/^[0-9+().\- ]+$/', $telephone))
    || !validerMotDePasse($motDePasse) || !hash_equals($motDePasse, $confirmation)) {
    $_SESSION['message'] = ['text' => 'Verifiez les champs et utilisez un mot de passe confirme de 12 a 72 caracteres.', 'type' => 'danger'];
    header('Location: ../views/login.php');
    exit;
}

$photo = null;
try {
    $photo = stockerPhotoUtilisateur($_FILES['photo'] ?? null, true);
    $connexion->beginTransaction();

    $reqSetup = $connexion->query('SELECT admin_initialise FROM auth_configuration WHERE id = 1 FOR UPDATE');
    $setup = $reqSetup->fetch(PDO::FETCH_ASSOC);
    $reqAdmin = $connexion->query(
        "SELECT id FROM utilisateur WHERE role = 'admin' AND actif = 1 AND mot_de_passe_hash IS NOT NULL FOR UPDATE"
    );
    if (!$setup || (int) $setup['admin_initialise'] === 1 || $reqAdmin->fetch(PDO::FETCH_ASSOC)) {
        throw new DomainException('La configuration du premier administrateur est deja terminee.');
    }

    $req = $connexion->prepare(
        'INSERT INTO utilisateur (nom, prenom, email, telephone, role, actif, photo, mot_de_passe_hash)
         VALUES (?, ?, ?, ?, \'admin\', 1, ?, ?)'
    );
    $req->execute([
        $nom,
        $prenom,
        $email,
        $telephone === '' ? null : $telephone,
        $photo,
        password_hash($motDePasse, PASSWORD_DEFAULT),
    ]);
    $id = (int) $connexion->lastInsertId();

    $connexion->exec('UPDATE auth_configuration SET admin_initialise = 1 WHERE id = 1');
    $connexion->commit();

    $reqUtilisateur = $connexion->prepare(
        'SELECT id, nom, prenom, email, role, actif, photo, session_version
         FROM utilisateur WHERE id = ?'
    );
    $reqUtilisateur->execute(array($id));
    $admin = $reqUtilisateur->fetch(PDO::FETCH_ASSOC);
    enregistrerSessionAuthentifiee($admin);
    header('Location: ../views/dashboard.php');
    exit;
} catch (DomainException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    supprimerPhotoUtilisateur($photo);
    $_SESSION['message'] = ['text' => $e->getMessage(), 'type' => 'danger'];
} catch (PDOException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    supprimerPhotoUtilisateur($photo);
    $_SESSION['message'] = [
        'text' => $e->getCode() === '23000'
            ? 'Cette adresse courriel est deja utilisee.'
            : 'Impossible de creer le premier administrateur.',
        'type' => 'danger',
    ];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    supprimerPhotoUtilisateur($photo);
    $_SESSION['message'] = ['text' => 'Impossible de creer le premier administrateur.', 'type' => 'danger'];
}

header('Location: ../views/login.php');
exit;