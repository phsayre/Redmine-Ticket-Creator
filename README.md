# Redmine-Ticket-Creator

### Required plugins for Redmine: 
1) RedmineUP HelpDesk https://www.redmineup.com/pages/plugins/helpdesk
2) Redmine Custom JS https://github.com/martin-denizet/redmine_custom_js


## Thanks for visiting
This code uses the Redmine Custom JS plugin to insert a custom button into Redmine's upper toolbar.
The button I've created is labeled "New Helpdesk Ticket." Clicking the button opens a resizable widget with input fields (e.g. Name, Email, Message, Project, etc) to create a new helpdesk ticket anywhere in the web application.
jQuery handles dynamically adding a new "Attachment" button every time a file is attached, up to 7 files.
PHP handles the form data and cURL posts to Redmine's API. 
