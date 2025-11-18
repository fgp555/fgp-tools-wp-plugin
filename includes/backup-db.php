<?php

if (!defined('ABSPATH')) exit;

/**
 * RENDER PAGE: Backup DB
 */
function fgp_tools_render_backup_db() {

    $backup_dir = WP_CONTENT_DIR . '/fgp-backup-db';

    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }

    /*---------------------------------------------------------
        DELETE BACKUP
    ---------------------------------------------------------*/
    if (isset($_GET['delete'])) {
        $file = basename($_GET['delete']);
        $path = $backup_dir . '/' . $file;

        if (file_exists($path)) unlink($path);

        echo "<div class='notice notice-success'><p>Backup deleted.</p></div>";
    }

    /*---------------------------------------------------------
        RESTORE BACKUP
    ---------------------------------------------------------*/
    if (isset($_GET['restore'])) {
        $file = basename($_GET['restore']);
        $path = $backup_dir . '/' . $file;

        if (file_exists($path)) {

            $cmd = sprintf(
                'mysql --user=%s --password=%s --host=%s %s < %s',
                escapeshellarg(DB_USER),
                escapeshellarg(DB_PASSWORD),
                escapeshellarg(DB_HOST),
                escapeshellarg(DB_NAME),
                escapeshellarg($path)
            );

            shell_exec($cmd);

            echo "<div class='notice notice-success'><p>Database restored successfully.</p></div>";
        }
    }

    /*---------------------------------------------------------
        RUN BACKUP
    ---------------------------------------------------------*/
    if (isset($_POST['run_backup_db'])) {

        $timestamp = date('Y-m-d_H-i-s');
        $db_file = "$backup_dir/db-$timestamp.sql";

        $cmd = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASSWORD),
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_NAME),
            escapeshellarg($db_file)
        );

        shell_exec($cmd);

        echo "<div class='notice notice-success'><p>
            Database backup created successfully.
        </p></div>";
    }

    /*---------------------------------------------------------
        LIST BACKUPS
    ---------------------------------------------------------*/
    $backups = glob($backup_dir . '/*.sql');

    ?>
    <div class="wrap">
        <h1>Backup Database</h1>
        <p>Create or restore SQL database backups.</p>

        <form method="post">
            <button class="button button-primary" name="run_backup_db">
                Run Backup DB
            </button>
        </form>

        <hr>

        <h2>Available Backups</h2>

        <?php if (empty($backups)): ?>
            <p>No backups found.</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Date</th>
                        <th>Size</th>
                        <th style="width:200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $file): 
                        $basename = basename($file);
                    ?>
                        <tr>
                            <td><?php echo esc_html($basename); ?></td>
                            <td><?php echo date("Y-m-d H:i:s", filemtime($file)); ?></td>
                            <td><?php echo size_format(filesize($file)); ?></td>
                            <td>
                                <a class="button" 
                                   href="<?php echo content_url('fgp-backup-db/' . $basename); ?>" 
                                   download>
                                   Download
                                </a>

                                <a class="button button-primary" 
                                   href="?page=fgp-tools-backup-db&restore=<?php echo esc_attr($basename); ?>"
                                   onclick="return confirm('Restore this backup? It will overwrite your current database.')">
                                   Restore
                                </a>

                                <a class="button button-danger" 
                                   style="background:#d63638;color:#fff;"
                                   href="?page=fgp-tools-backup-db&delete=<?php echo esc_attr($basename); ?>"
                                   onclick="return confirm('Delete this backup?')">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>
    <?php
}

// Render page
fgp_tools_render_backup_db();
