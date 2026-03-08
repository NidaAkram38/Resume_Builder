<?php
session_start();
require_once "config.php";
require_once "vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

// START OUTPUT BUFFER
ob_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

$uid = $_SESSION['user_id'];
$resume_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $resume_id, $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Resume not found");
}

$resume = $result->fetch_assoc();

/* ===== FULL HTML TEMPLATE ===== */
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; }
h1 { text-align:center; }
.section { margin-top:15px; }
</style>
</head>
<body>

<h1>'.$resume['name'].'</h1>
<p style="text-align:center;">'.$resume['summary'].'</p>

<hr>

<div class="section">
<h3>Contact</h3>
<p>Email: '.$resume['email'].'<br>
Phone: '.$resume['phone'].'</p>
</div>

<div class="section">
<h3>Skills</h3>
<p>'.$resume['skills'].'</p>
</div>

<div class="section">
<h3>Experience</h3>
<p>'.$resume['experience'].'</p>
</div>

<div class="section">
<h3>Education</h3>
<p>'.$resume['education'].'</p>
</div>

</body>
</html>
';

/* ===== DOMPDF SETTINGS ===== */
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// CLEAR BUFFER (IMPORTANT)
ob_end_clean();

$dompdf->stream("resume.pdf", ["Attachment" => true]);
exit;