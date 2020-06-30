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

?>
<tr>
    <th scope="row"><?php _e('Button skin', 'digilan-token'); ?></th>
    <td>
        <fieldset>
            <label>
                <input type="radio" name="skin" value="default" <?php if ($settings->get('skin') == 'uniform') : ?> checked="checked" <?php endif; ?>>
                <span><?php _e('Uniform', 'digilan-token'); ?></span><br />
                <img src="<?php echo plugins_url('images/google/uniform.png', DLT_ADMIN_PATH) ?>" />
            </label>
            <label>
                <input type="radio" name="skin" value="light" <?php if ($settings->get('skin') == 'light') : ?> checked="checked" <?php endif; ?>>
                <span><?php _e('Light', 'digilan-token'); ?></span><br />
                <img src="<?php echo plugins_url('images/google/light.png', DLT_ADMIN_PATH) ?>" />
            </label>
            <label>
                <input type="radio" name="skin" value="dark" <?php if ($settings->get('skin') == 'dark') : ?> checked="checked" <?php endif; ?>>
                <span><?php _e('Dark', 'digilan-token'); ?></span><br />
                <img src="<?php echo plugins_url('images/google/dark.png', DLT_ADMIN_PATH) ?>" />
            </label><br>
        </fieldset>
    </td>
</tr>