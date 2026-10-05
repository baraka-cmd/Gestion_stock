<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole(array('admin', 'gestionnaire'), '../views/login.php');
verifierCsrf('application');

$idArticle = filter_input(INPUT_POST, 'id_article', FILTER_VALIDATE_INT);
$idClient = filter_input(INPUT_POST, 'id_client', FILTER_VALIDATE_INT);
$quantite = filter_input(INPUT_POST, 'quantite', FILTER_VALIDATE_INT);
$prix = $_POST['prix'] ?? null;

if (!$idArticle || !$idClient || !$quantite || $quantite < 1 || !is_numeric($prix) || (float) $prix < 0) {
    $_SESSION['message'] = ['text' => 'Veuillez renseigner des valeurs valides.', 'type' => 'danger'];
    header('Location: ../views/vente.php');
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
    if ($quantite > $stockAvant) {
        throw new DomainException("La quantite a vendre n'est pas disponible.");
    }
    $stockApres = $stockAvant - $quantite;

    $reqVente = $connexion->prepare(
        'INSERT INTO vente (id_article, id_client, quantite, prix) VALUES (?, ?, ?, ?)'
    );
    $reqVente->execute([$idArticle, $idClient, $quantite, $prix]);
    $idVente = $connexion->lastInsertId();

    $reqStock = $connexion->prepare('UPDATE article SET quantite = ? WHERE id = ?');
    $reqStock->execute([$stockApres, $idArticle]);

    enregistrerMouvementStock(
        $idArticle,
        'sortie',
        $quantite,
        $stockAvant,
        $stockApres,
        'Vente #' . $idVente
    );

    $connexion->commit();
    $_SESSION['message'] = ['text' => 'Vente effectuee avec succes.', 'type' => 'success'];
} catch (DomainException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => $e->getMessage(), 'type' => 'danger'];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => 'Impossible d effectuer cette vente.', 'type' => 'danger'];
}

header('Location: ../views/vente.php');
exit;
