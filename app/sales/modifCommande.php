<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole(array('admin', 'gestionnaire'), '../views/login.php');
verifierCsrf('application');

$idCommande = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$idArticle = filter_input(INPUT_POST, 'id_article', FILTER_VALIDATE_INT);
$idFournisseur = filter_input(INPUT_POST, 'id_fournisseur', FILTER_VALIDATE_INT);
$quantite = filter_input(INPUT_POST, 'quantite', FILTER_VALIDATE_INT);
$prix = $_POST['prix'] ?? null;

if (!$idCommande || !$idArticle || !$idFournisseur || !$quantite || $quantite < 1
    || !is_numeric($prix) || (float) $prix < 0) {
    $_SESSION['message'] = ['text' => 'Veuillez renseigner des valeurs valides.', 'type' => 'danger'];
    header('Location: ../views/toutesCommandes.php');
    exit;
}

try {
    $connexion->beginTransaction();
    $reqCommande = $connexion->prepare(
        'SELECT id_article, quantite FROM commande WHERE id = ? AND etat = 1 FOR UPDATE'
    );
    $reqCommande->execute([$idCommande]);
    $ancienneCommande = $reqCommande->fetch(PDO::FETCH_ASSOC);
    if (!$ancienneCommande) {
        throw new DomainException('Commande introuvable ou deja annulee.');
    }

    $ancienArticleId = (int) $ancienneCommande['id_article'];
    $ancienneQuantite = (int) $ancienneCommande['quantite'];
    $reqArticles = $connexion->prepare(
        'SELECT id, quantite FROM article WHERE id IN (?, ?) ORDER BY id FOR UPDATE'
    );
    $reqArticles->execute([$ancienArticleId, $idArticle]);
    $stocks = [];
    foreach ($reqArticles->fetchAll(PDO::FETCH_ASSOC) as $article) {
        $stocks[(int) $article['id']] = (int) $article['quantite'];
    }
    if (!array_key_exists($ancienArticleId, $stocks) || !array_key_exists($idArticle, $stocks)) {
        throw new DomainException('Article introuvable.');
    }

    if ($ancienArticleId === $idArticle) {
        $stockAvant = $stocks[$idArticle];
        $stockApres = $stockAvant - $ancienneQuantite + $quantite;
        if ($stockApres < 0) {
            throw new DomainException('Stock insuffisant pour reduire cette commande.');
        }
        if ($stockApres !== $stockAvant) {
            $type = $stockApres > $stockAvant ? 'entree' : 'sortie';
            enregistrerMouvementStock(
                $idArticle,
                $type,
                abs($stockApres - $stockAvant),
                $stockAvant,
                $stockApres,
                'Modification commande #' . $idCommande
            );
            $reqStock = $connexion->prepare('UPDATE article SET quantite = ? WHERE id = ?');
            $reqStock->execute([$stockApres, $idArticle]);
        }
    } else {
        $stockAncienAvant = $stocks[$ancienArticleId];
        $stockAncienApres = $stockAncienAvant - $ancienneQuantite;
        if ($stockAncienApres < 0) {
            throw new DomainException('Impossible de remplacer l article: une partie du stock a deja ete vendue.');
        }
        $stockNouveauAvant = $stocks[$idArticle];
        $stockNouveauApres = $stockNouveauAvant + $quantite;

        $reqStock = $connexion->prepare('UPDATE article SET quantite = ? WHERE id = ?');
        $reqStock->execute([$stockAncienApres, $ancienArticleId]);
        $reqStock->execute([$stockNouveauApres, $idArticle]);
        enregistrerMouvementStock(
            $ancienArticleId,
            'sortie',
            $ancienneQuantite,
            $stockAncienAvant,
            $stockAncienApres,
            'Remplacement commande #' . $idCommande
        );
        enregistrerMouvementStock(
            $idArticle,
            'entree',
            $quantite,
            $stockNouveauAvant,
            $stockNouveauApres,
            'Modification commande #' . $idCommande
        );
    }

    $reqUpdate = $connexion->prepare(
        'UPDATE commande SET id_article = ?, id_fournisseur = ?, quantite = ?, prix = ?
         WHERE id = ? AND etat = 1'
    );
    $reqUpdate->execute([$idArticle, $idFournisseur, $quantite, $prix, $idCommande]);

    $connexion->commit();
    $_SESSION['message'] = ['text' => 'Commande modifiee avec succes.', 'type' => 'success'];
} catch (DomainException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => $e->getMessage(), 'type' => 'danger'];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => 'Impossible de modifier cette commande.', 'type' => 'danger'];
}

header('Location: ../views/toutesCommandes.php');
exit;