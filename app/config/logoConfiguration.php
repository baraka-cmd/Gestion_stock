<?php

function stockerLogoConfiguration($fichier)
{
    if (!is_array($fichier) || !isset($fichier['error']) || $fichier['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        throw new DomainException('Le transfert du logo a echoue.');
    }
    if (!isset($fichier['size']) || $fichier['size'] < 1 || $fichier['size'] > 2 * 1024 * 1024
        || empty($fichier['tmp_name']) || !is_uploaded_file($fichier['tmp_name'])) {
        throw new DomainException('Le logo doit etre une image valide de 2 Mo maximum.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($fichier['tmp_name']);
    $extensions = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
    $dimensions = @getimagesize($fichier['tmp_name']);
    if (!isset($extensions[$mime]) || $dimensions === false || ($dimensions['mime'] ?? '') !== $mime) {
        throw new DomainException('Format de logo refuse. Utilisez une image JPEG, PNG ou WebP.');
    }
    $largeur = (int) $dimensions[0];
    $hauteur = (int) $dimensions[1];
    if ($largeur < 1 || $hauteur < 1 || $largeur > 4000 || $hauteur > 4000
        || $largeur * $hauteur > 12000000) {
        throw new DomainException('Les dimensions du logo sont trop grandes.');
    }

    $repertoire = __DIR__ . '/../public/uploads/configuration';
    if (!is_dir($repertoire) && !mkdir($repertoire, 0755, true) && !is_dir($repertoire)) {
        throw new RuntimeException('Impossible de preparer le repertoire du logo.');
    }

    $nomFichier = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($fichier['tmp_name'], $repertoire . '/' . $nomFichier)) {
        throw new RuntimeException('Impossible d enregistrer le logo.');
    }
    return $nomFichier;
}

function supprimerLogoConfiguration($nomFichier)
{
    if (!is_string($nomFichier) || !preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $nomFichier)) {
        return;
    }

    $chemin = __DIR__ . '/../public/uploads/configuration/' . $nomFichier;
    if (is_file($chemin)) {
        unlink($chemin);
    }
}