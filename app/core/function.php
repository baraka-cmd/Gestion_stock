<?php
include 'connexion.php';

function getArticle($id = null)
{
    if (!empty($id)) {
        $req = $GLOBALS['connexion']->prepare('SELECT * FROM article WHERE id = ?');
        $req->execute(array($id));
        return $req->fetch();
    }

    $req = $GLOBALS['connexion']->query('SELECT * FROM article');
    return $req->fetchAll();
}

function getStock($id = null)
{
    if (!empty($id)) {
        $req = $GLOBALS['connexion']->prepare(
            'SELECT id, nom_article, categorie, quantite, prix_unitaire FROM article WHERE id = ?'
        );
        $req->execute(array($id));
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    $req = $GLOBALS['connexion']->query(
        'SELECT id, nom_article, categorie, quantite, prix_unitaire FROM article ORDER BY nom_article'
    );
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

function getHistoriqueStock($limite = 50)
{
    $limite = max(1, min(500, (int) $limite));
    $req = $GLOBALS['connexion']->query(
        'SELECT m.id, m.id_article, m.type_mouvement, m.quantite, m.stock_avant,
                m.stock_apres, m.motif, m.date_mouvement, a.nom_article
         FROM mouvement_stock AS m
         INNER JOIN article AS a ON a.id = m.id_article
         ORDER BY m.date_mouvement DESC, m.id DESC
         LIMIT ' . $limite
    );
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

function enregistrerMouvementStock($idArticle, $type, $quantite, $stockAvant, $stockApres, $motif)
{
    $req = $GLOBALS['connexion']->prepare(
        'INSERT INTO mouvement_stock
            (id_article, type_mouvement, quantite, stock_avant, stock_apres, motif)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $req->execute(array($idArticle, $type, $quantite, $stockAvant, $stockApres, $motif));
}

function getClient($id = null)
{
    if (!empty($id)) {
        $req = $GLOBALS['connexion']->prepare('SELECT * FROM client WHERE id = ?');
        $req->execute(array($id));
        return $req->fetch();
    }

    $req = $GLOBALS['connexion']->query('SELECT * FROM client');
    return $req->fetchAll();
}

function getUtilisateur($id = null)
{
    if (!empty($id)) {
        $req = $GLOBALS['connexion']->prepare('SELECT * FROM utilisateur WHERE id = ?');
        $req->execute(array($id));
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    $req = $GLOBALS['connexion']->query('SELECT * FROM utilisateur ORDER BY nom, prenom');
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

function getUtilisateursPagines($recherche = '', $etat = '', $limite = 10, $decalage = 0)
{
    $limite = max(1, min(100, (int) $limite));
    $decalage = max(0, (int) $decalage);
    $conditions = array();
    $parametres = array();

    if (is_string($recherche) && trim($recherche) !== '') {
        $conditions[] = '(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ?)';
        $motif = '%' . trim($recherche) . '%';
        array_push($parametres, $motif, $motif, $motif, $motif);
    }
    if (in_array((string) $etat, array('0', '1'), true)) {
        $conditions[] = 'actif = ?';
        $parametres[] = (int) $etat;
    }

    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    $sql = 'SELECT id, nom, prenom, email, telephone, role, actif, photo, date_creation
            FROM utilisateur' . $where . ' ORDER BY nom, prenom, id
            LIMIT ' . $limite . ' OFFSET ' . $decalage;
    $req = $GLOBALS['connexion']->prepare($sql);
    $req->execute($parametres);
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

function compterUtilisateurs($recherche = '', $etat = '')
{
    $conditions = array();
    $parametres = array();

    if (is_string($recherche) && trim($recherche) !== '') {
        $conditions[] = '(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ?)';
        $motif = '%' . trim($recherche) . '%';
        array_push($parametres, $motif, $motif, $motif, $motif);
    }
    if (in_array((string) $etat, array('0', '1'), true)) {
        $conditions[] = 'actif = ?';
        $parametres[] = (int) $etat;
    }

    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    $req = $GLOBALS['connexion']->prepare('SELECT COUNT(*) FROM utilisateur' . $where);
    $req->execute($parametres);
    return (int) $req->fetchColumn();
}

function getVente($id = null, $inclureAnnulee = false)
{
    if (!empty($id)) {
        $sql = 'SELECT v.id, v.id_article, v.id_client, v.etat, a.nom_article,
                       c.nom, c.prenom, v.quantite, v.prix, v.date_vente,
                       a.prix_unitaire, c.adresse, c.telephone
                FROM vente AS v
                INNER JOIN article AS a ON a.id = v.id_article
                INNER JOIN client AS c ON c.id = v.id_client
                WHERE v.id = ?';
        $parametres = array($id);
        if (!$inclureAnnulee) {
            $sql .= ' AND v.etat = ?';
            $parametres[] = 1;
        }
        $req = $GLOBALS['connexion']->prepare($sql);
        $req->execute($parametres);
        return $req->fetch();
    }

    $req = $GLOBALS['connexion']->prepare(
        'SELECT v.id, v.id_article, v.id_client, v.etat, a.nom_article,
                c.nom, c.prenom, v.quantite, v.prix, v.date_vente,
                a.id AS idArticle
         FROM vente AS v
         INNER JOIN article AS a ON a.id = v.id_article
         INNER JOIN client AS c ON c.id = v.id_client
         WHERE v.etat = ?'
    );
    $req->execute(array(1));
    return $req->fetchAll();
}

function sourceToutesCommandesSql()
{
    return "SELECT 'vente' AS type_commande, v.id, v.date_vente AS date_operation,
                   a.nom_article, v.quantite, v.prix,
                   CONCAT(c.nom, ' ', c.prenom) AS tiers, v.etat
            FROM vente AS v
            INNER JOIN article AS a ON a.id = v.id_article
            INNER JOIN client AS c ON c.id = v.id_client
            UNION ALL
            SELECT 'commande' AS type_commande, co.id, co.date_commande AS date_operation,
                   a.nom_article, co.quantite, co.prix,
                   CONCAT(f.nom, ' ', f.prenom) AS tiers, co.etat
            FROM commande AS co
            INNER JOIN article AS a ON a.id = co.id_article
            INNER JOIN fournisseur AS f ON f.id = co.id_fournisseur";
}

function filtresToutesCommandesSql($filtres, &$parametres)
{
    $conditions = array();
    $parametres = array();

    if (in_array($filtres['type'] ?? '', array('vente', 'commande'), true)) {
        $conditions[] = 'type_commande = ?';
        $parametres[] = $filtres['type'];
    }
    if (in_array((string) ($filtres['etat'] ?? ''), array('0', '1'), true)) {
        $conditions[] = 'etat = ?';
        $parametres[] = $filtres['etat'];
    }
    if (!empty($filtres['date_debut'])) {
        $conditions[] = 'date_operation >= ?';
        $parametres[] = $filtres['date_debut'] . ' 00:00:00';
    }
    if (!empty($filtres['date_fin'])) {
        $conditions[] = 'date_operation < DATE_ADD(?, INTERVAL 1 DAY)';
        $parametres[] = $filtres['date_fin'] . ' 00:00:00';
    }
    if (!empty($filtres['recherche'])) {
        $conditions[] = "(nom_article LIKE ? OR tiers LIKE ? OR CONCAT(IF(type_commande = 'vente', 'V-', 'C-'), id) LIKE ?)";
        $recherche = '%' . $filtres['recherche'] . '%';
        array_push($parametres, $recherche, $recherche, $recherche);
    }

    return $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
}

function getToutesCommandes($filtres = array(), $limite = 10, $decalage = 0)
{
    $limite = max(1, min(100, (int) $limite));
    $decalage = max(0, (int) $decalage);
    $conditions = filtresToutesCommandesSql($filtres, $parametres);
    $sql = 'SELECT * FROM (' . sourceToutesCommandesSql() . ') AS toutes_commandes'
        . $conditions
        . ' ORDER BY date_operation DESC, type_commande ASC, id DESC'
        . ' LIMIT ' . $limite . ' OFFSET ' . $decalage;
    $req = $GLOBALS['connexion']->prepare($sql);
    $req->execute($parametres);
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

function compterToutesCommandes($filtres = array())
{
    $conditions = filtresToutesCommandesSql($filtres, $parametres);
    $sql = 'SELECT COUNT(*) FROM (' . sourceToutesCommandesSql() . ') AS toutes_commandes' . $conditions;
    $req = $GLOBALS['connexion']->prepare($sql);
    $req->execute($parametres);
    return (int) $req->fetchColumn();
}

function getFournisseur($id = null)
{
    if (!empty($id)) {
        $req = $GLOBALS['connexion']->prepare('SELECT * FROM fournisseur WHERE id = ?');
        $req->execute(array($id));
        return $req->fetch();
    }

    $req = $GLOBALS['connexion']->query('SELECT * FROM fournisseur');
    return $req->fetchAll();
}

function getCommande($id = null)
{
    $sql = 'SELECT co.id, co.id_article, co.id_fournisseur, co.etat,
                   a.nom_article, f.nom, f.prenom, f.telephone, f.adresse,
                   co.quantite, co.prix, co.date_commande, a.prix_unitaire,
                   a.id AS idArticle
            FROM commande AS co
            INNER JOIN fournisseur AS f ON f.id = co.id_fournisseur
            INNER JOIN article AS a ON a.id = co.id_article';

    if (!empty($id)) {
        $req = $GLOBALS['connexion']->prepare($sql . ' WHERE co.id = ?');
        $req->execute(array($id));
        return $req->fetch();
    }

    return $GLOBALS['connexion']->query($sql)->fetchAll();
}

function getAllCommande()
{
    $req = $GLOBALS['connexion']->query('SELECT COUNT(*) AS nbre FROM commande');
    return $req->fetch();
}

function getAllVente()
{
    $req = $GLOBALS['connexion']->prepare('SELECT COUNT(*) AS nbre FROM vente WHERE etat = ?');
    $req->execute(array(1));
    return $req->fetch();
}

function getAllArticle()
{
    $req = $GLOBALS['connexion']->query('SELECT COUNT(*) AS nbre FROM article');
    return $req->fetch();
}

function getDashboardIndicateurs($seuilStock)
{
    $req = $GLOBALS['connexion']->prepare(
        "SELECT
            (SELECT COUNT(*) FROM commande WHERE etat = '1') AS commandes_actives,
            (SELECT COUNT(*) FROM vente WHERE etat = '1') AS ventes_actives,
            (SELECT COALESCE(SUM(prix), 0) FROM vente WHERE etat = '1' AND DATE(date_vente) = CURDATE()) AS revenu_jour,
            (SELECT COALESCE(SUM(prix), 0) FROM vente WHERE etat = '1' AND date_vente >= DATE_FORMAT(CURDATE(), '%Y-%m-01')) AS revenu_mois,
            (SELECT COUNT(*) FROM article) AS nombre_articles,
            (SELECT COALESCE(SUM(quantite), 0) FROM article) AS unites_stock,
            (SELECT COUNT(*) FROM article WHERE quantite <= ?) AS articles_en_alerte"
    );
    $req->execute(array((int) $seuilStock));
    return $req->fetch(PDO::FETCH_ASSOC);
}

function getVentesMensuellesDashboard($nombreMois = 6)
{
    $nombreMois = max(2, min(12, (int) $nombreMois));
    $debut = new DateTimeImmutable('first day of this month');
    $debut = $debut->modify('-' . ($nombreMois - 1) . ' months');
    $req = $GLOBALS['connexion']->prepare(
        "SELECT DATE_FORMAT(date_vente, '%Y-%m') AS cle_mois,
                COALESCE(SUM(prix), 0) AS revenu,
                COUNT(*) AS nombre_ventes
         FROM vente
         WHERE etat = '1' AND date_vente >= ?
         GROUP BY DATE_FORMAT(date_vente, '%Y-%m')"
    );
    $req->execute(array($debut->format('Y-m-01 00:00:00')));
    $parMois = array();
    foreach ($req->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
        $parMois[$ligne['cle_mois']] = $ligne;
    }

    $nomsMois = array('', 'janv.', 'fevr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'aout', 'sept.', 'oct.', 'nov.', 'dec.');
    $resultat = array();
    for ($index = 0; $index < $nombreMois; $index++) {
        $mois = $debut->modify('+' . $index . ' months');
        $cle = $mois->format('Y-m');
        $donnees = $parMois[$cle] ?? array('revenu' => 0, 'nombre_ventes' => 0);
        $resultat[] = array(
            'cle' => $cle,
            'label' => $nomsMois[(int) $mois->format('n')] . ' ' . $mois->format('Y'),
            'revenu' => (float) $donnees['revenu'],
            'nombre_ventes' => (int) $donnees['nombre_ventes'],
        );
    }
    return $resultat;
}

function getStockParCategorieDashboard()
{
    $req = $GLOBALS['connexion']->query(
        "SELECT COALESCE(NULLIF(TRIM(categorie), ''), 'Sans categorie') AS categorie,
                COALESCE(SUM(quantite), 0) AS unites
         FROM article
         GROUP BY COALESCE(NULLIF(TRIM(categorie), ''), 'Sans categorie')
         ORDER BY unites DESC"
    );
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

function getArticlesEnAlerteDashboard($seuilStock, $limite = 6)
{
    $limite = max(1, min(20, (int) $limite));
    $req = $GLOBALS['connexion']->prepare(
        'SELECT id, nom_article, categorie, quantite, prix_unitaire
         FROM article
         WHERE quantite <= ?
         ORDER BY quantite ASC, nom_article ASC
         LIMIT ' . $limite
    );
    $req->execute(array((int) $seuilStock));
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

function getMeilleursArticlesDashboard($limite = 5)
{
    $limite = max(1, min(10, (int) $limite));
    $req = $GLOBALS['connexion']->query(
        "SELECT a.id, a.nom_article, COALESCE(SUM(v.quantite), 0) AS unites_vendues,
                COALESCE(SUM(v.prix), 0) AS revenu
         FROM article AS a
         INNER JOIN vente AS v ON v.id_article = a.id AND v.etat = '1'
         GROUP BY a.id, a.nom_article
         ORDER BY unites_vendues DESC, revenu DESC
         LIMIT " . $limite
    );
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

function getConfiguration($verrouiller = false)
{
    $sql = 'SELECT id, nom_entreprise, adresse, telephone, email, devise,
                   seuil_stock_faible, texte_pied_recu, logo, date_modification
            FROM configuration WHERE id = 1';
    if ($verrouiller) {
        $sql .= ' FOR UPDATE';
    }

    $configuration = $GLOBALS['connexion']->query($sql)->fetch(PDO::FETCH_ASSOC);
    if (!$configuration) {
        throw new RuntimeException('La configuration par defaut est absente.');
    }
    return $configuration;
}

function getHistoriqueConfiguration($limite = 10)
{
    $limite = max(1, min(100, (int) $limite));
    $req = $GLOBALS['connexion']->query(
        'SELECT ancienne_valeur, nouvelle_valeur, adresse_ip, date_modification
         FROM journal_configuration
         WHERE configuration_id = 1
         ORDER BY id DESC
         LIMIT ' . $limite
    );
    return $req->fetchAll(PDO::FETCH_ASSOC);
}