# Symfony 6.4 Project Description

This Symfony 6.4 project fulfills the following requirements:

1. **Database Setup:** Utilizes MySQL/MariaDB for relational data storage.
   
2. **Initial Migration:** Includes an initial migration class defining database structure without seeding data.

3. **Entity Structure:**
   - **Product:** Represents a product with essential fields such as `id`, `name`, `price`, etc.
   - **Order:** Represents an order with fields like `id`, `customer_id`, `created_at`, etc.
   - **OrderItem:** Relates to items purchased in a single order, with fields `id`, `order_id`, `product_id`, `quantity`, etc.

4. **Order Controller:**
   - Provides endpoints:
     - `POST /orders/create`: Creates an order by accepting a list of product IDs and quantities in JSON format. Returns HTTP 200 and JSON response consistent with 4b.
     - `GET /orders/{order_id}`: Retrieves essential order details in JSON format based on `order_id`.

5. **Symfony Configuration:**
   - Configured to include `x-task: 1` header in every application response.

6. **Calculation Service and Collector Pattern:**
   - **OrderCalculationService:** Calculates total item prices, VAT (assuming 23% per product), and grand total for an order.
   - Uses collector pattern with separate classes for:
     - Summing regular prices,
     - Calculating VAT,
     - Aggregating totals,
   - Configured and integrated within Symfony for efficient order calculation and response formatting.


## Getting Started

To start the project, follow these steps:

1. **Build Docker Container and Install Dependencies:**

   Run the following command to build the Docker container and install dependencies:
   ```bash
   make start
   ```

2. **Start Docker Container:**

   Start the Docker container:
   ```bash
   make up
   ```

3. **Run Migrations:**

   Execute database migrations:
   ```bash
   make migrate
   ```

4. **Run Data Fixtures:**

   Populate the database structure with initial data:
   ```bash
   make fixtures
   ```

### Running the Application

Access the application via:

[Application](http://localhost:8000)


### Accessing Container Console

Access the container's console for direct interaction:
```bash
make console
```

### Database Credentials

- **Database Name:** mydatabase
- **User:** myuser
- **Password:** mypassword

### OPEN API documentation

[OPEN API documentation](http://localhost:8000/api/doc)

### API Endpoints

- **Create Order:**
  ```
  POST /orders/create
  ```

- **Get Order by ID:**
  ```
  GET /orders/{id}
  ```

For detailed information on each endpoint, refer to the `OrderControllerApi` controller.
