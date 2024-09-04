# Overview

This guide provides instructions on how to install and run the project, including the necessary dependencies and environment setup.

### Versions Used

- **Laravel:** 11.x
- **PHP:** 8.3
- **Docker:** (Specify your Docker version)
- **Mailparse PHP Extension:** Required

## Prerequisites

Before installing the project, ensure that you have the following installed:

- PHP (version 8.3)
- Composer (for managing PHP dependencies)
- Mailparse PHP extension
- Docker (if you plan to run the project in a Docker container)

### Installing the Mailparse PHP Extension

To install the Mailparse PHP extension, you can use the following commands depending on your operating system:

**For Ubuntu:**

```bash
sudo apt install php8.3-mailparse
```

## Manual Installation

```
cd /var/www/html
git clone https://github.com/g-zarate/designli-email-parser.git designli-email-parser
cd designli-email-parser
```

```
composer install
```

Make sure the .env file has the follwing configrations
```
APP_URL=http://localhost:8000
....
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

Run the following artisan commands
```
php artisan key:generate
```

```
php artisan migrate
```

## Run with docker

Build the Docker image using the provided Dockerfile
```
docker build -t your-project-name .
```
Start the container
```
docker run -d -p 8000:8000 designli-email-parser
```

The application should now be accessible at http://localhost:8000
