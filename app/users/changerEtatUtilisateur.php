<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/utilisateur_guard.php';
require_once __DIR__ . '/../auth/auth.php';

exigerRole('admin', '../views/login.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/utilisateur.php');
    exit;
}
verifierCsrfUtilisateurs();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$actif = filter_input(INPUT_POST, 'actif', FILTER_VALIDATE_INT);
if (!$id || !in_array($actif, array(0, 1), true)) {
    $_SESSION['message'] = ['text' => 'Demande invalide.', 'type' => 'danger'];
    header('Location: ../views/utilisateur.php');
    exit;
}

try {
    $connexion->beginTransaction();
    $actuel = verifierDernierAdministrateur($connexion, $id, null, $actif);
    $req = $connexion->prepare('UPDATE utilisateur SET actif = ? WHERE id = ?');
    $req->execute([$actif, $id]);
    $connexion->commit();

    $_SESSION['message'] = [
        'text' => $actif === 1 ? 'Utilisateur active.' : 'Utilisateur desactive.',
        'type' => 'success',
    ];
} catch (DomainException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => $e->getMessage(), 'type' => 'danger'];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => 'Impossible de changer l etat de cet utilisateur.', 'type' => 'danger'];
}

header('Location: ../views/utilisateur.php');
exit;