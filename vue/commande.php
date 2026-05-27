    <?php
        include 'entete.php';

        if(!empty($_GET['id'])){
            $article = getCommande($_GET['id']);
        }
    ?>
    <div class="home-content">
        <div class="overview-boxes">
            <div class="box">
                <form action="<?= !empty($_GET['id']) ? '../Model/modifCommande.php' : '../model/ajoutCommande.php' ?>" method="POST">

                    <input value="<?= !empty($_GET['id']) ? $article['id'] : ""?>" type="hidden" name = "id" id = "id">

                    <label for="nom_article">Article</label>
                    <select onchange ="setPrix()" name="id_article" id="id_article">
                    <?php
                        $articles = getArticle();
                        if(!empty($articles) && is_array($articles)){
                            foreach ($articles as $key => $value){
                                ?>
                                <option data-prix = "<?= $value['prix_unitaire']?>" value="<?= $value['id']?>"><?= $value['nom_article']."   -   ".$value['quantite']."   "."disponible"?></option>
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
                                <option value="<?= $value['id']?>"><?= $value['nom']."      ".$value['prenom']?></option>
                                <?php
                            }
                        }
                    ?>
                    </select>
                    

                    <label for="quantite">Quantite</label>
                    <input onkeyup = "setPrix()" value="<?= !empty($_GET['id']) ? $article['quantite'] :""?>" type="number"name = "quantite" id = "quantite" placeholder = "Veuillez saisir la quantite">

                    <label for="prix">Prix</label>
                    <input value="<?= !empty($_GET['id']) ? $article['prix'] :""?>" type="number"name = "prix" id = "prix" placeholder = "Veuillez saisir le prix">

                    <button type = "submit">Valider</button>
                    
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

            <div class="box">
                <table class="mtable">
                    <tr>
                        <th>Article</th>
                        <th>Fournisseur</th>
                        <th>Quantite</th>
                        <th>Prix</th>
                        <th>Date</th>
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
                      
                            <td><a href="?id=<?= $value['id'] ?>">Edit</a></td>
                            <td>
                                <a href="recuVente.php?id=<?= $value['id'] ?>">Print</a>
                                <a onclick="annuleVente(<?= $value['id'] ?>, <?= $value['idArticle'] ?>, <?= $value['quantite'] ?>)" style="color: red;">Ann</a>
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
    <?php
    include 'pied.php';
    ?>


<script>

    function annuleVente(idVente, idArticle, quantite){
        if(confirm("voulez-vous annuler cette vente ?")){
            window.location.href = "../model/annuleVente.php?idVente="+idVente+"&idArticle="+idArticle+"&quantite="+quantite
        }
    }

    function setPrix() {
        var article = document.querySelector('#id_article');
        var quantite = document.querySelector('#quantite');
        var prix = document.querySelector('#prix');

        var prixUnitaire = article.options[article.selectedIndex].getAttribute('data-prix');
        prix.value = Number(quantite.value) * Number(prixUnitaire);
    }
</script>