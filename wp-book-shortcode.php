<?php
/*
Plugin Name: F13 Book Shortcode
Plugin URI: http://f13dev.com/wordpress-plugin-book-shortcode/
Description: Embed information about a book into a WordPress blog post or page using shortcode.
Version: 1.0
Author: Jim Valentine - f13dev
Author URI: http://f13dev.com
Text Domain: f13-book-shortcode
License: GPLv3
*/

/*
Copyright 2016 James Valentine - f13dev (jv@f13dev.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// Register the shortcode
add_shortcode( 'book', 'f13_book_shortcode');
// Register the CSS
add_action( 'wp_enqueue_scripts', 'f13_book_shortcode_stylesheet');
// Register the admin page
add_action('admin_menu', 'f13_bs_create_menu');

function f13_book_shortcode( $atts, $content = null )
{
    // Get the attributes
    extract( shortcode_atts ( array (
        'isbn' => '', // Get the ISBN attribute
    ), $atts ));

}

function f13_book_shortcode_stylesheet()
{
    wp_register_style( 'f13book-style', plugins_url('wp-book-shortcode.css', __FILE__));
    wp_enqueue_style( 'f13book-style' );
}

function f13_bs_create_menu()
{
    // Create the top-level menu
    add_options_page('F13Devs Book Shortcode Settings', 'F13 Book Shortcode', 'administrator', 'f13-book-shortcode', 'f13_bs_settings_page');
    // Retister the Settings
    add_action( 'admin_init', 'f13_bs_settings');
}

function f13_bs_settings()
{
    // Register settings for token and timeout
    register_setting( 'f13-bs-settings-group', 'f13bs_token');
    register_setting( 'f13-bs-settings-group', 'f13bs_timeout');
}

function f13_bs_settings_page()
{
?>
    <div class="wrap">
        <h2>F13 Book Shortcode Settings</h2>
        <p>
            This plugin requires an API Key from Outpan in order to function.
        </p>
        <p>
            To obtain an Outpan API Key:
            <ol>
                <li>
                    Instructions.
                </li>
            </ol>
        </p>

        <form method="post" action="options.php">
            <?php settings_fields( 'f13-bs-settings-group' ); ?>
            <?php do_settings_sections( 'f13-bs-settings-group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        Outpan API Key
                    </th>
                    <td>
                        <input type="password" name="f13bs_token" value="<?php echo esc_attr( get_option( 'f13bs_token' ) ); ?>" style="width: 50%;"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        Cache timeout (minutes)
                    </th>
                    <td>
                        <input type="number" name="f13bs_timeout" value="<?php echo esc_attr( get_option( 'f13bs_timeout' ) ); ?>" style="width: 75px;"/>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}
