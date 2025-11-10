<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize auth system
$user = new User($db);
$auth = new Auth($user);

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            if ($auth->login($username, $password, $rememberMe)) {
                $redirectTo = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirectTo);
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
        $username = trim($_POST['reg_username'] ?? '');
        $email = trim($_POST['reg_email'] ?? '');
        $password = $_POST['reg_password'] ?? '';
        $confirmPassword = $_POST['reg_confirm_password'] ?? '';
        $firstName = trim($_POST['reg_first_name'] ?? '');
        $lastName = trim($_POST['reg_last_name'] ?? '');
        
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Please fill in all required fields.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            $userId = $user->register($username, $email, $password, $firstName, $lastName);
            if ($userId) {
                $success = 'Account created successfully! You can now log in.';
            } else {
                $error = 'Username or email already exists.';
            }
        }
    }
}

if (isset($_GET['message']) && $_GET['message'] === 'login_required') {
    $error = 'Please log in to access the catalog.';
} elseif (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
    $success = 'You have been successfully logged out.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ElectroStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-orange-50 to-gray-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <h1 class="text-3xl font-bold text-orange-600 mb-2">ElectroStore</h1>
                <h2 class="text-2xl font-semibold text-gray-900">Welcome Back</h2>
                <p class="text-gray-600">Access your oscilloscope catalog</p>
            </div>

            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Login/Register Tabs -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="flex border-b">
                    <button id="loginTab" class="flex-1 py-3 px-4 text-center font-medium text-orange-600 border-b-2 border-orange-600 bg-orange-50">
                        Login
                    </button>
                    <button id="registerTab" class="flex-1 py-3 px-4 text-center font-medium text-gray-600 hover:text-orange-600">
                        Register
                    </button>
                </div>

                <!-- Login Form -->
                <div id="loginForm" class="p-6">
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="login">
                        
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                                Username or Email
                            </label>
                            <div class="relative">
                                <input type="text" id="username" name="username" required
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                       placeholder="Enter your username or email">
                                <i class="fas fa-user absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                       placeholder="Enter your password">
                                <i class="fas fa-lock absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" name="remember_me" class="rounded text-orange-600 focus:ring-orange-500">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                            <a href="#" class="text-sm text-orange-600 hover:text-orange-700">Forgot password?</a>
                        </div>

                        <button type="submit" class="w-full bg-orange-600 text-white py-2 px-4 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Sign In
                        </button>
                    </form>

                    <!-- Demo Credentials -->
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Demo Credentials:</h4>
                        <div class="text-xs text-gray-600 space-y-1">
                            <div><strong>Admin:</strong> admin / admin123</div>
                            <div><strong>User:</strong> testuser / user123</div>
                        </div>
                    </div>
                </div>

                <!-- Register Form -->
                <div id="registerForm" class="p-6 hidden">
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="register">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="reg_first_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    First Name
                                </label>
                                <input type="text" id="reg_first_name" name="reg_first_name"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                       placeholder="First name">
                            </div>
                            <div>
                                <label for="reg_last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Last Name
                                </label>
                                <input type="text" id="reg_last_name" name="reg_last_name"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                       placeholder="Last name">
                            </div>
                        </div>

                        <div>
                            <label for="reg_username" class="block text-sm font-medium text-gray-700 mb-1">
                                Username *
                            </label>
                            <div class="relative">
                                <input type="text" id="reg_username" name="reg_username" required
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                       placeholder="Choose a username">
                                <i class="fas fa-user absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div>
                            <label for="reg_email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email Address *
                            </label>
                            <div class="relative">
                                <input type="email" id="reg_email" name="reg_email" required
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                       placeholder="Enter your email">
                                <i class="fas fa-envelope absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div>
                            <label for="reg_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password *
                            </label>
                            <div class="relative">
                                <input type="password" id="reg_password" name="reg_password" required
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                       placeholder="Create a password">
                                <i class="fas fa-lock absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div>
                            <label for="reg_confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Confirm Password *
                            </label>
                            <div class="relative">
                                <input type="password" id="reg_confirm_password" name="reg_confirm_password" required
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                       placeholder="Confirm your password">
                                <i class="fas fa-lock absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-orange-600 text-white py-2 px-4 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                            <i class="fas fa-user-plus mr-2"></i>
                            Create Account
                        </button>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-600">
                <p>© 2024 ElectroStore. Professional test equipment catalog.</p>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        const loginTab = document.getElementById('loginTab');
        const registerTab = document.getElementById('registerTab');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');

        loginTab.addEventListener('click', () => {
            loginTab.classList.add('text-orange-600', 'border-b-2', 'border-orange-600', 'bg-orange-50');
            loginTab.classList.remove('text-gray-600');
            registerTab.classList.remove('text-orange-600', 'border-b-2', 'border-orange-600', 'bg-orange-50');
            registerTab.classList.add('text-gray-600');
            
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
        });

        registerTab.addEventListener('click', () => {
            registerTab.classList.add('text-orange-600', 'border-b-2', 'border-orange-600', 'bg-orange-50');
            registerTab.classList.remove('text-gray-600');
            loginTab.classList.remove('text-orange-600', 'border-b-2', 'border-orange-600', 'bg-orange-50');
            loginTab.classList.add('text-gray-600');
            
            registerForm.classList.remove('hidden');
            loginForm.classList.add('hidden');
        });
    </script>
</body>
</html>
