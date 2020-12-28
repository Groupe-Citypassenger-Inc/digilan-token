Test
=== Digilan Token ===
Contributors: digilan
Donate link: https://www.citypassenger.com/
Tags: digilan, token, third party, authenticator
Requires at least: 4.9.8
Tested up to: 5.3
Requires PHP: 7.0
Stable tag: 2.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin helps transform a WordPress into a third party authenticator services.

== Description ==

Turn your WordPress site into a third party for authentication services.

Features

* Registration and login with Facebook, Google and Twitter.
* Simple to setup and use.
* Record user connections in a table with data filtering and csv export.
* Display your authentication buttons in any page with widgets.

API

* Create session: DigilanToken::initialize_new_connection()
* Authenticate: DigilanToken::authenticate_ap_user_on_wp()
* Validate: DigilanToken::validate_user_connection()
* Delete user data: DigilanTokenUser::forget_me()
* Configure: DigilanTokenActivator::get_ap_settings()

Usage

* Configure your social providers.
* Add your social authentication buttons with a shortcode or a widget.
** To display for example google and facebook given the fact you configured those two providers:
`[digilan_token google="1" facebook="1"]`
* To use the plugin as a widget, go to "Appearance > Widgets" in the admin panel.
* Under "Available widgets" section find "Digilan Token Buttons" and "add widget", then select the providers you want to display.

== Frequently Asked Questions ==
How do I activate all the plugin's features?  Enter the pin code you received with your access point.

== Screenshots ==
1.  Access point configuration panel
2.  Connection logs panel

== Changelog ==

= 1.0 =
* Initial version.
* First review

== Upgrade Notice ==

= 1.0 =
First version.

= 2.7 =
 * Fix timetables

 = 2.8 =
 * Merge and rebase of all branches.
 * Cleaning codes.
 * Databases setup modifications.

