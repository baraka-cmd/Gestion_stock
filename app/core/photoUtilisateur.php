<?php

function stockerPhotoUtilisateur($fichier, $obligatoire = true)
{
    if (!is_array($fichier) || !isset($fichier['error'])) {
        if (!$obligatoire) {
            return null;
        }
        throw new DomainException('Veuillez choisir une photo.');
    }

    if ($fichier['error'] === UPLOAD_ERR_NO_FILE && !$obligatoire) {
        return null;
    }
    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        throw new DomainException('Le transfert de la photo a echoue.');
    }
    if (!isset($fichier['size']) || $fichier['size'] < 1 || $fichier['size'] > 2 * 1024 * 1024) {
        throw new DomainException('La photo doit faire au maximum 2 Mo.');
    }
    if (empty($fichier['tmp_name']) || !is_uploaded_file($fichier['tmp_name'])) {
        throw new DomainException('Le fichier transmis n est pas une photo valide.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($fichier['tmp_name']);
    $typesAutorises = array(
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    );
    $dimensions = @getimagesize($fichier['tmp_name']);
    if (!isset($typesAutorises[$mime]) || $dimensions === false
        || ($dimensions['mime'] ?? '') !== $mime) {
        throw new DomainException('Format refuse. Utilisez une image JPEG, PNG ou WebP.');
    }
    $largeur = (int) $dimensions[0];
    $hauteur = (int) $dimensions[1];
    if ($largeur < 1 || $hauteur < 1 || $largeur > 4000 || $hauteur > 4000
        || $largeur * $hauteur > 12000000) {
        throw new DomainException('Les dimensions de la photo sont trop grandes.');
    }

    $repertoire = __DIR__ . '/../public/uploads/users';
    if (!is_dir($repertoire) && !mkdir($repertoire, 0755, true) && !is_dir($repertoire)) {
        throw new RuntimeException('Impossible de preparer le repertoire des photos.');
    }

    $nomFichier = bin2hex(random_bytes(16)) . '.' . $typesAutorises[$mime];
    if (!move_uploaded_file($fichier['tmp_name'], $repertoire . '/' . $nomFichier)) {
        throw new RuntimeException('Impossible d enregistrer la photo.');
    }

    return $nomFichier;
}

function supprimerPhotoUtilisateur($nomFichier)
{
    if (!is_string($nomFichier) || !preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $nomFichier)) {
        return;
    }

    $chemin = __DIR__ . '/../public/uploads/users/' . $nomFichier;
    if (is_file($chemin)) {
        unlink($chemin);
    }
}