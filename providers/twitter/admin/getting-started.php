<?php
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

defined('ABSPATH') || die();
/** @var $this DigilanTokenSocialProviderAdmin */

$provider = $this->getProvider();
?>

<div class="dlt-admin-sub-content">
    <h2 class="title"><?php _e('Getting Started', 'digilan-token'); ?></h2>

    <p style="max-width:55em;"><?php printf(__('To allow your visitors to log in with their %1$s account, first you must create a %1$s App. The following guide will help you through the %1$s App creation process. After you have created your %1$s App, head over to "Settings" and configure the given "%2$s" and "%3$s" according to your %1$s App.', 'digilan-token'), "Twitter", "Consumer Key", "Consumer Secret"); ?></p>

    <h2 class="title"><?php printf(_x('Create %s', 'App creation', 'digilan-token'), 'Twitter App'); ?></h2>

    <ol>
        <li><?php printf(__('Navigate to %s', 'digilan-token'), '<a href="https://developer.twitter.com/en/apps/create" target="_blank">https://developer.twitter.com/en/apps/create</a>'); ?></li>
        <li><?php printf(__('Log in with your %s credentials if you are not logged in yet', 'digilan-token'), 'Twitter'); ?></li>
        <li><?php _e('If you don\'t have a developer account yet, please apply one by filling all the required details! This is required for the next steps!', 'digilan-token'); ?></li>
        <li><?php printf(__('Once your developer account is complete, navigate back to %s if you aren\'t already there!', 'digilan-token'), '<a href="https://developer.twitter.com/en/apps/create" target="_blank">https://developer.twitter.com/en/apps/create</a>'); ?>
        <li><?php printf(__('Fill the App name, Application description fields. Then enter your site\'s URL to the Website URL field: <b>%s</b>', 'digilan-token'), site_url()); ?></li>
        <li><?php _e('Tick the checkbox next to Enable Sign in with Twitter!', 'digilan-token'); ?></li>
        <li><?php printf(__('Add the following URL to the "Callback URLs" field: <b>%s</b>', 'digilan-token'), $provider->getRedirectUriForApp()); ?></li>
        <li><?php _e('Fill the “Terms of Service URL", "Privacy policy URL" and "Tell us how this app will be used” fields!', 'digilan-token'); ?></li>
        <li><?php _e('Click the Create button.', 'digilan-token'); ?></li>
        <li><?php _e('Read the Developer Terms and click the Create button again!', 'digilan-token'); ?></li>
        <li><?php _e('Select the Permissions tab and click Edit.', 'digilan-token'); ?></li>
        <li><?php _e('Tick the Request email address from users under the Additional permissions section and click Save.', 'digilan-token'); ?></li>
        <li><?php _e('Go to the Keys and tokens tab and find the API key and API secret key', 'digilan-token'); ?></li>
    </ol>

    <a href="<?php echo $this->getUrl('settings'); ?>" class="button button-primary"><?php printf(__('I am done setting up my %s', 'digilan-token'), 'Twitter App'); ?></a>

    <br>

</div>