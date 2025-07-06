#!/usr/bin/php
<?php

/**
 * imapsync-php-automation
 *
 * A PHP script to read a CSV file and execute imapsync for each entry.
 * Now supports domain-level default options via a `_xferoptions@domain` user.
 *
 * @author Gemini
 * @version 2.0
 */

// --- Configuration ---
$csvFile = 'xfer.csv';
// Path to the imapsync executable
$imapsyncPath = 'imapsync';
$defaultOptionsIdentifier = '_xferoptions@';
// --- End Configuration ---

// --- Main Execution Logic ---

// 1. Validate that the imapsync executable is available
if (!is_executable($imapsyncPath) && !executable_in_path($imapsyncPath)) {
    echo "Error: The imapsync command '{$imapsyncPath}' was not found or is not executable.\n";
    echo "Please check the \$imapsyncPath variable or ensure imapsync is in your system's PATH.\n";
    exit(1);
}

// 2. Validate CSV file readability
if (!file_exists($csvFile) || !is_readable($csvFile)) {
    echo "Error: The CSV file '{$csvFile}' was not found or is not readable.\n";
    echo "Please ensure the file exists and has the correct permissions.\n";
    exit(1);
}

// 3. Pre-scan CSV to find and store domain-specific default options
$domainDefaults = [];
if (($handle = fopen($csvFile, "r")) !== false) {
    while (($data = fgetcsv($handle)) !== false) {
        $user = $data[1] ?? '';
        if (strpos($user, $defaultOptionsIdentifier) === 0) {
            $domain = substr($user, strlen($defaultOptionsIdentifier));
            $options = $data[6] ?? '';
            if (!empty($domain) && !empty($options)) {
                $domainDefaults[$domain] = $options;
                echo "Found default options for domain '{$domain}': {$options}\n";
            }
        }
    }
    fclose($handle);
}
echo "Default options scan complete. Starting migrations...\n";


// 4. Open and Process the CSV for migrations
$handle = fopen($csvFile, "r");
if ($handle === false) {
    echo "Error: Failed to open the CSV file for reading a second time.\n";
    exit(1);
}

$rowNumber = 0;
while (($data = fgetcsv($handle)) !== false) {
    $rowNumber++;

    // Skip the header row (first row)
    if ($rowNumber == 1) {
        continue;
    }

    // Assign CSV columns to variables for clarity
    list(
        $source_host, 
        $source_user, 
        $source_pass, 
        $dest_host, 
        $dest_user, 
        $dest_pass
    ) = $data;
    $user_specific_options = isset($data[6]) ? trim($data[6]) : '';

    // Skip the special options users in the main processing loop
    if (strpos($source_user, $defaultOptionsIdentifier) === 0) {
        continue;
    }
    
    if($source_host[0]=='#')
        continue;
    // Basic validation to ensure key fields are not empty
    if (empty($source_host) || empty($source_user) || empty($source_pass) || empty($dest_host) || empty($dest_user) || empty($dest_pass)) {
        echo "--------------------------------------------------\n";
        echo "Warning: Skipping row #{$rowNumber} ({$source_user}) due to missing required data.\n";
        echo "--------------------------------------------------\n";
        continue;
    }
    
    echo "==================================================\n";
    echo "Processing User: {$source_user}\n";
    echo "==================================================\n";

    // 5. Determine final imapsync options by merging defaults with specific options
    $source_domain = substr(strrchr($source_user, "@"), 1);
    $base_options = $domainDefaults[$source_domain] ?? '';
    $final_options = trim($base_options . ' ' . $user_specific_options);

    echo "Applied Options: {$final_options}\n";

    // 6. Construct the imapsync command securely
    $command = $imapsyncPath .
        ' --host1 ' . escapeshellarg($source_host) .
        ' --user1 ' . escapeshellarg($source_user) .
        ' --password1 ' . escapeshellarg($source_pass)  .
        ' --host2 ' . escapeshellarg($dest_host) .
        ' --user2 ' . escapeshellarg($dest_user) .
        ' --password2 ' . escapeshellarg($dest_pass)  .
        ' ' . $final_options; // Append the combined final options

    echo "Executing command...\n";
    
    // 7. Execute the command and capture output
    system($command,$retval);

    echo "--- imapsync output ---\n";
//    echo $output;
    echo "retval=".(int)$retval."\n";
    echo "--- End of output for {$source_user} ---\n\n";
}

fclose($handle);
echo "Batch process completed.\n";

/**
 * Helper function to check if a command exists in the system's PATH.
 *
 * @param string $command The command to check.
 * @return bool True if the command is found and executable, false otherwise.
 */
function executable_in_path(string $command): bool
{
    $paths = explode(PATH_SEPARATOR, getenv('PATH'));
    foreach ($paths as $path) {
        $file = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $command;
        if (is_executable($file)) {
            return true;
        }
    }
    return false;
}
