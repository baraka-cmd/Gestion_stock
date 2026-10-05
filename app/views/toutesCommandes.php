<?php
include 'entete.php';

$type = $_GET['type'] ?? 'tous';
if (!in_array($type, array('tous', 'vente', 'commande'), true)) {
    $type = 'tous';
}

$etat = isset($_GET['etat']) && in_array((string) $_GET['etat'], array('0', '1'), true)
    ? (string) $_GET['etat']
    : '';
$recherche = isset($_GET['recherche']) && is_string($_GET['recherche'])
    ? trim($_GET['recherche'])
    : '';
$normaliserDate = static function ($valeur) {
    if (!is_string($valeur)) {
        return '';
    }
    $date = DateTime::createFromFormat('!Y-m-d', $valeur);
    return $date && $date->format('Y-m-d') === $valeur ? $valeur : '';
};
$dateDebut = $normaliserDate($_GET['date_debut'] ?? '');
$dateFin = $normaliserDate($_GET['date_fin'] ?? '');
if ($dateDebut !== '' && $dateFin !== '' && $dateDebut > $dateFin) {
    $dateDebut = '';
    $dateFin = '';
}

$filtres = array(
    'type' => $type === 'tous' ? '' : $type,
    'etat' => $etat,
    'date_debut' => $dateDebut,
    'date_fin' => $dateFin,
    'recherche' => $recherche,
);
$parPage = 10;
$total = compterToutesCommandes($filtres);
$totalPages = max(1, (int) ceil($total / $parPage));
$page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
$page = max(1, min($totalPages, $page === false ? 1 : $page));
$commandes = getToutesCommandes($filtres, $parPage, ($page - 1) * $parPage);
$parametresPage = array(
    'type' => $type,
    'etat' => $etat,
    'date_debut' => $dateDebut,
    'date_fin' => $dateFin,
    'recherche' => $recherche,
);
$urlPage = static function ($numero) use ($parametresPage) {
    return '?' . http_build_query(array_merge($parametresPage, array('page' => $numero)));
};
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
?>
<div class="home-content">
    <div class="overview-boxes">
        <div class="box" style="display:block; width:100%;">
            <h2>Toutes les commandes</h2>
            <form method="GET" action="toutesCommandes.php">
                <label for="recherche">Recherche (article, tiers ou reference)</label>
                <input type="search" id="recherche" name="recherche"
                       value="<?= htmlspecialchars($recherche, ENT_QUOTES, 'UTF-8') ?>">

                <label for="type">Type</label>
                <select name="type" id="type">
                    <option value="tous" <?= $type === 'tous' ? 'selected' : '' ?>>Tous</option>
                    <option value="vente" <?= $type === 'vente' ? 'selected' : '' ?>>Ventes</option>
                    <option value="commande" <?= $type === 'commande' ? 'selected' : '' ?>>Commandes fournisseur</option>
                </select>

                <label for="etat">Etat</label>
                <select name="etat" id="etat">
                    <option value="">Tous</option>
                    <option value="1" <?= $etat === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $etat === '0' ? 'selected' : '' ?>>Annulee</option>
                </select>

                <label for="date_debut">Du</label>
                <input type="date" id="date_debut" name="date_debut" value="<?= htmlspecialchars($dateDebut, ENT_QUOTES, 'UTF-8') ?>">
                <label for="date_fin">Au</label>
                <input type="date" id="date_fin" name="date_fin" value="<?= htmlspecialchars($dateFin, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i> Filtrer</button>
                <a class="text-link" href="toutesCommandes.php"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Reinitialiser</a>
            </form>

            <?php if (!empty($message['text'])) { ?>
                <div class="alert <?= htmlspecialchars($message['type'] ?? 'danger', ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($message['text'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php } ?>

            <p><?= $total ?> resultat(s), page <?= $page ?> sur <?= $totalPages ?>.</p>
            <div style="overflow-x:auto;">
                <table class="mtable">
                    <tr>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Article</th>
                        <th>Client / fournisseur</th>
                        <th>Quantite</th>
                        <th>Prix</th>
                        <th>Etat</th>
                        <th>Actions</th>
                    </tr>
                    <?php if (!$commandes) { ?>
                        <tr><td colspan="9">Aucun resultat pour ces filtres.</td></tr>
                    <?php } ?>
                    <?php foreach ($commandes as $commande) {
                        $estVente = $commande['type_commande'] === 'vente';
                        $estActive = (string) $commande['etat'] === '1';
                        $reference = ($estVente ? 'V-' : 'C-') . (int) $commande['id'];
                        $dateAffichee = date('d/m/Y H:i', strtotime($commande['date_operation']));
                        $urlModification = $estVente
                            ? 'vente.php?id=' . (int) $commande['id']
                            : 'commande.php?id=' . (int) $commande['id'];
                        $urlAnnulation = $estVente
                            ? '../annuleVente.php?idVente=' . (int) $commande['id']
                            : '../annuleCommande.php?id=' . (int) $commande['id'];
                        $urlRecu = $estVente
                            ? 'recuVente.php?id=' . (int) $commande['id']
                            : 'recuCommande.php?id=' . (int) $commande['id'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $estVente ? 'Vente' : 'Commande fournisseur' ?></td>
                            <td><?= htmlspecialchars($dateAffichee, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($commande['nom_article'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($commande['tiers'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) $commande['quantite'] ?></td>
                            <td><?= number_format((float) $commande['prix'], 0, ',', ' ') ?> F</td>
                            <td><?= $estActive ? 'Active' : 'Annulee' ?></td>
                            <td>
                                <?php if ($peutModifier && $estActive) { ?>
                                                <a href="<?= htmlspecialchars($urlModification, ENT_QUOTES, 'UTF-8') ?>"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Modifier</a>
                                                <form action="<?= htmlspecialchars($urlAnnulation, ENT_QUOTES, 'UTF-8') ?>" method="POST" class="inline-action" onsubmit="return confirm('Confirmer l annulation ?')">
                                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($jetonApplication, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="<?= $estVente ? 'idVente' : 'id' ?>" value="<?= (int) $commande['id'] ?>">
                                                    <button class="action-danger" type="submit"><i class="fa-solid fa-ban" aria-hidden="true"></i> Annuler</button>
                                                </form>
                                <?php } ?>
                                          <a href="<?= htmlspecialchars($urlRecu, ENT_QUOTES, 'UTF-8') ?>"><i class="fa-solid fa-print" aria-hidden="true"></i> Imprimer</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <nav aria-label="Pagination">
                <?php if ($page > 1) { ?>
                    <a href="<?= htmlspecialchars($urlPage(1), ENT_QUOTES, 'UTF-8') ?>"><i class="fa-solid fa-angles-left" aria-hidden="true"></i> Premiere</a>
                    <a href="<?= htmlspecialchars($urlPage($page - 1), ENT_QUOTES, 'UTF-8') ?>"><i class="fa-solid fa-angle-left" aria-hidden="true"></i> Precedente</a>
                <?php } ?>
                <span>Page <?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages) { ?>
                    <a href="<?= htmlspecialchars($urlPage($page + 1), ENT_QUOTES, 'UTF-8') ?>">Suivante <i class="fa-solid fa-angle-right" aria-hidden="true"></i></a>
                    <a href="<?= htmlspecialchars($urlPage($totalPages), ENT_QUOTES, 'UTF-8') ?>">Derniere <i class="fa-solid fa-angles-right" aria-hidden="true"></i></a>
                <?php } ?>
            </nav>
        </div>
    </div>
</div>
<?php include 'pied.php'; ?>