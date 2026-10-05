<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole(array('admin', 'gestionnaire'), '../views/login.php');
verifierCsrf('application');

$idArticle = filter_input(INPUT_POST, 'id_article', FILTER_VALIDATE_INT);
$idFournisseur = filter_input(INPUT_POST, 'id_fournisseur', FILTER_VALIDATE_INT);
$quantite = filter_input(INPUT_POST, 'quantite', FILTER_VALIDATE_INT);
$prix = $_POST['prix'] ?? null;

if (!$idArticle || !$idFournisseur || !$quantite || $quantite < 1 || !is_numeric($prix) || (float) $prix < 0) {
    $_SESSION['message'] = ['text' => 'Veuillez renseigner des valeurs valides.', 'type' => 'danger'];
    header('Location: ../views/commande.php');
    exit;
}

try {
    $connexion->beginTransaction();

    $reqArticle = $connexion->prepare('SELECT quantite FROM article WHERE id = ? FOR UPDATE');
    $reqArticle->execute([$idArticle]);
    $article = $reqArticle->fetch(PDO::FETCH_ASSOC);
    if (!$article) {
        throw new DomainException('Article introuvable.');
    }

    $stockAvant = (int) $article['quantite'];
    $stockApres = $stockAvant + $quantite;

    $reqCommande = $connexion->prepare(
        'INSERT INTO commande (id_article, id_fournisseur, quantite, prix) VALUES (?, ?, ?, ?)'
    );
    $reqCommande->execute([$idArticle, $idFournisseur, $quantite, $prix]);
    $idCommande = $connexion->lastInsertId();

    $reqStock = $connexion->prepare('UPDATE article SET quantite = ? WHERE id = ?');
    $reqStock->execute([$stockApres, $idArticle]);
    enregistrerMouvementStock(
        $idArticle,
        'entree',
        $quantite,
        $stockAvant,
        $stockApres,
        'Commande #' . $idCommande
    );

    $connexion->commit();
    $_SESSION['message'] = ['text' => 'Commande effectuee avec succes.', 'type' => 'success'];
} catch (DomainException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => $e->getMessage(), 'type' => 'danger'];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => 'Impossible d enregistrer cette commande.', 'type' => 'danger'];
}

header('Location: ../views/commande.php');
exit;
