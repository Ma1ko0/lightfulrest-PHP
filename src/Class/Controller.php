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

	public function shiftUriParts()
	{
		array_shift($this->uriParts);
	}
	/**
	 * Get the value of uriParts
	 */
	public function getUriParts()
	{
		return $this->uriParts;
	}

	/**
	 * Set the value of uriParts
	 *
	 * @return  self
	 */
	public function setUriParts($uriParts)
	{
		$this->uriParts = $uriParts;

		return $this;
	}

	/**
	 * Get the value of method
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Set the value of method
	 *
	 * @return  self
	 */
	public function setMethod($method)
	{
		$this->method = $method;

		return $this;
	}
	/**
	 * Deletes a directory and all its contents
	 *
	 * @param string $target The File/Folder to delete
	 * @return void
	 */
	public function deleteDirectory($target)
	{
		if (basename($target) === "." || basename($target) === "..") {
			return;
		}
		if (is_file($target)) {
			unlink($target);
			return;
		}
		$objectsToDelete = scandir($target);
		foreach ($objectsToDelete as $object) {
			$this->deleteDirectory($target . "/" . $object);
		}
		rmdir($target);
	}
}
