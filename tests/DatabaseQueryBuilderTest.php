<?php
include_once __DIR__ . "/../src/Core/DatabaseQueryBuilder.php";

use App\DatabaseQueryBuilder;
use PHPUnit\Framework\TestCase;

class DatabaseQueryBuilderTest extends TestCase
{

	private PDO $pdo;

	protected function setUp(): void
	{
		$this->pdo = new PDO('sqlite::memory:');//new PDO('mysql:host=127.0.0.1;dbname=testdb', 'testuser', 'testpass');
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec("DROP TABLE IF EXISTS test_users");
		$this->pdo->exec(<<<SQL
			CREATE TABLE test_users (
				id INT AUTO_INCREMENT PRIMARY KEY,
				username VARCHAR(255),
				email VARCHAR(255),
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
			)
		SQL);
	}

	public function testInsertAndSelect(): void
	{
		$builder = new DatabaseQueryBuilder($this->pdo);
		$builder->table('test_users')->insert([
			'username' => 'johndoe',
			'email' => 'john@example.com'
		])->execute();

		$results = (new DatabaseQueryBuilder($this->pdo))
			->select()
			->table('test_users')
			->where('username', '=', 'johndoe')
			->get();

		$this->assertCount(1, $results);
		$this->assertEquals('john@example.com', $results[0]['email']);
	}

	public function testUpdate(): void
	{
		$this->pdo->exec("INSERT INTO test_users (username, email) VALUES ('jane', 'jane@example.com')");

		$builder = new DatabaseQueryBuilder($this->pdo);
		$builder->table('test_users')->update([
			'email' => 'jane.doe@example.com'
		])->where('username', '=', 'jane')->execute();

		$stmt = $this->pdo->query("SELECT email FROM test_users WHERE username = 'jane'");
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$this->assertEquals('jane.doe@example.com', $row['email']);
	}

	public function testDelete(): void
	{
		$this->pdo->exec("INSERT INTO test_users (username, email) VALUES ('tempuser', 'temp@example.com')");

		$builder = new DatabaseQueryBuilder($this->pdo);
		$builder->table('test_users')->delete()->where('username', '=', 'tempuser')->execute();

		$stmt = $this->pdo->query("SELECT COUNT(*) as count FROM test_users WHERE username = 'tempuser'");
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$this->assertEquals(0, $row['count']);
	}

	public function testWhereBetween(): void
	{
		$this->pdo->exec("INSERT INTO test_users (username, email, created_at) VALUES ('alice', 'a@example.com', '2024-01-01 00:00:00')");
		$this->pdo->exec("INSERT INTO test_users (username, email, created_at) VALUES ('bob', 'b@example.com', '2025-01-01 00:00:00')");

		$builder = new DatabaseQueryBuilder($this->pdo);
		$results = $builder->select()
			->table('test_users')
			->whereBetween('created_at', ['2023-12-31', '2024-12-31'])
			->get();

		$this->assertCount(1, $results);
		$this->assertEquals('alice', $results[0]['username']);
	}

	public function testInvalidIdentifierThrows(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$builder = new DatabaseQueryBuilder($this->pdo);
		$builder->table('invalid-table');
	}
	public function testSimpleSelect()
	{
		$pdoStub = $this->createStub(PDO::class);
		$databaseName = "Test";

		$sql = (new DatabaseQueryBuilder($pdoStub))->select()->table($databaseName)->getSQL();

		$expected = "SELECT * FROM $databaseName";
		$this->assertEquals($expected, $sql);
	}

	public function testSimpleInsert()
	{
		$pdoStub = $this->createStub(PDO::class);
		$databaseName = "Test";
		$insertData = ["Name" => "TestName", "KEY" => "VALUE"];

		$sql = (new DatabaseQueryBuilder($pdoStub))->insert($insertData)->table($databaseName)->getSQL();

		$expected = "INSERT INTO $databaseName (" . implode(", ", array_keys($insertData)) . ") VALUES (:" .  implode(", :", array_keys($insertData)) . ")";
		$this->assertEquals($expected, $sql);
	}
}
