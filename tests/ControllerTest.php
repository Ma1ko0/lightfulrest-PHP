<?php

include_once __DIR__ . "/../src/Class/Controller.php";
include_once __DIR__ . "/../src/Enums/Methods.php";
include_once __DIR__ . "/../src/Core/Request.php";

use PHPUnit\Framework\TestCase;
use App\Controller;
use App\Request;

class TestController extends Controller{}

final class ControllerTest extends TestCase
{
	public function testDeleteDirectory()
	{
		$tmpDir = sys_get_temp_dir() . '/test-dir';
		mkdir($tmpDir . '/subdir', 0777, true);
		file_put_contents($tmpDir . '/file.txt', 'test');
		file_put_contents($tmpDir . '/subdir/file2.txt', 'test');

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/';
		$request = new Request();
		$controller = new TestController($request);
		$controller->deleteDirectory($tmpDir);

		$this->assertDirectoryDoesNotExist($tmpDir);
	}
}
