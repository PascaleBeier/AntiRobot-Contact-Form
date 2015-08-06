=== AntiRobot Contact Form ===
Contributors: pascalebeier
Donate link: https://pascalebeier.de/
Tags: contact form, recaptcha, contact, recaptcha 2.0, recaptcha contact form, recaptcha 2, simple contact form
Requires at least: 3.4
Tested up to: 4.2.4
Stable tag: 1.0.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

AntiRobot Contact Form is a fast and simple spam-blocking contact form using the reCAPTCHA 2.0 API.

== Description ==

Looking for a lightweight alternative to existing contact form plugins, I decided to create one on my own, as I didn't like additional loaded files where they were not needed and the lack of security.

You have all freedom to adjust the styling of the contact form in your child-theme, as this plugin won't deliver any styling on its own. 

Furthermore, AntiRobot Contact form makes use of the reCAPTCHA 2.0 API, providing top-notch security and a graceful fallback for non-javascript users.

== Installation ==

1. Upload `antirobot-contact-form` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin throught the 'Settings' => 'AntiRobot Contact Form' menu
4. Place the shortcode `[antirobot_contact_form]` on pages or posts you want to use the contact form

== Frequently Asked Questions ==

= How can I translate the AntiRobot Contact Form? =

You'll find the antirobot-contact-form.pot file in the /languages/ subdirectory. You can use this file for tools like PoEdit to translate it to your liking. 

= How can I style the Contact Form? =

The AntiRobot Contact Form should fit to the styling of your theme. You can apply additional styling through the id `#arcf-contact-form`.

Error and success feedback notices are styled via .arcf-error and .arcf-success.

= I think feature X is missing.  =

The plugin was originally created to provide a lightweight alternative to existing feature-blown contact form plugins. 
I wanted to create a simple, fast and secure contact form solution. 

= I need help =

I'll answer your question in the official support forums as fast as possible.

== Screenshots ==

1. A German frontend example.

2. Another English frontend example.

3. Backend as of 1.0.0.

== Changelog ==
= 1.0.0 =
* Tested for WordPress 4.2.4
* Improved Backend
* Improved localization
* added classes for styling

= 0.2.1 =
* Updated German translation

= 0.2.0 =
* Tested for WordPress 4.2.2
* Add placeholder values
* Add Reply-To value for E-Mail clients
* Updated translations

= 0.1.3 =
* Fixed Typo
* Added Instructions for styling

= 0.1.2 =
* Updated German language

= 0.1.1 =
* Support for https://
* updated readme.txt

= 0.1.0 =
* Inital Release on WordPress.org
* Setting up for i18n
* Added English and German language
