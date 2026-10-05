<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole(array('admin', 'gestionnaire'), '../views/login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/toutesCommandes.php');
    exit;
}
verifierCsrf('application');
$idCommande = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

try {
    if (!$idCommande) {
        throw new DomainException('Identifiant de commande invalide.');
    }

    $connexion->beginTransaction();
    $reqCommande = $connexion->prepare(
        'SELECT id_article, quantite FROM commande WHERE id = ? AND etat = 1 FOR UPDATE'
    );
    $reqCommande->execute([$idCommande]);
    $commande = $reqCommande->fetch(PDO::FETCH_ASSOC);
    if (!$commande) {
        throw new DomainException('Commande introuvable ou deja annulee.');
    }

    $idArticle = (int) $commande['id_article'];
    $quantite = (int) $commande['quantite'];
    if ($quantite < 1) {
        throw new DomainException('Quantite de commande invalide.');
    }

    $reqArticle = $connexion->prepare('SELECT quantite FROM article WHERE id = ? FOR UPDATE');
    $reqArticle->execute([$idArticle]);
    $article = $reqArticle->fetch(PDO::FETCH_ASSOC);
    if (!$article) {
        throw new DomainException('Article associe a la commande introuvable.');
    }

    $stockAvant = (int) $article['quantite'];
    $stockApres = $stockAvant - $quantite;
    if ($stockApres < 0) {
        throw new DomainException('Stock insuffisant pour annuler cette commande.');
    }

    $reqEtat = $connexion->prepare('UPDATE commande SET etat = 0 WHERE id = ? AND etat = 1');
    $reqEtat->execute([$idCommande]);
    $reqStock = $connexion->prepare('UPDATE article SET quantite = ? WHERE id = ?');
    $reqStock->execute([$stockApres, $idArticle]);
    enregistrerMouvementStock(
        $idArticle,
        'sortie',
        $quantite,
        $stockAvant,
        $stockApres,
        'Annulation commande #' . $idCommande
    );

    $connexion->commit();
    $_SESSION['message'] = ['text' => 'Commande annulee et stock mis a jour.', 'type' => 'success'];
} catch (DomainException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => $e->getMessage(), 'type' => 'danger'];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => 'Impossible d annuler cette commande.', 'type' => 'danger'];
}

header('Location: ../views/toutesCommandes.php');
exit;