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
class DigilanTokenNoAccessWidget extends WP_Widget
{

  public static function register()
  {
    register_widget('DigilanTokenNoAccessWidget');
  }

  public function __construct()
  {
    parent::__construct(
      'digilan-token_schedule',
      sprintf(__('%s Schedule', 'digilan-token'), 'Digilan Token')
    );
  }

  public function widget($args, $instance)
  {
    echo do_shortcode('[digilan_token_schedule]');
  }
}

add_action('widgets_init', 'DigilanTokenNoAccessWidget::register');
