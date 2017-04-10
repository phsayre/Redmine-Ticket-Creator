<?php
/*** Author: Philip Sayre
**** 4/10/2017
**** github.com/phsayre
*/
session_start();
header('Access-Control-Allow-Origin: *');

if(isset($_SESSION['loggedOnUserApiKey'])){
	unset($_SESSION['loggedOnUserApiKey']);
}

?>

<!DOCTYPE HTML>


<html>
<title>Create a ticket</title>
<!-- <script
  src="https://code.jquery.com/jquery-3.1.1.min.js"
  integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
  crossorigin="anonymous"></script>
<script
  src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
  integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
  crossorigin="anonymous"></script>	-->

<head>
<style>

	.left {
    	width: 20%;
    	float: left;
    	text-align: right;
	}

	.right {
    	width: 65%;
    	margin-left: 10px;
    	float:left;
	}
	
	
	.center {
	text-align: center;
	}
	
	.center-block {
	display: block;
	margin-left: auto;
	margin-right: auto;
	}
	
	#large-submit {
	width: 16em;  
	height: 3em;
	font-weight: bold;
	}
	
	#small-submit {
	width: 6em;  
	height: 2em;
	font-weight: bold;
	}

	.loader {
	border: 16px solid #f3f3f3; /* Light grey */
	border-top: 16px solid #3498db; /* Blue */
	border-bottom: 16px solid #3498db; /* Blue */
	border-radius: 50%;
	width: 120px;
	height: 120px;
	animation: spin 2s linear infinite;
	}
	
	@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
	}
	
</style>
</head>

<body>


<h1 class="center">Create a ticket</h1>

<!-- form for ticket fields -->
<div id="success">
<form name="ticket" id="create" action="/SetonEmail/createTicket.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="loggedOnUserId" id="loggedOnUserId" value="" />
	<div class="left">
	First name: 
	</div>
	<div class="right">
	<input type="text" name="first_name" required="required" /><font color="green">*Required field</font><br /><br />
	</div>
	<div class="left">
	Last name:
	</div>
	<div class="right">
	<input type="text" name="last_name" /><br /><br />
    </div>
	<div class="left">
	Email address:   
	</div>
	<div class="right">
	<input id="email" type="text" name="email" required="required" /><font color="green">*Required field</font><br /><br />
	</div>
	<div class="left">
	Subject:    
	</div>
	<div class="right">
	<input type="text" name="subject" required="required" /><font color="green">*Required field</font><br /><br />
	</div>
	<div class="left">
	Project:
	</div>
	<div class="right">
		<select name="project_id" id="selectProject">
			<option value="setonhome-support">Test(SHS)</option>
			<option value="setontesting">Seton Testing</option>
			<option value="stanfordonline">Stanford Online</option>
		</select><br /><br />
	</div>
	<div class="left">
	Assignee: 
	</div>
	<div class="right">
			<select name="assigned_to_id">
				<option value="">Select...</option>
				<option value="40">Andrew</option>
				<option value="39">Bernadette</option>
				<option value="38">Brigid</option>
				<option value="3">Carl</option>
				<option value="29">Felicity</option>
				<option value="30">Jake</option>
				<option value="37">Lisa</option>
				<option value="11">Patty</option>
				<option value="35">Rose</option>
				<option value="32">Testing_Manager</option>
				<option value="7">Testing_Support</option>
				<option value="33">Testing_Stanford_Online</option>
			</select><br /><br />
	</div>
	<div class="left">
	Email Body:
	</div>
	<div class="right">
	<textarea name="description" id="description" rows="20" cols="80"></textarea><br /><br />
    </div>
	
	<!-- attaching files -->
	<div class="left">
	Attachment:
	</div>
	<div class="right">
	<input type="file" name="0"/><br /><br />
	</div>
	<div class="left" id="addAnotherAttachmentField">
	<!-- reference for javascript to insert new attachment field -->
	&nbsp
	</div>
	<div id="addAnotherAttachment">
	<!-- reference for javascript to insert "Attach More" link -->
	</div><br /><br />
	<div id="onfail">
	</div>
	<div id="spinner">
	<center>
	<input type="submit" id="large-submit" name="submit" value="Create Ticket" />
	</center>
	</div>
	<br /><br />
</form>
</div>

</body>

<!-- dynamically add attachment fields -->
<script>

var NewEmail_i = 0;

function NewEmail_addAttachmentField(){
	if(NewEmail_i == 6){
		alert('There is a 7 attachment limit.  Please send additional files as a "note" to the customer.');
		return;
	}
	NewEmail_i++; //the name variable to autoincrement file names as added
	var part1 = '<div class="left">Attachment:</div><div class="right"><input type="file" name="'; //html code broken before name variable
	var part2 = '" /><br /><br /></div>'; //html code broken after name variable
	var addField = jQuery(part1 + NewEmail_i + part2); 
	addField.insertBefore('#addAnotherAttachmentField');
}	

function isEmail(email) {
	  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	  return regex.test(email);
	}

jQuery(document).ready(function(){
	var str = '<div class="right"><a href="javascript: NewEmail_addAttachmentField();" title="Add Another Attachment">Attach More</a></div>';
	jQuery("#addAnotherAttachment").html(str);
	var userPath = jQuery('#loggedas').find('a').attr('href');
	
	var userId = userPath.match(/\d+/);
	
	if(userId){
		userId = userId[0];
	}
	
	jQuery("#loggedOnUserId").val(userId);
});


jQuery('#create').submit(function() { // catch the form's submit event
    if (!isEmail(document.forms["ticket"]["email"].value)){	//validate email
		alert("Please enter a valid email address");
		return false;
    }   

	jQuery('#spinner').html('<center><div class="loader"></div></center>');
	 
  	var NewEmail_formData = new FormData(jQuery(this)[0]);
    
    	jQuery.ajax({ // create an AJAX call...
        data: NewEmail_formData, // get the form data
        type: jQuery(this).attr('method'), // GET or POST
        url: jQuery(this).attr('action'), // the file to call
        processData: false,
        contentType: false,
        
        success: function(response) { // on success..
			if(response.includes("error")){
				
				if(response.includes("upload file failed")){
					alert('One of the attchments failed to upload.  The file is probably too large. Cannot be larger than 5mb.');
					jQuery('#spinner').html('<center><input type="submit" id="large-submit" name="submit" value="Create Ticket" />');
					return false;	
				}
				else {
					alert('error: request not sent... make sure you have permission to create a ticket for this project');	//alert user of error
					jQuery('#spinner').html('<center><input type="submit" id="large-submit" name="submit" value="Create Ticket" />');
					return false;
				}	
			}
			else {
            			jQuery('#success').html(response); // update the DIV wrapping the entire form
        		}
        		}});
    return false; // cancel original event to prevent form submitting
});

</script>
</html>
