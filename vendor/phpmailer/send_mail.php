<?php

//include phpmailer
require_once('class.phpmailer.php');

//SMTP Settings
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPAuth   = true; 
$mail->SMTPSecure = "tls"; 
$mail->Host       = "email-smtp.us-east-1.amazonaws.com";
$mail->Username   = "SMTP-Username";
$mail->Password   = "SMTP-Password";
//

$mail->SetFrom('verified@address.com', 'Sender Name'); //from (verified email address)
$mail->Subject = "Email Subject"; //subject

//message
$body = "This is a test message.";
$body = eregi_replace("[\]",'',$body);
$mail->MsgHTML($body);
//

//recipient
$mail->AddAddress("contact@recipient.com", "Test Recipient"); 

//Success
if ($mail->Send()) { 
	echo "Message sent!"; die; 
}

//Error
if(!$mail->Send()) { 
	echo "Mailer Error: " . $mail->ErrorInfo; 
} 

?>
