<?php
session_start();
header("Content-Type: application/json");
require_once("config.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status"=>"error","message"=>"Invalid request"]);
    exit();
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$google_id = isset($_POST['google_id']) ? trim($_POST['google_id']) : '';
$profile_pic = isset($_POST['profile_pic']) ? trim($_POST['profile_pic']) : NULL;

if (empty($name) || empty($email)) {
    echo json_encode(["status"=>"error","message"=>"Name and Email are required"]);
    exit();
}

// Check if email already exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["status"=>"error","message"=>"Email already exists"]);
    exit();
}

// ===============================
// 1️⃣ Normal Email Registration
// ===============================
if (!empty($password)) {

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name,email,password,profile_pic) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $profile_pic);

}

// ===============================
// 2️⃣ Google Registration
// ===============================
elseif (!empty($google_id)) {

    $stmt = $conn->prepare("INSERT INTO users (name,email,google_id,profile_pic) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $name, $email, $google_id, $profile_pic);

}

else {
    echo json_encode(["status"=>"error","message"=>"Invalid registration method"]);
    exit();
}

if ($stmt->execute()) {

    $_SESSION['user_email'] = $email;
    $_SESSION['user_id'] = $stmt->insert_id;

    echo json_encode(["status"=>"success","message"=>"Registered Successfully"]);

} else {
    echo json_encode(["status"=>"error","message"=>"Registration failed"]);
}

$stmt->close();
$conn->close();
?>
