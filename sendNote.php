<?php
/*** Author: Philip Sayre
**** 4/10/2017
**** github.com/phsayre
*/
session_start(['read_and_close'=>1]);


$issue_number = $_SESSION["issue_number"];	//grabs the issue number from the session global variable set in createTicket.php

$content = check_input($_POST['content'], "<center><h2>error: Enter some content</h2></center>");	//required field on html form


function check_input($data, $problem='')	//checks required fields
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	if ($problem && strlen($data) == 0)
	{
		echo '	<br /><br /><br /><br /><br /><center><button onclick="goBack()">Go Back</button></center>

				<script>
				function goBack() {
    				window.history.back();
				}
				</script><br /><br />';	//Back button to fix errors
		die($problem);
	}
}

$content = htmlspecialchars($_POST['content']);	//sets the content of the email from the html form data to a variable

/*set data in XML format*/
$data = '<message>
			<issue_id>';
$data .=	$issue_number . '</issue_id>
			<content>';
$data .= $content . '</content>
		</message>';


$data = sendEmail($data); //run the POST request


function sendEmail($data){
	
	$ch = curl_init('https://redmineinstall/helpdesk/email_note.xml');	//this has been edited for privacy
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array (
			'Content-Type: application/xml',
			'Content-Length: ' . strlen ($data),
			'X-Redmine-API-Key: someAPIkey'	//this has been edited for privacy
	) );
	
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	
	$response = curl_exec($ch);
	if ($response === false) {
		echo ("error");
		$last_error = 'ERROR_REQUEST_FAILED';
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
	}
	
	curl_close($ch);
	$issue_number = $_SESSION["issue_number"];	//grabs the issue number from the session global variable set in createTicket.php
	var_dump($data);
	var_dump($response);
	
	echo '<br /><br /><h1><center>Success!</center></h1><br />';
	echo '<center><form action="emailform.html">
					<input type="submit" value="Send Another Email to Ticket #' . $issue_number . '" />
			</form></center>
			<center><form action="ticketform.html">
			<input type="submit" value="Create Another Ticket" />
		</form></center>';
	
}
?>