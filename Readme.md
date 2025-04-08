# Custom PHP Lightweight Framework

This project is an ultra-lightweight PHP framework designed to provide only the essential building blocks for web applications. It serves as an alternative to heavier frameworks by focusing on simplicity and minimalism, giving developers just enough to build web applications without unnecessary complexity.

## Why This Framework?

The goal of this framework is to create a minimalist, easy-to-understand PHP framework for small to medium-sized projects. It's perfect for developers who want to avoid the overhead of full-fledged frameworks and need only the most basic features to get started quickly.

This framework includes:
- A custom routing system.
- Simple PDO-based database interaction.
- Basic error handling.
- Environment configuration with `.env` files.

If you need a lightweight foundation to build your project, this framework will give you the flexibility to add just what you need without the bloat.

## Features

- **Minimalist Design**: A lightweight framework with only the essential features.
- **Custom Routing**: A simple and flexible routing system to handle HTTP requests.
- **PDO Database Interaction**: Basic database operations using PDO for secure and efficient database access.
- **Environment Configuration**: Supports `.env` files to manage environment-specific settings (e.g., database credentials).
- **Error Handling**: Basic error handling with appropriate HTTP status codes.
- **PSR-4 Autoloading**: Automatically loads classes based on the PSR-4 autoloading standard.
- **Logging**: Built-in logging for tracking application events and debugging.

## Installation

### 1. Clone the repository

Clone the repository to your local machine:
```bash
git clone https://github.com/kakajks/LightfulRest.git
```

### 2. Set up the environment

Copy the `.env.example` file to `.env` and configure your environment variables (e.g., database credentials):
```bash
cp .env.example .env
```

Update the `.env` file with your database details, such as:
```
DB_HOST=localhost
DB_NAME=mydatabase
DB_USER=root
DB_PASSWORD=12345678
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

## Usage

This framework provides a basic structure for handling HTTP requests. Below are some examples of how to use it.

### Routes

- **GET** `/your-endpoint`: Handle GET requests to fetch data or perform actions.
- **POST** `/your-endpoint`: Handle POST requests to submit data or perform actions.

### Example Code for Adding a New Route:

1. Create a controller class that extends the base controller.
2. Define methods to handle GET or POST requests.
3. Add your logic to process the request and return a response.

```php
namespace App;

class ExampleController extends Controller
{
    public function processRequest(): void
    {
        if ($this->getMethod() === 'GET') {
            echo 'Hello, world!';
        }
    }
}
```

## Future Features

- **Authentication**: Implement token-based authentication or session management.
- **Logging Improvements**: Enhanced logging features and logging levels.
- **Advanced Database Helpers**: Implement more advanced database operations like query builders.
- **Unit Testing**: Add test cases for better test coverage.
- **Expanded Documentation**: Provide more detailed usage examples and documentation.

## Contributing

Feel free to fork the repository and submit pull requests if you'd like to contribute! This project is open to contributions, whether it's adding new features, improving documentation, or fixing bugs.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
