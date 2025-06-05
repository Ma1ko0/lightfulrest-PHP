<?php

namespace App;

use PDO;

class DatabaseQueryBuilder
{
	private PDO $databaseConnection;
	private string $table = '';
	private string $queryMode = 'SELECT';
	private array $columns = ['*'];
	private array $whereClauses = [];
	private array $bindings = [];
	private array $orderBy = [];
	private ?int $limit = null;
	private array $joinClauses = [];
	private array $insertData = [];
	private array $updateData = [];
	private array $groupBy = [];
	private array $havingClauses = [];

	public function __construct(PDO $databaseConnection)
	{
		$this->databaseConnection = $databaseConnection;
	}

	/**
	 * Sets the columns to be selected in the SQL query.
	 *
	 * Validates each column identifier and updates the query mode to 'SELECT'.
	 *
	 * @param array $columns An array of column names to select. Defaults to ['*'] for all columns.
	 * @return self Returns the current instance for method chaining.
	 * @throws InvalidArgumentException If any column identifier is invalid.
	 */
	public function select(array $columns = ['*']): self
	{
		$this->columns = $columns;
		$this->queryMode = 'SELECT';
		return $this;
	}

	/**
	 * Prepares an INSERT query with the provided data.
	 *
	 * @param array $data Associative array of column-value pairs to insert.
	 * @return self Returns the current instance for method chaining.
	 * @throws \InvalidArgumentException If the provided data array is empty.
	 */
	public function insert(array $data): self
	{
		if (empty($data)) {
			throw new \InvalidArgumentException("Insert data cannot be empty.");
		}
		$this->queryMode = 'INSERT';
		$this->insertData = $data;
		return $this;
	}

	/**
	 * Sets the query mode to 'UPDATE' and stores the data to be updated.
	 *
	 * @param array $data Associative array of column-value pairs to update.
	 * @return self Returns the current instance for method chaining.
	 * @throws \InvalidArgumentException If the provided data array is empty.
	 */
	public function update(array $data): self
	{
		if (empty($data)) {
			throw new \InvalidArgumentException("Update data cannot be empty.");
		}
		$this->queryMode = 'UPDATE';
		$this->updateData = $data;
		return $this;
	}

	/**
	 * Sets the query mode to 'DELETE' for building a DELETE SQL statement.
	 *
	 * @return self Returns the current instance for method chaining.
	 */
	public function delete(): self
	{
		$this->queryMode = 'DELETE';
		return $this;
	}

	/**
	 * Sets the table name for the query after validating the identifier.
	 *
	 * @param string $table The name of the table to use in the query.
	 * @return self Returns the current instance for method chaining.
	 * @throws InvalidArgumentException If the table name is not a valid identifier.
	 */
	public function table(string $table): self
	{
		$this->validateIdentifier($table);
		$this->table = $table;
		return $this;
	}

	/**
	 * Adds an INNER JOIN clause to the query.
	 *
	 * @param string $table    The name of the table to join.
	 * @param string $first    The first column for the join condition.
	 * @param string $operator The operator for the join condition (e.g., '=', '<', '>', etc.).
	 * @param string $second   The second column for the join condition.
	 * @return self           Returns the current instance for method chaining.
	 * @throws InvalidArgumentException If any identifier is invalid.
	 */
	public function join(string $table, string $first, string $operator, string $second): self
	{
		return $this->addJoin("INNER", $table, $first, $operator, $second);
	}

	/**
	 * Adds an OUTER JOIN clause to the query.
	 *
	 * @param string $table    The name of the table to join.
	 * @param string $first    The first column for the join condition.
	 * @param string $operator The operator for the join condition (e.g., '=', '<', '>', etc.).
	 * @param string $second   The second column for the join condition.
	 * @return self           Returns the current instance for method chaining.
	 * @throws InvalidArgumentException If any identifier is invalid.
	 */
	public function outerJoin(string $table, string $first, string $operator, string $second): self
	{
		return $this->addJoin("OUTER", $table, $first, $operator, $second);
	}

	/**
	 * Adds a JOIN clause to the query with the specified type.
	 *
	 * This method is used internally to add different types of JOIN clauses (LEFT, RIGHT, INNER).
	 *
	 * @param string $type     The type of join (e.g., 'LEFT', 'RIGHT', 'INNER').
	 * @param string $table    The name of the table to join.
	 * @param string $first    The first column for the join condition.
	 * @param string $operator The operator for the join condition (e.g., '=', '<', '>', etc.).
	 * @param string $second   The second column for the join condition.
	 * @return self           Returns the current instance for method chaining.
	 * @throws InvalidArgumentException If any identifier is invalid.
	 */
	private function addJoin(string $type, string $table, string $first, string $operator, string $second): self
	{
		$this->validateIdentifier($table);
		$this->validateIdentifier($first);
		$this->validateIdentifier($second);
		$this->joinClauses[] = "$type JOIN $table ON $first $operator $second";
		return $this;
	}

	/**
	 * Adds a LEFT JOIN clause to the query.
	 *
	 * @param string $table    The name of the table to join.
	 * @param string $first    The first column for the join condition.
	 * @param string $operator The operator for the join condition (e.g., '=', '<', '>', etc.).
	 * @param string $second   The second column for the join condition.
	 * @return self           Returns the current instance for method chaining.
	 * @throws InvalidArgumentException If any identifier is invalid.
	 */
	public function leftJoin(string $table, string $first, string $operator, string $second): self
	{
		return $this->addJoin("LEFT", $table, $first, $operator, $second);
	}

	/**
	 * Adds a RIGHT JOIN clause to the query.
	 *
	 * @param string $table    The name of the table to join.
	 * @param string $first    The first column for the join condition.
	 * @param string $operator The operator for the join condition (e.g., '=', '<', etc.).
	 * @param string $second   The second column for the join condition.
	 * @return self           Returns the current instance for method chaining.
	 * @throws InvalidArgumentException If any identifier is invalid.
	 */
	public function rightJoin(string $table, string $first, string $operator, string $second): self
	{
		return $this->addJoin("RIGHT", $table, $first, $operator, $second);
	}

	/**
	 * Adds a WHERE clause to the query with the specified column, operator, and value.
	 *
	 * Validates the column name and ensures the operator is allowed. Supports standard comparison operators,
	 * as well as 'LIKE', 'IN', and 'NOT IN'. For 'IN' and 'NOT IN', the value must be an array.
	 * Binds the value(s) to the query to prevent SQL injection.
	 *
	 * @param string $column   The column name to filter by.
	 * @param string $operator The comparison operator (e.g., '=', '!=', '<', 'IN', etc.).
	 * @param mixed  $value    The value to compare the column against. For 'IN'/'NOT IN', must be an array.
	 *
	 * @return self Returns the current instance for method chaining.
	 *
	 * @throws \InvalidArgumentException If the operator is invalid or if the value for 'IN'/'NOT IN' is not an array.
	 */
	public function where(string $column, string $operator, mixed $value): self
	{
		$this->validateIdentifier($column);
		$allowedOperators = ['=', '!=', '<>', '<', '<=', '>', '>=', 'LIKE', 'IN', 'NOT IN'];

		if (!in_array(strtoupper($operator), $allowedOperators, true)) {
			throw new \InvalidArgumentException("Invalid operator: $operator");
		}

		$param = ":param_" . count($this->bindings);

		if (in_array(strtoupper($operator), ['IN', 'NOT IN'])) {
			if (!is_array($value)) {
				throw new \InvalidArgumentException("Value for IN operator must be an array.");
			}
			$placeholders = [];
			foreach ($value as $v) {
				$p = ":param_" . count($this->bindings);
				$placeholders[] = $p;
				$this->bindings[$p] = $v;
			}
			$this->whereClauses[] = "$column $operator (" . implode(', ', $placeholders) . ")";
		} else {
			$this->whereClauses[] = "$column $operator $param";
			$this->bindings[$param] = $value;
		}

		return $this;
	}

	/**
	 * Adds a WHERE clause to the query to check that the specified column is not NULL.
	 *
	 * @param string $column The name of the column to check for non-null values.
	 * @return self Returns the current instance for method chaining.
	 * @throws InvalidArgumentException If the column identifier is invalid.
	 */
	public function whereNotNull(string $column): self
	{
		$this->validateIdentifier($column);
		$this->whereClauses[] = "$column IS NOT NULL";
		return $this;
	}

	/**
	 * Adds a WHERE BETWEEN clause to the query for the specified column and value range.
	 *
	 * @param string $column The name of the column to apply the BETWEEN condition on.
	 * @param array $values An array containing exactly two values: the lower and upper bounds for the BETWEEN condition.
	 * @return self Returns the current instance for method chaining.
	 * @throws \InvalidArgumentException If the $values array does not contain exactly two elements.
	 */
	public function whereBetween(string $column, array $values): self
	{
		$this->validateIdentifier($column);
		if (count($values) !== 2) {
			throw new \InvalidArgumentException("Values for BETWEEN must be an array with exactly two elements.");
		}
		$param1 = ":param_" . count($this->bindings);
		$param2 = ":param_" . (count($this->bindings) + 1);
		$this->whereClauses[] = "$column BETWEEN $param1 AND $param2";
		$this->bindings[$param1] = $values[0];
		$this->bindings[$param2] = $values[1];
		return $this;
	}

	/**
	 * Adds a WHERE clause to the query to check if the specified column is NULL.
	 *
	 * @param string $column The name of the column to check for NULL values.
	 * @return self Returns the current instance for method chaining.
	 * @throws InvalidArgumentException If the column identifier is invalid.
	 */
	public function whereNull(string $column): self
	{
		$this->validateIdentifier($column);
		$this->whereClauses[] = "$column IS NULL";
		return $this;
	}

	/**
	 * Adds an OR condition to the WHERE clause of the query.
	 *
	 * This method combines the previous WHERE clause with a new condition using the OR operator.
	 * It validates the column identifier, adds the new condition, and then merges it with the last clause.
	 *
	 * @param string $column   The column name to apply the condition on.
	 * @param string $operator The comparison operator (e.g., '=', '!=', '<', '>', etc.).
	 * @param mixed  $value    The value to compare the column against.
	 * @return self           Returns the current instance for method chaining.
	 */
	public function orWhere(string $column, string $operator, mixed $value): self
	{
		$this->validateIdentifier($column);
		$lastClause = array_pop($this->whereClauses);
		$this->where($column, $operator, $value);
		$newClause = array_pop($this->whereClauses);
		$this->whereClauses[] = "($lastClause OR $newClause)";
		return $this;
	}

	/**
	 * Adds an OR WHERE clause to the query to check if the specified column is NULL.
	 *
	 * @param string $column The name of the column to check for NULL.
	 * @return self Returns the current instance for method chaining.
	 */
	public function orWhereNull(string $column): self
	{
		$this->validateIdentifier($column);
		$this->appendOrClause("$column IS NULL");
		return $this;
	}

	/**
	 * Appends a new OR clause to the existing WHERE clauses.
	 *
	 * This method pops the last WHERE clause from the internal whereClauses array,
	 * combines it with the provided clause using the OR operator, and pushes the
	 * resulting clause back onto the array. This is useful for building complex
	 * conditional queries with OR logic.
	 *
	 * @param string $clause The SQL condition to append with an OR operator.
	 *
	 * @return void
	 */
	private function appendOrClause(string $clause): void
	{
		$last = array_pop($this->whereClauses);
		$this->whereClauses[] = "($last OR $clause)";
	}

	/**
	 * Adds an ORDER BY clause to the query.
	 *
	 * @param string $column    The name of the column to order by.
	 * @param string $direction The direction of sorting, either 'ASC' (ascending) or 'DESC' (descending). Defaults to 'ASC'.
	 *
	 * @return self Returns the current instance for method chaining.
	 *
	 * @throws \InvalidArgumentException If the provided direction is not 'ASC' or 'DESC'.
	 */
	public function orderBy(string $column, string $direction = 'ASC'): self
	{
		$this->validateIdentifier($column);
		$direction = strtoupper($direction);
		if (!in_array($direction, ['ASC', 'DESC'])) {
			throw new \InvalidArgumentException("Invalid order direction: $direction");
		}
		$this->orderBy[] = "$column $direction";
		return $this;
	}

	/**
	 * Sets the maximum number of records to retrieve in the query.
	 *
	 * @param int $limit The maximum number of records to return. Must be non-negative.
	 * @return self Returns the current instance for method chaining.
	 * @throws \InvalidArgumentException If the provided limit is negative.
	 */
	public function limit(int $limit): self
	{
		if ($limit < 0) {
			throw new \InvalidArgumentException("Limit must be non-negative.");
		}
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Executes the built SQL query and returns the result set as an array.
	 *
	 * Prepares the SQL statement using the current bindings, executes it,
	 * and fetches all resulting rows as associative arrays.
	 *
	 * @return array The result set as an array of associative arrays.
	 * @throws PDOException If the query execution fails.
	 */
	public function get(): array
	{
		$sql = $this->getSQL();
		$stmt = $this->databaseConnection->prepare($sql);
		foreach ($this->bindings as $param => $value) {
			$stmt->bindValue($param, $value);
		}
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Executes the prepared SQL statement using the current bindings.
	 *
	 * This method prepares the SQL statement generated by getSQL(), binds all parameters
	 * from the $bindings array to the statement, and then executes it.
	 *
	 * @return bool Returns true on success or false on failure.
	 */
	public function execute(): bool
	{
		$sql = $this->getSQL();
		$stmt = $this->databaseConnection->prepare($sql);
		foreach ($this->bindings as $param => $value) {
			$stmt->bindValue($param, $value);
		}
		return $stmt->execute();
	}

	/**
	 * Returns the last inserted ID from the database.
	 *
	 * This method retrieves the ID of the last inserted row in the current database connection.
	 * It is typically used after an INSERT operation to get the primary key of the newly created record.
	 *
	 * @return string The last inserted ID as a string.
	 */
	public function getLastInsertId(): string
	{
		return $this->databaseConnection->lastInsertId();
	}

	/**
	 * Adds a GROUP BY clause to the query.
	 *
	 * @param string ...$columns The columns to group by.
	 * @return self Returns the current instance for method chaining.
	 * @throws InvalidArgumentException If any column identifier is invalid.
	 */
	public function groupBy(string ...$columns): self
	{
		foreach ($columns as $column) {
			$this->validateIdentifier($column);
			$this->groupBy[] = $column;
		}
		return $this;
	}

	/**
	 * Adds a HAVING clause to the query.
	 *
	 * This method allows you to specify a condition that must be met by the grouped results.
	 * It does not validate the condition, so it is assumed to be safe and correctly formatted.
	 *
	 * @param string $condition A raw SQL condition (assumed safe).
	 * @return self Returns the current instance for method chaining.
	 */
	public function having(string $condition): self
	{
		$this->havingClauses[] = $condition;
		return $this;
	}

	/**
	 * Generates and returns the SQL query string based on the current query mode and parameters.
	 *
	 * Supported query modes:
	 * - SELECT: Builds a SELECT statement with specified columns, table, joins, where clauses, order, and limit.
	 * - INSERT: Builds an INSERT statement with specified data and binds values to placeholders.
	 * - UPDATE: Builds an UPDATE statement with specified data, binds values to unique placeholders, and validates column identifiers.
	 * - DELETE: Builds a DELETE statement for the specified table.
	 *
	 * Additional clauses (JOIN, WHERE, ORDER BY, LIMIT) are appended as appropriate for SELECT, UPDATE, and DELETE queries.
	 *
	 * @throws \Exception If the query mode is unsupported.
	 * @return string The generated SQL query string.
	 */
	public function getSQL(): string
	{
		switch ($this->queryMode) {
			case 'SELECT':
				$sql = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";
				break;
			case 'INSERT':
				$columns = implode(', ', array_keys($this->insertData));
				$placeholders = implode(', ', array_map(fn($k) => ":$k", array_keys($this->insertData)));
				foreach ($this->insertData as $k => $v) {
					$this->bindings[":$k"] = $v;
				}
				return "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
			case 'UPDATE':
				$updates = [];
				foreach ($this->updateData as $k => $v) {
					$this->validateIdentifier($k);
					$p = ":param_" . count($this->bindings);
					$updates[] = "$k = $p";
					$this->bindings[$p] = $v;
				}
				$sql = "UPDATE {$this->table} SET " . implode(', ', $updates);
				break;
			case 'DELETE':
				$sql = "DELETE FROM {$this->table}";
				break;
			default:
				throw new \Exception("Unsupported query mode: {$this->queryMode}");
		}

		if (!empty($this->joinClauses)) {
			$sql .= ' ' . implode(' ', $this->joinClauses);
		}
		if (!empty($this->whereClauses)) {
			$sql .= " WHERE " . implode(' AND ', $this->whereClauses);
		}
		if (!empty($this->groupBy)) {
			$sql .= " GROUP BY " . implode(', ', $this->groupBy);
		}
		if (!empty($this->havingClauses)) {
			$sql .= " HAVING " . implode(' AND ', $this->havingClauses);
		}
		if (!empty($this->orderBy)) {
			$sql .= " ORDER BY " . implode(', ', $this->orderBy);
		}
		if ($this->limit !== null) {
			$sql .= " LIMIT {$this->limit}";
		}
		return $sql;
	}

	/**
	 * Validates a database identifier (e.g., table or column name).
	 *
	 * @param string $identifier
	 * @return void
	 */
	private function validateIdentifier(string $identifier): void
	{
		if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
			throw new \InvalidArgumentException("Invalid identifier: $identifier");
		}
	}
}
