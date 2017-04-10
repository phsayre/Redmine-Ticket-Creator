<?php
/*** Author: Philip Sayre
**** 4/10/2017
**** github.com/phsayre
*/
function sendAttachmentsEmail($email_info, $file_info) {
	//require_once ('/php/PHPMailer/PHPMailerAutoload.php');
	require_once('vendor/autoload.php');

	//$txt = 'This is some text for the email body.';
	//var_dump($email_info, $file_info);
	
	$mail = new PHPMailer();
	$mail->Host = 'somehost';	//this has been edited for privacy
	$mail->SMTPAuth = true;
	$mail->Username = 'someemail';	//this has been edited for privacy
	$mail->Password = 'somepassword';	//this has been edited for privacy
	$mail->SMTPSecure = 'tls';
	$mail->Port = 25;
	$mail->IsSMTP();
	$mail->SMTPOptions = ['ssl'=> ['allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false]];

	$mail->isHTML(false);
	$mail->Body = $email_info["body"];
	//$mail->AltBody = "Hello, this is an alternate text.";
	
	foreach ( $file_info as $file ) {	//adding attachments
		$path = $file["tmp_name"];
		$name = $file["name"];
		//$type = $file["type"];
		
		$mail->AddAttachment($path, $name);
	}
	
	$mail->Subject = $email_info['subject'] . " (With Attachments)" .  " [Support #" . $email_info['issue_number'] . "]";

	$toName = null;
	if(isset($email_info['to_name']) && !empty($email_info['to_name'])){
		$toName = $email_info['to_name'];
	}

	$mail->SetFrom($email_info['from_email'], $email_info['from_name']);
	$mail->AddAddress($email_info['to_email'], $toName);
	// $mail->AddBCC('someemail');	//this has been edited for privacy
	
	if (! $mail->send ()) {
		echo 'Email could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;
	} 
	else {
		echo '<center><h3>Email with attachments has been sent to the customer</h3></center>';
	}
}
?>
