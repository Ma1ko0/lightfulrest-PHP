<?php

namespace App;

use Methods;

class Request
{
	private Methods $method;
	private string $uri;
	private string $path;
	private array $query;
	private array $headers;
	private ?string $body;
	private array $postData;
	private array $files;

	public function __construct()
	{
		$this->method = $this->parseMethod();
		$this->uri = $_SERVER['REQUEST_URI'] ?? '';
		$this->path = parse_url($this->uri, PHP_URL_PATH) ?? '';
		$this->query = $_GET;
		$this->headers = $this->parseHeaders();
		$this->body = $this->parseBody();
		$this->postData = $_POST;
		$this->files = $_FILES;
	}

	private function parseMethod(): Methods
	{
		$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
		return Methods::from($method) ?? Methods::UNKNOWN;
	}

	private function parseHeaders(): array
	{
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            foreach ($_SERVER as $key => $value) {
                if (str_starts_with($key, 'HTTP_')) {
                    $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $headers[$header] = $value;
                }
            }
        }
        // Handle CONTENT_TYPE and CONTENT_LENGTH which are not HTTP_ prefixed
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        }
        return $headers;
    }

    private function parseBody(): ?string
	{
		return file_get_contents('php://input') ?: null;
	}

	public function getMethod(): Methods
	{
		return $this->method;
	}

	public function getUri(): string
	{
		return $this->uri;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getQuery(?string $key = null): mixed
	{
		if ($key === null) {
			return $this->query;
		}
		return $this->query[$key] ?? null;
	}

	public function getHeaders(?string $key = null): mixed
	{
		if ($key === null) {
			return $this->headers;
		}
		return $this->headers[$key] ?? null;
	}

	public function getBody(): ?string
	{
		return $this->body;
	}

	public function getPostData(?string $key = null): mixed
	{
		if ($key === null) {
			return $this->postData;
		}
		return $this->postData[$key] ?? null;
	}

	public function getFiles(): array
	{
		return $this->files;
	}

	public function isMethod(Methods $method): bool
	{
		return $this->method === $method;
	}
}