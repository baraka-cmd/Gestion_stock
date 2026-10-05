<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole(array('admin', 'gestionnaire'), '../views/login.php');
verifierCsrf('application');

if(!empty($_POST['nom']) 
    && !empty($_POST['prenom']) 
    && !empty($_POST['telephone']) 
    && !empty($_POST['adresse']))

{
    $sql = "INSERT INTO fournisseur(nom, prenom, telephone, adresse) 
            VALUES (?, ?, ?, ?)";
    $req = $connexion->prepare($sql);
    $req->execute(array(
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['telephone'],
        $_POST['adresse']
    ));

    if($req->rowCount() !=0){
        $_SESSION['message']['text'] = "fournisseur ajoute avec succes";
        $_SESSION['message']['type'] = "success";
    }else{
        $_SESSION['message']['text'] = "une erreur s'est produite lors de l'ajoute d'un fournisseur";
        $_SESSION['message']['type'] = "danger";
    }

}else{
    $_SESSION['message']['text'] = "une information obligatoire non renseignee";
    $_SESSION['message']['type'] = "danger";
}

header('Location: ../views/fournisseur.php');
