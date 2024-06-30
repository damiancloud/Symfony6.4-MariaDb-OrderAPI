symfony 6.3
pgsql
php 8.1

## Getting Started

To start the project, follow these steps:

1. Run the following command to build the Docker container and install dependencies:

```bash
make start
```

2. Start the Docker container:

```bash
make up
```

3. Run migration
```bash
make migrate
```

4. Run data fixtures - populate db structure
```bash
make fixtures
```

# Run app

```plaintext
http://localhost:8000/
```
Run test
```bash
var/www# php bin/phpunit
```

Access the container's console:

```bash
make console
```

# DB 
    DATABASE: mydatabase
    USER: myuser
    PASSWORD: mypassword

# API Endpoints:

- **Create order:** `POST /order/create`
- **Get order by id:** `GET /order/{id}`

For detailed information on each endpoint, refer to the controller `OrderControllerApi`.
