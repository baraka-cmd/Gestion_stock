    <?php
        include 'entete.php';

        if(!empty($_GET['id'])){
            $vente = getVente($_GET['id']);
        }
    ?>
    <div class="home-content">
        <div class="button">
            <button class="hidden-print" id="btnPrint" style="position: relative; left: 45%;">Imprimer</button>
        </div>
        <div class="page">
            <div class="cote-a-cote">
                <h2>D-CLIC Stock</h2>
                <div>
                    <p>Recu N : <?= $vente['id'] ?></p>
                    <p>Date : <?= date('d/m/Y H:i:s', strtotime( $vente['date_vente'])) ?></p>
                </div>
            </div>

            <div class="cote-a-cote" style="width: 50%;">
                <p>Nom : </p>
                <p><?= $vente['nom'] ." ". $vente['prenom'] ?> </p>
            </div>

            <div class="cote-a-cote" style="width: 50%;">
                <p>Tel : </p>
                <p><?= $vente['telephone'] ?> </p>
            </div>

            <div class="cote-a-cote" style="width: 50%;">
                <p>Adresse : </p>
                <p><?= $vente['adresse'] ?> </p>
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
                    <td><?= $vente['nom_article']?></td>
                    <td><?= $vente['quantite']?></td>
                    <td><?= $vente['prix_unitaire']?></td>
                    <td><?= $vente['prix']?></td>

                </tr>
                        
            </table>
        </div>
        
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