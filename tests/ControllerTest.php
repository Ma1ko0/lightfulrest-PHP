<?php

include_once __DIR__ . "/../src/Class/Controller.php";
include_once __DIR__ . "/../src/Enums/Methods.php";

use PHPUnit\Framework\TestCase;
use App\Controller;

class TestController extends Controller
{
    public function processRequest(): void
    {
        // Dummy implementation for abstract method
    }
}

final class ControllerTest extends TestCase
{
    public function testGetUriParts()
    {
        $controller = new TestController('GET', ['users', '123']);
        $this->assertSame(['users', '123'], $controller->getUriParts());
    }

    public function testShiftUriParts()
    {
        $controller = new TestController('GET', ['users', '123']);
        $controller->shiftUriParts();
        $this->assertSame(['123'], $controller->getUriParts());
    }

    public function testGetFirstUriPart()
    {
        $controller = new TestController('GET', ['users', '123']);
        $this->assertEquals('users', $controller->getFirstUriPart());
    }

    public function testGetLastUriPart()
    {
        $controller = new TestController('GET', ['users', '123']);
        $this->assertEquals('123', $controller->getLastUriPart());
    }

    public function testGetUriPart()
    {
        $controller = new TestController('GET', ['users', '123']);
        $this->assertEquals('123', $controller->getUriPart(1));
        $this->assertNull($controller->getUriPart(5));
    }

    public function testGetUriSize()
    {
        $controller = new TestController('GET', ['users', '123']);
        $this->assertEquals(2, $controller->getUriSize());
    }

    public function testSetUriParts()
    {
        $controller = new TestController('GET', ['users']);
        $controller->setUriParts(['posts', '456']);
        $this->assertSame(['posts', '456'], $controller->getUriParts());
    }

    public function testGetMethod()
    {
        $controller = new TestController('post', []);
        $this->assertEquals(Methods::POST, $controller->getMethod());
    }

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
