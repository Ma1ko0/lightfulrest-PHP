<?php

include_once __DIR__ . "/../src/Class/Controller.php";
include_once __DIR__ . "/../src/Enums/Methods.php";

use PHPUnit\Framework\TestCase;
use App\Controller;

class TestController extends Controller{}

final class ControllerTest extends TestCase
{
    public function testDeleteDirectory()
    {
        $tmpDir = sys_get_temp_dir() . '/test-dir';
        mkdir($tmpDir . '/subdir', 0777, true);
        file_put_contents($tmpDir . '/file.txt', 'test');
        file_put_contents($tmpDir . '/subdir/file2.txt', 'test');

        $controller = new TestController('GET', []);
        $controller->deleteDirectory($tmpDir);

        $this->assertDirectoryDoesNotExist($tmpDir);
    }
}
