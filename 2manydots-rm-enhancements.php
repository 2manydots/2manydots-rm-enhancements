<?php
/*
Plugin Name: 2manydots - RM Enhancements
Plugin URI: https://www.2manydots.nl/
Description: This plugin enhancements all optimizations for our RM projects.
Author: 2manydots
Version: 1.1.1
*/

// Plugin update checker
require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/2manydots/2manydots-rm-enhancements',
    __FILE__,
    '2manydots-rm-enhancements'
);

$myUpdateChecker->setBranch('master');

// Add option page to settings
add_action('admin_menu', 'rm_enhancements_menu');

function rm_enhancements_menu() {
    add_options_page('RM Enhancements Options', 'RM Enhancements', 'manage_options', 'rm-enhancements', 'rm_enhancements_options_page');
}

function rm_enhancements_options_page() {
    ?>
    <div class="wrap">
    <h2>RM Enhancements</h2>
    <form method="post" action="options.php">
        <?php settings_fields('rm-enhancements-options'); ?>
        <?php do_settings_sections('rm-enhancements'); ?>
        <table class="form-table">
            <tr valign="top">
            <th scope="row">Enable HTTPS Redirection</th>
            <td>
                <input type="checkbox" name="https_redirection_enabled" value="1" <?php checked(1, get_option('https_redirection_enabled', 1)); ?> />
            </td>
            </tr>
            <tr valign="top">
            <th scope="row">Enable URL Lowercase Redirection</th>
            <td>
                <input type="checkbox" name="url_lowercase_redirection_enabled" value="1" <?php checked(1, get_option('url_lowercase_redirection_enabled', 1)); ?> />
            </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
    </div>
    <?php
}

// Register settings
add_action('admin_init', 'register_rm_enhancements_settings');

function register_rm_enhancements_settings() {
    register_setting('rm-enhancements-options', 'https_redirection_enabled');
    register_setting('rm-enhancements-options', 'url_lowercase_redirection_enabled');
}

// Main redirection function
function rm_enhancements_redirect() {
    if (get_option('https_redirection_enabled', 1) && !is_ssl() && !is_admin()) {
        wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
        exit();
    }

    if (get_option('url_lowercase_redirection_enabled', 1)) {
        $uri = $_SERVER['REQUEST_URI'];
        if (preg_match('/[A-Z]/', $uri)) {
            wp_redirect(strtolower($uri), 301);
            exit();
        }
    }
}

add_action('template_redirect', 'rm_enhancements_redirect');

// Multisite network options

add_action('network_admin_menu', 'rm_enhancements_network_menu');

function rm_enhancements_network_menu() {
    add_submenu_page(
        'settings.php', // Parent slug
        'RM Enhancements Network Settings', // Page title
        'RM Enhancements', // Menu title
        'manage_network_options', // Capability
        'rm-enhancements-network', // Menu slug
        'rm_enhancements_network_options_page' // Callback function
    );
}

function rm_enhancements_network_options_page() {
    ?>
    <div class="wrap">
        <h2>RM Enhancements Network Settings</h2>
        <form method="post" action="edit.php?action=rm_enhancements_save_network_options">
            <?php settings_fields('rm-enhancements-network-options'); ?>
            <?php do_settings_sections('rm-enhancements-network'); ?>
            <table class="form-table">
                <tr valign="top">
                <th scope="row">Enable HTTPS Redirection for All Sites</th>
                <td>
                    <input type="checkbox" name="network_https_redirection_enabled" value="1" <?php checked(1, get_site_option('network_https_redirection_enabled', 0)); ?> />
                </td>
                </tr>
                <tr valign="top">
                <th scope="row">Enable URL Lowercase Redirection for All Sites</th>
                <td>
                    <input type="checkbox" name="network_url_lowercase_redirection_enabled" value="1" <?php checked(1, get_site_option('network_url_lowercase_redirection_enabled', 0)); ?> />
                </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action('network_admin_edit_rm_enhancements_save_network_options', 'rm_enhancements_save_network_options');

function rm_enhancements_save_network_options() {
    check_admin_referer('rm-enhancements-network-options-options');

    update_site_option('network_https_redirection_enabled', isset($_POST['network_https_redirection_enabled']) ? 1 : 0);
    update_site_option('network_url_lowercase_redirection_enabled', isset($_POST['network_url_lowercase_redirection_enabled']) ? 1 : 0);

    wp_redirect(add_query_arg(['page' => 'rm-enhancements-network', 'updated' => 'true'], network_admin_url('settings.php')));
    exit;
}

?>