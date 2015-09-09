<?php
require_once 'mock-mail.php';
require_once 'mock-option.php';
global $current_screen;
$current_screen = WP_Screen::get( 'admin_init' );


class UploadNotifyTest extends WP_UnitTestCase {
	public function test_is_admin() {
		$this->assertTrue( is_admin() );
	}

	public function test_sends_email_when_file_uploaded() {
		$this->apply_upload_filters();

		$this->check_email_was_sent();
	}

	public function test_no_message_sent_for_non_upload_event() {
		$this->apply_upload_filters( array(), 'sideload' );

		$this->check_no_email_was_sent();
	}

	public function test_link_appears_in_message() {
		$url = 'http://castlestreet.localhost/wp-content/uploads/2013/02/WNS-2013-02-03.pdf';

		$file_details = array( 'url' => $url );

		$this->apply_upload_filters( $file_details );

		$email = $this->get_last_email();
		$message = $email['message'];

		$this->assertThat( $message, $this->stringContains( $url ) );
	}

	public function test_from_address_read_from_config()
	{
		global $wp_options;

		$email_address = 'wordpress@castlestreet.org.uk';
		$this->set_option( 'from_email', $email_address );

		$this->apply_upload_filters();

		$this->check_email_contains_header( "From: <$email_address>" );
	}

	public function test_message_read_from_config()
	{
		$message = 'A new weekly notice sheet has been uploaded to the Castle Street Website';

		$this->set_option( 'message', $message );
		$this->apply_upload_filters();

		$email = $this->get_last_email();
		$this->assertThat( $email['message'], $this->stringContains( $message ) );
	}

	public function test_email_sent_when_pattern_matches()
	{
		$this->set_option( 'pattern', 'WNS-\d{4}-\d{2}-\d{2}\.pdf' );

		$url = 'http://castlestreet.localhost/wp-content/uploads/2013/02/WNS-2013-02-03.pdf';
		$this->apply_upload_filters( array( 'url' => $url ) );

		$this->check_email_was_sent();
	}

	public function test_no_email_sent_when_pattern_does_not_match()
	{
		$this->set_option( 'pattern', 'WNS-\d{4}-\d{2}-\d{2}\.pdf' );

		$url = 'http://castlestreet.localhost/wp-content/uploads/2013/02/church_downhill.jpg';
		$this->apply_upload_filters( array( 'url' => $url ) );

		$this->check_no_email_was_sent();
	}

	private function set_option( $name, $value ) {
		global $_UPLOAD_NOTIFIER_MOCK_OPTIONS;

		$_UPLOAD_NOTIFIER_MOCK_OPTIONS[ $name ] = $value;
	}

	private function apply_upload_filters(
		$file_details = array(),
		$event = 'upload' )	{
		$default_file_details = array(
			'file' => '',
			'url' => '',
			'type' => '',
		);

		$file_details = array_merge(
			$default_file_details,
			$file_details
		);

		apply_filters(
			'wp_handle_upload', $file_details, $event
		);
	}

	protected function check_email_contains_header( $expected_header ) {
		$email = $this->get_Last_email();
		$headers = explode( "\r\n", $email['additional_headers'] );

		$this->assertTrue(
			in_array( $expected_header, $headers ),
			"Failed to find $expected_header in " .
			print_r( $headers, true )
		);
	}

	private function check_email_was_sent( $message = '' ) {
		$this->assertTrue(
			$this->get_last_email() !== null,
			"Unexpectedly an email was not sent\n$message"
		);
	}

	protected function check_no_email_was_sent( $message = '' ) {
		$this->assertTrue(
			$this->get_last_email() === null,
			"Unexpectedly an email was sent\n$message"
		);
	}

	private function get_last_email() {
		global $mail_messages;
		return array_pop( $mail_messages );
	}
}
