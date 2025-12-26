<?php
/**
 * Automated Database Backup Script
 * Run this via Windows Task Scheduler or Cron
 * Command: php d:\lap trinh kiem com\Web\WebSec\backup_script.php
 */

// 1. CONFIGURATION
$dbHost = '127.0.0.1'; // Use IP to force TCP/IP and avoid socket/plugin issues
$dbUser = 'root'; // Must use ROOT or high-privilege user for backups
$dbPass = 'tuanduongne2004';
$dbName = 'store';

// Path to mysqldump in XAMPP (Adjust if your XAMPP is elsewhere)
$mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

// Backup directory
$backupDir = __DIR__ . '/backups';
$retentionDays = 7; // Keep backups for 7 days

// ==========================================

// Security Check: Only allow running from Command Line (CLI)
if (php_sapi_name() !== 'cli') {
    die("SECURITY ALERT: This script can only be run from the command line.");
}

// Ensure backup directory exists
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// 2. CREATE BACKUP
$date = date('Y-m-d_H-i-s');
$filename = "backup_{$dbName}_{$date}.sql";
$filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

// Build command
// We cd to the bin directory first to ensure DLLs are found
$command = sprintf(
    'cd /d "C:\\xampp\\mysql\\bin" && mysqldump --user=%s --password=%s --host=%s --single-transaction --routines --triggers %s > "%s"',
    escapeshellarg($dbUser),
    escapeshellarg($dbPass),
    escapeshellarg($dbHost),
    escapeshellarg($dbName),
    $filepath
);

echo "Starting backup for database '$dbName'...\n";
exec($command, $output, $returnVar);

if ($returnVar === 0) {
    echo "[SUCCESS] Backup created: $filename\n";
    
    // Optional: Zip the file to save space
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        $zipFilename = $filepath . '.zip';
        if ($zip->open($zipFilename, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($filepath, $filename);
            $zip->close();
            unlink($filepath); // Delete raw SQL after zipping
            echo "[SUCCESS] Compressed to: {$filename}.zip\n";
        }
    }
} else {
    echo "[ERROR] Backup failed! Return code: $returnVar\n";
    exit(1);
}

// 3. CLEANUP OLD BACKUPS
echo "Checking for old backups (Retention: $retentionDays days)...\n";
$files = glob($backupDir . '/*.zip'); // Or *.sql if not zipping
$now = time();

foreach ($files as $file) {
    if (is_file($file)) {
        if ($now - filemtime($file) >= 60 * 60 * 24 * $retentionDays) {
            unlink($file);
            echo "[CLEANUP] Deleted old backup: " . basename($file) . "\n";
        }
    }
}

echo "Backup process completed.\n";
?>