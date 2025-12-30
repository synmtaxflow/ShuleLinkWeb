<?php
/**
 * Check if PHP Sockets extension is enabled
 * Access this file via browser: http://localhost/shuleLink/public/check_sockets.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Sockets Extension Check</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #940000; padding-bottom: 10px; }
        .status { padding: 15px; margin: 10px 0; border-radius: 5px; font-weight: bold; }
        .enabled { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .disabled { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .info strong { display: block; margin-bottom: 5px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        ul { margin: 10px 0; padding-left: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 PHP Sockets Extension Check</h1>
        
        <?php
        $socketsLoaded = extension_loaded('sockets');
        $socketCreateExists = function_exists('socket_create');
        $phpIniPath = php_ini_loaded_file();
        $phpIniScanned = php_ini_scanned_files();
        ?>
        
        <div class="status <?php echo $socketsLoaded ? 'enabled' : 'disabled'; ?>">
            <?php if ($socketsLoaded): ?>
                ✅ <strong>Sockets Extension: ENABLED</strong>
            <?php else: ?>
                ❌ <strong>Sockets Extension: DISABLED</strong>
            <?php endif; ?>
        </div>
        
        <div class="status <?php echo $socketCreateExists ? 'enabled' : 'disabled'; ?>">
            <?php if ($socketCreateExists): ?>
                ✅ <strong>socket_create() Function: AVAILABLE</strong>
            <?php else: ?>
                ❌ <strong>socket_create() Function: NOT AVAILABLE</strong>
            <?php endif; ?>
        </div>
        
        <div class="info">
            <strong>PHP Configuration:</strong>
            <ul>
                <li><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></li>
                <li><strong>Loaded php.ini:</strong> <code><?php echo $phpIniPath ?: 'Not found'; ?></code></li>
                <li><strong>Additional .ini files:</strong> <?php echo $phpIniScanned ?: 'None'; ?></li>
                <li><strong>Server API:</strong> <?php echo php_sapi_name(); ?></li>
            </ul>
        </div>
        
        <?php if (!$socketsLoaded || !$socketCreateExists): ?>
        <div class="info">
            <strong>⚠️ How to Enable Sockets Extension:</strong>
            <ol>
                <li>Open <code><?php echo $phpIniPath ?: 'php.ini'; ?></code></li>
                <li>Find the line: <code>;extension=sockets</code></li>
                <li>Remove the semicolon: <code>extension=sockets</code></li>
                <li>Save the file</li>
                <li><strong>Restart Apache</strong> in XAMPP Control Panel</li>
                <li>Refresh this page to verify</li>
            </ol>
        </div>
        <?php else: ?>
        <div class="info">
            <strong>✅ Everything is configured correctly!</strong>
            <p>Sockets extension is enabled and ready to use with ZKTeco devices.</p>
        </div>
        <?php endif; ?>
        
        <div class="info">
            <strong>Test Socket Functions:</strong>
            <ul>
                <li>socket_create(): <?php echo function_exists('socket_create') ? '✅ Available' : '❌ Not Available'; ?></li>
                <li>socket_connect(): <?php echo function_exists('socket_connect') ? '✅ Available' : '❌ Not Available'; ?></li>
                <li>socket_close(): <?php echo function_exists('socket_close') ? '✅ Available' : '❌ Not Available'; ?></li>
            </ul>
        </div>
    </div>
</body>
</html>

