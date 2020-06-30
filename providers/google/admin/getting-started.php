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

    <p style="max-width:55em;"><?php printf(__('To allow your visitors to log in with their %1$s account, first you must create a %1$s App. The following guide will help you through the %1$s App creation process. After you have created your %1$s App, head over to "Settings" and configure the given "%2$s" and "%3$s" according to your %1$s App.', 'digilan-token'), "Google", "Client ID", "Client secret"); ?></p>

    <h2 class="title"><?php printf(_x('Create %s', 'App creation', 'digilan-token'), 'Google App'); ?></h2>

    <ol>
        <li><?php printf(__('Navigate to %s', 'digilan-token'), '<a href="https://console.developers.google.com/apis/" target="_blank">https://console.developers.google.com/apis/</a>'); ?></li>
        <li><?php printf(__('Log in with your %s credentials if you are not logged in', 'digilan-token'), 'Google'); ?></li>
        <li><?php _e('If you don\'t have a project yet, you\'ll need to create one. You can do this by clicking on the blue "Create project" button on the right side!  ( If you already have a project, click on the name of your project in the dashboard instead, which will bring up a modal and click New Project. )', 'digilan-token'); ?></li>
        <li><?php _e('Click the Create button.', 'digilan-token'); ?></li>
        <li><?php _e('Name your project and then click on the Create button again', 'digilan-token'); ?></li>
        <li><?php _e('Once you have a project, you\'ll end up in the dashboard.', 'digilan-token'); ?></li>
        <li><?php _e('Click on the "Credentials" in the left hand menu to create new API credentials', 'digilan-token'); ?></li>
        <li><?php _e('Select the OAuth consent screen!', 'digilan-token'); ?></li>
        <li><?php _e('Enter a name for your App under the "Application name" field, which will appear as the name of the app asking for consent.', 'digilan-token'); ?></li>
        <li><?php printf(__('Fill the "Authorized domains" field with your domain name probably: <b>%s</b> without subdomains!', 'digilan-token'), str_replace('www.', '', $_SERVER['HTTP_HOST'])); ?></li>
        <li><?php _e('Press "Save" and you will be redirected back to Credentials screen.', 'digilan-token'); ?></li>
        <li><?php _e('Click the Create credentials button and select "OAuth client ID" from the dropdown.', 'digilan-token'); ?></li>
        <li><?php _e('Your application type should be "Web application"', 'digilan-token'); ?></li>
        <li><?php _e('Name your application', 'digilan-token'); ?></li>
        <li><?php printf(__('Add the following URL to the "Authorised redirect URIs" field: <b>%s</b>', 'digilan-token'), $provider->getLoginUrl()); ?></li>
        <li><?php _e('Click on the Create button', 'digilan-token'); ?></li>
        <li><?php _e('A modal should pop up with your credentials. If that doesn\'t happen, go to the Credentials in the left hand menu and select your app by clicking on its name and you\'ll be able to copy-paste the Client ID and Client Secret from there.', 'digilan-token'); ?></li>
    </ol>

    <a href="<?php echo $this->getUrl('settings'); ?>" class="button button-primary"><?php printf(__('I am done setting up my %s', 'digilan-token'), 'Google App'); ?></a>

    <br>

</div>