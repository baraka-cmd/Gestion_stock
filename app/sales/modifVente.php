<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole(array('admin', 'gestionnaire'), '../views/login.php');
verifierCsrf('application');

$idVente = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$idArticle = filter_input(INPUT_POST, 'id_article', FILTER_VALIDATE_INT);
$idClient = filter_input(INPUT_POST, 'id_client', FILTER_VALIDATE_INT);
$quantite = filter_input(INPUT_POST, 'quantite', FILTER_VALIDATE_INT);
$prix = $_POST['prix'] ?? null;

if (!$idVente || !$idArticle || !$idClient || !$quantite || $quantite < 1
    || !is_numeric($prix) || (float) $prix < 0) {
    $_SESSION['message'] = ['text' => 'Veuillez renseigner des valeurs valides.', 'type' => 'danger'];
    header('Location: ../views/toutesCommandes.php');
    exit;
}

try {
    $connexion->beginTransaction();
    $reqVente = $connexion->prepare(
        'SELECT id_article, quantite FROM vente WHERE id = ? AND etat = 1 FOR UPDATE'
    );
    $reqVente->execute([$idVente]);
    $ancienneVente = $reqVente->fetch(PDO::FETCH_ASSOC);
    if (!$ancienneVente) {
        throw new DomainException('Vente introuvable ou deja annulee.');
    }

    $ancienArticleId = (int) $ancienneVente['id_article'];
    $ancienneQuantite = (int) $ancienneVente['quantite'];
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
        $stockApres = $stockAvant + $ancienneQuantite - $quantite;
        if ($stockApres < 0) {
            throw new DomainException('Stock insuffisant pour augmenter cette vente.');
        }
        if ($stockApres !== $stockAvant) {
            $type = $stockApres > $stockAvant ? 'entree' : 'sortie';
            enregistrerMouvementStock(
                $idArticle,
                $type,
                abs($stockApres - $stockAvant),
                $stockAvant,
                $stockApres,
                'Modification vente #' . $idVente
            );
            $reqStock = $connexion->prepare('UPDATE article SET quantite = ? WHERE id = ?');
            $reqStock->execute([$stockApres, $idArticle]);
        }
    } else {
        $stockAncienAvant = $stocks[$ancienArticleId];
        $stockAncienApres = $stockAncienAvant + $ancienneQuantite;
        $stockNouveauAvant = $stocks[$idArticle];
        $stockNouveauApres = $stockNouveauAvant - $quantite;
        if ($stockNouveauApres < 0) {
            throw new DomainException('Stock insuffisant pour changer l article de cette vente.');
        }

        $reqStock = $connexion->prepare('UPDATE article SET quantite = ? WHERE id = ?');
        $reqStock->execute([$stockAncienApres, $ancienArticleId]);
        $reqStock->execute([$stockNouveauApres, $idArticle]);
        enregistrerMouvementStock(
            $ancienArticleId,
            'entree',
            $ancienneQuantite,
            $stockAncienAvant,
            $stockAncienApres,
            'Remplacement vente #' . $idVente
        );
        enregistrerMouvementStock(
            $idArticle,
            'sortie',
            $quantite,
            $stockNouveauAvant,
            $stockNouveauApres,
            'Modification vente #' . $idVente
        );
    }

    $reqUpdate = $connexion->prepare(
        'UPDATE vente SET id_article = ?, id_client = ?, quantite = ?, prix = ?
         WHERE id = ? AND etat = 1'
    );
    $reqUpdate->execute([$idArticle, $idClient, $quantite, $prix, $idVente]);

    $connexion->commit();
    $_SESSION['message'] = ['text' => 'Vente modifiee avec succes.', 'type' => 'success'];
} catch (DomainException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => $e->getMessage(), 'type' => 'danger'];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => 'Impossible de modifier cette vente.', 'type' => 'danger'];
}

header('Location: ../views/toutesCommandes.php');
exit;