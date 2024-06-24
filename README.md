# User Authentication and Password Management System

A secure user authentication system with rate limiting, CAPTCHA verification, and password encryption using AES for safe storage. The project includes registration, login, and a user dashboard for managing saved passwords.

## Features

- **User Registration:**
    - Rate-limited to prevent abuse.
    - CAPTCHA to prevent automated registrations.
    - Passwords are hashed using `password_hash` for security.

- **User Login:**
    - Rate-limited to prevent brute-force attacks.
    - CAPTCHA to prevent automated logins.
    - Logs successful and failed login attempts.

- **Dashboard:**
    - Allows users to store and retrieve passwords securely.
    - Passwords are encrypted using AES encryption with CBC mode.

- **Security:**
    - Sessions are used to manage user states.
    - Sensitive operations are logged with user IP and browser details.

## Installation

1. **Clone the repository:**
    ```sh
    git clone https://github.com/samarpreetxd/PasswordManager.git
    cd PasswordManager
    ```

2. **Install dependencies:**
    ```sh
    composer install
    ```

3. **Configure your database and create a `config.php` file with your database credentials and encryption key:**
    ```php
    <?php
    $servername = "localhost";
    $username = "INSERT USER HERE";
    $password = "INSERT PASSWORD HERE";
    $dbname = "user_auth";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Define your encryption key (32 bytes long)
    define('ENCRYPTION_KEY', 'your-32-byte-long-encryption-key');
    ?>
    ```

4. **Create the database and required tables:**
    ```sql
    CREATE DATABASE user_auth;

    USE user_auth;

    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    );

    CREATE TABLE passwords (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        site_name VARCHAR(255) NOT NULL,
        password TEXT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
    ```

5. **Start the PHP server:**
    ```sh
    php -S localhost:8000
    ```
    or you can directly start the server using the bash file:
    ```
    chmod +x ./manage_php_server.sh
    ./manage_php_server.sh
    ```

6. **Open your browser and navigate to `http://localhost:8000/register.php` to register a new user.**

## Usage

### Registration

- Navigate to the registration page and fill out the form.
- You will be asked to enter a CAPTCHA to complete the registration process.
- Upon successful registration, you can proceed to the login page.

### Login

- Navigate to the login page and enter your credentials.
- You will be asked to enter a CAPTCHA to complete the login process.
- Successful login will redirect you to the dashboard.

### Dashboard

- In the dashboard, you can store new passwords for different sites.
- All stored passwords are encrypted using AES encryption.
- You can view your saved passwords, which will be decrypted and displayed.

## Security Considerations

- **Rate Limiting:** To prevent abuse, registration and login attempts are rate-limited. Users will be locked out after a specified number of failed attempts.
- **CAPTCHA:** CAPTCHA is used to prevent automated bots from registering or logging in.
- **Encryption:** Passwords are stored encrypted in the database using AES encryption to ensure they are secure.

## Logging

- **Registration Attempts:** All registration attempts, both successful and failed, are logged with the user’s IP address and browser details.
- **Login Attempts:** All login attempts, both successful and failed, are logged with the user’s IP address and browser details.

## License

This project is licensed under the [MIT License](https://raw.githubusercontent.com/samarpreetxd/PasswordManager/main/LICENSE) - see the [LICENSE](https://raw.githubusercontent.com/samarpreetxd/PasswordManager/main/LICENSE) file for details.
