<?php
/**
 * Validates that the AJAX request contains a token matching the session token.
 * <p>
 *   Start a session if one is not already exist. Then compare the token from the request
 *   (GET, POST) against the one stored in the session of the current user.
 *   If the token is not matched, the request is rejected with JSON error response.
 *   This helps us prevent unauthorised requests, where we prevent anyone calling php endpoints for data response.
 *   It ensures the request is from actual website that we have implemented.
 * </p>
 */
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function validateAjaxToken()
{
  $token = $_SESSION['ajaxToken'] ?? '';
  $requestToken = $_GET['token'] ?? $_POST['token'] ?? '';

  if (!$token || !$requestToken || $token !== $requestToken) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid or missing token.']);
    exit();
  }
}