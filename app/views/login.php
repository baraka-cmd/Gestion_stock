<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';

$configuration = getConfiguration();
$premierAdmin = !premierAdministrateurConfigure();
verifierTransportAuthentification($premierAdmin);

if (!empty($_SESSION['utilisateur']['id'])) {
    authentifierUtilisateurCourant('login.php');
    header('Location: dashboard.php');
    exit;
}

$jeton = jetonCsrf('authentification');
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
$echapper = static function ($valeur) {
    return htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$logoValide = is_string($configuration['logo'])
    && preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $configuration['logo']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $premierAdmin ? 'Configuration initiale' : 'Connexion' ?> | <?= $echapper($configuration['nom_entreprise']) ?></title>
    <link rel="stylesheet" href="../../public/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/common.css">
    <link rel="stylesheet" href="../../public/css/views/login.css">
</head>
<body class="auth-page" data-page="login">
    <main class="auth-shell">
        <section class="auth-panel">
            <a class="auth-brand" href="login.php">
                <?php if ($logoValide) { ?>
                    <img src="../../public/uploads/configuration/<?= rawurlencode($configuration['logo']) ?>" alt="">
                <?php } else { ?>
                    <span class="brand-mark" aria-hidden="true"><i class="fa-solid fa-boxes-stacked"></i></span>
                <?php } ?>
                <span><?= $echapper($configuration['nom_entreprise']) ?></span>
            </a>

            <div class="auth-heading">
                <p class="eyebrow"><?= $premierAdmin ? 'MISE EN SERVICE' : 'ESPACE SECURISE' ?></p>
                <h1><?= $premierAdmin ? 'Creer le premier administrateur' : 'Connexion' ?></h1>
                <p><?= $premierAdmin
                    ? 'Cette etape unique configure le compte administrateur principal.'
                    : 'Connectez-vous avec votre adresse courriel et votre mot de passe.' ?></p>
            </div>

            <?php if (!empty($message['text'])) { ?>
                <div class="alert <?= $echapper($message['type'] ?? 'danger') ?>" role="alert">
                    <?= $echapper($message['text']) ?>
                </div>
            <?php } ?>

            <?php if ($premierAdmin) { ?>
                <form class="auth-form" action="../auth/initialiserAdmin.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= $echapper($jeton) ?>">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" maxlength="80" autocomplete="family-name" required>

                    <label for="prenom">Prenom</label>
                    <input type="text" id="prenom" name="prenom" maxlength="80" autocomplete="given-name" required>

                    <label for="email">Adresse courriel</label>
                    <input type="email" id="email" name="email" maxlength="254" autocomplete="email" required>

                    <label for="telephone">Telephone (facultatif)</label>
                    <input type="tel" id="telephone" name="telephone" maxlength="30" autocomplete="tel">

                    <label for="mot_de_passe">Mot de passe</label>
                    <div class="password-field">
                        <input type="password" id="mot_de_passe" name="mot_de_passe" minlength="12" maxlength="72" autocomplete="new-password" required>
                        <button type="button" class="toggle-password" data-toggle-password="#mot_de_passe" aria-label="Afficher le mot de passe"><i class="fa-regular fa-eye"></i></button>
                    </div>

                    <label for="confirmation">Confirmer le mot de passe</label>
                    <div class="password-field">
                        <input type="password" id="confirmation" name="confirmation" minlength="12" maxlength="72" autocomplete="new-password" required>
                        <button type="button" class="toggle-password" data-toggle-password="#confirmation" aria-label="Afficher la confirmation"><i class="fa-regular fa-eye"></i></button>
                    </div>

                    <label for="photo">Photo de profil</label>
                    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" required>
                    <small>JPEG, PNG ou WebP; 2 Mo maximum.</small>

                    <button class="auth-submit" type="submit"><i class="fa-solid fa-user-shield" aria-hidden="true"></i> Creer le compte administrateur</button>
                </form>
            <?php } else { ?>
                <form class="auth-form" action="../auth/authentifier.php" method="POST">
                    <input type="hidden" name="_csrf" value="<?= $echapper($jeton) ?>">
                    <label for="email">Adresse courriel</label>
                    <input type="email" id="email" name="email" maxlength="254" autocomplete="username" required autofocus>

                    <label for="mot_de_passe">Mot de passe</label>
                    <div class="password-field">
                        <input type="password" id="mot_de_passe" name="mot_de_passe" maxlength="72" autocomplete="current-password" required>
                        <button type="button" class="toggle-password" data-toggle-password="#mot_de_passe" aria-label="Afficher le mot de passe"><i class="fa-regular fa-eye"></i></button>
                    </div>

                    <button class="auth-submit" type="submit"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Se connecter</button>
                </form>
            <?php } ?>

            <p class="auth-footer">Les acces et donnees de session sont controles par l application.</p>
        </section>
    </main>
    <script src="../../public/js/common.js"></script>
    <script src="../../public/js/views/login.js"></script>
</body>
</html>