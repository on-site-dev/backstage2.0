<?php
declare(strict_types=1);

/**
 * Handles an HTTP file upload (i.e. a single entry from the $_FILES superglobal)
 * and moves it into a target directory on disk.
 *
 * Expects $_FILES['fieldName'] to be a *single*-file entry, e.g. produced by:
 *   <input type="file" name="fieldName">
 *
 * @param array  $fileEntry   One element of $_FILES, e.g. $_FILES['fieldName'].
 *                            Must contain the standard keys: name, type, tmp_name, error, size.
 * @param string $targetPath  Directory the file should be moved into. Created if it doesn't exist.
 * @param array  $allowedExt  Optional whitelist of allowed extensions (lowercase, no dot),
 *                            e.g. ['jpg', 'png', 'pdf']. Empty array = no restriction.
 * @param int    $maxBytes    Optional max file size in bytes. 0 = no explicit check here
 *                             (php.ini upload_max_filesize / post_max_size still apply).
 *
 * @return string Full path of the uploaded file at its new location.
 *
 * @throws InvalidArgumentException If the $fileEntry array is malformed, the filename is
 *                                  unsafe, the extension isn't allowed, or the file exceeds $maxBytes.
 * @throws RuntimeException         If the upload failed (per PHP's error code), the file isn't
 *                                  a genuine upload, the target directory can't be created/written
 *                                  to, or the move fails.
 */
function uploadHttpFile(
    array $fileEntry,
    string $targetPath,
    array $allowedExt = [],
    int $maxBytes = 0
): string {
    // ---- Basic shape validation -------------------------------------------------
    foreach (['name', 'tmp_name', 'error', 'size'] as $key) {
        if (!array_key_exists($key, $fileEntry)) {
            throw new InvalidArgumentException("Malformed upload entry: missing '{$key}'.");
        }
    }
    if (trim($targetPath) === '') {
        throw new InvalidArgumentException('Target path is required.');
    }

    // ---- Check PHP's own upload error code first -------------------------------
    if ($fileEntry['error'] !== UPLOAD_ERR_OK) {
        $message = match ($fileEntry['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Uploaded file exceeds the maximum allowed size.',
            UPLOAD_ERR_PARTIAL   => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE   => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server is missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Server failed to write the file to disk.',
            UPLOAD_ERR_EXTENSION  => 'Upload was stopped by a PHP extension.',
            default => 'Unknown upload error (code ' . $fileEntry['error'] . ').',
        };
        throw new RuntimeException($message);
    }

    // ---- Confirm this is a genuine HTTP upload (blocks path-injection attacks) --
    if (!is_uploaded_file($fileEntry['tmp_name'])) {
        throw new RuntimeException('File was not received via a genuine HTTP upload.');
    }

    // ---- Sanitize the original filename ----------------------------------------
    $safeName = basename($fileEntry['name']);
    if ($safeName === '') {
        throw new InvalidArgumentException('Uploaded file has no usable filename.');
    }

    // ---- Optional extension whitelist ------------------------------------------
    $ext = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
    if (!empty($allowedExt) && !in_array($ext, $allowedExt, true)) {
        throw new InvalidArgumentException("File type '.{$ext}' is not allowed.");
    }

    // ---- Optional explicit size check -------------------------------------------
    if ($maxBytes > 0 && (int) $fileEntry['size'] > $maxBytes) {
        throw new InvalidArgumentException(
            "File is too large ({$fileEntry['size']} bytes); max allowed is {$maxBytes} bytes."
        );
    }

    // ---- Prepare target directory ------------------------------------------------
    $targetDir = rtrim($targetPath, '/\\');
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new RuntimeException("Unable to create target directory: {$targetDir}");
        }
    }
    if (!is_writable($targetDir)) {
        throw new RuntimeException("Target directory is not writable: {$targetDir}");
    }

    // Avoid clobbering an existing file with the same name
    $targetFile = $targetDir . DIRECTORY_SEPARATOR . $safeName;
    if (is_file($targetFile)) {
        $base   = pathinfo($safeName, PATHINFO_FILENAME);
        $suffix = $ext !== '' ? ".{$ext}" : '';
        $targetFile = $targetDir . DIRECTORY_SEPARATOR . $base . '_' . uniqid() . $suffix;
    }

    // ---- Move the file out of PHP's temp location into place ---------------------
    if (!move_uploaded_file($fileEntry['tmp_name'], $targetFile)) {
        throw new RuntimeException("Failed to move uploaded file to '{$targetFile}'.");
    }

    return $targetFile;
}

// -----------------------------------------------------------------------------
// Example usage (in the script handling your <form method="post" enctype="multipart/form-data">)
// -----------------------------------------------------------------------------
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploadedFile'])) {
    try {
        $newPath = uploadHttpFile(
            fileEntry:  $_FILES['uploadedFile'],
            targetPath: __DIR__ . '/uploads/schedules',
            allowedExt: ['jpg', 'jpeg', 'png', 'pdf', 'csv'],
            maxBytes:   5 * 1024 * 1024 // 5 MB
        );
        echo "File uploaded to: {$newPath}";
    } catch (InvalidArgumentException | RuntimeException $e) {
        error_log($e->getMessage());
        echo "Upload failed: " . $e->getMessage();
    }
}
*/
