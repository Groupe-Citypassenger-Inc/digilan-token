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
class DigilanTokenSocialProviderAdmin
{

    /** @var string path to global /admin folder */
    public static $globalPath;

    /** @var DigilanTokenSocialProvider */
    protected $provider;

    /** @var string path to current providers /admin folder */
    protected $path;

    /**
     * DigilanTokenSocialProviderAdmin constructor.
     *
     * @param DigilanTokenSocialProvider $provider
     */
    public function __construct($provider)
    {
        $this->provider = $provider;

        $this->path = $this->provider->getPath() . '/admin';

        add_filter('dlt_update_settings_validate_' . $this->provider->getOptionKey(), array(
            $this,
            'validateSettings'
        ), 10, 2);
    }

    /**
     *
     * @return DigilanTokenSocialProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     *
     * @param string $subview
     *            Returns the admin URL for a subview.
     *            
     * @return string
     */
    public function getUrl($subview = '')
    {
        return add_query_arg(array(
            'subview' => $subview
        ), DigilanTokenAdmin::getAdminUrl('provider-' . $this->provider->getId()));
    }

    /**
     * Returns the validated settings for the buttons.
     *
     * @param
     *            $newData
     * @param
     *            $postedData
     *            
     *            
     * @return mixed
     */
    public function validateSettings($newData, $postedData)
    {
        $newData = $this->provider->validateSettings($newData, $postedData);

        if (isset($postedData['custom_default_button'])) {
            if (isset($postedData['custom_default_button_enabled']) && $postedData['custom_default_button_enabled'] == '1') {
                $newData['custom_default_button'] = $postedData['custom_default_button'];
            } else {
                if ($postedData['custom_default_button'] != '') {
                    $newData['custom_default_button'] = '';
                }
            }
        }

        if (isset($postedData['custom_icon_button'])) {
            if (isset($postedData['custom_icon_button_enabled']) && $postedData['custom_icon_button_enabled'] == '1') {
                $newData['custom_icon_button'] = wp_kses_post($postedData['custom_icon_button']);
            } else {
                if ($postedData['custom_icon_button'] != '') {
                    $newData['custom_icon_button'] = '';
                }
            }
        }

        foreach ($postedData as $key => $value) {

            switch ($key) {
                case 'login_label':
                    break;
                case 'link_label':
                    break;
                case 'unlink_label':
                    $newData[$key] = wp_kses_post($value);
                    break;
                case 'user_prefix':
                    break;
                case 'user_fallback':
                    $newData[$key] = preg_replace("/[^A-Za-z0-9\-_ ]/", '', $value);
                    break;
                case 'settings_saved':
                    $newData[$key] = intval($value) ? 1 : 0;
                    break;
                case 'oauth_redirect_url':
                    $newData[$key] = $value;
                    break;
            }
        }

        return $newData;
    }

    /**
     * Displays a subview if it is set in the URL.
     */
    public function settingsForm()
    {
        $subview = DigilanTokenSanitize::sanitize_request('subview');
        if (!$subview)
            $subview = '';
        $this->displaySubView($subview);
    }

    /**
     * Display the requested subview.
     *
     * @param
     *            $subview
     *            
     */
    protected function displaySubView($subview)
    {
        if (!$this->provider->adminDisplaySubView($subview)) {
            switch ($subview) {
                case 'settings':
                    $this->render('settings');
                    break;
                case 'buttons':
                    $this->render('buttons');
                    break;
                default:
                    $this->render('getting-started');
                    break;
            }
        }
    }

    /**
     *
     * @param
     *            $view
     * @param bool $showMenu
     *            Enframe the specified part-view with the complete view(header, menu, footer).
     */
    public function render($view, $showMenu = true)
    {
        include(self::$globalPath . '/templates/header.php');
        $_view = $view;
        $view = 'providers';
        include(self::$globalPath . '/templates/menu.php');
        $view = $_view;
        echo '<div class="dlt-admin-content">';
        echo '<h1>' . $this->provider->getLabel() . '</h1>';
        if ($showMenu) {
            include(self::$globalPath . '/templates-provider/menu.php');
        }

        \DLT\Notices::displayNotices();

        if ($view == 'buttons') {
            include(self::$globalPath . '/templates-provider/buttons.php');
        } else {
            include($this->path . '/' . $view . '.php');
        }
        echo '</div>';
        include(self::$globalPath . '/templates/footer.php');
    }

    /**
     * Display the Verify part of the settings subview.
     */
    public function renderSettingsHeader()
    {
        $provider = $this->provider;

        $state = $provider->getState();
?>
        <?php if ($state == 'not-tested') : ?>
            <div class="dlt-box dlt-box-blue">
                <h2 class="title"><?php _e('Your configuration needs to be verified', 'digilan-token'); ?></h2>
                <p><?php _e('Before you can start letting your users register with your app it needs to be tested. This test makes sure that no users will have troubles with the login and registration process. <br> If you see error message in the popup check the copied ID and secret or the app itself. Otherwise your settings are fine.', 'digilan-token'); ?></p>

                <p id="dlt-test-configuration">
                    <a id="dlt-test-button" href="<?php echo add_query_arg('test', '1', $provider->getLoginUrl()); ?>" class="button button-primary"><?php _e('Verify Settings', 'digilan-token'); ?></a>
                    <span id="dlt-test-please-save"><?php _e('Please save your changes to verify settings.', 'digilan-token'); ?></span>
                </p>
            </div>
        <?php endif; ?>


        <?php if ($provider->settings->get('tested') == '1') : ?>
            <div class="dlt-box <?php if ($state == 'configured') : ?>dlt-box-green<?php else : ?> dlt-box-yellow dlt-box-exclamation-mark<?php endif; ?>">
                <h2 class="title"><?php _e('Works Fine', 'digilan-token'); ?>
                </h2>
                <p><?php
                    switch ($state) {
                        case 'configured':
                            printf(__('This provider works fine, but you can test it again. If you don’t want to let users register or login with %s anymore you can disable it.', 'digilan-token'), $provider->getLabel());
                            echo '</p>';
                            echo '<p>';
                            printf(__('This provider is currently enabled, which means that users can register or login via their %s account.', 'digilan-token'), $provider->getLabel());
                            break;
                    }

                    ?>
                </p>
                <p id="dlt-test-configuration">
                    <a id="dlt-test-button" href="<?php echo add_query_arg('test', '1', $provider->getLoginUrl()); ?>" class="button button-secondary"><?php _e('Verify Settings Again', 'digilan-token'); ?></a>
                    <span id="dlt-test-please-save"><?php _e('Please save your changes before verifying settings.', 'digilan-token'); ?></span>
                </p>
            </div>
<?php endif;
        wp_register_script('dlt-tester', plugins_url('/js/admin/template-provider/digilan-test.js', DLT_PLUGIN_BASENAME));
        $data = array(
            'fields' => '#' . implode(',#', array_keys($provider->getRequiredFields()))
        );
        wp_enqueue_script('dlt-tester');
        wp_localize_script('dlt-tester', 'dlt_test', $data);
    }

    /**
     * Displays message if Oauth Redirect URI has changed.
     */
    public function renderOauthChangedInstruction()
    {
        echo '<h2>' . $this->provider->getLabel() . '</h2>';

        include($this->path . '/fix-redirect-uri.php');
    }
}

DigilanTokenSocialProviderAdmin::$globalPath = DLT_PATH . '/admin';
