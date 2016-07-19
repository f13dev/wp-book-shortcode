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

    // Check if the api key is present, it it is not
    // then allert the user.
    if (esc_attr( get_option('f13bs_token')) == '')
    {
        $response = 'An Outpan API key is required to use this plugin<br />
        please visit \'WPAdmin => Settings => F13 Book Shortcode\' for more information';
    }
    else
    {
        // Check if the ISBN attribute has been completed,
        // if not allert the user that the ISBN attribute is
        // a required field.
        if ($isbn == '')
        {
            $response = 'The ISBN attribute is required, please enter an ISBN and try again.<br />
            e.g. [book isbn="anISBN"]<br />
            Please note, the ISBN field may take a valid ISBN or GTIN as found on Outpan.com.';
        }
        else
        {
            // Generate the book data.
            $data = f13_get_book_data($isbn);

            // Check if an error has been returned.
            if (array_key_exists('error', $data))
            {
                // Alert the user that the ISBN returned an error
                $response = 'The ISBN: ' . $isbn . ' could not be found.';
            }
            else
            {
                // Generate the response.
                $response = f13_book_shortcode_format($data);
            }
        }
    }
    // Return the response
    return $response;
}

function f13_book_shortcode_format($data)
{
    // Create a response
    $response = '';

    // Create a container div
    $response .= '<div class="f13-book-container">';

        // Place the title in a div
        $response .= '<div class="f13-book-title">';

            // Output the books name
            $response .= $data['name'] . '<br />';

        // Close the title div
        $response .= '</div>';

        // Check if an image exists
        if ($data['image'][0] != '')
        {
            // Output the image
            $response .= '<img src="' . $data['images'][0] . '" />';
        }

        // For each attribute, output the key and value
        $response .= '<ul>';

            foreach($data['attributes'] as $key => $value)
            {
                // Create a new list item for each attribute
                $response .= '<li><span>' . $key . ':</span> ' . $value . '</li>';
            }

        $response .= '</ul>';

    // Clear the styling
    $response .= '<br clear="both" />';

    // Close the container div
    $response .= '</div>';

    // Return the response
    return $response;
}

function f13_get_book_data($anISBN)
{
    // Get the API Key from the admin settings
    $key = esc_attr( get_option('f13bs_token'));

    // start curl
    $curl = curl_init();

    // Remove hyphens from the ISBN
    $anISBN = str_replace('-', '', $anISBN);

    // set the curl URL
    $url = 'https://api.outpan.com/v2/products/' . $anISBN . '?apikey=' . $key;

    // Set curl options
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPGET, true);

    // Set the user agent
    curl_setopt($curl, CURLOPT_USERAGENT, 'F13 WP Book Shortcode/1.0');
    // Set curl to return the response, rather than print it
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Get the results and store the XML to results
    $results = json_decode(curl_exec($curl), true);

    // Close the curl session
    curl_close($curl);

    return $results;
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
