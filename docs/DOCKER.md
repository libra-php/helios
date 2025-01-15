## Docker Environment

Getting started with Helios is easy, especially with Docker. Just follow these steps to have your application running in no time!

### Step 1: Clone the Project
Clone the latest version of Helios from GitHub.
```bash
git clone https://github.com/libra-php/helios.git
```

### Step 2: Configure Environment
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

### Step 3: Set Up Docker Containers
Run Docker Compose to start the application stack, which includes MySQL, nginx, and PHP.
```bash
sudo docker compose up --build --detach
```

### Step 4: Install Composer Dependencies
```bash
docker exec -it helios-php composer install
```

### Step 5: Generate Application Key
To ensure the security of your application, generate a unique application key.
```bash
# Generate a new application key
docker exec -it helios-php bin/console generate:key
```

### Step 6: Database Migrations
Run the database migration command from the PHP container
```bash
docker exec -it helios-php bin/console migrate:fresh
```

### Step 7: Admin Sign-In
To access the admin backend, visit https://0.0.0.0:8080/sign-in. Use the default credentials:
- **Email:** administrator
- **Password:** Admin2025!

**Important:** Change the default password immediately after logging in.


