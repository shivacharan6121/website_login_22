<?php
// Database configuration
$host = 'localhost';
$dbname = 'connectors.db';
$username = 'root';
$password = '';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Initialize variables
$loginError = '';
$usernameError = '';
$passwordError = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $inputUsername = trim($_POST['username']);
    $inputPassword = trim($_POST['password']);
    
    // Simple validation
    if (empty($inputUsername)) {
        $usernameError = 'Username is required';
    }
    
    if (empty($inputPassword)) {
        $passwordError = 'Password is required';
    }
    
    // Proceed if no basic validation errors
    if (empty($usernameError) && empty($passwordError)) {
        try {
            // Create database connection
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if username exists
            $stmt = $conn->prepare("SELECT username, password FROM users WHERE username = :username");
            $stmt->bindParam(':username', $inputUsername);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password (assuming passwords are hashed)
                if (password_verify($inputPassword, $user['password'])) {
                    // Password is correct, set session and redirect
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['loggedin'] = true;
                    
                    // Redirect to homepage
                    header("Location: homepage.php");
                    exit();
                } else {
                    // Password is incorrect
                    $passwordError = 'Incorrect password';
                }
            } else {
                // Username doesn't exist
                $usernameError = 'Username does not exist';
            }
        } catch(PDOException $e) {
            $loginError = "Database error: " . $e->getMessage();
        }
        
        // Close connection
        $conn = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #151b27;
            color: #ffffff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background-color: #1a2332;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            width: 500px;
            max-width: 90%;
        }

        .login-container h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: white;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background-color: #151b27;
            border: none;
            outline: none;
            border-radius: 5px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .error {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 0.3rem;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: #9fef00;
            color: #151b27;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
        }

        .login-btn:hover {
            background-color: #8cdb00;
            transform: translateY(-2px);
        }

        .admin-login {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }

        .admin-login a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }

        .admin-login a:hover {
            text-decoration: none;
            color: #8cdb00;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #2a3649;
        }

        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #2a3649;
        }

        .divider span {
            padding: 0 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        
        <?php if (!empty($loginError)): ?>
            <div class="error" style="text-align: center; margin-bottom: 1rem;"><?php echo $loginError; ?></div>
        <?php endif; ?>
        
        <form id="loginForm" method="POST" action="">
            <div class="form-group">
                <input type="text" id="username" name="username" placeholder="Username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                <?php if (!empty($usernameError)): ?>
                    <div class="error"><?php echo $usernameError; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <?php if (!empty($passwordError)): ?>
                    <div class="error"><?php echo $passwordError; ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="login-btn">Login</button>
            
            <div class="divider">
                <span>OR</span>
            </div>
            
            <div class="admin-login">
                <a href="#">Login as Admin</a>
            </div>
        </form>
    </div>
</body>
</html>