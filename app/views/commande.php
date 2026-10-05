    <?php
        include 'entete.php';

        $commande = !empty($_GET['id']) ? getCommande($_GET['id']) : null;
    ?>
    <div class="home-content">
        <div class="overview-boxes">
            <?php if ($peutModifier) { ?>
            <div class="box">
                <form action="<?= !empty($_GET['id']) ? '../modifCommande.php' : '../ajoutCommande.php' ?>" method="POST">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($jetonApplication, ENT_QUOTES, 'UTF-8') ?>">

                    <input value="<?= !empty($commande) ? $commande['id'] : ""?>" type="hidden" name="id" id="id">

                    <label for="nom_article">Article</label>
                    <select onchange ="setPrix()" name="id_article" id="id_article">
                    <?php
                        $articles = getArticle();
                        if(!empty($articles) && is_array($articles)){
                            foreach ($articles as $key => $value){
                                ?>
                                <option data-prix="<?= $value['prix_unitaire'] ?>" value="<?= $value['id'] ?>" <?= !empty($commande) && (int) $commande['id_article'] === (int) $value['id'] ? 'selected' : '' ?>><?= htmlspecialchars($value['nom_article'], ENT_QUOTES, 'UTF-8') ?> - <?= (int) $value['quantite'] ?> disponible(s)</option>
                                <?php
                            }
                        }
                    ?>
                    </select>


                    <label for="id_fournisseur">Fournisseur</label>
                    <select name="id_fournisseur" id="id_fournisseur">
                    <?php
                        $fournisseur = getFournisseur();
                        if(!empty($fournisseur) && is_array($fournisseur)){
                            foreach ($fournisseur as $key => $value){
                                ?>
                                <option value="<?= $value['id'] ?>" <?= !empty($commande) && (int) $commande['id_fournisseur'] === (int) $value['id'] ? 'selected' : '' ?>><?= htmlspecialchars($value['nom'] . ' ' . $value['prenom'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php
                            }
                        }
                    ?>
                    </select>
                    

                    <label for="quantite">Quantite</label>
                    <input onkeyup="setPrix()" value="<?= !empty($commande) ? (int) $commande['quantite'] : '' ?>" type="number" name="quantite" id="quantite" min="1" placeholder="Veuillez saisir la quantite">

                    <label for="prix">Prix</label>
                    <input value="<?= !empty($commande) ? (int) $commande['prix'] : '' ?>" type="number" name="prix" id="prix" min="0" placeholder="Veuillez saisir le prix">

                    <button type="submit"><i class="fa-solid <?= !empty($commande) ? 'fa-floppy-disk' : 'fa-truck-ramp-box' ?>" aria-hidden="true"></i> <?= !empty($commande) ? 'Enregistrer la commande' : 'Enregistrer la commande' ?></button>
                    
                    <?php
                    if(!empty($_SESSION['message']['text'])){
                    ?>
                        <div class="alert <?= $_SESSION['message']['type']?>">
                            <?= $_SESSION['message']['text']?>
                        </div>
                    <?php
                    }
                    ?>
                </form>
            </div>
            <?php } ?>

            <div class="box">
                <div class="table-scroll">
                <table class="mtable">
                    <tr>
                        <th>Article</th>
                        <th>Fournisseur</th>
                        <th>Quantite</th>
                        <th>Prix</th>
                        <th>Date</th>
                        <th>Etat</th>
                        <th>Modifier</th>
                        <th>Imprimer</th>
                    </tr>

                    <?php
                        $vente = getCommande();
                        if(!empty($vente) && is_array($vente)){
                            foreach ($vente as $key => $value){
                        ?> 
                        <tr>
                            <td><?= $value['nom_article']?></td>
                            <td><?= $value['nom']. '  '.$value['prenom']?></td>
                            <td><?= $value['quantite']?></td>
                            <td><?= $value['prix']?></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($value['date_commande']))?></td>
                            <td><?= (string) $value['etat'] === '1' ? 'Active' : 'Annulee' ?></td>
                            <td><?= $peutModifier && (string) $value['etat'] === '1' ? '<a class="icon-action" aria-label="Modifier la commande C-' . (int) $value['id'] . '" title="Modifier" href="?id=' . (int) $value['id'] . '"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a>' : '-' ?></td>
                            <td>
                                <a class="icon-action" href="recuCommande.php?id=<?= (int) $value['id'] ?>" aria-label="Imprimer le bon de commande C-<?= (int) $value['id'] ?>" title="Imprimer"><i class="fa-solid fa-print" aria-hidden="true"></i></a>
                                <?php if ($peutModifier && (string) $value['etat'] === '1') { ?>
                                    <form action="../annuleCommande.php" method="POST" class="inline-action" onsubmit="return confirm('Confirmer l annulation ?')">
                                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($jetonApplication, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="id" value="<?= (int) $value['id'] ?>">
                                        <button class="icon-action action-danger" type="submit" aria-label="Annuler la commande C-<?= (int) $value['id'] ?>" title="Annuler"><i class="fa-solid fa-ban" aria-hidden="true"></i></button>
                                    </form>
                                <?php } ?>
                            </td>

                        </tr>
                        <?php    
                            }
                        
                        }
                    ?>
                </table>
                </div>
            </div>
        </div>
    </div>
    <?php
    include 'pied.php';
    ?>


<script>

    function setPrix() {
        var article = document.querySelector('#id_article');
        var quantite = document.querySelector('#quantite');
        var prix = document.querySelector('#prix');

        var prixUnitaire = article.options[article.selectedIndex].getAttribute('data-prix');
        prix.value = Number(quantite.value) * Number(prixUnitaire);
    }
</script>