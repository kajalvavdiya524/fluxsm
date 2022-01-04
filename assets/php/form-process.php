<?php
require '../inc/PHPMailerAutoload.php';
$mail = new PHPMailer;

$errorMSG = "";

// NAME
if (empty($_POST["name"])) {
    $errorMSG = "Name is required ";
} else {
    $name = $_POST["name"];
}

// EMAIL
if (empty($_POST["email"])) {
    $errorMSG .= "Email is required ";
} else {
    $email = $_POST["email"];
}

// MSG SUBJECT
if (empty($_POST["msg_subject"])) {
    $errorMSG .= "Subject is required ";
} else {
    $msg_subject = $_POST["msg_subject"];
}


// MESSAGE
if (empty($_POST["message"])) {
    $errorMSG .= "Message is required ";
} else {
    $message = $_POST["message"];
}


$EmailTo = "hello@yoursite.com";
$Subject = "New Message Received";

// prepare email body text
$Body = "";
$Body .= "Name: ";
$Body .= $name;
$Body .= "\n";
$Body .= "Email: ";
$Body .= $email;
$Body .= "\n";
$Body .= "Subject: ";
$Body .= $msg_subject;
$Body .= "\n";
$Body .= "Message: ";
$Body .= $message;
$Body .= "\n";

// send email
$mail->isSMTP();							// Set mailer to use SMTP
$mail->Host = 'smtp.zoho.com';					// Specify main and backup SMTP servers
$mail->SMTPAuth = true;						// Enable SMTP authentication
$mail->Username = 'justin@majorleaguetire.com';			// SMTP username
$mail->Password = '28kV6gY!';                           	// SMTP password
$mail->SMTPSecure = 'ssl';						// Enable TLS encryption, `ssl` also accepted
$mail->Port =465;							// TCP port to connect to

$mail->setFrom('justin@majorleaguetire.com', 'Contact Form');
$mail->addAddress('sjaguar13@gmail.com', 'Justin');	// Add a recipient
$mail->Subject = 'Flux Contact Form';

$mail->isHTML(false);							// Set email format to HTML
$mail->Body = $Body;

// redirect to success page
if ($mail->send() && $errorMSG == ""){
   echo "success";
}else{
    if($errorMSG == ""){
        echo "Something went wrong";
    } else {
        echo $errorMSG;
    }
}

?>