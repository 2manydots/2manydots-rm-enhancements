<?php
/*
Plugin Name: 2manydots - HTTPS forcer
Plugin URI: https://www.2manydots.nl/
Description: This plugin forces all URLs to be HTTPS. That secures your website!
Author: 2manydots
Version: 1.1
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
                <td>
                    <input type="checkbox" name="https_forcer_enabled" value="1" <?php checked(1, get_option('https_forcer_enabled', 1)); ?> />
                    <p class="description">This setting forces everything to be HTTPS. Any questions? Feel free to ask <a href="mailto:support@2manydots.nl">support@2manydots.nl</a>.</p>
                </td>
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

function force_https() {
    $network_enabled = get_site_option('network_https_forcer_enabled', 0);
    $site_enabled = get_option('https_forcer_enabled', 1);

    if (($network_enabled || $site_enabled) && !is_ssl() && !is_admin()) {
        wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
        exit();
    }
}

// Multisite network options

add_action('network_admin_menu', 'https_forcer_network_menu');

function https_forcer_network_menu() {
    add_menu_page(
        'HTTPS Forcer Network Settings', // Page title
        'HTTPS Forcer', // Menu title
        'manage_network_options', // Capability
        'https-forcer-network', // Menu slug
        'https_forcer_network_options_page', // Callback function
        'dashicons-lock' // Icon (optional)
    );
}

function https_forcer_network_options_page() {
    ?>
        <div class="wrap">
            <h2>HTTPS Forcer Network Settings</h2>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="https_forcer_update_network_settings">
            <?php settings_fields('https-forcer-network-options'); ?>
            <?php do_settings_sections('https-forcer-network'); ?>
                <table class="form-table">
                    <tr valign="top">
                    <th scope="row">Enable HTTPS Forcer for All Sites</th>
                    <td>
                        <input type="checkbox" name="network_https_forcer_enabled" value="1" <?php checked(1, get_site_option('network_https_forcer_enabled', 0)); ?> />
                        <p class="description">Enabling this option will force all sites in the network to use HTTPS. If this option is disabled, each site administrator can choose to enable or disable HTTPS forcing individually.</p>
                    </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
}

add_action('network_admin_edit_https_forcer_update_network_options', 'https_forcer_update_network_options');
function https_forcer_update_network_options() {
    // Verify nonce and permissions
    if (!current_user_can('manage_network_options')) {
        wp_die('Not allowed');
    }

    check_admin_referer('https-forcer-network-options');

    // Update the option and redirect
    update_site_option('network_https_forcer_enabled', isset($_POST['network_https_forcer_enabled']) ? 1 : 0);
    wp_redirect(add_query_arg(array('page' => 'https-forcer-network', 'updated' => 'true'), network_admin_url('admin.php')));
    exit;
}