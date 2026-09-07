<?php

namespace App\Core;

/**
 * Gestion des uploads d'images (contrôle MIME, taille, extensions).
 */
class Upload
{
    private const MAX_SIZE = 5242880; // 5 Mo
    private const ALLOWED  = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];

    /**
     * Sauvegarde une image. Retourne le nom de fichier, ou null.
     */
    public static function image(array $file, string $folder = 'equipements'): ?string
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        if ($file['size'] > self::MAX_SIZE) {
            throw new \RuntimeException("L'image dépasse la taille maximale autorisée (5 Mo).");
        }

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mime     = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, self::ALLOWED, true)) {
            throw new \RuntimeException('Format d\'image non autorisé (JPG, PNG ou WEBP uniquement).');
        }

        $ext      = array_search($mime, self::ALLOWED, true);
        $filename = 'eq_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $dir = PUBLIC_DIR . '/assets/img/' . trim($folder, '/');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            throw new \RuntimeException('Impossible d\'enregistrer l\'image.');
        }
        return trim($folder, '/') . '/' . $filename;
    }

    public static function remove(?string $filename, string $folder = 'equipements'): void
    {
        if (!$filename) {
            return;
        }
        if (str_contains($filename, '/')) {
            $path = PUBLIC_DIR . '/assets/img/' . ltrim($filename, '/');
        } else {
            $path = PUBLIC_DIR . '/assets/img/' . trim($folder, '/') . '/' . $filename;
        }
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
