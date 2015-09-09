<?php
global $_UPLOAD_NOTIFIER_MOCK_OPTIONS;

$_UPLOAD_NOTIFIER_MOCK_OPTIONS = array();

function mock_get_option( $option, $default = false ) {
	switch ( $option ) {
		case 'upload_notifier_general_settings':
			$defaults = array(
				'from_email' => '',
				'to_email' => '',
				'message' => '',
				'pattern' => '',
				'subject' => '',
			);

			global $_UPLOAD_NOTIFIER_MOCK_OPTIONS;

			$options = array_merge( $defaults, $_UPLOAD_NOTIFIER_MOCK_OPTIONS );

			return $options;

		case 'home':
			return 'http://test.localhost';

		default:
			return "Mock option: $option";
	}
}

$mock_function_args = array(
	'get_option' => '$option,$default = false',
);

include 'define-mock-functions.php';
