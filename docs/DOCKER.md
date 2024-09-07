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

### Step 6: Handle Permissions
Access the application at https://0.0.0.0:8080. If you see a "Hello, world!" message, everything is working correctly. 

- If you encounter permission issues with the `app.log` file, access the PHP container and fix it with:
```bash
chown -R www-data:www-data storage/
```

### Step 7: Database Migrations
Run the database migration command from the PHP container
```bash
./bin/console migrate:fresh
```

### Step 8: Admin Sign-In
To access the admin backend, visit https://0.0.0.0:8080/sign-in. Use the default credentials:
- **Email:** administrator@localhost
- **Password:** admin2024!

**Important:** Change the default password immediately after logging in.
