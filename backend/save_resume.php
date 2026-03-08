<?php
session_start();
include "config.php";

// Check if user logged in
if (!isset($_SESSION['uid'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['uid'];

// Get POST data safely
$name       = $_POST['name'] ?? '';
$email      = $_POST['email'] ?? '';
$phone      = $_POST['phone'] ?? '';
$summary    = $_POST['profile'] ?? '';
$skills     = $_POST['skills'] ?? '';
$education  = $_POST['education'] ?? '';
$experience = $_POST['experience'] ?? '';
$photo      = ''; // Optional (if later adding upload)

// Prepare secure SQL query
$stmt = $conn->prepare("INSERT INTO resumes 
(user_id, name, email, phone, summary, skills, education, experience, photo) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "issssssss",
    $user_id,
    $name,
    $email,
    $phone,
    $summary,
    $skills,
    $education,
    $experience,
    $photo
);

// Execute
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Resume Saved Successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error saving resume"]);
}

$stmt->close();
$conn->close();
?>