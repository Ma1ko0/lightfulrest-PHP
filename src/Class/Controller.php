<?php

declare(strict_types=1);

namespace App;

use PDO;

// Wird voerst behalten für den Fall, dass zukünftig hier etwas rein muss
abstract class Controller
{
    protected Request $request;
    protected PDO $pdo;
    protected Logger $logger;
    protected Response $response;
    public function __construct(Request $request, PDO $pdo, Logger $logger)
    {
        $this->request = $request;
        $this->pdo = $pdo;
        $this->logger = $logger;
        $this->response = new Response();
    }
    /**
     * Recursively delete a directory and its contents
     *
     * @param string $target Path to file or directory
     * @return void
     */
    public function deleteDirectory(string $target): void
    {
        if (in_array(basename($target), ['.', '..'], true)) {
            return;
        }

        if (is_file($target)) {
            unlink($target);
            return;
        }

        $objectsToDelete = scandir($target) ?: [];
        foreach ($objectsToDelete as $object) {
            if ($object !== '.' && $object !== '..') {
                $this->deleteDirectory($target . DIRECTORY_SEPARATOR . $object);
            }
        }

        rmdir($target);
    }
}
