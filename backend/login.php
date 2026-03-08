<?php
session_start();
header("Content-Type: application/json");
require_once("config.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$google_id = isset($_POST['google_id']) ? trim($_POST['google_id']) : '';

if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Email is required"]);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {

    $user = $result->fetch_assoc();

    // =========================
    // Normal Email Login
    // =========================
    if (!empty($password)) {

        if (!empty($user['password']) && password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            echo json_encode(["status" => "success", "message" => "Login successful"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Wrong password"]);
        }
    }

    // =========================
    // Google Login
    // =========================
    elseif (!empty($google_id)) {

        if (!empty($user['google_id']) && $user['google_id'] === $google_id) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            echo json_encode(["status" => "success", "message" => "Google login successful"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Google authentication failed"]);
        }
    }

    else {
        echo json_encode(["status" => "error", "message" => "Invalid login method"]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}

$stmt->close();
$conn->close();
?>