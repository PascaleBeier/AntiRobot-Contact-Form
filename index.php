<?php
/*
Plugin Name: AntiRobot Contact Form
Plugin URI: https://wordpress.org/plugins/antirobot-contact-form/
Description: AntiRobot Contact Form is a fast and simple spam-blocking contact form using the reCAPTCHA 2.0 API.
Version: 1.0.0
Text Domain: antirobot-contact-form
Domain Path: /languages/
Author: Pascale Beier
Author URI: https://pascalebeier.de/
*/
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

/*
Load textdomain early to enable localization
@since 0.1.0
*/

function arcf_textdomain()
{
    load_plugin_textdomain('antirobot-contact-form', FALSE, basename(dirname(__FILE__)) . '/languages/');
}

add_action('plugins_loaded', 'arcf_textdomain');
/*
register and enqueue reCAPTCHA libraries
@since 0.0.1
@changed 0.1.1
*/

function arcf_add_recaptcha()
{
    wp_register_script('arcf_recaptcha', '//www.google.com/recaptcha/api.js');
    wp_enqueue_script('arcf_recaptcha');
}

add_action('wp_head', 'arcf_add_recaptcha');
/*
contains the contact form frontend code
@since 0.0.1
@changed 0.2.0
*/

function arcf_frontend()
{
    echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post" id="arcf-contact-form">';
    echo '<p>';
    echo '<label>' . __('Your Name', 'antirobot-contact-form') . '</label> <br />';
    echo '<input type="text" name="arcf-name" pattern="[a-zA-Z0-9 ]+" placeholder="' . __('Jon Doe', 'antirobot-contact-form') . '" value="' . (isset($_POST["arcf-name"]) ? esc_attr($_POST["arcf-name"]) : '') . '" size="40" required="required"  />';
    echo '</p>';
    echo '<p>';
    echo '<label>' . __('Your E-Mail', 'antirobot-contact-form') . '</label> <br />';
    echo '<input type="email" name="arcf-email" placeholder="' . __('mail@example.org', 'antirobot-contact-form') . '" value="' . (isset($_POST["arcf-email"]) ? esc_attr($_POST["arcf-email"]) : '') . '" size="40"  required="required" />';
    echo '</p>';
    echo '<p>';
    echo '<label>' . __('Your Message', 'antirobot-contact-form') . '</label> <br />';
    echo '<textarea rows="10" cols="40" name="arcf-message" placeholder="' . __('Enter your message here', 'antirobot-contact-form') . '" required="required">' . (isset($_POST["arcf-message"]) ? esc_attr($_POST["arcf-message"]) : '') . '</textarea>';
    echo '</p>';
    echo '<p>';
    echo '<div class="g-recaptcha" data-sitekey="' . esc_attr(get_option('arcf_publickey')) . '"></div>';
    echo '</p>';
    echo '<p><input type="submit" name="arcf-submitted" value="' . __('Submit', 'antirobot-contact-form') . '"></p>';
    echo '</form>';
}

/*
Validate contact form input and ReCaptcha solution and mail contents to admin mail
@since 0.0.1
@changed 0.1.0
*/

function arcf_validation()
{
    if (isset($_POST['arcf-submitted'])) {
        $name = sanitize_text_field($_POST["arcf-name"]);
        $email = sanitize_email($_POST["arcf-email"]);
        $message = esc_textarea($_POST["arcf-message"]);
        $to = esc_attr(get_option('arcf_mailto'));
        $subject = esc_attr(get_option('arcf_subject'));
        $privatekey = esc_attr(get_option('arcf_privatekey'));
        $headers = "From: $name <$email>" . "\r\n";
        $headers .= "Reply-To: <$email>";
        $captcha;
        if (isset($_POST['g-recaptcha-response'])) {
            $captcha = $_POST['g-recaptcha-response'];
        }

        if (!$captcha) {
            echo '<h2>' . __('Solve the reCAPTCHA', 'antirobot-contact-form') . '</h2>';
            exit;
        }

        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $privatekey . "&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
        if ($response . $success == false) {
            echo '<h2>' . __('Solve the reCAPTCHA', 'antirobot-contact-form') . '</h2>';
        }
        else {
            if (wp_mail($to, $subject, $message, $headers)) {
                echo '<div class="arcf-success">';
                echo '<p>' . __('Form successfully sent.', 'antirobot-contact-form') . '</p>';
                echo '</div>';
            }
            else {
                echo '<div class="arcf-error"><p>' . __('An unexpected error occured. If this problem persists, please contact the webmaster.', 'antirobot-contact-form') . '</p></div>';
            }
        }
    }
}

/*
Register recaptcha_2_contact_form shortcode to use in pages or posts
@since 0.0.1
*/

function arcf_shortcode()
{
    ob_start();
    arcf_add_recaptcha();
    arcf_validation();
    arcf_frontend();
    return ob_get_clean();
}

add_shortcode('antirobot_contact_form', 'arcf_shortcode');
/*
Register backend option page
@since 0.0.2
@changed 0.1.0
*/
add_action('admin_menu', 'arcf_setup_menu');
add_action('admin_init', 'arcf_register_settings');

function arcf_register_settings()
{
    register_setting('arcf-option-group', 'arcf_publickey');
    register_setting('arcf-option-group', 'arcf_privatekey');
    register_setting('arcf-option-group', 'arcf_mailto');
    register_setting('arcf-option-group', 'arcf_subject');
}

function arcf_setup_menu()
{
    add_options_page('AntiRobot Contact Form', 'AntiRobot Contact Form', 'manage_options', 'antirobot-contact-form', 'arcf_init');
}

/*
Display backend settings page
@since 0.0.2
@changed 1.0.0
*/

function arcf_init()
{
?>
        <div class="wrap">
        <h2><?php
    _e('AntiRobot Contact Form', 'antirobot-contact-form'); ?></h2>
        <form method="post" action="options.php"> 
        <?php
    settings_fields('arcf-option-group');
    do_settings_sections('arcf-option-group');
?>
        <h3><?php
    _e('reCAPTCHA', 'antirobot-contact-form'); ?></h3>(<a href="https://www.google.com/recaptcha/admin"><?php
    _e('Get your keys', 'antirobot-contact-form'); ?></a>)
        <p>
        <label><?php
    _e('Public Key', 'antirobot-contact-form'); ?></label> <br />
        <input type="text" size="45" name="arcf_publickey" value="<?php
    echo esc_attr(get_option('arcf_publickey')); ?>" />
        </p>
        <p>
        <label><?php
    _e('Secret Key', 'antirobot-contact-form'); ?></label> <br />
        <input type="text" size="45" name="arcf_privatekey" value="<?php
    echo esc_attr(get_option('arcf_privatekey')); ?>" />
        </p>
        <hr>
        <h3><?php
    _e('Contact Form', 'antirobot-contact-form'); ?></h3>
        <p>
        <label><?php
    _e('Recipient', 'antirobot-contact-form'); ?></label> <br />
        <input type="email" size="45" name="arcf_mailto" value="<?php
    echo esc_attr(get_option('arcf_mailto')); ?>" />
        </p>
        <p>
        <label><?php
    _e('Subject', 'antirobot-contact-form'); ?></label> <br />
        <input type="text" size="45" name="arcf_subject" value="<?php
    echo esc_attr(get_option('arcf_subject')); ?>" />
        </p>
        <?php
    submit_button(); ?>
        </form>
        <h3><?php
    _e('Usage', 'antirobot-contact-form'); ?></h3>
        <p><?php
    _e('After setting up, you may insert the shortcode <code>[antirobot_contact_form]</code> on pages or posts to display the contact form.', 'antirobot-contact-form'); ?></p>
        </table>
            </div>
<?php
}

?>
