<?php
/**
 * Smart Campus Management System - Private Database Configuration Template
 * 
 * INSTRUCTIONS FOR INFINITYFREE:
 * 1. Copy this file and rename it to 'db_credentials.php' in the same folder:
 *    backend/config/db_credentials.php
 * 2. Enter your actual InfinityFree MySQL vPanel password under 'production' -> 'password'.
 * 3. Save the file.
 * 4. This file ('db_credentials.php') is ignored by Git, so your password will never be exposed.
 */

return [
    // InfinityFree MySQL Cloud Configuration
    'production' => [
        'host'     => 'sql103.infinityfree.com',
        'port'     => 3306,
        'database' => 'if0_43131018_smart_campus',
        'user'     => 'if0_43131018',
        'password' => 'ENTER_YOUR_INFINITYFREE_MYSQL_PASSWORD_HERE',
    ],

    // Local XAMPP MySQL Configuration
    'local' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'smart_campus',
        'user'     => 'root',
        'password' => '',
    ],

    /**
     * Environment Selector:
     * - 'auto'       : Automatically detects localhost / 127.0.0.1 (local) vs hosted domain (production)
     * - 'local'      : Forces local XAMPP database
     * - 'production' : Forces InfinityFree MySQL database
     */
    'environment' => 'auto',
];
