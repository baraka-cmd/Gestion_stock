<?php

function verifierAccesLocal()
{
    $adresse = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!in_array($adresse, array('127.0.0.1', '::1'), true)) {
        http_response_code(403);
        exit('Gestion des utilisateurs accessible uniquement depuis cette machine.');
    }
}

function verifierAccesLocalUtilisateurs()
{
    verifierAccesLocal();
}

function jetonCsrf($portee)
{
    if (!in_array($portee, array('utilisateurs', 'configuration', 'authentification', 'application'), true)) {
        throw new InvalidArgumentException('Portee CSRF invalide.');
    }
    $cle = 'csrf_' . $portee;
    if (empty($_SESSION[$cle])) {
        $_SESSION[$cle] = bin2hex(random_bytes(32));
    }
    return $_SESSION[$cle];
}

function verifierCsrf($portee)
{
    if (!in_array($portee, array('utilisateurs', 'configuration', 'authentification', 'application'), true)) {
        throw new InvalidArgumentException('Portee CSRF invalide.');
    }
    $cle = 'csrf_' . $portee;
    $jeton = $_POST['_csrf'] ?? '';
    if (!is_string($jeton) || empty($_SESSION[$cle]) || !hash_equals($_SESSION[$cle], $jeton)) {
        http_response_code(403);
        exit('Formulaire invalide ou expire. Rechargez la page.');
    }
}

function jetonCsrfUtilisateurs()
{
    return jetonCsrf('utilisateurs');
}

function verifierCsrfUtilisateurs()
{
    verifierCsrf('utilisateurs');
}

function validerRoleUtilisateur($role)
{
    return is_string($role) && in_array($role, array('admin', 'gestionnaire', 'lecture'), true);
}

function verifierDernierAdministrateur($connexion, $idUtilisateur, $nouveauRole, $nouvelEtat)
{
    $reqActuel = $connexion->prepare('SELECT role, actif, photo FROM utilisateur WHERE id = ? FOR UPDATE');
    $reqActuel->execute(array($idUtilisateur));
    $actuel = $reqActuel->fetch(PDO::FETCH_ASSOC);

    if (!$actuel) {
        throw new DomainException('Utilisateur introuvable.');
    }

    if ($actuel['role'] === 'admin' && (int) $actuel['actif'] === 1
        && ($nouveauRole !== 'admin' || (int) $nouvelEtat !== 1)) {
        $reqAdmins = $connexion->query("SELECT id FROM utilisateur WHERE role = 'admin' AND actif = 1 FOR UPDATE");
        if (count($reqAdmins->fetchAll(PDO::FETCH_COLUMN)) <= 1) {
            throw new DomainException('Impossible de desactiver ou retrograder le dernier administrateur actif.');
        }
    }

    return $actuel;
}