<?php
/*
  Plugin Name: Upload Notifier
  Description: Notify by email when files are uploaded
  Version: 0.1
  Author: Martin Burchell
*/

class UploadNotifier {
	public static function admin_menu() {
		add_settings_section(
			'upload-notifier',
			'Upload Notifier',
			array( 'UploadNotifier', 'add_settings_callback' ),
			'media'
		);
	}

	public static function admin_init() {
		self::set_defaults();

		register_setting(
			'media',
			'upload_notifier_general_settings'
		);
	}

	private static function set_defaults() {
		$options = get_option( 'upload_notifier_general_settings' );

		$options = wp_parse_args(
			$options,
			array(
				'from_email' => '',
				'to_email' => '',
				'subject' => 'New file uploaded',
				'message' => 'A new file has been uploaded to ',
				'pattern' => '',
			)
		);

		update_option( 'upload_notifier_general_settings', $options );
	}

	public static function add_settings_callback() {
		$options = get_option( 'upload_notifier_general_settings' );
		?>
		<table class="form-table">
		<tr>
		<th>Email address of sender</th>
		<td>
		<input type="text" id="upload-notifier-from-email" name="upload_notifier_general_settings[from_email]" value="<?php echo esc_attr( $options['from_email'] ) ?>" size="50" />
		</td>
		</tr>
		<tr>
		<th>Email address of recipient</th>
		<td>
		<input type="text" id="upload-notifier-to-email" name="upload_notifier_general_settings[to_email]" value="<?php echo esc_attr( $options['to_email'] ) ?>" size="50" />
		</td>
		</tr>
		<tr>
		<th>Email subject</th>
		<td>
		<input type="text" id="upload-notifier-subject" name="upload_notifier_general_settings[subject]" value="<?php echo esc_attr( $options['subject'] ) ?>" size="50" />
		</td>
		</tr>
		<tr>
		<th>Message</th>
		<td>
		<textarea id="upload-notifier-message" name="upload_notifier_general_settings[message]"><?php echo esc_attr( $options['message'] ) ?></textarea>
		</td>
		</tr>
		<tr>
		<th>Pattern used for matching filenames</th>
		<td>
		<input type="text" id="upload-notifier-pattern" name="upload_notifier_general_settings[pattern]" value="<?php echo esc_attr( $options['pattern'] ) ?>" size="50" />
		</td>
		</tr>
		</table>
		<?php
	}

	public static function handle_upload( $file_details, $event )
	{
		if ( $event == 'upload' ) {
			$url = $file_details['url'];

			$options = get_option( 'upload_notifier_general_settings' );
			$pattern = $options['pattern'];

			$basename = basename( $url );

			if ( preg_match( "/$pattern/", $basename ) ) {
				self::send_email( $url );
			}
		}

		return $file_details;
	}

	private static function send_email( $url ) {
		$options = get_option( 'upload_notifier_general_settings' );

		$headers = array();
		$headers[] = "From: <{$options['from_email']}>";

		return mail(
			$options['to_email'],
			$options['subject'],
			"{$options['message']}<$url>",
			implode( "\r\n", $headers )
		);
	}
}

if ( is_admin() ) {
	add_action( 'admin_menu', array( 'UploadNotifier', 'admin_menu' ) );
	add_action( 'admin_init', array( 'UploadNotifier', 'admin_init' ) );
	add_filter( 'wp_handle_upload',
				array( 'UploadNotifier', 'handle_upload' ), 10, 2 );
}
