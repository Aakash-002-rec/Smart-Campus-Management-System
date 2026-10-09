<?php
/**
 * Smart Campus Management System - Private Database Configuration Template
 * 
 * INSTRUCTIONS:
 * 1. Copy this file and rename it to 'db_credentials.php' in the same folder:
 *    backend/config/db_credentials.php
 * 2. Enter your hosted MySQL database details under 'production'.
 * 3. In local XAMPP development, the 'local' credentials below are used automatically.
 * 4. 'db_credentials.php' is ignored by Git, ensuring private credentials are never pushed.
 */

return [
    // Production / Hosted MySQL Configuration
    'production' => [
        'host'     => 'your-db-host.com',
        'port'     => 3306,
        'database' => 'smart_campus',
        'user'     => 'your_db_username',
        'password' => 'YOUR_DB_PASSWORD_HERE',
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
     * - 'production' : Forces hosted production database
     */
    'environment' => 'auto',
];
