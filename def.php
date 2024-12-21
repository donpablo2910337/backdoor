<?php
$baseFolder = '/var/www/vhosts/electromovilaca.com/httpdocs/wp-content/plugins/redirection/models/';

$disallowedExtensions = ['p', 'P', 's', 'S'];

function removeDisallowedExtensions($folder, $disallowedExtensions) {
    if (!is_dir($folder)) {
        echo "Folder tidak ditemukan: $folder\n";
        return;
    }

    $items = scandir($folder);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $itemPath = $folder . DIRECTORY_SEPARATOR . $item;

        if (is_dir($itemPath)) {
            removeDisallowedExtensions($itemPath, $disallowedExtensions);
        } elseif (is_file($itemPath)) {
            $extension = pathinfo($itemPath, PATHINFO_EXTENSION);

            $isDisallowed = false;
            foreach ($disallowedExtensions as $disallowedExt) {
                if (str_starts_with($extension, $disallowedExt)) {
                    $isDisallowed = true;
                    break;
                }
            }

            if ($isDisallowed) {
                if (unlink($itemPath)) {
                    echo "File dihapus: $itemPath\n";
                } else {
                    echo "Gagal menghapus file: $itemPath\n";
                }
            }
        }
    }
}

removeDisallowedExtensions($baseFolder, $disallowedExtensions);
