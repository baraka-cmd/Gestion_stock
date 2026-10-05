    <?php
        include 'entete.php';

        if(!empty($_GET['id'])){
            $article = getVente($_GET['id']);
        }
    ?>
    <div class="home-content">
        <div class="overview-boxes">
            <?php if ($peutModifier) { ?>
            <div class="box">
                    <form action="<?= !empty($_GET['id']) ? '../modifVente.php' : '../ajoutVente.php' ?>" method="POST">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($jetonApplication, ENT_QUOTES, 'UTF-8') ?>">

                    <input value="<?= !empty($_GET['id']) ? $article['id'] : ""?>" type="hidden" name = "id" id = "id">

                    <label for="nom_article">Article</label>
                    <select onchange ="setPrix()" name="id_article" id="id_article">
                    <?php
                        $articles = getArticle();
                        if(!empty($articles) && is_array($articles)){
                            foreach ($articles as $key => $value){
                                ?>
                                <option data-prix="<?= $value['prix_unitaire'] ?>" value="<?= $value['id'] ?>" <?= !empty($article) && (int) $article['id_article'] === (int) $value['id'] ? 'selected' : '' ?>><?= htmlspecialchars($value['nom_article'], ENT_QUOTES, 'UTF-8') ?> - <?= (int) $value['quantite'] ?> disponible(s)</option>
                                <?php
                            }
                        }
                    ?>
                    </select>


                    <label for="id_client">Client</label>
                    <select name="id_client" id="id_client">
                    <?php
                        $clients = getClient();
                        if(!empty($clients) && is_array($clients)){
                            foreach ($clients as $key => $value){
                                ?>
                                <option value="<?= $value['id'] ?>" <?= !empty($article) && (int) $article['id_client'] === (int) $value['id'] ? 'selected' : '' ?>><?= htmlspecialchars($value['nom'] . ' ' . $value['prenom'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php
                            }
                        }
                    ?>
                    </select>
                    

                    <label for="quantite">Quantite</label>
                    <input onkeyup="setPrix()" value="<?= !empty($article) ? (int) $article['quantite'] : '' ?>" type="number" name="quantite" id="quantite" min="1" placeholder="Veuillez saisir la quantite">

                    <label for="prix">Prix</label>
                    <input value="<?= !empty($article) ? (int) $article['prix'] : '' ?>" type="number" name="prix" id="prix" min="0" placeholder="Veuillez saisir le prix">

                    <button type="submit"><i class="fa-solid <?= !empty($_GET['id']) ? 'fa-floppy-disk' : 'fa-cart-plus' ?>" aria-hidden="true"></i> <?= !empty($_GET['id']) ? 'Enregistrer la vente' : 'Enregistrer la vente' ?></button>
                    
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
                        <th>Client</th>
                        <th>Quantite</th>
                        <th>Prix</th>
                        <th>Date</th>
                        <th>Modifier</th>
                        <th>Imprimer</th>
                    </tr>

                    <?php
                        $vente = getVente();
                        if(!empty($vente) && is_array($vente)){
                            foreach ($vente as $key => $value){
                        ?> 
                        <tr>
                            <td><?= $value['nom_article']?></td>
                            <td><?= $value['nom']. '  '.$value['prenom']?></td>
                            <td><?= $value['quantite']?></td>
                            <td><?= $value['prix']?></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($value['date_vente']))?></td>
                      
                            <td><?php if ($peutModifier) { ?><a class="icon-action" href="?id=<?= (int) $value['id'] ?>" aria-label="Modifier la vente V-<?= (int) $value['id'] ?>" title="Modifier"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a><?php } ?></td>
                            <td>
                                <a class="icon-action" href="recuVente.php?id=<?= (int) $value['id'] ?>" aria-label="Imprimer le recu de vente V-<?= (int) $value['id'] ?>" title="Imprimer"><i class="fa-solid fa-print" aria-hidden="true"></i></a>
                                <?php if ($peutModifier) { ?>
                                <form action="../annuleVente.php" method="POST" class="inline-action" onsubmit="return confirm('Confirmer l annulation ?')">
                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($jetonApplication, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="idVente" value="<?= (int) $value['id'] ?>">
                                    <button class="icon-action action-danger" type="submit" aria-label="Annuler la vente V-<?= (int) $value['id'] ?>" title="Annuler"><i class="fa-solid fa-ban" aria-hidden="true"></i></button>
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