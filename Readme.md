# Custom PHP Lightweight Framework

This project is an ultra-lightweight PHP framework designed to provide only the essential building blocks for web applications. It serves as an alternative to heavier frameworks by focusing on simplicity and minimalism, giving developers just enough to build web applications without unnecessary complexity.

---

## Why This Framework?

The goal of this framework is to create a minimalist, easy-to-understand PHP foundation for small to medium-sized projects. It's perfect for developers who want to avoid the overhead of full-fledged frameworks and need only the most basic features to get started quickly.

This framework includes:
- A custom routing system with middleware support and route grouping.
- JWT authentication with configurable algorithms (HS256, RS256, ES256, etc.).
- Comprehensive middleware pipeline (CORS, authentication, validation, logging, rate limiting).
- Database interaction via a custom query builder.
- Basic error and exception handling.
- Environment configuration with `.env` files.
- Built-in logging with configurable levels.
- Docker support with docker-compose.

If you need a lightweight foundation to build your project, this framework gives you the flexibility to add just what you need without the bloat.

---

## Features

- **Minimalist Design**: Lightweight, with only essential features.
- **Custom Routing**: Simple and flexible routing with middleware support and route grouping.
- **JWT Authentication**: Secure token-based authentication with configurable algorithms (HS256, RS256, ES256, etc.).
- **Middleware Pipeline**: Comprehensive request processing with CORS, authentication, JSON validation, logging, rate limiting, and session management.
- **PDO Database Interaction**: Secure database operations using a custom query builder and PDO.
- **Environment Configuration**: Supports `.env` files for environment-specific settings (database, JWT, logging, etc.).
- **Error Handling**: Centralized error and exception handling with appropriate HTTP status codes.
- **PSR-4 Autoloading**: Automatically loads classes based on the PSR-4 autoloading standard.
- **Logging**: Built-in logging for tracking application events and debugging.
- **Docker Support**: Ready-to-use docker-compose setup for development and deployment.

---

## Project Structure

```
lightfulrest-PHP/
│
├── index.php                # Entry point, handles autoloading and request dispatch
├── .env                      # Environment configuration (copy from .env.example)
├── .env.example             # Example environment configuration
├── docker-compose.yml       # Docker setup for development
├── Dockerfile.api          # Docker configuration for the API
├── composer.json           # PHP dependencies
├── phpunit.xml             # PHPUnit test configuration
├── src/
│   ├── Class/               # Base classes (Controller, Repository, Router, Route)
│   ├── Controller/          # Application controllers (UserController, WebLoginController)
│   ├── Core/                # Core framework logic (Logger, ErrorHandler, QueryBuilder, etc.)
│   ├── Enums/               # Enum definitions (HTTP methods)
│   ├── Middleware/          # Middleware classes (Auth, CORS, JSON validation, etc.)
│   └── Models/              # Data models and repositories
│       └── User/            # User model and repository
├── tests/                   # PHPUnit test files
│   ├── Fakes/              # Test fake classes
│   └── *.php               # Test files
└── vendor/                  # Composer dependencies
```

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/Ma1ko0/lightfulrest-PHP.git
cd lightfulrest-PHP
```

### 2. Set up the environment

Copy the `.env.example` file to `.env` and configure your environment variables:
```bash
cp .env.example .env
```

Update the `.env` file with your configuration:

```env
# Database Configuration
DB_HOST=mydb
DB_NAME=mydatabase
DB_USER=root
DB_PASSWORD=12345678

# Application Settings
DEBUG_MODE=true
TIMEZONE=Europe/Berlin
LOCALE=de_DE
LOG_LEVEL=ERROR,WARN,DEBUG

# JWT Authentication (choose algorithm and configure accordingly)
JWT_ALGORITHM=HS256
JWT_SECRET=your-super-secure-jwt-secret-key-change-this-in-production
# For asymmetric algorithms (RS256, ES256), use these instead:
# JWT_PUBLIC_KEY=-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----
# JWT_PRIVATE_KEY=-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----
```

### 3. Install Dependencies

Install PHP dependencies using Composer:
```bash
composer install
```

### 4. Start the application (with Docker)

If you're using Docker, you can start the application by running:
```bash
docker-compose up
```

This will start:
- **API Server**: Available at `http://localhost:8080`
- **MariaDB Database**: Available at `localhost:3306`
- **phpMyAdmin**: Available at `http://localhost:9090`

### 5. Alternative: Run locally

Alternatively, you can run the application locally using a web server like Apache or Nginx, or use PHP's built-in server:
```bash
php -S localhost:8080 index.php
```

### 6. Access the application

- **API**: `http://localhost:8080`
- **phpMyAdmin**: `http://localhost:9090` (user: root, password: from .env)
- **Database**: `localhost:3306` (credentials from .env)

---

## Usage

This framework provides a comprehensive structure for handling HTTP requests with authentication, middleware, and routing. Below are examples of how to use its features.

### JWT Authentication

The framework includes JWT (JSON Web Token) authentication with support for multiple algorithms.

**Generating a JWT Token:**

```php
use App\Middleware\RestAuthMiddleware;

// Generate a token for a user
$payload = [
    'user_id' => 123,
    'username' => 'john_doe',
    'role' => 'admin'
];

$token = RestAuthMiddleware::generateToken($payload, 24); // 24 hours expiration
```

**Using JWT Authentication in Routes:**

```php
// In src/Core/Routes.php
Route::group(['middleware' => [RestAuthMiddleware::class]], function() {
    Route::get('/api/profile', [UserController::class, 'getProfile']);
    Route::post('/api/users', [UserController::class, 'createUser']);
});
```

**Client-side Usage:**

```javascript
// Include the token in Authorization header
fetch('/api/profile', {
    headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
    }
});
```

**Supported JWT Algorithms:**
- `HS256` (HMAC SHA-256) - Default, uses shared secret
- `RS256` (RSA SHA-256) - Uses public/private key pair
- `ES256` (ECDSA SHA-256) - Uses elliptic curve keys
- And other algorithms supported by firebase/php-jwt

### Middleware

The framework includes a comprehensive middleware system for request processing.

**Available Middleware:**

- `RestAuthMiddleware` - JWT token authentication
- `AuthMiddleware` - Session-based authentication
- `CorsMiddleware` - Cross-Origin Resource Sharing headers
- `JsonContentTypeMiddleware` - Validates JSON content-type
- `LoggingMiddleware` - Request logging
- `RateLimitMiddleware` - API rate limiting
- `SessionMiddleware` - Session management

**Using Middleware:**

```php
// Apply to all routes in a group
Route::group(['middleware' => [LoggingMiddleware::class, RateLimitMiddleware::class]], function() {
    Route::get('/api/users', [UserController::class, 'getUsers']);
});

// Apply to specific routes
Route::get('/api/admin', [AdminController::class, 'dashboard'])
    ->middleware([RestAuthMiddleware::class])
    ->register();
```

**Creating Custom Middleware:**

```php
// src/Middleware/CustomMiddleware.php
namespace App\Middleware;

use App\Request;

class CustomMiddleware
{
    public static function handle(Request $request, callable $next)
    {
        // Pre-processing logic
        if (/* some condition */) {
            Response::error('Access denied', 403);
            return;
        }
        
        // Continue to next middleware/controller
        return $next($request);
    }
}
```

### Routing & Controllers

Routes are defined in [`src/Core/Routes.php`](src/Core/Routes.php) using the fluent Route API.

**Basic Route Definition:**

```php
// src/Core/Routes.php
use App\Route;
use App\UserController;

// Simple routes
Route::get('/hello', function(Request $request) {
    return Response::json(['message' => 'Hello, World!']);
})->register();

// Routes with parameters
Route::get('/users/(\d+)', [UserController::class, 'getUserDataByID'])->register();

// Different HTTP methods
Route::post('/users', [UserController::class, 'createUser'])->register();
Route::put('/users/(\d+)', [UserController::class, 'updateUser'])->register();
Route::delete('/users/(\d+)', [UserController::class, 'deleteUser'])->register();
```

**Route Grouping with Middleware:**

```php
// Group routes with common middleware and prefixes
Route::group(['prefix' => '/api', 'middleware' => [LoggingMiddleware::class]], function() {
    Route::get('/users', [UserController::class, 'getUsers'])->register();
    Route::get('/users/(\d+)', [UserController::class, 'getUserDataByID'])->register();
    
    // Nested groups
    Route::group(['middleware' => [RestAuthMiddleware::class]], function() {
        Route::post('/users', [UserController::class, 'createUser'])->register();
        Route::put('/users/(\d+)', [UserController::class, 'updateUser'])->register();
    });
});
```

**Route-specific Middleware:**

```php
// Apply middleware to individual routes
Route::post('/login', [AuthController::class, 'login'])
    ->middleware([RateLimitMiddleware::class])
    ->register();
```

- The first argument is the HTTP method (use the `Methods` enum).
- The second argument is the route pattern (regex supported).
- The third argument is either a `[ControllerClass::class, 'methodName']` array or a callable.

**To add a new controller:**
1. Create a new file in `src/Controller/`, e.g., `ExampleController.php`.
2. Extend the `Controller` base class.
3. Add your handler methods.

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
    Response::error("User not found", 404);
}
```

---

### Error Handling & Logging

Errors and exceptions are handled centrally. Logging is configurable via the `.env` file.

**Example: Logging an Event**

```php
Logger::logging("User login attempt", INFO);
```

**Example: Handling an Error**

If an error occurs, the framework will return a JSON error response with the appropriate HTTP status code.

---

### Testing

The framework includes comprehensive unit tests using PHPUnit.

**Running Tests:**

```bash
# Run all tests
./vendor/bin/phpunit

# Run with coverage report
./vendor/bin/phpunit --coverage-html build/coverage

# Run specific test file
./vendor/bin/phpunit tests/MiddlewareTest.php
```

**Writing Tests:**

```php
// tests/ExampleTest.php
use PHPUnit\Framework\TestCase;
use App\Middleware\ExampleMiddleware;
use App\Request;

class ExampleTest extends TestCase
{
    public function testExampleMiddleware()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $request = new Request();

        ob_start();
        ExampleMiddleware::handle($request, function($req) {
            echo 'success';
        });
        $output = ob_get_clean();

        $this->assertEquals('success', $output);
    }
}
```

## Extending the Framework

- **Add new controllers** in `src/Controller/` for different endpoints.
- **Create new models and repositories** in `src/Models/`.
- **Customize error handling** in `src/Core/ErrorHandler.php`.
- **Add middleware** for authentication, logging, etc. in `src/Middleware/`.

---

## Example Request Flow

1. **Request**: User sends a GET request to `/users/1`.
2. **Routing**: `index.php` loads `src/Core/Routes.php`, which dispatches to `UserController`.
3. **Controller**: `UserController` calls `UserRepository` to fetch user data.
4. **Response**: Returns JSON with user information or error message.

---

## Future Features

- **Expanded Documentation**: More detailed usage examples and API documentation.
- **Better Request Validation**: Built-in validation for incoming request data (required fields, types, formats).
- **Response Formatting**: Support for multiple response formats (e.g., JSON, XML).
- **API Documentation Generator**: Automatic generation of OpenAPI/Swagger documentation from controllers.
- **Caching**: Support for caching responses or database queries to improve performance.
- **File Upload Handling**: Secure file upload and storage utilities.
- **CLI Tooling**: Command-line tools for migrations, scaffolding, and maintenance.
- **Database Migrations**: Built-in migration system for database schema changes.
- **API Versioning**: Support for API versioning strategies.
- **Performance Monitoring**: Built-in performance metrics and monitoring.
---

## Contributing

Feel free to fork the repository and submit pull requests! Contributions are welcome, whether it's adding new features, improving documentation, or fixing bugs.

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE).