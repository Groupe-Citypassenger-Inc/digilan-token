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

    <?php if (substr($provider->getLoginUrl(), 0, 8) !== 'https://') : ?>
        <div class="error">
            <p><?php printf(__('%1$s allows HTTPS OAuth Redirects only. You must move your site to HTTPS in order to allow login with %1$s.', 'digilan-token'), 'Facebook'); ?></p>
        </div>
    <?php else : ?>
        <h2 class="title"><?php _e('Getting Started', 'digilan-token'); ?></h2>

        <p style="max-width:55em;"><?php printf(__('To allow your visitors to log in with their %1$s account, first you must create a %1$s App. The following guide will help you through the %1$s App creation process. After you have created your %1$s App, head over to "Settings" and configure the given "%2$s" and "%3$s" according to your %1$s App.', 'digilan-token'), "Facebook", "App ID", "App secret"); ?></p>

        <h2 class="title"><?php printf(_x('Create %s', 'App creation', 'digilan-token'), 'Facebook App'); ?></h2>

        <ol>
            <li><?php printf(__('Navigate to %s', 'digilan-token'), '<a href="https://developers.facebook.com/apps/" target="_blank">https://developers.facebook.com/apps/</a>'); ?></li>
            <li><?php printf(__('Log in with your %s credentials if you are not logged in', 'digilan-token'), 'Facebook'); ?></li>
            <li><?php _e('Click on the "Add a New App" button', 'digilan-token'); ?></li>
            <li><?php _e('Fill "Display Name" and "Contact Email"', 'digilan-token'); ?></li>
            <li><?php _e('Click on blue "Create App ID" button', 'digilan-token'); ?></li>
            <li><?php _e('Select "Integrate Facebook Login" at the Select a Scenario page, then click Confirm.', 'digilan-token'); ?></li>
            <li><?php _e('Enter your domain name to the App Domains', 'digilan-token'); ?></li>
            <li><?php _e('Fill up the "Privacy Policy URL". Provide a publicly available and easily accessible privacy policy that explains what data you are collecting and how you will use that data.', 'digilan-token'); ?></li>
            <li><?php _e('Click on "Save Changes"', 'digilan-token'); ?></li>
            <li><?php _e('In the left sidebar under the Products section, click on "Facebook Login" and select Settings', 'digilan-token'); ?></li>
            <li><?php printf(__('Add the following URL to the "Valid OAuth redirect URIs" field: <b>%s</b>', 'digilan-token'), $provider->getLoginUrl()); ?></li>
            <li><?php _e('Click on "Save Changes"', 'digilan-token'); ?></li>
            <li><?php _e('In the top of the left sidebar, click on "Settings" and select "Basic"', 'digilan-token'); ?></li>
            <li><?php _e('Here you can see your "APP ID" and you can see your "App secret" if you click on the "Show" button. These will be needed in plugin\'s settings.', 'digilan-token'); ?></li>
            <li><?php _e('Your application is currently private ( Status: In Development ), which means that only you can log in with it. In the top bar click on the "OFF" switcher and select a category for your App.', 'digilan-token'); ?></li>
            <li><?php _e('By clicking "Confirm", the Status of your App will go Live.', 'digilan-token'); ?></li>
        </ol>

        <a href="<?php echo $this->getUrl('settings'); ?>" class="button button-primary"><?php printf(__('I am done setting up my %s', 'digilan-token'), 'Facebook App'); ?></a>

        <br>

    <?php endif; ?>
</div>