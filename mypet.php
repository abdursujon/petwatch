<?php
// Start a session
session_start();
// Inheritance the required database model classes
require_once('Models/Database.php');
require_once('Models/PetDataSets.php');

// Check if a user is not logged in
if (empty($_SESSION['user_id'])) {
 $view = new stdClass();
 // Show error on the login form when not logged in
 $view->errorMessage = "You must log in to add and manage pet";
 // Load the login view
 require_once('Views/login.phtml');
 exit();
}

// Get logged-in user ID (always cast to int for safety)
$user_id = (int)$_SESSION['user_id'];

// Create model instance
$petsDataSet = new PetDataSets();

// View setup
$view = new stdClass();
$view->title = "My Pets";
$view->successMessage = '';
$view->errorMessage = '';

/**
 * Validate pet data from form input
 * Makes sure name, breed, color, and description meet length/pattern rules
 */
function validateMypetData($input): array {
 // Trim and sanitize input
 $name = trim($input['name'] ?? '');
 $breed = trim($input['breed'] ?? '');
 $color = trim($input['color'] ?? '');
 $description = trim($input['description'] ?? '');

 $errors = [];

 // Validate lengths only, keeping input flexible and secure
 if (mb_strlen($name) > 20)
  $errors[] = "Pet name must be up to 20 characters.";
 elseif (mb_strlen($breed) > 50)
  $errors[] = "Breed name must be up to 50 characters.";
 elseif (mb_strlen($color) > 20)
  $errors[] = "Color must be up to 20 characters.";
 elseif (mb_strlen($description) > 200)
  $errors[] = "Description must be up to 200 characters.";

 // Escape for output safety (prevents XSS), converts special characters
 // into safe html entities, ENT_QUOTES encodes both single and double quotes
 $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
 $breed = htmlspecialchars($breed, ENT_QUOTES, 'UTF-8');
 $color = htmlspecialchars($color, ENT_QUOTES, 'UTF-8');
 $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');

 return [$name, $breed, $color, $description, $errors];
}

/**
 * Handles pet image upload
 * Verifies allowed extensions and moves file to /images/pets
 * @throws Exception
 */
function handlePetPhotoUpload(string $fieldName): ?string {
 // No file uploaded
 if (!isset($_FILES[$fieldName]) ||
     $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
  return null;
 }

 // For this project, we save uploaded images in a local directory.
 // In a real-world app, images would typically be stored in cloud storage.
 $uploadDir = __DIR__ . '/images/pets';
 if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

 // Get temp path, extract extension, allow only safe formats
 $fileTmp = $_FILES[$fieldName]['tmp_name'];
 $fileExt = strtolower(
  pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION)
 );
 $allowed = ['jpg', 'jpeg', 'png', 'gif'];

 // Only allow specified formats
 if (!in_array($fileExt, $allowed)) {
  throw new Exception("Invalid file type. Only .gif,.jpg,.jpeg,.png allowed");
 }

 // Generate unique file name to avoid collisions
 $newFileName = uniqid('pet_', true) . '.' . $fileExt;
 $targetPath = "$uploadDir/$newFileName";

 // Move file to target directory
 if (!move_uploaded_file($fileTmp, $targetPath)) {
  throw new Exception("Error moving uploaded file.");
 }

 // Return relative path to store in DB
 return "images/pets/$newFileName";
}

// Handle all form submissions such as addPet, update, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $action = '';

 // Detect which button was clicked
 if (isset($_POST['addPet'])) $action = 'create';
 elseif (isset($_POST['update'])) $action = 'update';
 elseif (isset($_POST['deletePet'])) $action = 'delete';

 try {
  switch ($action) {
   // Create new pet
   case 'create':
    // Validate before creating in db
    [$name, $breed, $color, $description, $errors] =
     validateMypetData($_POST);
    $species = htmlspecialchars(trim($_POST['species'] ?? ''));

    if (!empty($errors)) {
     $view->errorMessage = implode("<br>", $errors);
     break;
    }

    // Upload pet photo
    $photo_url = handlePetPhotoUpload('petphoto');
    if (!$photo_url)
     throw new Exception("No photo uploaded or upload error.");

    // Add pet to db
    $petsDataSet->addPet(
     $user_id, $name, $species, $breed, $color, $description, $photo_url
    );
    $view->successMessage = "Pet added successfully!";
    break;

   // Update existing pet
   case 'update':
    $petId = (int)($_POST['update'] ?? 0);
    if ($petId <= 0 || !isset($_POST['pets'][$petId])) {
     throw new Exception("Invalid pet data.");
    }

    $petInput = $_POST['pets'][$petId];
    [$name, $breed, $color, $description, $errors] =
     validateMypetData($petInput);
    $species = htmlspecialchars(trim($petInput['species'] ?? ''));

    if (!empty($errors)) {
     $view->errorMessage = implode("<br>", $errors);
     break;
    }

    // Build data array for update
    $data = [
     'name' => $name,
     'breed' => $breed,
     'color' => $color,
     'description' => $description,
     'species' => $species
    ];

    // If new photo uploaded, replace old one
    $photoField = 'photo_' . $petId;
    $photo_url = handlePetPhotoUpload($photoField);
    if ($photo_url) $data['photo_url'] = $photo_url;

    // Update database
    $updated = $petsDataSet->updatePet($petId, $user_id, $data);
    $view->successMessage = $updated
     ? "Pet updated successfully."
     : "Pet update failed.";
    break;

   // Delete pet data
   case 'delete':
    $petId = (int)($_POST['deletePet'] ?? 0);
    if ($petId <= 0) throw new Exception("Invalid pet ID.");

    $deleted = $petsDataSet->deletePet($petId, $user_id);
    $view->successMessage = $deleted
     ? "Pet deleted successfully."
     : "Could not delete pet.";
    break;

   default:
    // No form action triggered
    break;
  }
 } catch (Exception $e) {
  $view->errorMessage = $e->getMessage();
 }
}

// Always load user's pets after actions
$view->pets = $petsDataSet->getPetByUserID($user_id);

// Load the pet view
require_once('Views/mypet.phtml');
