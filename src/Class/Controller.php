<?php

namespace App;

// Wird voerst behalten für den Fall, dass zukünftig hier etwas rein muss
abstract class Controller
{
	protected Request $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
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
