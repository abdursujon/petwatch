<?php
$lifetime = 3600;

/**
 * Secure session configuration.
 * Sets cookie lifetime, scope, HTTPS-only, HttpOnly, and SameSite policy.
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

require_once('Models/UserAuthentication.php');

$userAuthentication = new UserAuthentication();

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
  $_SESSION = [];
  session_destroy();
  $view->successMessage = 'You have been logged out successfully.';
}

/**
 * Login rate limiting.
 * Allows 3 failed attempts, then enforces a 2-minute cooldown.
 */
if (!isset($_SESSION['login_attempts'])) {
  $_SESSION['login_attempts'] = 0;
  $_SESSION['last_attempt_time'] = time();
}

if ($_SESSION['login_attempts'] >= 3) {
  $elapsed = time() - $_SESSION['last_attempt_time'];

  if ($elapsed < 120) {
    $remaining = 120 - $elapsed;
    $view->loginError = "Too many login attempts. Please wait {$remaining} seconds before trying again.";
    require_once('Views/login.phtml');
    exit();
  } else {
    $_SESSION['login_attempts'] = 0;
  }
}

if (isset($_POST['login'])) {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  $username = htmlspecialchars($username, ENT_QUOTES);
  $password = htmlspecialchars($password, ENT_QUOTES);

  $user = $userAuthentication->verifyLogin($username, $password);

  if ($user) {
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $user->getUsername();
    $_SESSION['user_id'] = $user->getUserId();
    $_SESSION['role'] = $user->getRole();
    $_SESSION['login_attempts'] = 0;
    $view->successMessage = 'Login successful!';
  } else {
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    $view->loginError = 'Invalid username or password.';
  }
}

require_once('Views/login.phtml');
