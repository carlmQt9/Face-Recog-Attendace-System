<?php
/**
 * Timezone Configuration Test
 * Visit: http://localhost/FACE%20RECOGNITION%20BASED%20ATTENDANCE%20SYSTEM/public/test-timezone.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timezone Configuration Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 0;
        }
        .result {
            background: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #4CAF50;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #2196F3;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #4CAF50;
            color: white;
        }
        .expected {
            font-size: 0.9em;
            color: #888;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h1>⏰ Timezone Configuration Test</h1>
    
    <div class="card">
        <h2>🔧 Configuration Settings</h2>
        
        <div class="result">
            <span class="label">PHP Timezone:</span> 
            <span class="value"><?php echo date_default_timezone_get(); ?></span>
            <div class="expected">Expected: Asia/Manila</div>
        </div>
        
        <div class="result">
            <span class="label">Laravel App Timezone:</span> 
            <span class="value"><?php echo config('app.timezone'); ?></span>
            <div class="expected">Expected: Asia/Manila</div>
        </div>
        
        <div class="result">
            <span class="label">Database Timezone:</span> 
            <span class="value"><?php echo config('database.connections.mysql.timezone', 'Not set'); ?></span>
            <div class="expected">Expected: +08:00</div>
        </div>
    </div>
    
    <div class="card">
        <h2>🕐 Current Times</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Current Time</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PHP date()</td>
                    <td><?php echo date('Y-m-d H:i:s (D)'); ?></td>
                </tr>
                <tr>
                    <td>PHP time()</td>
                    <td><?php echo date('Y-m-d H:i:s (D)', time()); ?></td>
                </tr>
                <tr>
                    <td>Laravel now()</td>
                    <td><?php echo now()->format('Y-m-d H:i:s (D)'); ?></td>
                </tr>
                <tr>
                    <td>Carbon::now()</td>
                    <td><?php echo \Carbon\Carbon::now()->format('Y-m-d H:i:s (D)'); ?></td>
                </tr>
                <tr>
                    <td>Carbon (Manila)</td>
                    <td><?php echo \Carbon\Carbon::now('Asia/Manila')->format('Y-m-d H:i:s (D)'); ?></td>
                </tr>
                <tr>
                    <td>UTC Time</td>
                    <td><?php echo \Carbon\Carbon::now('UTC')->format('Y-m-d H:i:s (D)'); ?></td>
                </tr>
            </tbody>
        </table>
        
        <div class="expected" style="margin-top: 15px;">
            <strong>Expected:</strong> All times (except UTC) should show Philippine time (UTC+8)
        </div>
    </div>
    
    <div class="card">
        <h2>📊 Timezone Verification</h2>
        
        <?php
        $phpTz = date_default_timezone_get();
        $laravelTz = config('app.timezone');
        $dbTz = config('database.connections.mysql.timezone', null);
        
        $allCorrect = true;
        
        if ($phpTz !== 'Asia/Manila') {
            echo '<div class="result error">❌ PHP timezone is incorrect. Expected: Asia/Manila, Got: ' . $phpTz . '</div>';
            $allCorrect = false;
        } else {
            echo '<div class="result success">✅ PHP timezone is correct</div>';
        }
        
        if ($laravelTz !== 'Asia/Manila') {
            echo '<div class="result error">❌ Laravel timezone is incorrect. Expected: Asia/Manila, Got: ' . $laravelTz . '</div>';
            $allCorrect = false;
        } else {
            echo '<div class="result success">✅ Laravel timezone is correct</div>';
        }
        
        if ($dbTz !== '+08:00') {
            echo '<div class="result warning">⚠️ Database timezone is not set. Expected: +08:00, Got: ' . ($dbTz ?? 'null') . '</div>';
        } else {
            echo '<div class="result success">✅ Database timezone is correct</div>';
        }
        
        // Check if time difference between now() and UTC is 8 hours
        $nowManila = \Carbon\Carbon::now('Asia/Manila');
        $nowUtc = \Carbon\Carbon::now('UTC');
        $diffHours = $nowManila->diffInHours($nowUtc);
        
        if ($diffHours == 8) {
            echo '<div class="result success">✅ Time offset is correct (8 hours ahead of UTC)</div>';
        } else {
            echo '<div class="result error">❌ Time offset is incorrect. Expected 8 hours, Got: ' . $diffHours . ' hours</div>';
            $allCorrect = false;
        }
        
        if ($allCorrect) {
            echo '<div class="result success" style="margin-top: 20px; font-size: 1.2em;">
                🎉 All timezone settings are correct! New attendance records will have accurate timestamps.
            </div>';
        } else {
            echo '<div class="result error" style="margin-top: 20px;">
                ⚠️ Some timezone settings need correction. Please follow the instructions in TIMEZONE_FIX_INSTRUCTIONS.md
            </div>';
        }
        ?>
    </div>
    
    <div class="card">
        <h2>📝 Next Steps</h2>
        
        <div class="result">
            <strong>If times are correct:</strong>
            <ol>
                <li>Clear cache: <code>php artisan config:clear</code></li>
                <li>Clear cache: <code>php artisan cache:clear</code></li>
                <li>Restart Apache in XAMPP</li>
                <li>Test by scanning attendance</li>
            </ol>
        </div>
        
        <div class="result">
            <strong>If times are incorrect:</strong>
            <ol>
                <li>Check .env file for APP_TIMEZONE=Asia/Manila</li>
                <li>Check .env file for DB_TIMEZONE=+08:00</li>
                <li>Run <code>php artisan config:clear</code></li>
                <li>Restart Apache</li>
                <li>Refresh this page</li>
            </ol>
        </div>
    </div>
    
    <div class="card">
        <h2>ℹ️ Technical Info</h2>
        <div class="result">
            <strong>Philippine Standard Time (PST)</strong><br>
            • Timezone: Asia/Manila<br>
            • UTC Offset: +08:00<br>
            • No Daylight Saving Time<br>
            • 8 hours ahead of UTC
        </div>
    </div>
    
    <p style="text-align: center; color: #888; margin-top: 30px;">
        Generated on: <?php echo date('Y-m-d H:i:s'); ?>
    </p>
</body>
</html>
```

Let me verify the diagnostics to ensure no errors:

<function_calls>
<invoke name="get_diagnostics">
<parameter name="paths">["c:\\xampp\\htdocs\\FACE RECOGNITION BASED ATTENDANCE SYSTEM\\app\\Providers\\AppServiceProvider.php", "c:\\xampp\\htdocs\\FACE RECOGNITION BASED ATTENDANCE SYSTEM\\config\\database.php"]