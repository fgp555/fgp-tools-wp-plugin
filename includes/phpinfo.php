<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1>PHP Info</h1>

    <?php
    ob_start();
    phpinfo();
    $phpinfo = ob_get_clean();

    // Clean HTML for WordPress
    $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
    ?>

    <div style="background:#fff; padding:20px;">
        <?php echo $phpinfo; ?>
    </div>
</div>
