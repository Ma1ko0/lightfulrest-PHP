<?php

declare(strict_types=1);

include_once __DIR__ . "/../src/Class/Model.php";
include_once __DIR__ . "/../src/Models/User/User.php";

use PHPUnit\Framework\TestCase;
use App\User\User;

final class ModelTest extends TestCase
{
    public function testJsonSerializeHidesPasswordHash(): void
    {
        $user = new User(1, 'Alice', 'alice@ex.com', 'secret-hash', '2026-01-01', '2026-01-02');
        $data = $user->jsonSerialize();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('username', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayNotHasKey('password_hash', $data);
    }

    public function testJsonEncodeHidesPasswordHash(): void
    {
        $user = new User(1, 'Alice', 'alice@ex.com', 'secret-hash', '2026-01-01', '2026-01-02');
        $json = json_encode($user, JSON_THROW_ON_ERROR);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('alice', $decoded['username']);
        $this->assertArrayNotHasKey('password_hash', $decoded);
    }
}
