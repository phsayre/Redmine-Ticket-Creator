<?php
/*** Author: Philip Sayre
**** 4/10/2017
**** github.com/phsayre
*/
session_start();
header('Access-Control-Allow-Origin: *');
require_once('utilities.php');

if(import('getRedmineUserInformation')){
	die;
	$userInfo = getUserInformation(import('getRedmineUserInformation'));
	$userInfo = json_decode($userInfo);
	header('Content-type: application/json');
	echo json_encode($userInfo);
	die;
}

/*
ob_start();
var_dump($_FILES);
$filesStr = ob_get_clean();
error_log($filesStr);
echo 'error';
die;
*/

/*checks if files have been attached*/
if(thereIsAFile()){
	if(thereIsAFileError()){
		echo  'error - upload file failed';
		return;
	}

	uploadFiles();	//upload the files to get file tokens
}
else {
	setData();	//set data without considering files
}

function thereIsAFile(){
	foreach ($_FILES as $f){
		if(!empty($f['name']))
		{
			return true;
		}
	}

	return false;
}

function thereIsAFileError(){
	foreach($_FILES AS $f){
		if(isset($f['error'])){
			$error = $f['error'];
			if($error != 0 && !empty($f['name'])){
				return true;
			}
		}
	}

	return false;
}

function getUserApiKey($useAdminKey = false){
	if(!isset($_SESSION['loggedOnUserApiKey']) && import('loggedOnUserId') && !$useAdminKey){
		$userInfo = getUserInformation(import('loggedOnUserId'));
		$userInfo = json_decode($userInfo);
		$_SESSION['loggedOnUserApiKey'] = $userInfo->user->api_key;
	}
	
	$apiKey = null;
	
	if(isset($_SESSION['loggedOnUserApiKey'])){
		$apiKey = $_SESSION['loggedOnUserApiKey'];
	}
	
	if($useAdminKey){
		return 'admin key';	//this has been edited for privacy 
	} else {
		return $apiKey;
	}
}

function getUserInformation($redmineUserId){
	$ch = curl_init('https://redmineinstall'.$redmineUserId.'.json');	//this has been edited for privacy
	curl_setopt($ch, CURLOPT_POST, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array (
			'X-Redmine-API-Key: '.getUserApiKey(true)
	) );

	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	$response = curl_exec($ch);
	if ($response === false) {
		echo ("error-GUI");
		$last_error = 'ERROR_REQUEST_FAILED';
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
	}
	curl_close($ch);
	
	return $response;
}

function uploadFiles(){

$file_info = Array();	//stores name, type of file, and tokens
	foreach($_FILES as $f){
		$filepath = $f['tmp_name'];	//php temp file where the content is stored
		
		if(empty($f['name'])){
		continue;
		}
		
		$ch = curl_init('https://redmineinstall/uploads.xml');	//this has been edited for privacy
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($filepath));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array (
				'Content-Type: application/octet-stream',
				'X-Redmine-API-Key: '.getUserApiKey()
		) );
		
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		
		$response = curl_exec($ch);
		if ($response === false) {
			echo ("error-UPF");
			$last_error = 'ERROR_REQUEST_FAILED';
			$curl_errno = curl_errno($ch);
			$curl_error = curl_error($ch);
		}
		//error_log($response);

		$trim1 = mb_substr($response, 53);	//trim everything before token
		$trim2 = mb_substr($trim1, 0, -17);	//trim everything after token
		$file_info[] = array("name" => $f['name'], "type" => $f['type'], "token" => $trim2, "tmp_name" => $f['tmp_name']);
		
	}
	//var_dump($file_info);
	setData_files($file_info);
}


function setData_files($file_info){	
	
/*setting variables using data from html form*/
$project_id = htmlspecialchars($_POST['project_id']);
$description = htmlspecialchars($_POST['description']);
$assigned_to_id = htmlspecialchars($_POST['assigned_to_id']);
$last_name = htmlspecialchars($_POST['project_id']);
$subject = htmlspecialchars($_POST['subject']);
$first_name = htmlspecialchars($_POST['first_name']);
$email = htmlspecialchars($_POST['email']);

$noteAboutAttachments = "\r\n\r\n***Attachments will be included in a separate email***";
	
/*set data in XML format*/
$data ='<?xml version="1.0"?>
		<ticket>
		  <issue>
		    <project_id>';
		
$data .= 	 	$project_id . "</project_id>
		    <tracker_id>3</tracker_id>
		    <subject>";

$data .=		$subject . "</subject>
		    <description>";

$data .=		$description . $noteAboutAttachments . "</description>
		    <assigned_to_id>";

$data .= 		$assigned_to_id . "</assigned_to_id>";

$data .= 	'<uploads type="array">';

foreach($file_info as $f){	//create upload fields for each file
	$data .= '<upload>
				<token>' . $f['token'] . '</token>';
	$data .= '<filename>' . $f['name'] . '</filename>';
	$data .= '<content_type>' . $f['type'] . '</content_type>
				</upload>';
}
		
$data .=	'</uploads>';

$data .=	"</issue>
		    <contact>
		      <email>";

$data .=		$email . "</email>
		      <first_name>";
				
$data .=		$first_name . "</first_name>
		      <last_name>";
		
$data .=		$last_name . '</last_name>
		    </contact>
		</ticket>';


$email_info = array();	//information for the email with attachments
$email_info["subject"] = $subject;
$email_info["to_email"] = $email;
$email_info["body"] = $description;

if($project_id == 'setonhome-support'){	//set the correct 'from' address based on the project_id 
	$email_info["from_email"] = 'someemail';	//this has been edited for privacy
	$email_info["from_name"] = 'Support';	//this has been edited for privacy
}
else if($project_id == 'someproject'){	//this has been edited for privacy
	$email_info["from_email"] = 'someotheremail';	//this has been edited for privacy
	$email_info["from_name"] = 'Testing Support';	//this has been edited for privacy
	//Uncomment the below lines and remove the above lines in order to change Seton Testing's 'from' address and 'from' name 
	//$email_info["from_email"] = 'someotheremail';
	//$email_info["from_name"] = 'Testing Services';
}
else if($project_id == 'online'){	//this has been edited for privacy
	$email_info["from_email"] = 'anotheremail';	//this has been edited for privacy
	$email_info["from_name"] = 'Online';	//this has been edited for privacy
}

$data = postTicket_files($data, $email_info, $file_info); //run the POST request
}


function setData(){
	/*setting variables using data from html form*/
	$project_id = htmlspecialchars($_POST['project_id']);
	$description = htmlspecialchars($_POST['description']);
	$assigned_to_id = htmlspecialchars($_POST['assigned_to_id']);
	$last_name = htmlspecialchars($_POST['last_name']);
	$subject = htmlspecialchars($_POST['subject']);
	$first_name = htmlspecialchars($_POST['first_name']);
	$email = htmlspecialchars($_POST['email']);
	
	/*set data in XML format*/
	$data ='<?xml version="1.0"?>
		<ticket>
		  <issue>
		    <project_id>';
	
	$data .= 	 	$project_id . "</project_id>
		    <tracker_id>3</tracker_id>
		    <subject>";
	
	$data .=		$subject . "</subject>
		    <description>";
	
	$data .=		$description . "</description>
		    <assigned_to_id>";
	
	$data .= 		$assigned_to_id . "</assigned_to_id>";
	
	$data .=	"</issue>
		    <contact>
		      <email>";
	
	$data .=		$email . "</email>
		      <first_name>";
	
	$data .=		$first_name . "</first_name>
		      <last_name>";
	
	$data .=		$last_name . '</last_name>
		    </contact>
		</ticket>';
	
	$data = postTicket($data); //run the POST request
}

function postTicket_files($data, $email_info, $file_info) {	//POST function specifically for tickets with attachments
	
	include_once 'sendEmail.php';	//for sending the email with attachments

	$ch = curl_init('https://redmineinstall/helpdesk/create_ticket.xml');	//this has been edited for privacy
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array (
			'Content-Type: application/xml',
			'Content-Length: ' . strlen ($data),
			'X-Redmine-API-Key: '.getUserApiKey()
	) );

	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	$response = curl_exec($ch);
	if (empty($response)) {
		echo ("error-PT");
		$last_error = 'ERROR_REQUEST_FAILED';
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		return $response;
	}

	$issue_number = intval(preg_replace('/[^0-9]+/', '', $response), 10);	//extracts the issue number from the CURL response
	//$_SESSION["issue_number"] = $issue_number;	//sets issue_number as a session global variable
	curl_close($ch);

	echo '<br /><br /><center><h2>Success! ' . $response . '</h2></center>';	//alert on success
	
	$email_info["issue_number"] = $issue_number;
	
	sendAttachmentsEmail($email_info, $file_info);	//send the attachments in a separate email
		
}

function postTicket($data) {	//POST function specifically for tickets without attachments
	$ch = curl_init('https://redmineinstall/helpdesk/create_ticket.xml');	//this has been edited for privacy
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array (
			'Content-Type: application/xml',
			'Content-Length: ' . strlen ($data),
			'X-Redmine-API-Key: '.getUserApiKey()
	) );

	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	$response = curl_exec($ch);
	if (empty($response)) {
		$key = getUserApiKey();
		echo ('error-CT-'.$key);
		$last_error = 'ERROR_REQUEST_FAILED';
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		return $response; 
	}
	
	$issue_number = intval(preg_replace('/[^0-9]+/', '', $response), 10);	//extracts the issue number from the CURL response
	$_SESSION["issue_number"] = $issue_number;	//sets issue_number as a session global variable
	curl_close($ch);
	
	//alert on success
	echo '<br /><br /><center><h2>Success! ' . $response . '</h2></center>';
	
	
	/*html forms used as page navigation buttons*/
	//echo '<h1><center>Success!</center></h1>';
	//echo '<center>' . $response . '</center>';
	/*echo '<center><form action="ticketform.php">
			<input type="submit" value="Create Another Ticket" />
		</form></center>';*/
}
?>
