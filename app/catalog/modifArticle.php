<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole(array('admin', 'gestionnaire'), '../views/login.php');
verifierCsrf('application');

$idArticle = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nomArticle = trim($_POST['nom_article'] ?? '');
$categorie = trim($_POST['categorie'] ?? '');
$quantite = filter_input(INPUT_POST, 'quantite', FILTER_VALIDATE_INT);
$prix = $_POST['prix_unitaire'] ?? null;
$dateFabrication = str_replace('T', ' ', $_POST['date_fabrication'] ?? '');
$dateExpiration = str_replace('T', ' ', $_POST['date_expiration'] ?? '');

if (!$idArticle || $nomArticle === '' || $categorie === '' || $quantite === false || $quantite === null
    || $quantite < 0 || !is_numeric($prix) || (float) $prix < 0
    || $dateFabrication === '' || $dateExpiration === '') {
    $_SESSION['message'] = ['text' => 'Veuillez renseigner des valeurs valides.', 'type' => 'danger'];
    header('Location: ../views/article.php');
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
    $req = $connexion->prepare(
        'UPDATE article
         SET nom_article = ?, categorie = ?, quantite = ?, prix_unitaire = ?,
             date_fabrication = ?, date_expiration = ?
         WHERE id = ?'
    );
    $req->execute([
        $nomArticle,
        $categorie,
        $quantite,
        $prix,
        $dateFabrication,
        $dateExpiration,
        $idArticle,
    ]);

    if ($quantite !== $stockAvant) {
        $type = $quantite > $stockAvant ? 'entree' : 'sortie';
        $ecart = abs($quantite - $stockAvant);
        enregistrerMouvementStock(
            $idArticle,
            $type,
            $ecart,
            $stockAvant,
            $quantite,
            'Ajustement depuis la fiche article'
        );
    }

    $connexion->commit();
    $_SESSION['message'] = $req->rowCount() > 0
        ? ['text' => 'Article modifie avec succes.', 'type' => 'success']
        : ['text' => "Rien n a ete modifie.", 'type' => 'warning'];
} catch (DomainException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => $e->getMessage(), 'type' => 'danger'];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    $_SESSION['message'] = ['text' => 'Impossible de modifier cet article.', 'type' => 'danger'];
}

header('Location: ../views/article.php');
exit;
