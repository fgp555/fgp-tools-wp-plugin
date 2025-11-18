<?php
/**
 * Plugin Name: FGP Tools
 * Description: Toolkit that includes Backup WP, Backup DB, Search & Replace, and PHP Info.
 * Version: 1.2
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

    // SUBMENU: Backup WP (archivos)
    add_submenu_page(
        'fgp-tools',
        'Backup WP',
        'Backup WP',
        'manage_options',
        'fgp-tools-backup',
        'fgp_tools_backup_page'
    );

    // SUBMENU: Backup DB (solo base de datos)
    add_submenu_page(
        'fgp-tools',
        'Backup DB',
        'Backup DB',
        'manage_options',
        'fgp-tools-backup-db',
        'fgp_tools_backup_db_page'
    );

    // SUBMENU: Search & Replace (Migration)
    add_submenu_page(
        'fgp-tools',
        'Search & Replace',
        'Search & Replace',
        'manage_options',
        'fgp-tools-search-replace',
        'fgp_tools_search_replace_page'
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

function fgp_tools_backup_db_page() {
    include_once __DIR__ . '/includes/backup-db.php';
}

function fgp_tools_search_replace_page() {
    include_once __DIR__ . '/includes/search-replace.php';
}

function fgp_tools_phpinfo_page() {
    include_once __DIR__ . '/includes/phpinfo.php';
}

