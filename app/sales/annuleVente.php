<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole(array('admin', 'gestionnaire'), '../views/login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/toutesCommandes.php');
    exit;
}
verifierCsrf('application');
$idVente = filter_input(INPUT_POST, 'idVente', FILTER_VALIDATE_INT);

try {
    if (!$idVente) {
        throw new DomainException('Identifiant de vente invalide.');
    }

    $connexion->beginTransaction();

    $reqVente = $connexion->prepare(
        'SELECT id_article, quantite FROM vente WHERE id = ? AND etat = 1 FOR UPDATE'
    );
    $reqVente->execute([$idVente]);
    $vente = $reqVente->fetch(PDO::FETCH_ASSOC);

    if (!$vente) {
        throw new DomainException('Vente introuvable ou deja annulee.');
    }

    $idArticle = (int) $vente['id_article'];
    $quantite = (int) $vente['quantite'];
    if ($quantite < 1) {
        throw new DomainException('Quantite de vente invalide.');
    }

    $reqArticle = $connexion->prepare('SELECT quantite FROM article WHERE id = ? FOR UPDATE');
    $reqArticle->execute([$idArticle]);
    $article = $reqArticle->fetch(PDO::FETCH_ASSOC);
    if (!$article) {
        throw new DomainException('Article associe a la vente introuvable.');
    }

    $stockAvant = (int) $article['quantite'];
    $stockApres = $stockAvant + $quantite;

    $reqAnnulation = $connexion->prepare('UPDATE vente SET etat = 0 WHERE id = ? AND etat = 1');
    $reqAnnulation->execute([$idVente]);
    $reqStock = $connexion->prepare('UPDATE article SET quantite = ? WHERE id = ?');
    $reqStock->execute([$stockApres, $idArticle]);
    enregistrerMouvementStock(
        $idArticle,
        'entree',
        $quantite,
        $stockAvant,
        $stockApres,
        'Annulation vente #' . $idVente
    );

    $connexion->commit();
    $_SESSION['message'] = ['text' => 'Vente annulee et stock remis a jour.', 'type' => 'success'];
} catch (DomainException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => $e->getMessage(), 'type' => 'danger'];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => 'Impossible d annuler cette vente.', 'type' => 'danger'];
}

header('Location: ../views/toutesCommandes.php');
exit;
