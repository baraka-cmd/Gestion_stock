<?php
include 'connexion.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole(array('admin', 'gestionnaire'), '../views/login.php');
verifierCsrf('application');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/stock.php');
    exit;
}

$idArticle = filter_input(INPUT_POST, 'id_article', FILTER_VALIDATE_INT);
$quantite = filter_input(INPUT_POST, 'quantite', FILTER_VALIDATE_INT);
$typeMouvement = $_POST['type_mouvement'] ?? '';
$motif = trim($_POST['motif'] ?? '');

if (!$idArticle || !$quantite || $quantite < 1 || !in_array($typeMouvement, ['entree', 'sortie'], true) || $motif === '') {
    $_SESSION['message'] = [
        'text' => 'Veuillez renseigner des valeurs valides pour tous les champs.',
        'type' => 'danger',
    ];
    header('Location: ../views/stock.php');
    exit;
}

try {
    $connexion->beginTransaction();

    $requeteArticle = $connexion->prepare('SELECT quantite FROM article WHERE id = ? FOR UPDATE');
    $requeteArticle->execute([$idArticle]);
    $article = $requeteArticle->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        throw new RuntimeException('Article introuvable.');
    }

    $stockAvant = (int) $article['quantite'];
    $stockApres = $typeMouvement === 'entree'
        ? $stockAvant + $quantite
        : $stockAvant - $quantite;

    if ($stockApres < 0) {
        throw new DomainException('La sortie depasse le stock disponible.');
    }

    $requeteStock = $connexion->prepare('UPDATE article SET quantite = ? WHERE id = ?');
    $requeteStock->execute([$stockApres, $idArticle]);

    $requeteMouvement = $connexion->prepare(
        'INSERT INTO mouvement_stock
            (id_article, type_mouvement, quantite, stock_avant, stock_apres, motif)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $requeteMouvement->execute([
        $idArticle,
        $typeMouvement,
        $quantite,
        $stockAvant,
        $stockApres,
        $motif,
    ]);

    $connexion->commit();
    $_SESSION['message'] = [
        'text' => 'Le stock a ete mis a jour.',
        'type' => 'success',
    ];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }

    $_SESSION['message'] = [
        'text' => $e instanceof DomainException
            ? $e->getMessage()
            : 'Impossible d effectuer cet ajustement du stock.',
        'type' => 'danger',
    ];
}

header('Location: ../views/stock.php');
exit;