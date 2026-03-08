<?php
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