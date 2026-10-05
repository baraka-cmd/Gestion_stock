<?php
include 'entete.php';

$configuration = getConfiguration();
$idCommande = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$commande = $idCommande ? getCommande($idCommande) : false;
?>
<div class="home-content">
    <div class="page">
        <?php if (!$commande) { ?>
            <p>Commande introuvable.</p>
        <?php } else { ?>
            <div class="button hidden-print">
                <button type="button" id="btnPrint"><i class="fa-solid fa-print" aria-hidden="true"></i> Imprimer</button>
            </div>
            <div class="cote-a-cote">
                <div>
                    <?php if (is_string($configuration['logo']) && preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $configuration['logo'])) { ?>
                        <img src="../../public/uploads/configuration/<?= rawurlencode($configuration['logo']) ?>" alt="Logo" style="max-width:180px; max-height:80px; object-fit:contain;">
                    <?php } ?>
                    <h2><?= htmlspecialchars($configuration['nom_entreprise'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    <?php if (!empty($configuration['adresse'])) { ?><p><?= htmlspecialchars($configuration['adresse'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p><?php } ?>
                    <?php if (!empty($configuration['telephone'])) { ?><p><?= htmlspecialchars($configuration['telephone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p><?php } ?>
                    <?php if (!empty($configuration['email'])) { ?><p><?= htmlspecialchars($configuration['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p><?php } ?>
                </div>
                <div>
                    <p>Bon de commande N : <?= (int) $commande['id'] ?></p>
                    <p>Date : <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($commande['date_commande'])), ENT_QUOTES, 'UTF-8') ?></p>
                    <p>Etat : <?= (string) $commande['etat'] === '1' ? 'Active' : 'Annulee' ?></p>
                </div>
            </div>
            <div class="cote-a-cote" style="width: 50%;">
                <p>Fournisseur :</p>
                <p><?= htmlspecialchars($commande['nom'] . ' ' . $commande['prenom'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="cote-a-cote" style="width: 50%;">
                <p>Telephone :</p>
                <p><?= htmlspecialchars($commande['telephone'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="cote-a-cote" style="width: 50%;">
                <p>Adresse :</p>
                <p><?= htmlspecialchars($commande['adresse'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <table class="mtable">
                <tr>
                    <th>Designation</th>
                    <th>Quantite</th>
                    <th>Prix total</th>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($commande['nom_article'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int) $commande['quantite'] ?></td>
                    <td><?= number_format((float) $commande['prix'], 0, ',', ' ') ?> <?= htmlspecialchars($configuration['devise'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                </tr>
            </table>
            <?php if (!empty($configuration['texte_pied_recu'])) { ?>
                <p><?= htmlspecialchars($configuration['texte_pied_recu'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php } ?>
        <?php } ?>
    </div>
</div>
<?php include 'pied.php'; ?>
<script>
    const printButton = document.querySelector('#btnPrint');
    if (printButton) {
        printButton.addEventListener('click', () => window.print());
    }
</script>