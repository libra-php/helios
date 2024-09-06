# Helios

Helios is a lightweight, flexible PHP framework designed for building modern web applications. It integrates seamlessly with htmx to provide dynamic, interactive user interfaces, making web development more intuitive and efficient.

Helios is a hobby project developed for learning and experimentation purposes. While it provides a flexible framework for building web applications, it is not designed or tested for production environments or mission-critical use cases. Users should be aware that Helios may lack the robustness, security, and performance optimizations required for high-stakes or enterprise-level applications. For such scenarios, it is recommended to use well-established frameworks with proven track records.

## Docker Environment

Getting started with Helios is easy, especially with Docker. Just follow these steps to have your application running in no time!

### Step 1: Clone the Project
Clone the latest version of Helios from GitHub.
```bash
git clone https://github.com/libra-php/helios.git
```

### Step 2: Install Dependencies
Use [Composer](https://getcomposer.org) to install the necessary dependencies.
```bash
composer install
```

### Step 3: Configure Environment
Copy the example environment file and update the database configuration with your credentials.
```bash
cp env.example .env

# Edit .env file with your settings
# DB_ENABLED=true
# DB_NAME=helios
# DB_USERNAME=helios
# DB_ROOTPASS=rootpass
# DB_PASSWORD=userpass
# DB_HOST=mysql
# DB_PORT=3306
# DB_CHARSET=utf8mb4
```

### Step 4: Set Up Docker Containers
Run Docker Compose to start the application stack, which includes MySQL, nginx, and PHP.
```bash
sudo docker compose up --build --detach
```

### Step 5: Generate Application Key
To ensure the security of your application, generate a unique application key.
```bash
# Access the PHP container
sudo docker exec -it helios-php bash
```
```bash
# Generate a new application key
./bin/console generate:key
```

### Step 6: Handle Permissions and Migrations
Access the application at https://0.0.0.0:8080. If you see a "Hello, world!" message, everything is working correctly. If you encounter permission issues with the `app.log` file, fix it with:
```bash
# Inside the PHP container
chown -R www-data:www-data storage/
```

Then, run the database migrations:
```bash
# Inside the PHP container
./bin/console migrate:fresh
```

### Step 7: Admin Sign-In
To access the admin backend, visit https://0.0.0.0:8080/sign-in. Use the default credentials:
- **Email:** administrator@localhost
- **Password:** admin2024!

**Important:** Change the default password immediately after logging in.
