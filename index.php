<?php
/*
Plugin Name: AntiRobot Contact Form
Plugin URI: https://wordpress.org/plugins/antirobot-contact-form/
Description: AntiRobot Contact Form is a fast and simple spam-blocking contact form using the reCAPTCHA 2.0 API.
Version: 1.5.0
Text Domain: antirobot-contact-form
Domain Path: /languages/
Author: Pascale Beier
Author URI: https://pascalebeier.de/
*/

/**
 * AntiRobot Contact Form
 *
 * AntiRobot Contact Form is a fast and simple spam-blocking contact form using the reCAPTCHA 2.0 API.
 *
 * @package antirobot-contact-form
 * @author Pascale Beier <mail@pascalebeier.de>
 */

/**
 * Enable Localization.
 */
function arcf_textdomain() {
	load_plugin_textdomain( 'antirobot-contact-form', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'arcf_textdomain' );

/**
 * Contact Form Frontend Code.
 */
function arcf_frontend() {
	?>

	<form action="<?php echo( $_SERVER['REQUEST_URI'] ) ?>" method="post" id="arcf-contact-form">

		<fieldset class="form-group">
			<label for="arcf-name"><?php _e( 'Name', 'antirobot-contact-form' ) ?></label>
			<input type="text" class="form-control" id="arcf-name" name="arcf-name"
			       placeholder="<?php _e( 'Jon Doe', 'antirobot-contact-form' ) ?>"
			       value="<?php echo ! empty( $_POST['arcf-name'] ) ? sanitize_text_field( $_POST['arcf-name'] ) : '' ?>"
			       required>
		</fieldset>

		<fieldset class="form-group">
			<label for="arcf-email"><?php _e( 'E-Mail', 'antirobot-contact-form' ) ?></label>
			<input type="email" class="form-control" id="arcf-email" name="arcf-email"
			       placeholder="<?php _e( 'mail@example.org', 'antirobot-contact-form' ) ?>"
			       value="<?php echo ! empty( $_POST['arcf-email'] ) ? sanitize_email( $_POST['arcf-email'] ) : '' ?>"
			       required>
		</fieldset>

		<fieldset class="form-group">
			<label for="arcf-message"><?php _e( 'Your Message', 'antirobot-contact-form' ) ?></label>
			<textarea class="form-control" id="arcf-message" name="arcf-message"
			          placeholder="<?php _e( 'Enter your message here', 'antirobot-contact-form' ) ?>"
			          required><?php echo ! empty( $_POST['arcf-message'] ) ? sanitize_text_field( $_POST['arcf-message'] ) : '' ?>
			</textarea>
		</fieldset>

		<div class="g-recaptcha"
		     data-sitekey="<?php echo sanitize_text_field( get_option( 'arcf_publickey' ) ) ?>"></div>

		<script type="text/javascript"
		        src="https://www.google.com/recaptcha/api.js?hl=<?php echo get_locale() ?>"></script>

		<fieldset class="form-group">
			<button type="submit" class="btn btn-primary" name="arcf-submitted">
				<?php _e( 'Submit', 'antirobot-contact-form' ) ?>
			</button>
		</fieldset>

	</form>

	<?php
}

/**
 *
 */
function arcf_validation() {
	if ( isset( $_POST['arcf-submitted'] ) ) {
		$admin_email = sanitize_email( get_option( 'arcf_mailto' ) );

		$sender_name    = sanitize_text_field( $_POST['arcf-name'] );
		$sender_email   = sanitize_email( $_POST['arcf-email'] );
		$sender_subject = sanitize_text_field( get_option( 'arcf_subject' ) );
		$sender_message = sprintf(
			/* translators: 1: Sender Name 2: Sender E-Mail */
			__( 'You received a new message from %1$s <%2$s>', 'antirobot-contact-form' ),
			$sender_name,
			$sender_email
		);
		$sender_message .= "\r\n\r\n";
		$sender_message .= sanitize_text_field( $_POST['arcf-message'] );

		$admin_message = sprintf(
			/* translators: 1: Admin E-Mail 2: WordPress URL */
			__( 'You successfully sent the following message to %1$s (via %2$s)', 'antirobot-contact-form' ),
			$admin_email,
			get_bloginfo( 'url' )
		);
		$admin_message .= "\r\n\r\n";
		$admin_message .= sanitize_text_field( $_POST['arcf-message'] );

		$admin_subject = __( 'You succesfully sent us an E-Mail!', 'antirobot-contact-form' );

		$admin_headers[] = "From: $admin_email";
		$admin_headers[] = "Reply-To: $admin_email";

		$sender_headers[] = "From: $sender_name <$sender_email>";
		$sender_headers[] = "Reply-To: $sender_name <$sender_email>";


		$privatekey = sanitize_text_field( get_option( 'arcf_privatekey' ) );

		$captcha = null;

		// Verify that a POST Request was issued to reCAPTCHA ...
		! empty( $_POST['g-recaptcha-response'] ) ? $captcha = $_POST['g-recaptcha-response'] : null;

		// ... if not, throw an Error ...
		if ( empty( $captcha ) ) {
			echo '<div class="alert alert-danger"><p>' .
			     __( 'Please solve the reCAPTCHA before submitting the form.', 'antirobot-contact-form' )
			     . '</p></div>';
		} else {
			// ... if so, get the response from the reCAPTCHA service ...
			$response = wp_remote_fopen( 'https://www.google.com/recaptcha/api/siteverify?secret='.$privatekey.'&response='.$captcha.'&remoteip='.$_SERVER['REMOTE_ADDR'] );
			$json = json_decode( $response, true );
			// ... if the service did not return true, throw an error ...
			if ( true !== $json['success'] ) {
				echo '<div class="alert alert-danger"><p>' .
				     __( 'reCAPTCHA did not authorize your request. Make sure your keys are correct.', 'antirobot-contact-form' )
				     . '</p></div>';
				// ... if it did return true, send these mails ...
			} else {
				// ... and if wp_mail() is not causing troubles ...
				if ( wp_mail( $admin_email, $sender_subject, $sender_message, $sender_headers ) &&
				     wp_mail( $sender_email, $admin_subject, $admin_message, $admin_headers )
				) {
					// ... and notify the user of that successfully send mail.
					echo '<div class="alert alert-success"></p>' .
					     __( 'Message successfully sent. You will receive an E-Mail confirmation soon.', 'antirobot-contact-form' )
					     . '</p></div>';
					// ... but if wp_mail() is troubling, notify the user.
				} else {
					echo '<div class="alert alert-danger"><p>' .
					     __( 'Mail Delivery with wp_mail() failed. Is your web server configuration allowing to send mails?', 'antirobot-contact-form' )
					     . '</p></div>';
				}
			}
		}
	}
}

/**
 * Register the Antirobot Contact Form Shortcode.
 *
 * @return string
 */
function arcf_shortcode() {
	ob_start();
	arcf_validation();
	arcf_frontend();

	return ob_get_clean();
}

add_shortcode( 'antirobot_contact_form', 'arcf_shortcode' );

/**
 * Register the backend settings.
 */
add_action( 'admin_menu', 'arcf_setup_menu' );
add_action( 'admin_init', 'arcf_register_settings' );

function arcf_register_settings() {
	register_setting( 'arcf-option-group', 'arcf_publickey' );
	register_setting( 'arcf-option-group', 'arcf_privatekey' );
	register_setting( 'arcf-option-group', 'arcf_mailto' );
	register_setting( 'arcf-option-group', 'arcf_subject' );
}

/**
 * Setup the backend menu.
 */
function arcf_setup_menu() {
	add_options_page( 'AntiRobot Contact Form', 'AntiRobot Contact Form', 'manage_options', 'antirobot-contact-form', 'arcf_init' );
}

/**
 * Backend GUI.
 */
function arcf_init() {
	?>
	<div class="wrap">

		<h1><?php _e( 'AntiRobot Contact Form', 'antirobot-contact-form' ); ?></h1>

		<form method="post" action="options.php">
			<?php settings_fields( 'arcf-option-group' ) ?>
			<?php do_settings_sections( 'arcf-option-group' ) ?>

			<h3><?php _e( 'reCAPTCHA', 'antirobot-contact-form' ) ?>
				<a class="page-title-action" href="https://www.google.com/recaptcha/admin">
					<?php _e( 'Get your keys', 'antirobot-contact-form' ); ?>
				</a>
			</h3>

			<p>
				<label><?php _e( 'Public Key', 'antirobot-contact-form' ); ?></label> <br/>
				<input type="text" size="45" name="arcf_publickey"
				       value="<?php echo esc_attr( get_option( 'arcf_publickey' ) ) ?> ">
			</p>

			<p>
				<label><?php _e( 'Secret Key', 'antirobot-contact-form' ); ?></label> <br/>
				<input type="text" size="45" name="arcf_privatekey"
				       value="<?php echo esc_attr( get_option( 'arcf_privatekey' ) ) ?>"/>
			</p>

			<hr>

			<h3><?php _e( 'Contact Form', 'antirobot-contact-form' ) ?></h3>

			<p>
				<label><?php _e( 'Recipient', 'antirobot-contact-form' ) ?></label> <br/>
				<input type="email" size="45" name="arcf_mailto"
				       value="<?php echo esc_attr( get_option( 'arcf_mailto' ) ) ?>"/>
			</p>

			<p>
				<label><?php _e( 'Subject', 'antirobot-contact-form' ) ?></label> <br/>
				<input type="text" size="45" name="arcf_subject"
				       value="<?php echo esc_attr( get_option( 'arcf_subject' ) ) ?>"/>
			</p>

			<?php submit_button() ?>
		</form>

		<h3><?php _e( 'Usage', 'antirobot-contact-form' ); ?></h3>

		<p><?php _e( 'After setting up, you may insert the shortcode <code>[antirobot_contact_form]</code> on pages or posts to display the contact form.', 'antirobot-contact-form' ); ?></p>

		<h3><?php _e( 'Did you save time?', 'antirobot-contact-form' ); ?></h3>

		<p><?php _e( 'If this Plugin has done its job saving your time, <a href="https://wordpress.org/support/view/plugin-reviews/antirobot-contact-form#postform">leave a review</a> and spread the word. If you want to support my coffee addiction, you can <a href="https://pascalebeier.de/donate/">tip me on paypal</a>.</p>', 'antirobot-contact-form' ); ?></p>
	</div>
	<?php
}
