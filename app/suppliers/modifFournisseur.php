<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole(array('admin', 'gestionnaire'), '../views/login.php');
verifierCsrf('application');

if(!empty($_POST['nom']) 
    && !empty($_POST['prenom']) 
    && !empty($_POST['telephone']) 
    && !empty($_POST['adresse']) 
    && !empty($_POST['id']))

{
    $sql = "UPDATE fournisseur SET nom = ?, prenom =? , telephone = ?, adresse = ? WHERE id = ?";
    $req = $connexion->prepare($sql);
    $req->execute(array(
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['telephone'],
        $_POST['adresse'],
        $_POST['id']
    ));

    if($req->rowCount() !=0){
        $_SESSION['message']['text'] = "fournisseur modifie avec succes";
        $_SESSION['message']['type'] = "success";
    }else{
        $_SESSION['message']['text'] = "Rien n'a ete modifier";
        $_SESSION['message']['type'] = "warning";
    }

}else{
    $_SESSION['message']['text'] = "une information obligatoire non renseignee";
    $_SESSION['message']['type'] = "danger";
}

header('Location: ../views/fournisseur.php');
