<?php
if (!defined('ABSPATH')) exit;

/* ----------------------------------------------------
   Ensure backup directory exists
---------------------------------------------------- */
$FGP_BACKUP_DIR = WP_CONTENT_DIR . '/fgp-backup';
if (!file_exists($FGP_BACKUP_DIR)) {
    mkdir($FGP_BACKUP_DIR, 0755, true);
}

/* ----------------------------------------------------
   ADMIN PAGE: Backup WP
---------------------------------------------------- */
?>

<div class="wrap">
    <h1>FGP Backup WP</h1>

    <?php
    // Handle actions
    if (isset($_POST['run_backup'])) {
        fgp_backup_wp_run_backup();
    }

    if (isset($_GET['delete'])) {
        fgp_backup_wp_delete($_GET['delete']);
    }

    if (isset($_GET['restore_zip'])) {
        fgp_backup_wp_restore($_GET['restore_zip']);
    }

    if (isset($_GET['restore_db'])) {
        fgp_backup_wp_restore_db($_GET['restore_db']);
    }
    ?>

    <form method="post">
        <p>This tool creates:</p>
        <ul>
            <li>📁 ZIP backup of <strong>/wp-content</strong></li>
            <li>🗄️ SQL database dump (outside ZIP)</li>
        </ul>

        <button type="submit" class="button button-primary" name="run_backup">
            Create Backup
        </button>
    </form>

    <hr>

    <h2>Existing Backups</h2>

    <?php fgp_backup_wp_list_backups(); ?>

</div>

<?php
/* ----------------------------------------------------
   CREATE BACKUP
---------------------------------------------------- */
function fgp_backup_wp_run_backup() {
    $backup_dir = WP_CONTENT_DIR . '/fgp-backup';
    $timestamp  = date('Y-m-d_H-i-s');

    $zip_filename = "$backup_dir/backup-$timestamp.zip";
    $db_filename  = "$backup_dir/db-$timestamp.sql";

    $internal_folder = "wp-content-fgp-backup";
    $exclude_dir = realpath($backup_dir);
    $plugin_dir  = realpath(WP_PLUGIN_DIR . '/fgp-tools');

    /* 1️⃣ Dump Database */
    $cmd = sprintf(
        'mysqldump --user=%s --password=%s --host=%s %s > %s',
        escapeshellarg(DB_USER),
        escapeshellarg(DB_PASSWORD),
        escapeshellarg(DB_HOST),
        escapeshellarg(DB_NAME),
        escapeshellarg($db_filename)
    );
    shell_exec($cmd);

    /* 2️⃣ Create ZIP */
    if (class_exists('ZipArchive')) {

        $zip = new ZipArchive();
        if ($zip->open($zip_filename, ZipArchive::CREATE) !== TRUE) {
            echo "<div class='notice notice-error'><p>Error creating ZIP file.</p></div>";
            return;
        }

        $source = realpath(WP_CONTENT_DIR);

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $file = realpath($file);

            // Prevent recursive backup
            if (strpos($file, $exclude_dir) === 0) continue;
            if (strpos($file, $plugin_dir) === 0) continue;

            if (is_dir($file)) continue;

            $relativePath = substr($file, strlen($source) + 1);

            $zip->addFile(
                $file,
                "$internal_folder/$relativePath"
            );
        }

        $zip->close();

    } else {

        require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';

        $archive = new PclZip($zip_filename);

        $archive->create(
            WP_CONTENT_DIR,
            PCLZIP_OPT_REMOVE_PATH, WP_CONTENT_DIR,
            PCLZIP_OPT_ADD_PATH, $internal_folder
        );
    }

    echo "<div class='notice notice-success'><p>
        Backup created.<br><br>
        <strong>ZIP File:</strong> 
        <a href='" . content_url("fgp-backup/backup-$timestamp.zip") . "' download>
            Download ZIP
        </a><br>
        <strong>Database File:</strong> 
        <a href='" . content_url("fgp-backup/db-$timestamp.sql") . "' download>
            Download SQL
        </a>
    </p></div>";
}

/* ----------------------------------------------------
   LIST BACKUPS
---------------------------------------------------- */
function fgp_backup_wp_list_backups() {

    $backup_dir = WP_CONTENT_DIR . '/fgp-backup';
    $files = scandir($backup_dir);

    if (!$files) {
        echo "<p>No backups found.</p>";
        return;
    }

    echo "<table class='widefat'>";
    echo "<thead><tr>
            <th>File</th>
            <th>Actions</th>
          </tr></thead><tbody>";

    foreach ($files as $file) {

        if ($file === '.' || $file === '..') continue;

        $url = content_url("fgp-backup/$file");

        echo "<tr>
            <td>$file</td>
            <td>
                <a class='button' href='$url' download>Download</a>
        ";

        if (str_ends_with($file, '.zip')) {
            echo "<a class='button' href='?page=fgp-tools-backup&restore_zip=$file'>Restore Files</a> ";
        }

        if (str_ends_with($file, '.sql')) {
            echo "<a class='button' href='?page=fgp-tools-backup&restore_db=$file'>Restore DB</a> ";
        }

        echo "
                <a class='button button-danger' 
                   href='?page=fgp-tools-backup&delete=$file'
                   onclick='return confirm(\"Delete this backup?\")'>
                    Delete
                </a>
            </td>
        </tr>";
    }

    echo "</tbody></table>";
}

/* ----------------------------------------------------
   DELETE BACKUP
---------------------------------------------------- */
function fgp_backup_wp_delete($file) {
    $path = WP_CONTENT_DIR . "/fgp-backup/" . basename($file);
    if (file_exists($path)) unlink($path);

    echo "<div class='notice notice-success'><p>Backup deleted.</p></div>";
}

/* ----------------------------------------------------
   RESTORE FILES FROM ZIP
---------------------------------------------------- */
function fgp_backup_wp_restore($zip_file) {

    $zip_path = WP_CONTENT_DIR . "/fgp-backup/" . basename($zip_file);

    if (!file_exists($zip_path)) {
        echo "<div class='notice notice-error'><p>ZIP file not found.</p></div>";
        return;
    }

    $temp_dir = WP_CONTENT_DIR . '/fgp-backup-temp';

    if (file_exists($temp_dir)) fgp_delete_dir($temp_dir);
    mkdir($temp_dir, 0755);

    /* Extract ZIP */
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive;
        $zip->open($zip_path);
        $zip->extractTo($temp_dir);
        $zip->close();
    } else {
        require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
        $archive = new PclZip($zip_path);
        $archive->extract(PCLZIP_OPT_PATH, $temp_dir);
    }

    /* Restore files */
    $source = $temp_dir . '/wp-content-fgp-backup';
    fgp_recursive_copy($source, WP_CONTENT_DIR);

    fgp_delete_dir($temp_dir);

    echo "<div class='notice notice-success'><p>Files restored successfully.</p></div>";
}

/* ----------------------------------------------------
   RESTORE DATABASE
---------------------------------------------------- */
function fgp_backup_wp_restore_db($sql_file) {

    $path = WP_CONTENT_DIR . "/fgp-backup/" . basename($sql_file);

    if (!file_exists($path)) {
        echo "<div class='notice notice-error'><p>SQL file not found.</p></div>";
        return;
    }

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

/* ----------------------------------------------------
   RECURSIVE COPY
---------------------------------------------------- */
function fgp_recursive_copy($src, $dst) {
    if (!is_dir($src)) return;

    @mkdir($dst, 0755);
    $dir = opendir($src);

    while (($file = readdir($dir)) !== false) {

        if ($file === '.' || $file === '..') continue;

        // Avoid overwriting your plugin directory
        if (strpos($file, 'fgp-tools') !== false) continue;

        $src_path = "$src/$file";
        $dst_path = "$dst/$file";

        if (is_dir($src_path)) {
            fgp_recursive_copy($src_path, $dst_path);
        } else {
            @copy($src_path, $dst_path);
        }
    }

    closedir($dir);
}

/* ----------------------------------------------------
   DELETE FOLDER RECURSIVELY
---------------------------------------------------- */
function fgp_delete_dir($dir) {
    if (!file_exists($dir)) return;

    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {
        $path = "$dir/$file";
        if (is_dir($path)) {
            fgp_delete_dir($path);
        } else {
            unlink($path);
        }
    }

    rmdir($dir);
}
