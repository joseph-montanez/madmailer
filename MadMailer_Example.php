<?php
$mailer = new MadMailer('LOGIN EMAIL ADDRESS (USERNAME)', 'API-KEY');

// Get all lists for this account..
$lists = $mailer->Lists();

// ...and loop through them.
foreach ($lists as $list) {
	echo $list['name'] . "<br />";
}

// Now, let's check a user's membership status...
$memberships = $mailer->Memberships('test@example.net');
foreach ($memberships as $list) {
	echo $list['name'] . "<br />";
}

// Maybe we just want to send a message?

$recipient = array('Name' => 'Nicholas Young', 'Email' => 'nicholas@nicholaswyoung.com');
$message = array('PromoName' => 'My Awesome Promotion', 'Subject' => 'You Gotta Read This', 'FromAddr' => 'noreply@example.com');
$body = array('Greeting' => 'Hello From MadMailer!');
$mailer->SendMessage($recipient, $message, $body);
?>