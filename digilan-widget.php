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

class DigilanToken_Social_Login_Widget extends WP_Widget
{

    public static function register()
    {
        register_widget('DigilanToken_Social_Login_Widget');
    }

    public function __construct()
    {
        parent::__construct('digilan-token_social_login', sprintf(__('%s Buttons', 'digilan-token'), 'Digilan Token'));
    }

    public function form($instance)
    {
        wp_enqueue_script('digilan-widget-form', plugins_url('/js/dlt-widget-form.js', DLT_PLUGIN_BASENAME), array(
            'wp-color-picker'
        ), true);
        wp_enqueue_style('wp-color-picker');

        $instance = wp_parse_args((array) $instance, array(
            'title' => '',
            'color' => '#000000',
            'size' => '16'
        ));
        $title = $instance['title'];

        $fontsize = $instance['size'];
        $color = $instance['color'];
        $button_CSS_override = $instance['button_CSS_override'];

        $style = isset($instance['style']) ? $instance['style'] : 'default';

        $providerButtons = array();

        foreach (DigilanToken::$providers as $provider) {
            if (isset($instance[$provider->getId()])) {
                $providerButtons[$provider->getId()] = intval($instance[$provider->getId()]);
            } else {
                $providerButtons[$provider->getId()] = true;
            }
        }

        $custom_portal_fields = array();
        $user_form_fields = get_option('digilan_token_user_form_fields');

        foreach ($user_form_fields as $field_key => $field_data) {
            if (isset($instance[$field_key])) {
                $custom_portal_fields[$field_key] = $instance[$field_key];
            } else {
                $custom_portal_fields[$field_key] = true;
            }
        }

?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /> </label>
        </p>
        <h3>
            <strong><?php _e('Activate social authentication', 'digilan-token'); ?></strong>
        </h3>
        <?php
        foreach (DigilanToken::$providers as $provider) :
            if ($provider->getState() == 'configured') :
                $providerId = $provider->getId();
        ?>
                <p>
                    <input name="<?= $this->get_field_name($providerId) ?>" type="hidden" value="0" />
                    <input
                        id="<?= htmlentities($this->get_field_id($providerId)) ?>"
                        name="<?= $this->get_field_name($providerId) ?>"
                        type="checkbox"
                        value="1"
                        <?php if ($providerButtons[$providerId]) : ?> checked <?php endif; ?>
                    />
                    <label for="<?= htmlentities($this->get_field_id($providerId)) ?>"><?= $providerId ?></label>
                </p>
        <?php endif;

        endforeach;
        ?>
        <h3>
            <strong><?php _e('Activate portal fields', 'digilan-token'); ?></strong>
        </h3>
        <?php
        foreach ($user_form_fields as $field_key=>$field_data) :
        ?>
            <p>
                <input name="<?= $this->get_field_name($field_key) ?>" type="hidden" value="0" />
                <input
                    id="<?= htmlentities($this->get_field_id($field_key)) ?>"
                    name="<?= $this->get_field_name($field_key) ?>"
                    type="checkbox"
                    value="1"
                    <?php if ($custom_portal_fields[$field_key]) : ?> checked <?php endif; ?>
                />
                <label for="<?= htmlentities($this->get_field_id($field_key)) ?>"><?= $field_key ?></label>
            </p>
        <?php
        endforeach;
        ?>
        <h3>
            <strong><?php _e('Terms and condition formatting', 'digilan-token'); ?></strong>
        </h3>
        <p>
            <label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Size:', 'digilan-token'); ?>
                <select class="widefat" id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>">
                    <?php

                    for ($i = 8; $i <= 24; ++$i) :
                        if ($i == esc_attr($fontsize)) :
                    ?>
                            <option value="<?php echo $i; ?>" selected><?php echo $i; ?></option>
                        <?php else : ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php

                        endif;
                    endfor;
                    ?>
                </select> </label>
        </p>
        <p>
            <?php

            _e('Color:', 'digilan-token');
            ?>
            <label for="<?php echo $this->get_field_id('color'); ?>"> <input class="widefat dlt-color" id="<?php echo $this->get_field_id('color'); ?>" name="<?php echo $this->get_field_name('color'); ?>" type="text" value="<?php echo esc_attr($color); ?>" />
            </label>
        </p>
        <p>
            <?php
            _e('Buttons CSS:', 'digilan-token');
            ?>
            <label for="<?php echo $this->get_field_id('button_CSS_override'); ?>">
                <input class="widefat" id="<?php echo $this->get_field_id('button_CSS_override'); ?>" name="<?php echo $this->get_field_name('button_CSS_override'); ?>" type="text" value="<?php echo esc_attr($button_CSS_override); ?>" />
            </label>
        </p>
        <?php
        // Workaround to have color picker working when configuring Widget in Elementor
        if (class_exists('Elementor\Editor')) {
            add_action('elementor/editor/before_enqueue_script', function () {
        ?>
                <script>
                    (function($) {
                        function initColorPicker(widget) {
                            widget.find('.dlt-color').wpColorPicker({
                                change: _.throttle(function() {
                                    $(this).trigger('change');
                                }, 3000)
                            });
                        }

                        function onFormUpdate(event, widget) {
                            initColorPicker(widget);
                        }
                        $(document).on('widget-added widget-updated', onFormUpdate);
                        $(document).ready(function() {
                            $('.widget-inside').each(function() {
                                initColorPicker($(this));
                            });
                            $('#widgets-right .widget:has(.dlt-color)').each(function() {
                                initColorPicker($(this));
                            });
                        });
                    }(jQuery));
                </script>

<?php

            });
            do_action('elementor/editor/before_enqueue_script');
        }
    }

    public function widget($args, $instance)
    {
        $title = '';
        if (!empty($instance['title'])) {
            $title = $instance['title'];
        }

        $title = apply_filters('widget_title', $title, $instance, $this->id_base);

        $style = 'default';
        if (!empty($instance['style'])) {
            $style = $instance['style'];
        }

        $providerButtons = array();
        foreach (DigilanToken::$providers as $provider) {
            if ($provider->getState() == 'configured') {
                $providerId = $provider->getId();
                $providerButtons[$providerId] = 1;
                if (isset($instance[$providerId])) {
                    $providerButtons[$providerId] = intval($instance[$providerId]);
                }
            }
        }

        echo $args['before_widget'];
        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        if (empty($providerButtons['google'])) {
            $google = 0;
        } else {
            $google = $providerButtons['google'];
        }

        if (empty($providerButtons['twitter'])) {
            $twitter = 0;
        } else {
            $twitter = $providerButtons['twitter'];
        }

        if (empty($providerButtons['facebook'])) {
            $facebook = 0;
        } else {
            $facebook = $providerButtons['facebook'];
        }

        if (empty($providerButtons['transparent'])) {
            $transparent = 0;
        } else {
            $transparent = $providerButtons['transparent'];
        }

        if (empty($providerButtons['mail'])) {
            $mail = 0;
        } else {
            $mail = $providerButtons['mail'];
        }

        $user_form_fields = get_option('digilan_token_user_form_fields');
        $portal_custom_fields = '';
        foreach ($user_form_fields as $field_key=>$field_data) {
            $portal_custom_fields .= sprintf('%s="%s" ', $field_key, $instance[$field_key]);
        }

        $in = '[digilan_token style="%s" google="%s" twitter="%s" facebook="%s" transparent="%s" mail="%s" color="%s" fontsize="%s" override-btn-css="%s" %s]';

        $color = sanitize_hex_color($instance['color']);
        $fontsize = $instance['size'];
        $button_CSS_override = $instance['button_CSS_override'];

        $shortcode = sprintf($in, $style, $google, $twitter, $facebook, $transparent, $mail, $color, $fontsize, $button_CSS_override, $portal_custom_fields);
        if (!$shortcode) {
            error_log('Could not format shortcode string.');
            return;
        }
        echo do_shortcode($shortcode);
        echo $args['after_widget'];
    }
}

add_action('widgets_init', 'DigilanToken_Social_Login_Widget::register');
