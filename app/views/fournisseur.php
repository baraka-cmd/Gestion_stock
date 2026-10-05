    <?php
        include 'entete.php';

        if(!empty($_GET['id'])){
            $fournisseur = getFournisseur($_GET['id']);
        }
    ?>
    <div class="home-content">
        <div class="overview-boxes">
            <?php if ($peutModifier) { ?>
            <div class="box">
                <form action="<?= !empty($_GET['id']) ? "../modifFournisseur.php" : "../ajoutFournisseur.php"?> " method ="POST">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($jetonApplication, ENT_QUOTES, 'UTF-8') ?>">
                    <label for="nom_client$client">Nom</label>
                    <input value="<?= !empty($_GET['id']) ? $fournisseur['nom'] : ""?>" type="text"name = "nom" id = "nom" placeholder = "Veuillez saisir le nom">
                    <input value="<?= !empty($_GET['id']) ? $fournisseur['id'] : ""?>" type="hidden"name = "id" id = "id">

                    <label for="quantite">Prenom</label>
                    <input value="<?= !empty($_GET['id']) ? $fournisseur['prenom'] :""?>" type="text" name = "prenom" id = "prenom" placeholder = "Veuillez saisir le prenom">

                    <label for="prix_unitaire">Telephone</label>
                    <input value="<?= !empty($_GET['id']) ? $fournisseur['telephone'] :""?>" type="text"name = "telephone" id = "telephone" placeholder = "Veuillez saisir N telephone">

                    <label for="date_fabrication">Adresse</label>
                    <input value="<?= !empty($_GET['id']) ? $fournisseur['adresse'] :""?>" type="text"name = "adresse" id = "adresse" placeholder = "Veuillez saisir l'adresse">

                    <button type="submit"><i class="fa-solid <?= !empty($_GET['id']) ? 'fa-floppy-disk' : 'fa-plus' ?>" aria-hidden="true"></i> <?= !empty($_GET['id']) ? 'Enregistrer' : 'Ajouter le fournisseur' ?></button>
                    
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
                        <th>Nom</th>
                        <th>Prenom</th>
                        <th>Telephone</th>
                        <th>Adresse</th>
                        <th>Action</th>
                    </tr>

                    <?php
                        $fournisseur = getFournisseur();
                        if(!empty($fournisseur) && is_array($fournisseur)){
                            foreach ($fournisseur as $key => $value){
                        ?> 
                        <tr>
                            <td><?= $value['nom']?></td>
                            <td><?= $value['prenom']?></td>
                            <td><?= $value['telephone']?></td>
                            <td><?= $value['adresse']?></td>
                            <?php if ($peutModifier) { ?><td><a class="icon-action" href="?id=<?= (int) $value['id'] ?>" aria-label="Modifier le fournisseur <?= htmlspecialchars($value['nom'] . ' ' . $value['prenom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" title="Modifier"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a></td><?php } ?>
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