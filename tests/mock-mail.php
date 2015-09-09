<?php
global $mail_return_code;
$mail_return_code = true;

global $mail_messages;
$mail_messages = array();

function mock_mail( $email_to, $subject, $message, $additional_headers ) {
	$mail_message = array(
		'to' => $email_to,
		'subject' => $subject,
		'message' => $message,
		'additional_headers' => $additional_headers,
	);

	global $mail_messages;
	$mail_messages[] = $mail_message;

	global $mail_return_code;
	return $mail_return_code;
}

$mock_function_args = array(
	'mail' => '$email_to, $subject, $message, $additional_headers',
);

include 'define-mock-functions.php';
