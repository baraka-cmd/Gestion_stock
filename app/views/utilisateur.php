<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole('admin', 'login.php');

$jetonCsrf = jetonCsrfUtilisateurs();
$recherche = isset($_GET['recherche']) && is_string($_GET['recherche'])
    ? trim($_GET['recherche'])
    : '';
$etat = isset($_GET['etat']) && in_array((string) $_GET['etat'], array('0', '1'), true)
    ? (string) $_GET['etat']
    : '';
$idEdition = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$utilisateurEdition = $idEdition ? getUtilisateur($idEdition) : null;
$parPage = 10;
$total = compterUtilisateurs($recherche, $etat);
$totalPages = max(1, (int) ceil($total / $parPage));
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = max(1, min($totalPages, $page === false || $page === null ? 1 : $page));
$utilisateurs = getUtilisateursPagines($recherche, $etat, $parPage, ($page - 1) * $parPage);
$parametresPage = array('recherche' => $recherche, 'etat' => $etat);
$urlPage = static function ($numero) use ($parametresPage) {
    return '?' . http_build_query(array_merge($parametresPage, array('page' => $numero)));
};
$echapper = static function ($valeur) {
    return htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$photoValide = static function ($nomPhoto) {
    return is_string($nomPhoto) && preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $nomPhoto);
};
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

include 'entete.php';
?>
<div class="home-content">
    <div class="overview-boxes">
        <div class="box" style="display:block;">
            <h2><?= $utilisateurEdition ? 'Modifier un utilisateur' : 'Ajouter un utilisateur' ?></h2>
            <?php if ($idEdition && !$utilisateurEdition) { ?>
                <div class="alert danger">Utilisateur introuvable.</div>
            <?php } ?>
            <form action="<?= $utilisateurEdition ? '../modifUtilisateur.php' : '../ajoutUtilisateur.php' ?>"
                  method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= $echapper($jetonCsrf) ?>">
                <?php if ($utilisateurEdition) { ?>
                    <input type="hidden" name="id" value="<?= (int) $utilisateurEdition['id'] ?>">
                <?php } ?>

                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" maxlength="80" required
                       value="<?= $echapper($utilisateurEdition['nom'] ?? '') ?>">

                <label for="prenom">Prenom</label>
                <input type="text" id="prenom" name="prenom" maxlength="80" required
                       value="<?= $echapper($utilisateurEdition['prenom'] ?? '') ?>">

                <label for="email">Courriel</label>
                <input type="email" id="email" name="email" maxlength="254" required
                       value="<?= $echapper($utilisateurEdition['email'] ?? '') ?>">

                <label for="telephone">Telephone (facultatif)</label>
                <input type="tel" id="telephone" name="telephone" maxlength="30"
                       value="<?= $echapper($utilisateurEdition['telephone'] ?? '') ?>">

                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <?php foreach (array('lecture' => 'Lecture', 'gestionnaire' => 'Gestionnaire', 'admin' => 'Administrateur') as $valeurRole => $libelleRole) { ?>
                        <option value="<?= $valeurRole ?>" <?= ($utilisateurEdition['role'] ?? 'lecture') === $valeurRole ? 'selected' : '' ?>><?= $libelleRole ?></option>
                    <?php } ?>
                </select>

                  <label for="mot_de_passe">Mot de passe <?= $utilisateurEdition ? '(laisser vide pour conserver)' : '' ?></label>
                  <input type="password" id="mot_de_passe" name="mot_de_passe" minlength="12" maxlength="72"
                      autocomplete="<?= $utilisateurEdition ? 'new-password' : 'new-password' ?>"
                      <?= $utilisateurEdition ? '' : 'required' ?>>

                  <label for="confirmation">Confirmer le mot de passe</label>
                  <input type="password" id="confirmation" name="confirmation" minlength="12" maxlength="72"
                      autocomplete="new-password" <?= $utilisateurEdition ? '' : 'required' ?>>

                <?php if ($utilisateurEdition && $photoValide($utilisateurEdition['photo'])) { ?>
                    <p>Photo actuelle</p>
                    <img src="../../public/uploads/users/<?= rawurlencode($utilisateurEdition['photo']) ?>"
                         alt="Photo de <?= $echapper($utilisateurEdition['prenom'] . ' ' . $utilisateurEdition['nom']) ?>"
                         width="96" height="96" style="object-fit:cover; border-radius:50%;">
                <?php } ?>
                <label for="photo">Photo <?= $utilisateurEdition ? '(facultative)' : '(obligatoire)' ?></label>
                <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp"
                       <?= $utilisateurEdition ? '' : 'required' ?>>
                <small>JPEG, PNG ou WebP; 2 Mo maximum.</small>

                <button type="submit"><i class="fa-solid <?= $utilisateurEdition ? 'fa-floppy-disk' : 'fa-user-plus' ?>" aria-hidden="true"></i> <?= $utilisateurEdition ? 'Enregistrer' : 'Creer utilisateur' ?></button>
                <?php if ($utilisateurEdition) { ?>
                    <a href="utilisateur.php">Annuler la modification</a>
                <?php } ?>
            </form>
        </div>

        <div class="box" style="display:block; overflow-x:auto;">
            <h2>Utilisateurs (<?= $total ?>)</h2>
            <?php if (!empty($message['text'])) { ?>
                <div class="alert <?= $echapper($message['type'] ?? 'danger') ?>"><?= $echapper($message['text']) ?></div>
            <?php } ?>

            <form method="GET" action="utilisateur.php">
                <label for="recherche">Rechercher</label>
                <input type="search" id="recherche" name="recherche" value="<?= $echapper($recherche) ?>"
                       placeholder="Nom, courriel ou telephone">
                <label for="etat">Etat</label>
                <select id="etat" name="etat">
                    <option value="">Tous</option>
                    <option value="1" <?= $etat === '1' ? 'selected' : '' ?>>Actifs</option>
                    <option value="0" <?= $etat === '0' ? 'selected' : '' ?>>Inactifs</option>
                </select>
                <button type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i> Filtrer</button>
                <a class="text-link" href="utilisateur.php"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Reinitialiser</a>
            </form>

            <table class="mtable">
                <tr>
                    <th>Photo</th>
                    <th>Nom</th>
                    <th>Courriel</th>
                    <th>Telephone</th>
                    <th>Role</th>
                    <th>Etat</th>
                    <th>Cree le</th>
                    <th>Actions</th>
                </tr>
                <?php if (!$utilisateurs) { ?>
                    <tr><td colspan="8">Aucun utilisateur pour ces criteres.</td></tr>
                <?php } ?>
                <?php foreach ($utilisateurs as $utilisateur) { ?>
                    <tr>
                        <td>
                            <?php if ($photoValide($utilisateur['photo'])) { ?>
                                <img src="../../public/uploads/users/<?= rawurlencode($utilisateur['photo']) ?>"
                                     alt="Photo de <?= $echapper($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?>"
                                     width="48" height="48" style="object-fit:cover; border-radius:50%;">
                            <?php } ?>
                        </td>
                        <td><?= $echapper($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?></td>
                        <td><?= $echapper($utilisateur['email']) ?></td>
                        <td><?= $echapper($utilisateur['telephone'] ?? '') ?></td>
                        <td><?= $echapper($utilisateur['role']) ?></td>
                        <td><?= (int) $utilisateur['actif'] === 1 ? 'Actif' : 'Inactif' ?></td>
                        <td><?= $echapper(date('d/m/Y H:i', strtotime($utilisateur['date_creation']))) ?></td>
                        <td>
                            <a class="icon-action" href="?<?= http_build_query(array_merge($parametresPage, array('id' => (int) $utilisateur['id'], 'page' => $page))) ?>" aria-label="Modifier l utilisateur <?= $echapper($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?>" title="Modifier"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a>
                            <form action="../changerEtatUtilisateur.php" method="POST" style="display:inline;">
                                <input type="hidden" name="_csrf" value="<?= $echapper($jetonCsrf) ?>">
                                <input type="hidden" name="id" value="<?= (int) $utilisateur['id'] ?>">
                                <input type="hidden" name="actif" value="<?= (int) $utilisateur['actif'] === 1 ? 0 : 1 ?>">
                                <button type="submit" class="button-quiet" aria-label="<?= (int) $utilisateur['actif'] === 1 ? 'Desactiver' : 'Reactiver' ?> <?= $echapper($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?>" title="<?= (int) $utilisateur['actif'] === 1 ? 'Desactiver' : 'Reactiver' ?>">
                                    <i class="fa-solid <?= (int) $utilisateur['actif'] === 1 ? 'fa-user-slash' : 'fa-user-check' ?>" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>

            <nav aria-label="Pagination utilisateurs" style="display:flex; gap:12px; align-items:center; padding:16px 0;">
                <?php if ($page > 1) { ?>
                    <a href="<?= $echapper($urlPage(1)) ?>"><i class="fa-solid fa-angles-left" aria-hidden="true"></i> Premiere</a>
                    <a href="<?= $echapper($urlPage($page - 1)) ?>"><i class="fa-solid fa-angle-left" aria-hidden="true"></i> Precedente</a>
                <?php } ?>
                <span>Page <?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages) { ?>
                    <a href="<?= $echapper($urlPage($page + 1)) ?>">Suivante <i class="fa-solid fa-angle-right" aria-hidden="true"></i></a>
                    <a href="<?= $echapper($urlPage($totalPages)) ?>">Derniere <i class="fa-solid fa-angles-right" aria-hidden="true"></i></a>
                <?php } ?>
            </nav>
        </div>
    </div>
</div>
<?php include 'pied.php'; ?>