<?php
/**
 * Plugin Name: FGP Tools
 * Description: Toolkit that includes Backup WP and PHP Info.
 * Version: 1.0
 * Author: Frank GP Developer
 * Author URI: https://frankgp.com
 */

if (!defined('ABSPATH')) exit;

/*---------------------------------------------------------------------
    ADMIN MENU
---------------------------------------------------------------------*/
add_action('admin_menu', function () {

    // MAIN MENU
    add_menu_page(
        'FGP Tools',
        'FGP Tools',
        'manage_options',
        'fgp-tools',
        'fgp_tools_dashboard_page',
        'dashicons-admin-tools',
        75
    );

    // SUBMENU: Backup WP
    add_submenu_page(
        'fgp-tools',
        'Backup WP',
        'Backup WP',
        'manage_options',
        'fgp-tools-backup',
        'fgp_tools_backup_page'
    );

    // SUBMENU: PHP Info
    add_submenu_page(
        'fgp-tools',
        'PHP Info',
        'PHP Info',
        'manage_options',
        'fgp-tools-phpinfo',
        'fgp_tools_phpinfo_page'
    );
});

/*---------------------------------------------------------------------
    LOAD SEPARATE FILES
---------------------------------------------------------------------*/
function fgp_tools_dashboard_page() {
    include_once __DIR__ . '/includes/dashboard.php';
}

function fgp_tools_backup_page() {
    include_once __DIR__ . '/includes/backup-wp.php';
}

function fgp_tools_phpinfo_page() {
    include_once __DIR__ . '/includes/phpinfo.php';
}
