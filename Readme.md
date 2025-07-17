# Custom PHP Lightweight Framework

This project is an ultra-lightweight PHP framework designed to provide only the essential building blocks for web applications. It serves as an alternative to heavier frameworks by focusing on simplicity and minimalism, giving developers just enough to build web applications without unnecessary complexity.

---

## Why This Framework?

The goal of this framework is to create a minimalist, easy-to-understand PHP foundation for small to medium-sized projects. It's perfect for developers who want to avoid the overhead of full-fledged frameworks and need only the most basic features to get started quickly.

This framework includes:
- A custom routing system.
- Database interaction via a custom query builder.
- Basic error and exception handling.
- Environment configuration with `.env` files.
- Built-in logging with configurable levels.

If you need a lightweight foundation to build your project, this framework gives you the flexibility to add just what you need without the bloat.

---

## Features

- **Minimalist Design**: Lightweight, with only essential features.
- **Custom Routing**: Simple and flexible routing to handle HTTP requests.
- **PDO Database Interaction**: Secure database operations using a custom query builder and PDO.
- **Environment Configuration**: Supports `.env` files for environment-specific settings (e.g., database credentials).
- **Error Handling**: Centralized error and exception handling with appropriate HTTP status codes.
- **PSR-4 Autoloading**: Automatically loads classes based on the PSR-4 autoloading standard.
- **Logging**: Built-in logging for tracking application events and debugging.

---

## Project Structure

```
lightfulrest-PHP/
│
├── index.php                # Entry point, handles autoloading and request dispatch
├── .env.example             # Example environment configuration
├── src/
│   ├── Class/               # Base classes (Controller, Repository, etc.)
│   ├── Controller/          # Application controllers (e.g., InputController)
│   ├── Core/                # Core framework logic (Logger, ErrorHandler, QueryBuilder, etc.)
│   ├── Enums/               # Enum definitions (e.g., HTTP methods)
│   ├── Models/              # Data models and repositories
│   │   └── User/            # Example user model and repository
│   └── Middleware/          # (Optional) Middleware for request handling
└── ...
```

---

## Installation

### 1. Clone the repository

Clone the repository to your local machine:
```bash
git clone https://github.com/Ma1ko0/lightfulrest-PHP.git
cd lightfulrest-PHP
```

### 2. Set up the environment

Copy the `.env.example` file to `.env` and configure your environment variables:
```bash
cp .env.example .env
```

Update the `.env` file with your database details, such as:
```
DB_HOST=localhost
DB_NAME=mydatabase
DB_USER=root
DB_PASSWORD=12345678
DEBUG_MODE=true
TIMEZONE=Europe/Berlin
LOCALE=de_DE
LOG_LEVEL=ERROR,WARN
```

### 3. Start the application (with Docker)

If you're using Docker, you can start the application by running:
```bash
docker-compose up
```

Alternatively, you can run the application locally using a web server like Apache or Nginx.

### 4. Access the application

The application will be accessible at:
```
http://localhost:8080
```

---

## Usage

This framework provides a basic structure for handling HTTP requests. Below are some examples of how to use it.

### Routing & Controllers

Routing is handled by parsing the request URI and dispatching to the appropriate controller. You can add new controllers in `src/Controller/`.

**Example: Creating a New Controller**

```php
namespace App;

class ExampleController extends Controller
{
    public function __construct(string $method, array $uriParts) {
        parent::__construct($method, $uriParts);
    }

    public function processRequest(): void
    {
        if ($this->method === Methods::GET) {
            Response::json("Hello World!", 200);
        }
        // Add more logic for POST, PUT, DELETE, etc.
    }
}
```

To register a new route, update your routing logic in `index.php` or extend the dispatcher.

---

### Database Access

Use the custom query builder for secure and flexible database operations.

**Example: Fetching a User by ID**

```php
$userRepo = new UserRepository();
$user = $userRepo->getUserById($userId);

if ($user) {
    Response::json($user->getUsername(), 200);
} else {
    Response::json("User not found", 404);
}
```

You can extend the query builder for more complex queries.

---

### Error Handling & Logging

Errors and exceptions are handled centrally. Logging is configurable via the `.env` file.

**Example: Logging an Event**

```php
Logger::info("User login attempt", ["userId" => $userId]);
```

**Example: Handling an Error**

If an error occurs, the framework will return a JSON error response with the appropriate HTTP status code.

---

### Environment Configuration

All sensitive and environment-specific settings are managed in the `.env` file. This includes database credentials, debug mode, timezone, locale, and log levels.

---

## Extending the Framework

- **Add new controllers** in `src/Controller/` for different endpoints.
- **Create new models and repositories** in `src/Models/`.
- **Customize error handling** in `src/Core/ErrorHandler.php`.
- **Add middleware** for authentication, logging, etc. in `src/Middleware/`.

---

## Example Request Flow

1. **Request**: User sends a GET request to `/users/1`.
2. **Routing**: `index.php` parses the URI and dispatches to `InputController`.
3. **Controller**: `InputController` calls `UserRepository` to fetch user data.
4. **Response**: Returns JSON with user information or error message.

---

## Future Features

- **Authentication**: Token-based authentication or session management.
- **Logging Improvements**: Enhanced logging features and levels.
- **Unit Testing**: Add test cases for better coverage.
- **Expanded Documentation**: More detailed usage examples and documentation.
- **Better Request Validation**: Built-in validation for incoming request data (required fields, types, formats).
- **Response Formatting**: Support for multiple response formats (e.g., JSON, XML).
- **API Documentation Generator**: Automatic generation of OpenAPI/Swagger documentation from controllers.
- **Caching**: Support for caching responses or database queries to improve performance.
- **File Upload Handling**: Secure file upload and storage utilities.
- **CLI Tooling**: Command-line tools for migrations, scaffolding, and maintenance.
- **CORS Support**: Middleware for Cross-Origin Resource Sharing to control access from different domains.
---

## Contributing

Feel free to fork the repository and submit pull requests! Contributions are welcome, whether it's adding new features, improving documentation, or fixing bugs.

---

## License

This project is licensed under the MIT License - see the see the [LICENSE](LICENSE) file for details.