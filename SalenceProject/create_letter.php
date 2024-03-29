<?php
session_start();
require_once "connection.php";
require_once "mail_sender.php";

$response = [];
$array = [];

$reciever = isset($_POST["reciever"]) ? $_POST["reciever"] : null;
$recipients = isset($_POST["recipients"]) ? $_POST["recipients"] : null;
$paper_type = isset($_POST["paper_type"]) ? $_POST["paper_type"] : null;
$material = isset($_POST["material"]) ? $_POST["material"] : null;
$letter = isset($_POST["letter"]) ? $_POST["letter"] : null;
$envelope_cover = isset($_POST["envelope_cover"]) ? $_POST["envelope_cover"] : null;
$envelope_message = isset($_POST["envelope_message"]) ? $_POST["envelope_message"] : null;
$user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null;

$reciever_escaped = mysqli_real_escape_string($conn, $reciever);
$paper_type_escaped = mysqli_real_escape_string($conn, $paper_type);
$material_escaped = mysqli_real_escape_string($conn, $material);
$letter_escaped = mysqli_real_escape_string($conn, $letter);
$envelope_cover_escaped = mysqli_real_escape_string($conn, $envelope_cover);
$envelope_message_escaped = mysqli_real_escape_string($conn, $envelope_message);

$sql_letter =  "INSERT INTO letters (`user_id`, `recipient`, `orientation`, `material`, `letter_body`, `envelope_color`, `envelope_message`) VALUES 
('$user_id', '$reciever_escaped', '$paper_type_escaped', '$material_escaped', '$letter_escaped', '$envelope_cover_escaped', '$envelope_message_escaped')";

if ($conn->query($sql_letter) == true){
    $response["created"] = "Created successfully";
}else{
    $response["failure"] = "Not created";
}

$query_id = "SELECT `id` FROM letters WHERE letter_body = '$letter_escaped'";
$query_result = $conn->query($query_id);

if ($query_result->num_rows > 0) {
    while ($row = $query_result->fetch_assoc()) {
        $id = $row["id"];
    }
}

if ($recipients != null){
    for ($i = 0; $i < count($recipients); $i++){
        $firstname = mysqli_real_escape_string($conn, $recipients[$i][0]);
        $lastname = mysqli_real_escape_string($conn, $recipients[$i][1]);
        $email = mysqli_real_escape_string($conn, $recipients[$i][2]);
        $company_name = mysqli_real_escape_string($conn, $recipients[$i][3]);
        $profile_link = mysqli_real_escape_string($conn, $recipients[$i][4]);
        $street_address =mysqli_real_escape_string($conn, $recipients[$i][5]);
        $city =  mysqli_real_escape_string($conn, $recipients[$i][6]);
        $post_code = mysqli_real_escape_string($conn, $recipients[$i][7]);
        $region = mysqli_real_escape_string($conn, $recipients[$i][8]);
        $sql = "INSERT INTO recipients (`letter_id`, `firstname`, `lastname`, `email`, `company_name`,  `profile_link`, `street_address`,  `city`, `post_code`, `region`) VALUES 
('$id', '$firstname', '$lastname', '$email', '$company_name', '$profile_link', '$street_address', '$city', '$post_code', '$region')";
        $conn->query($sql);
    }
}

$link = "http://localhost/letter.php?id=" .$id;
if(sendMail("itzsammy23@gmail.com", "Letter delivery", "",  $link) == "Mail sent"){
    $response["success"] = "Order sent";
} else{
    $response["error"] =  "Error sending order";
}


$conn->close();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

echo json_encode($response);
