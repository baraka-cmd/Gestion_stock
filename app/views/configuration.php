<?php
require_once __DIR__ . '/../core/function.php';
require_once __DIR__ . '/../auth/auth.php';
exigerRole('admin', 'login.php');

$configuration = getConfiguration();
$historique = getHistoriqueConfiguration(10);
$jeton = jetonCsrf('configuration');
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
$echapper = static function ($valeur) {
    return htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$logoValide = is_string($configuration['logo'])
    && preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $configuration['logo']);
$labelsConfiguration = array(
    'nom_entreprise' => 'Nom de l entreprise',
    'adresse' => 'Adresse',
    'telephone' => 'Telephone',
    'email' => 'Courriel',
    'devise' => 'Devise',
    'seuil_stock_faible' => 'Seuil de stock faible',
    'texte_pied_recu' => 'Texte de pied de recu',
    'logo' => 'Logo',
);

include 'entete.php';
?>
<div class="home-content">
    <div class="overview-boxes">
        <div class="box" style="display:block; max-width:760px;">
            <h2>Configuration de l entreprise</h2>
            <?php if (!empty($message['text'])) { ?>
                <div class="alert <?= $echapper($message['type'] ?? 'danger') ?>">
                    <?= $echapper($message['text']) ?>
                </div>
            <?php } ?>

            <form action="../modifConfiguration.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= $echapper($jeton) ?>">

                <label for="nom_entreprise">Nom de l entreprise</label>
                <input type="text" id="nom_entreprise" name="nom_entreprise" maxlength="120" required
                       value="<?= $echapper($configuration['nom_entreprise']) ?>">

                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="adresse" maxlength="255"
                       value="<?= $echapper($configuration['adresse'] ?? '') ?>">

                <label for="telephone">Telephone</label>
                <input type="tel" id="telephone" name="telephone" maxlength="30"
                       value="<?= $echapper($configuration['telephone'] ?? '') ?>">

                <label for="email">Courriel</label>
                <input type="email" id="email" name="email" maxlength="254"
                       value="<?= $echapper($configuration['email'] ?? '') ?>">

                <label for="devise">Libelle de devise</label>
                <input type="text" id="devise" name="devise" maxlength="8" pattern="[A-Za-z0-9]{1,8}" required
                       value="<?= $echapper($configuration['devise']) ?>">

                <label for="seuil_stock_faible">Seuil de stock faible</label>
                <input type="number" id="seuil_stock_faible" name="seuil_stock_faible"
                       min="0" max="1000000" step="1" required
                       value="<?= (int) $configuration['seuil_stock_faible'] ?>">

                <label for="texte_pied_recu">Texte de pied de recu</label>
                <input type="text" id="texte_pied_recu" name="texte_pied_recu" maxlength="255"
                       value="<?= $echapper($configuration['texte_pied_recu'] ?? '') ?>">

                <?php if ($logoValide) { ?>
                    <p>Logo actuel</p>
                    <img src="../../public/uploads/configuration/<?= rawurlencode($configuration['logo']) ?>"
                         alt="Logo de l entreprise" style="max-width:220px; max-height:100px; object-fit:contain;">
                    <label for="retirer_logo">
                        <input type="checkbox" id="retirer_logo" name="retirer_logo" value="1" class="radio">
                        Retirer le logo actuel
                    </label>
                <?php } ?>
                <label for="logo">Logo (JPEG, PNG ou WebP, 2 Mo maximum)</label>
                <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/webp">

                <button type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Enregistrer la configuration</button>
            </form>
            <p>Derniere modification : <?= $echapper(date('d/m/Y H:i', strtotime($configuration['date_modification']))) ?></p>
        </div>

        <div class="box" style="display:block; overflow-x:auto;">
            <h2>Historique des modifications</h2>
            <?php if (!$historique) { ?>
                <p>Aucune modification enregistree.</p>
            <?php } else { ?>
                <table class="mtable">
                    <tr>
                        <th>Date</th>
                        <th>Adresse locale</th>
                        <th>Changements</th>
                    </tr>
                    <?php foreach ($historique as $entree) {
                        $ancienne = json_decode($entree['ancienne_valeur'], true) ?: array();
                        $nouvelle = json_decode($entree['nouvelle_valeur'], true) ?: array();
                        $changements = array();
                        foreach ($labelsConfiguration as $cle => $libelle) {
                            if (($ancienne[$cle] ?? null) !== ($nouvelle[$cle] ?? null)) {
                                $avant = $ancienne[$cle] ?? '';
                                $apres = $nouvelle[$cle] ?? '';
                                if ($cle === 'logo') {
                                    $avant = $avant === '' ? 'Absent' : 'Present';
                                    $apres = $apres === '' ? 'Absent' : 'Present';
                                }
                                $changements[] = $libelle . ' : ' . $avant . ' -> ' . $apres;
                            }
                        }
                    ?>
                        <tr>
                            <td><?= $echapper(date('d/m/Y H:i', strtotime($entree['date_modification']))) ?></td>
                            <td><?= $echapper($entree['adresse_ip'] ?? '') ?></td>
                            <td><?= $echapper(implode('; ', $changements)) ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
        </div>
    </div>
</div>
<?php include 'pied.php'; ?>