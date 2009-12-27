<?php
require(dirname(__FILE__) . '/../MadMailer.class.php');

// There are a total of four arguments that can be used on the next line. The first two are shown here, the second two
// are optional. The first of them is a debugger, which defaults to false, and the second, allows you to print
// the transaction ID when sending a message. It also defaults to false.
$mailer = new MadMailer('YOUR USERNAME (OR E-MAIL ADDRESS)', 'YOUR API KEY'); 

// Let's create a new user array, and add that user to a list.
$user = array('Name' => 'Nicholas Young', 'Email' => 'rockandroll@example.com');
$mailer->AddUser($user);
?>