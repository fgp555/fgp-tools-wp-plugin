<?php

if (!defined('ABSPATH')) exit;

/**
 * Search & Replace Tool
 */
function fgp_tools_render_search_replace() {
    global $wpdb;

    // Handle search & replace
    if (isset($_POST['fgp_run_replace'])) {

        $search  = sanitize_text_field($_POST['fgp_search']);
        $replace = sanitize_text_field($_POST['fgp_replace']);

        if (!$search || !$replace) {
            echo "<div class='notice notice-error'><p>Search and Replace fields cannot be empty.</p></div>";
        } else {

            $tables = $wpdb->get_col("SHOW TABLES");
            $affected = 0;

            foreach ($tables as $table) {

                // get columns
                $columns = $wpdb->get_results("DESCRIBE `$table`");

                foreach ($columns as $col) {

                    if (stripos($col->Type, 'char') !== false ||
                        stripos($col->Type, 'text') !== false ||
                        stripos($col->Type, 'blob') !== false) {

                        // Use SQL REPLACE()
                        $result = $wpdb->query(
                            $wpdb->prepare(
                                "UPDATE `$table` 
                                 SET `$col->Field` = REPLACE(`$col->Field`, %s, %s)",
                                $search,
                                $replace
                            )
                        );

                        if ($result > 0) {
                            $affected += $result;
                        }
                    }
                }
            }

            echo "<div class='notice notice-success'><p>
                Search & Replace complete.<br>
                <strong>Affected rows:</strong> $affected
            </p></div>";
        }
    }

    ?>

    <div class="wrap">
        <h1>Search & Replace (Migration)</h1>

        <p>This tool updates all database entries, useful when migrating WordPress to a new domain or directory.</p>

        <form method="post" style="max-width:600px;">

            <table class="form-table">
                <tr>
                    <th><label>Search for:</label></th>
                    <td><input name="fgp_search" type="text" class="regular-text" placeholder="https://oldsite.com"></td>
                </tr>

                <tr>
                    <th><label>Replace with:</label></th>
                    <td><input name="fgp_replace" type="text" class="regular-text" placeholder="https://newsite.com"></td>
                </tr>
            </table>

            <p style="margin-top:20px;">
                <button class="button button-primary" name="fgp_run_replace"
                    onclick="return confirm('⚠ This will modify the entire database. Continue?');">
                    Run Search & Replace
                </button>
            </p>

        </form>

        <hr>
        <h2>Tips</h2>
        <ul>
            <li>Make a full DB backup before running this tool.</li>
            <li>Supports domain change (HTTP → HTTPS).</li>
            <li>Fixes wp_options `siteurl` and `home` automatically.</li>
        </ul>

    </div>

    <?php
}

// Render
fgp_tools_render_search_replace();

