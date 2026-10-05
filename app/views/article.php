    <?php
        include 'entete.php';

        if(!empty($_GET['id'])){
            $article = getArticle($_GET['id']);
        }
    ?>
    <div class="home-content">
        <div class="overview-boxes">
            <?php if ($peutModifier) { ?>
            <div class="box">
                <form action="<?= !empty($_GET['id']) ? "../modifArticle.php" : "../ajoutArticle.php"?> " method ="POST">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($jetonApplication, ENT_QUOTES, 'UTF-8') ?>">
                    <label for="nom_article">Nom de l'article</label>
                    <input value="<?= !empty($_GET['id']) ? $article['nom_article'] : ""?>" type="text"name = "nom_article" id = "nom_article" placeholder = "Veuillez saisir le nom">
                    <input value="<?= !empty($_GET['id']) ? $article['id'] : ""?>" type="hidden"name = "id" id = "id">

                    <label for="nom_article">Categorie</label>
                    <select name="categorie" id="categorie">
                        <option <?= !empty($_GET['id']) && $article['categorie'] == "Ordinateur" ? "selected" : "" ?> value="Ordinateur">Ordinateur</option>
                        <option <?= !empty($_GET['id']) && $article['categorie'] == "Imprimante" ? "selected" : "" ?> value="Imprimante">Imprimante</option>
                        <option <?= !empty($_GET['id']) && $article['categorie'] == "Accessoire" ? "selected" : "" ?> value="Accessoire">Accessoire</option>
                    </select>

                    <label for="quantite">Quantite</label>
                    <input value="<?= !empty($_GET['id']) ? $article['quantite'] :""?>" type="number"name = "quantite" id = "quantite" placeholder = "Veuillez saisir la quantite">

                    <label for="prix_unitaire">Prix unitaire</label>
                    <input value="<?= !empty($_GET['id']) ? $article['prix_unitaire'] :""?>" type="number"name = "prix_unitaire" id = "prix_unitaire" placeholder = "Veuillez saisir le prix">

                    <label for="date_fabrication">Date de fabrication</label>
                    <input value="<?= !empty($_GET['id']) ? $article['date_fabrication'] :""?>" type="datetime-local"name = "date_fabrication" id = "date_fabrication">

                    <label for="date_expiration">Date d'expiration</label>
                    <input value="<?= !empty($_GET['id']) ? $article['date_expiration'] :""?>" type="datetime-local"name = "date_expiration" id = "date_expiration">

                    <button type="submit"><i class="fa-solid <?= !empty($_GET['id']) ? 'fa-floppy-disk' : 'fa-plus' ?>" aria-hidden="true"></i> <?= !empty($_GET['id']) ? 'Enregistrer' : 'Ajouter l article' ?></button>
                    
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
                        <th>Nom article</th>
                        <th>Categorie</th>
                        <th>Cantite</th>
                        <th>Prix unitaire</th>
                        <th>Date fabrication</th>
                        <th>Date d'expiration</th>
                        <th>Action</th>
                    </tr>

                    <?php
                        $articles = getArticle();
                        if(!empty($articles) && is_array($articles)){
                            foreach ($articles as $key => $value){
                        ?> 
                        <tr>
                            <td><?= $value['nom_article']?></td>
                            <td><?= $value['categorie']?></td>
                            <td><?= $value['quantite']?></td>
                            <td><?= $value['prix_unitaire']?></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($value['date_fabrication']))?></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($value['date_expiration']))?></td>
                            <?php if ($peutModifier) { ?><td><a class="icon-action" href="?id=<?= (int) $value['id'] ?>" aria-label="Modifier <?= htmlspecialchars($value['nom_article'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" title="Modifier"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a></td><?php } ?>
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