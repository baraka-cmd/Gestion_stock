<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
$utilisateurCourant = authentifierUtilisateurCourant('login.php');
$peutModifier = in_array($utilisateurCourant['role'], array('admin', 'gestionnaire'), true);
$jetonDeconnexion = jetonCsrf('authentification');
$jetonApplication = jetonCsrf('application');
$configuration = getConfiguration();
$nomApplication = htmlspecialchars($configuration['nom_entreprise'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$logoValide = is_string($configuration['logo'])
  && preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $configuration['logo']);
$titresPages = array(
  'dashboard.php' => 'Vue d ensemble', 'article.php' => 'Articles', 'client.php' => 'Clients',
  'vente.php' => 'Ventes', 'fournisseur.php' => 'Fournisseurs',
  'commande.php' => 'Commandes fournisseur', 'stock.php' => 'Stock',
  'toutesCommandes.php' => 'Toutes les commandes', 'utilisateur.php' => 'Utilisateurs',
  'configuration.php' => 'Configuration', 'recuVente.php' => 'Recu de vente',
  'recuCommande.php' => 'Bon de commande',
);
$nomPage = basename($_SERVER['PHP_SELF']);
$nomVue = preg_replace('/\.php$/', '', $nomPage);
$titrePage = $titresPages[$nomPage] ?? 'Gestion';
$sessionUtilisateur = $utilisateurCourant;
$nomProfil = 'Acces local';
$roleProfil = 'Non authentifie';
$photoProfil = null;
if (is_array($sessionUtilisateur)) {
  $prenomSession = trim((string) ($sessionUtilisateur['prenom'] ?? ''));
  $nomSession = trim((string) ($sessionUtilisateur['nom'] ?? ''));
  if ($prenomSession !== '' || $nomSession !== '') {
    $nomProfil = htmlspecialchars(trim($prenomSession . ' ' . $nomSession), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
  if (isset($sessionUtilisateur['role']) && is_string($sessionUtilisateur['role'])) {
    $roleProfil = htmlspecialchars($sessionUtilisateur['role'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
  if (isset($sessionUtilisateur['photo']) && is_string($sessionUtilisateur['photo'])
    && preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $sessionUtilisateur['photo'])) {
    $photoProfil = '../../public/uploads/users/' . rawurlencode($sessionUtilisateur['photo']);
  }
}
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
  <head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($titrePage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> | <?= $nomApplication ?></title>
    <link rel="stylesheet" href="../../public/vendor/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="../../public/css/style.css" />
    <link rel="stylesheet" href="../../public/css/common.css" />
    <link rel="stylesheet" href="../../public/css/views/<?= htmlspecialchars($nomVue, ENT_QUOTES, 'UTF-8') ?>.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  </head>
  <body data-page="<?= htmlspecialchars($nomVue, ENT_QUOTES, 'UTF-8') ?>" class="layout-shell">
    
    <div class="sidebar hidden-print">
      <div class="logo-details">
        <?php if ($logoValide) { ?>
          <img class="brand-logo" src="../../public/uploads/configuration/<?= rawurlencode($configuration['logo']) ?>" alt="">
        <?php } else { ?>
          <span class="brand-mark" aria-hidden="true"><i class="fa-solid fa-boxes-stacked"></i></span>
        <?php } ?>
        <span class="logo_name"><?= $nomApplication ?></span>
        <button type="button" class="sidebar-close" aria-label="Fermer la navigation"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
      </div>
      <ul class="nav-links">
        <li>
          <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == "dashboard.php" ? "active" : ""?>">
            <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
            <span class="links_name">Dashboard</span>
          </a>
        </li>
        <li>
          <a href="article.php" class="<?php echo basename($_SERVER['PHP_SELF']) == "article.php" ? "active" : ""?>">
            <i class="fa-solid fa-box" aria-hidden="true"></i>
            <span class="links_name">Article</span>
          </a>
        </li>
        <li>
          <a href="client.php" class="<?php echo basename($_SERVER['PHP_SELF']) == "client.php" ? "active" : ""?>">
            <i class="fa-solid fa-users" aria-hidden="true"></i>
            <span class="links_name">Client</span>
          </a>
        </li>
        <li>
          <a href="vente.php" class="<?php echo basename($_SERVER['PHP_SELF']) == "vente.php" ? "active" : ""?>">
            <i class="fa-solid fa-cash-register" aria-hidden="true"></i>
            <span class="links_name">Vente</span>
          </a>
        </li>
        <li>
          <a href="fournisseur.php" class="<?php echo basename($_SERVER['PHP_SELF']) == "fournisseur.php" ? "active" : ""?>">
            <i class="fa-solid fa-truck-field" aria-hidden="true"></i>
            <span class="links_name">Fournisseur</span>
          </a>
        </li>
        <li>
          <a href="commande.php" class="<?php echo basename($_SERVER['PHP_SELF']) == "commande.php" ? "active" : ""?>">
            <i class="fa-solid fa-file-invoice" aria-hidden="true"></i>
            <span class="links_name">Commandes</span>
          </a>
        </li>
        <li>
          <a href="stock.php" class="<?php echo basename($_SERVER['PHP_SELF']) == "stock.php" ? "active" : ""?>">
            <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>
            <span class="links_name">Stock</span>
          </a>
        </li>
        <li>
          <a href="toutesCommandes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == "toutesCommandes.php" ? "active" : ""?>">
            <i class="fa-solid fa-list-check" aria-hidden="true"></i>
            <span class="links_name">Toutes les commandes</span>
          </a>
        </li>
        <?php if ($utilisateurCourant['role'] === 'admin') { ?>
        <?php if ($utilisateurCourant['role'] === 'admin') { ?>
        <li>
          <a href="utilisateur.php" class="<?php echo basename($_SERVER['PHP_SELF']) == "utilisateur.php" ? "active" : ""?>">
            <i class="fa-solid fa-user-gear" aria-hidden="true"></i>
            <span class="links_name">Utilisateur</span>
          </a>
        </li>
        <li>
          <a href="configuration.php" class="<?php echo basename($_SERVER['PHP_SELF']) == "configuration.php" ? "active" : ""?>">
            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
            <span class="links_name">Configuration</span>
          </a>
        </li>
        <?php } ?>
        <?php } ?>
        <li class="log_out">
          <form action="../deconnexion.php" method="POST">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($jetonDeconnexion, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="logout-button"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i><span class="links_name">Se deconnecter</span></button>
          </form>
        </li>
      </ul>
    </div>
    <section class="home-section">
      <nav class="hidden-print">
        <div class="sidebar-button">
          <button type="button" class="sidebar-toggle sidebarBtn" aria-label="Ouvrir ou fermer la navigation" aria-expanded="false">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
          </button>
          <span class="dashboard"><?= htmlspecialchars($titrePage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
          
        </div>
        <form class="search-box" action="toutesCommandes.php" method="GET" role="search">
          <label class="visually-hidden" for="global-search">Rechercher des ventes ou commandes</label>
          <input type="search" id="global-search" name="recherche" placeholder="Rechercher une operation..." />
          <button type="submit" aria-label="Rechercher"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
        </form>
        <div class="profile-details">
          <span class="profile-avatar">
            <?php if ($photoProfil) { ?><img src="<?= $photoProfil ?>" alt="">
            <?php } else { ?><i class="fa-solid fa-user" aria-hidden="true"></i><?php } ?>
          </span>
          <span class="profile-copy"><strong class="admin_name"><?= $nomProfil ?></strong><small><?= $roleProfil ?></small></span>
        </div>
      </nav>