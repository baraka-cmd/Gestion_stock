# Architecture du projet Gestion_stock

## Structure principale

- app/core : connexion, utilitaires, stockage de fichiers, base commune
- app/auth : authentification, session, login, logout, garde d'accès
- app/users : gestion des comptes et rôles
- app/catalog : articles et gestion du catalogue
- app/customers : clients
- app/suppliers : fournisseurs
- app/sales : commandes, ventes et annulations
- app/stock : mouvements et ajustements de stock
- app/config : configuration globale et logos
- app/views : vues PHP de l'application

## Rôle de la racine

- index.php : point d'entrée principal du projet
- model/ : couche de compatibilité historique
- vue/ : couche de compatibilité historique
- public/ : fichiers statiques web (CSS, images, uploads)

## Règle de conception

Les modules fonctionnels doivent rester regroupés par responsabilité métier. Les fichiers de compatibilité existent uniquement pour éviter les ruptures avec l'ancien code pendant la migration.
