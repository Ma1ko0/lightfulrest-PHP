<?php

namespace App;

abstract class Controller
{
    private readonly string $method;
    private array $uriParts;

    public function __construct(string $method, array $uriParts)
    {
        $this->method = $method;
        $this->uriParts = $uriParts;
    }

    abstract public function processRequest(): void;

    public function shiftUriParts(): void
    {
        array_shift($this->uriParts);
    }

    /**
     * Get the URI parts
     *
     * @return array
     */
    public function getUriParts(): array
    {
        return $this->uriParts;
    }

    /**
     * Set the URI parts
     *
     * @param array $uriParts
     * @return self
     */
    public function setUriParts(array $uriParts): self
    {
        $this->uriParts = $uriParts;
        return $this;
    }

    /**
     * Get the HTTP method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set the HTTP method
     *
     * @param string $method
     * @return self
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
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
