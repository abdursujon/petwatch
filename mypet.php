<?php
session_start();
require_once('models/Database.php');
require_once('models/PetDataSets.php');

if (empty($_SESSION['user_id'])) {
  $view = new stdClass();
  $view->errorMessage = "You must log in to add and manage pet";
  require_once('views/login.phtml');
  exit();
}

$user_id = (int)$_SESSION['user_id'];
$petsDataSet = new PetDataSets();

$view = new stdClass();
$view->title = "My Pets";
$view->successMessage = '';
$view->errorMessage = '';

/**
 * Validates and sanitizes pet form input.
 * Returns sanitized values and validation errors.
 */
function validateMypetData($input): array
{
  $name = trim($input['name'] ?? '');
  $breed = trim($input['breed'] ?? '');
  $color = trim($input['color'] ?? '');
  $description = trim($input['description'] ?? '');

  $errors = [];

  if (mb_strlen($name) > 20)
    $errors[] = "Pet name must be up to 20 characters.";
  elseif (mb_strlen($breed) > 50)
    $errors[] = "Breed name must be up to 50 characters.";
  elseif (mb_strlen($color) > 20)
    $errors[] = "Color must be up to 20 characters.";
  elseif (mb_strlen($description) > 200)
    $errors[] = "Description must be up to 200 characters.";

  $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
  $breed = htmlspecialchars($breed, ENT_QUOTES, 'UTF-8');
  $color = htmlspecialchars($color, ENT_QUOTES, 'UTF-8');
  $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');

  return [$name, $breed, $color, $description, $errors];
}

/**
 * Handles pet image upload and returns stored path.
 */
function handlePetPhotoUpload(string $fieldName): ?string
{
  if (!isset($_FILES[$fieldName]) ||
    $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
    return null;
  }

  $uploadDir = __DIR__ . '/images/pets';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

  $fileTmp = $_FILES[$fieldName]['tmp_name'];
  $fileExt = strtolower(
    pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION)
  );
  $allowed = ['jpg', 'jpeg', 'png', 'gif'];

  if (!in_array($fileExt, $allowed)) {
    throw new Exception("Invalid file type. Only .gif,.jpg,.jpeg,.png allowed");
  }

  $newFileName = uniqid('pet_', true) . '.' . $fileExt;
  $targetPath = "$uploadDir/$newFileName";

  if (!move_uploaded_file($fileTmp, $targetPath)) {
    throw new Exception("Error moving uploaded file.");
  }

  return "images/pets/$newFileName";
}

// Generate a form token to prevent duplicate submissions on reload
if (empty($_SESSION['form_token'])) {
  $_SESSION['form_token'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  //reject duplicate submission (e.g. page reload re-posting same form)
  $submittedToken = $_POST['form_token'] ?? '';

  if ($submittedToken !== ($_SESSION['form_token'] ?? '')) {
    $view->errorMessage = "Duplicate submission detected for the same pet. Please refresh and try  adding a new pet information again.";
  } else {
    // regenerate token immediately so the same POST can't be replayed
    $_SESSION['form_token'] = bin2hex(random_bytes(16));
    $action = '';

    if (isset($_POST['addPet'])) $action = 'create';
    elseif (isset($_POST['update'])) $action = 'update';
    elseif (isset($_POST['deletePet'])) $action = 'delete';

    try {
      switch ($action) {
        case 'create':
          [$name, $breed, $color, $description, $errors] = validateMypetData($_POST);
          $species = htmlspecialchars(trim($_POST['species'] ?? ''));

          if ($errors) {
            $view->errorMessage = implode("<br>", $errors);
            break;
          }

          $photo_url = handlePetPhotoUpload('petphoto');
          if (!$photo_url)
            throw new Exception("No photo uploaded or upload error.");

          $petsDataSet->addPet(
            $user_id, $name, $species, $breed,
            $color, $description, $photo_url
          );
          $view->successMessage = "Pet added successfully!";
          break;

        case 'update':
          $petId = (int)($_POST['update'] ?? 0);
          if ($petId <= 0 || !isset($_POST['pets'][$petId])) {
            throw new Exception("Invalid pet data.");
          }

          $petInput = $_POST['pets'][$petId];
          [$name, $breed, $color, $description, $errors] = validateMypetData($petInput);
          $species = htmlspecialchars(trim($petInput['species'] ?? ''));

          if ($errors) {
            $view->errorMessage = implode("<br>", $errors);
            break;
          }

          $status = trim($petInput['status'] ?? 'lost');
          if (!in_array($status, ['lost', 'found'])) {
            $status = 'lost';
          }

          $data = [
            'name' => $name,
            'breed' => $breed,
            'color' => $color,
            'description' => $description,
            'species' => $species,
            'status' => $status
          ];

          $photoField = 'photo_' . $petId;
          $photo_url = handlePetPhotoUpload($photoField);
          if ($photo_url) $data['photo_url'] = $photo_url;

          $updated = $petsDataSet->updatePet($petId, $user_id, $data);
          $view->successMessage = $updated
            ? "Pet updated successfully."
            : "Pet update failed.";
          break;

        case 'delete':
          $petId = (int)($_POST['deletePet'] ?? 0);
          if ($petId <= 0) throw new Exception("Invalid pet ID.");

          $deleted = $petsDataSet->deletePet($petId, $user_id);
          $view->successMessage = $deleted
            ? "Pet deleted successfully."
            : "Could not delete pet.";
          break;
      }
    } catch (Exception $e) {
      $view->errorMessage = $e->getMessage();
    }
  }
}

$view->pets = $petsDataSet->getPetByUserID($user_id);
require_once('views/mypet.phtml');
