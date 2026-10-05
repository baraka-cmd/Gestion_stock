<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole(array('admin', 'gestionnaire'), '../views/login.php');
verifierCsrf('application');

$nomArticle = trim($_POST['nom_article'] ?? '');
$categorie = trim($_POST['categorie'] ?? '');
$quantite = filter_input(INPUT_POST, 'quantite', FILTER_VALIDATE_INT);
$prix = $_POST['prix_unitaire'] ?? null;
$dateFabrication = str_replace('T', ' ', $_POST['date_fabrication'] ?? '');
$dateExpiration = str_replace('T', ' ', $_POST['date_expiration'] ?? '');

if ($nomArticle === '' || $categorie === '' || $quantite === false || $quantite === null || $quantite < 0
    || !is_numeric($prix) || (float) $prix < 0 || $dateFabrication === '' || $dateExpiration === '') {
    $_SESSION['message'] = ['text' => 'Veuillez renseigner des valeurs valides.', 'type' => 'danger'];
    header('Location: ../views/article.php');
    exit;
}

try {
    $connexion->beginTransaction();
    $req = $connexion->prepare(
        'INSERT INTO article
            (nom_article, categorie, quantite, prix_unitaire, date_fabrication, date_expiration)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $req->execute([$nomArticle, $categorie, $quantite, $prix, $dateFabrication, $dateExpiration]);
    $idArticle = $connexion->lastInsertId();

    enregistrerMouvementStock($idArticle, 'entree', $quantite, 0, $quantite, 'Stock initial');

    $connexion->commit();
    $_SESSION['message'] = ['text' => 'Article ajoute avec succes.', 'type' => 'success'];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => "Impossible d ajouter l article.", 'type' => 'danger'];
}

header('Location: ../views/article.php');
exit;
