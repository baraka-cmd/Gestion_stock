    <?php
        include 'entete.php';
        $configuration = getConfiguration();
        $idVente = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $vente = $idVente ? getVente($idVente, true) : false;
    ?>
    <div class="home-content">
        <?php if (!$vente) { ?>
            <p>Vente introuvable.</p>
        <?php } else { ?>
        <div class="button">
            <button class="hidden-print" id="btnPrint"><i class="fa-solid fa-print" aria-hidden="true"></i> Imprimer</button>
        </div>
        <div class="page">
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
                    <p>Recu N : <?= $vente['id'] ?></p>
                    <p>Date : <?= date('d/m/Y H:i:s', strtotime( $vente['date_vente'])) ?></p>
                    <p>Etat : <?= (string) $vente['etat'] === '1' ? 'Active' : 'Annulee' ?></p>
                </div>
            </div>

            <div class="cote-a-cote" style="width: 50%;">
                <p>Nom : </p>
                <p><?= htmlspecialchars($vente['nom'] . ' ' . $vente['prenom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> </p>
            </div>

            <div class="cote-a-cote" style="width: 50%;">
                <p>Tel : </p>
                <p><?= htmlspecialchars($vente['telephone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> </p>
            </div>

            <div class="cote-a-cote" style="width: 50%;">
                <p>Adresse : </p>
                <p><?= htmlspecialchars($vente['adresse'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> </p>
            </div>

            <br><br>
            <table class="mtable">
                <tr>
                    <th>Designation</th>
                    <th>Quantite</th>
                    <th>Prix Unitaire</th>
                    <th>Prix Total</th>
                </tr>

           
                <tr>
                    <td><?= htmlspecialchars($vente['nom_article'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td><?= (int) $vente['quantite'] ?></td>
                    <td><?= number_format((float) $vente['prix_unitaire'], 0, ',', ' ') ?> <?= htmlspecialchars($configuration['devise'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td><?= number_format((float) $vente['prix'], 0, ',', ' ') ?> <?= htmlspecialchars($configuration['devise'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>

                </tr>
                        
            </table>
        </div>
        <?php if (!empty($configuration['texte_pied_recu'])) { ?>
            <p><?= htmlspecialchars($configuration['texte_pied_recu'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php } ?>
        <?php } ?>
    </div>
    <?php
    include 'pied.php';
    ?>


<script>

    var btnPrint = document.querySelector('#btnPrint');
    btnPrint.addEventListener("click", () => {
        window.print();
    });
    function setPrix() {
        var article = document.querySelector('#id_article');
        var quantite = document.querySelector('#quantite');
        var prix = document.querySelector('#prix');

        var prixUnitaire = article.options[article.selectedIndex].getAttribute('data-prix');
        prix.value = Number(quantite.value) * Number(prixUnitaire);
    }
</script>