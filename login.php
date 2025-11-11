<?php
// Session setup for an hour
$lifetime = 3600;

/**
 * Cookie last an hour
 * Cookie is available to the whole website
 * Sends the cookie only over HTTPS if the connection is secure
 * httponly prevents access to JavaScript to prevent XSS attack
 * samesite: lax prevents CSRF (Cross Site Request Forgery)
 */
session_set_cookie_params([
 'lifetime' => $lifetime,
 'path' => '/',
 'secure' => isset($_SERVER['HTTPS']),
 'httponly' => true,
 'samesite' => 'Lax'
]);
session_start();

$view = new stdClass();
$view->title = "Login";
$view->loginError = '';
$view->successMessage = '';

// Include the UserAuthentication class to check users login data
// against the database
require_once('Models/UserAuthentication.php');

$userAuthentication = new UserAuthentication();

// When logout button is clicked, destroy the cookie session
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
 $_SESSION = [];
 session_destroy();
 $view->successMessage = 'You have been logged out successfully.';
}

/**
 * Implement anti-spam feature
 * Allow up to 3 failed login attempts, if three attempt failed,
 * block user for 2 min even when they type right password, it won't
 * allow to login to prevent spam
 */
if (!isset($_SESSION['login_attempts'])) {
 $_SESSION['login_attempts'] = 0;
 $_SESSION['last_attempt_time'] = time();
}

// If user exceeded 3 failed attempts
if ($_SESSION['login_attempts'] >= 3) {
 $elapsed = time() - $_SESSION['last_attempt_time'];

 // If less than 2 minutes (120 seconds) have passed
 if ($elapsed < 120) {
  // Show how many second they have to wait before trying again
  $remaining = 120 - $elapsed;
  $view->loginError = "Too many login attempts. Please wait 
  {$remaining} seconds before trying again.";
  require_once('Views/login.phtml');
  exit();
 } else {
  // Reset counter after cooldown period, now if user type right
  // password, they will be availed to log in
  $_SESSION['login_attempts'] = 0;
 }
}

// If the cookie is not set and user is not logged-in
if (isset($_POST['login'])) {
 // Get the username and password user posted
 $username = trim($_POST['username'] ?? '');
 $password = trim($_POST['password'] ?? '');

 // sanitize and validate inputs
 $username = htmlspecialchars($username, ENT_QUOTES);
 $password = htmlspecialchars($password, ENT_QUOTES);

 // Verify against the database information
 $user = $userAuthentication->verifyLogin($username, $password);

 // If login credential match, get the user data
 if ($user) {
  $_SESSION['loggedin'] = true;
  $_SESSION['username'] = $user->getUsername();
  $_SESSION['user_id'] = $user->getUserId();
  $_SESSION['role'] = $user->getRole();

  // Reset failed attempts after successful login
  $_SESSION['login_attempts'] = 0;

  $view->successMessage = 'Login successful!';
 } else {
  // Increment failed attempt count and record the time
  $_SESSION['login_attempts']++;
  $_SESSION['last_attempt_time'] = time();
  $view->loginError = 'Invalid username or password.';
 }
}

// Show them the login.phtml where login form and logout form view
// is hold
require_once('Views/login.phtml');
