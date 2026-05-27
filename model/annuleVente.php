<?php
include 'connexion.php';

if(
    !empty($_GET['idVente']) &&
    !empty($_GET['idArticle']) &&
    !empty($_GET['quantite'])
){
    // 1. On annule la vente (changement d'état)
    $sqlVente = "UPDATE vente SET etat = ? WHERE id = ?";
    $reqVente = $connexion->prepare($sqlVente);
    $reqVente->execute(array(0, $_GET['idVente']));
    
    // 2. Si la vente a bien été annulée, on remet le stock à jour
    if($reqVente->rowCount() != 0){
        $sqlStock = "UPDATE article SET quantite = quantite + ? WHERE id = ?";
        $reqStock = $connexion->prepare($sqlStock); // ON PRÉPARE LA NOUVELLE REQUÊTE
        $reqStock->execute(array($_GET['quantite'], $_GET['idArticle']));
        
        $_SESSION['message']['text'] = "Vente annulée et stock mis à jour";
        $_SESSION['message']['type'] = "success";
    }
}
header('location: ../vue/vente.php');
?>
