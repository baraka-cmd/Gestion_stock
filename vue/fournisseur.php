    <?php
        include 'entete.php';

        if(!empty($_GET['id'])){
            $fournisseur = getFournisseur($_GET['id']);
        }
    ?>
    <div class="home-content">
        <div class="overview-boxes">
            <div class="box">
                <form action="<?= !empty($_GET['id']) ? "../Model/modifFournisseur.php" : "../model/ajoutFournisseur.php"?> " method ="POST">
                    <label for="nom_client$client">Nom</label>
                    <input value="<?= !empty($_GET['id']) ? $fournisseur['nom'] : ""?>" type="text"name = "nom" id = "nom" placeholder = "Veuillez saisir le nom">
                    <input value="<?= !empty($_GET['id']) ? $fournisseur['id'] : ""?>" type="hidden"name = "id" id = "id">

                    <label for="quantite">Prenom</label>
                    <input value="<?= !empty($_GET['id']) ? $fournisseur['prenom'] :""?>" type="text" name = "prenom" id = "prenom" placeholder = "Veuillez saisir le prenom">

                    <label for="prix_unitaire">Telephone</label>
                    <input value="<?= !empty($_GET['id']) ? $fournisseur['telephone'] :""?>" type="text"name = "telephone" id = "telephone" placeholder = "Veuillez saisir N telephone">

                    <label for="date_fabrication">Adresse</label>
                    <input value="<?= !empty($_GET['id']) ? $fournisseur['adresse'] :""?>" type="text"name = "adresse" id = "adresse" placeholder = "Veuillez saisir l'adresse">

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
                            <td><a href="?id=<?= $value['id']?>">edit</a></td>
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