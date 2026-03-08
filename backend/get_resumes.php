<?php
session_start();
header("Content-Type: application/json");
include "config.php";

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit();
}

$uid = $_SESSION['user_id'];

// Fetch all resumes for the logged-in user
$stmt = $conn->prepare("SELECT id, name, email, phone, summary, skills, education, experience, photo, created_at FROM resumes WHERE user_id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

$resumes = [];
while($row = $result->fetch_assoc()){
    $resumes[] = $row;
}

echo json_encode(["status"=>"success","data"=>$resumes]);
exit();
