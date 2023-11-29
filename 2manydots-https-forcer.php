<?php
/*
Plugin Name: 2manydots - HTTPS forcer
Plugin URI: https://www.2manydots.nl/
Description: This plugin forces all URLs to be HTTPS. That secures your website!
Author: 2manydots
Version: 0.1
*/

//Plugin update chcker
require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/2manydots/2manydots-https-forcer',
	__FILE__,
	'2manydots-https-forcer'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');


//Add option page to disable the functions of the plug-in
add_action('admin_menu', 'https_forcer_menu');

function https_forcer_menu() {
    add_options_page('HTTPS Forcer', 'HTTPS Forcer', 'manage_options', 'https-forcer', 'https_forcer_options_page');
}

function https_forcer_options_page() {
    ?>
        <div class="wrap">
        <h2>HTTPS Forcer</h2>
        <form method="post" action="options.php">
            <?php settings_fields('https-forcer-options'); ?>
            <?php do_settings_sections('https-forcer'); ?>
            <table class="form-table">
                <tr valign="top">
                <th scope="row">Enable HTTPS Forcer</th>
                <td><input type="checkbox" name="https_forcer_enabled" value="1" <?php checked(1, get_option('https_forcer_enabled', 1)); ?> /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        </div>
    <?php
    }

//Update the plug-in settings
add_action('admin_init', 'register_https_forcer_settings');

function register_https_forcer_settings() {
    register_setting('https-forcer-options', 'https_forcer_enabled');
}

//Add function that forces everything to HTTPS
function force_https() {
    if (get_option('https_forcer_enabled', 1) && !is_ssl() && !is_admin()) {
        wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
        exit();
    }
}
