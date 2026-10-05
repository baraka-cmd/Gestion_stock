<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../config/logoConfiguration.php';

exigerRole('admin', '../views/login.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/configuration.php');
    exit;
}
verifierCsrf('configuration');

$lireTexte = static function ($cle) {
    return isset($_POST[$cle]) && is_string($_POST[$cle]) ? trim($_POST[$cle]) : '';
};
$nom = $lireTexte('nom_entreprise');
$adresse = $lireTexte('adresse');
$telephone = $lireTexte('telephone');
$email = strtolower($lireTexte('email'));
$devise = strtoupper($lireTexte('devise'));
$textePied = $lireTexte('texte_pied_recu');
$retirerLogo = isset($_POST['retirer_logo']) && $_POST['retirer_logo'] === '1';
$seuil = filter_input(INPUT_POST, 'seuil_stock_faible', FILTER_VALIDATE_INT);
$longueur = static function ($valeur) {
    return function_exists('mb_strlen') ? mb_strlen($valeur, 'UTF-8') : strlen($valeur);
};

$champsValides = $nom !== '' && $longueur($nom) <= 120
    && $longueur($adresse) <= 255
    && $longueur($telephone) <= 30
    && ($telephone === '' || preg_match('/^[0-9+().\- ]+$/', $telephone))
    && ($email === '' || (filter_var($email, FILTER_VALIDATE_EMAIL) && $longueur($email) <= 254))
    && preg_match('/^[A-Z0-9]{1,8}$/', $devise)
    && $longueur($textePied) <= 255
    && $seuil !== false && $seuil !== null && $seuil >= 0 && $seuil <= 1000000
    && !($retirerLogo && isset($_FILES['logo']) && ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK);

if (!$champsValides) {
    $_SESSION['message'] = ['text' => 'Verifiez les champs et leurs limites avant d enregistrer.', 'type' => 'danger'];
    header('Location: ../views/configuration.php');
    exit;
}

$nouveauLogo = null;
$ancienLogo = null;
try {
    $nouveauLogo = stockerLogoConfiguration($_FILES['logo'] ?? null);
    $connexion->beginTransaction();
    $ancienneConfiguration = getConfiguration(true);
    $ancienLogo = $ancienneConfiguration['logo'];

    $ancienneValeur = array(
        'nom_entreprise' => $ancienneConfiguration['nom_entreprise'],
        'adresse' => $ancienneConfiguration['adresse'],
        'telephone' => $ancienneConfiguration['telephone'],
        'email' => $ancienneConfiguration['email'],
        'devise' => $ancienneConfiguration['devise'],
        'seuil_stock_faible' => (int) $ancienneConfiguration['seuil_stock_faible'],
        'texte_pied_recu' => $ancienneConfiguration['texte_pied_recu'],
        'logo' => $ancienLogo,
    );
    $nouvelleValeur = array(
        'nom_entreprise' => $nom,
        'adresse' => $adresse === '' ? null : $adresse,
        'telephone' => $telephone === '' ? null : $telephone,
        'email' => $email === '' ? null : $email,
        'devise' => $devise,
        'seuil_stock_faible' => (int) $seuil,
        'texte_pied_recu' => $textePied === '' ? null : $textePied,
        'logo' => $nouveauLogo ?? ($retirerLogo ? null : $ancienLogo),
    );

    if ($ancienneValeur !== $nouvelleValeur) {
        $req = $connexion->prepare(
            'UPDATE configuration
             SET nom_entreprise = ?, adresse = ?, telephone = ?, email = ?, devise = ?,
                 seuil_stock_faible = ?, texte_pied_recu = ?, logo = ?
             WHERE id = 1'
        );
        $req->execute(array_values($nouvelleValeur));

        $reqJournal = $connexion->prepare(
            'INSERT INTO journal_configuration
                (configuration_id, ancienne_valeur, nouvelle_valeur, adresse_ip)
             VALUES (1, ?, ?, ?)'
        );
        $reqJournal->execute(array(
            json_encode($ancienneValeur, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            json_encode($nouvelleValeur, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            $_SERVER['REMOTE_ADDR'] ?? null,
        ));
    }

    $connexion->commit();
    if ($ancienneValeur['logo'] !== $nouvelleValeur['logo']) {
        supprimerLogoConfiguration($ancienLogo);
    }
    $_SESSION['message'] = [
        'text' => $ancienneValeur === $nouvelleValeur
            ? 'Aucun changement a enregistrer.'
            : 'Configuration enregistree avec succes.',
        'type' => 'success',
    ];
} catch (DomainException $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    supprimerLogoConfiguration($nouveauLogo);
    $_SESSION['message'] = ['text' => $e->getMessage(), 'type' => 'danger'];
} catch (Throwable $e) {
    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }
    supprimerLogoConfiguration($nouveauLogo);
    $_SESSION['message'] = ['text' => 'Impossible d enregistrer la configuration.', 'type' => 'danger'];
}

header('Location: ../views/configuration.php');
exit;