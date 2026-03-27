<?php
session_start();
$nom_serveur = "Localhost";
$nom_base_de_donnees = "gestion_stock_dclic";
$utilisateur = "root";
$motpass = "";

try{
    $connexion = new PDO("mysql:host=$nom_serveur;dbname=$nom_base_de_donnees", $utilisateur, $motpass);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (Exception $e){
    die("Erreur de connexion : ".$e->getMessage());
}
