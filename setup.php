<?php
/**
 * SkillBridge Installation & Setup Script
 * Run this file once to set up the database and verify installation
 * Usage: php setup.php
 */

// Prevent direct web access (optional - comment out if you want web access)
if (php_sapi_name() !== 'cli' && !isset($_GET['web'])) {
    die("This script should be run from command line: php setup.php\nOr add ?web=1 to URL for web access.\n");
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║           SkillBridge Installation & Setup Script            ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$errors = [];
$warnings = [];
$success = [];

// Step 1: Check PHP Version
echo "📋 Step 1: Checking PHP version...\n";
$php_version = phpversion();
$required_version = '7.4';
if (version_compare($php_version, $required_version, '>=')) {
    echo "   ✅ PHP version: $php_version (Required: >= $required_version)\n";
    $success[] = "PHP version check passed";
} else {
    echo "   ❌ PHP version: $php_version (Required: >= $required_version)\n";
    $errors[] = "PHP version must be >= $required_version";
}
echo "\n";

// Step 2: Check Required Extensions
echo "📋 Step 2: Checking required PHP extensions...\n";
$required_extensions = ['mysqli', 'mbstring', 'json', 'session'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext extension loaded\n";
    } else {
        echo "   ❌ $ext extension not found\n";
        $errors[] = "Missing PHP extension: $ext";
    }
}
echo "\n";

// Step 3: Check Database Configuration
echo "📋 Step 3: Reading database configuration...\n";
$config_file = __DIR__ . '/config/database.php';
if (file_exists($config_file)) {
    require_once $config_file;
    echo "   ✅ Configuration file found\n";
    echo "   📝 Database Host: " . DB_HOST . "\n";
    echo "   📝 Database User: " . DB_USER . "\n";
    echo "   📝 Database Name: " . DB_NAME . "\n";
} else {
    echo "   ❌ Configuration file not found: $config_file\n";
    $errors[] = "Database configuration file missing";
    die("\n❌ Cannot proceed without configuration file.\n\n");
}
echo "\n";

// Step 4: Test Database Connection
echo "📋 Step 4: Testing database connection...\n";
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        echo "   ❌ Connection failed: " . $conn->connect_error . "\n";
        $errors[] = "Database connection failed: " . $conn->connect_error;
        echo "\n";
        echo "💡 Troubleshooting:\n";
        echo "   - Check if MySQL/MariaDB is running\n";
        echo "   - Verify credentials in config/database.php\n";
        echo "   - For macOS: brew services start mysql\n";
        echo "   - For Linux: sudo systemctl start mysql\n";
        echo "   - For Windows: Start MySQL service from Services\n";
        die("\n");
    } else {
        echo "   ✅ Database connection successful!\n";
        $success[] = "Database connection established";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $errors[] = "Database connection error: " . $e->getMessage();
    die("\n");
}
echo "\n";

// Step 5: Create Database
echo "📋 Step 5: Creating database...\n";
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql)) {
    echo "   ✅ Database '" . DB_NAME . "' created/verified!\n";
    $success[] = "Database created successfully";
} else {
    echo "   ❌ Error creating database: " . $conn->error . "\n";
    $errors[] = "Failed to create database";
    $conn->close();
    die("\n");
}
echo "\n";

// Step 6: Select Database
echo "📋 Step 6: Selecting database...\n";
if ($conn->select_db(DB_NAME)) {
    echo "   ✅ Database selected successfully!\n";
} else {
    echo "   ❌ Error selecting database: " . $conn->error . "\n";
    $errors[] = "Failed to select database";
    $conn->close();
    die("\n");
}
echo "\n";

// Step 7: Import Database Schema
echo "📋 Step 7: Importing database schema...\n";
$sql_file = __DIR__ . '/database.sql';
if (!file_exists($sql_file)) {
    echo "   ❌ SQL file not found: $sql_file\n";
    $errors[] = "Database SQL file missing";
    $conn->close();
    die("\n");
}

echo "   📄 Reading SQL file...\n";
$sql_content = file_get_contents($sql_file);

// Remove CREATE DATABASE and USE statements (already handled)
$sql_content = preg_replace('/CREATE DATABASE.*?;/is', '', $sql_content);
$sql_content = preg_replace('/USE.*?;/is', '', $sql_content);

// Split by semicolon and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

$success_count = 0;
$error_count = 0;
$skipped_count = 0;

echo "   🔄 Executing SQL statements...\n";
foreach ($statements as $index => $statement) {
    if (!empty($statement) && !preg_match('/^--/', $statement) && !preg_match('/^\/\*/', $statement)) {
        if ($conn->query($statement)) {
            $success_count++;
        } else {
            // Ignore "table already exists" errors
            if (strpos($conn->error, 'already exists') !== false || strpos($conn->error, 'Duplicate') !== false) {
                $skipped_count++;
            } else {
                echo "   ⚠️  Warning on statement " . ($index + 1) . ": " . $conn->error . "\n";
                $warnings[] = "SQL statement " . ($index + 1) . ": " . $conn->error;
                $error_count++;
            }
        }
    }
}

echo "   ✅ Successful queries: $success_count\n";
if ($skipped_count > 0) {
    echo "   ⚠️  Skipped (already exists): $skipped_count\n";
}
if ($error_count > 0) {
    echo "   ❌ Errors: $error_count\n";
} else {
    $success[] = "Database schema imported successfully";
}
echo "\n";

// Step 8: Verify Tables
echo "📋 Step 8: Verifying database tables...\n";
$required_tables = ['users', 'students', 'companies', 'internships', 'applications'];
$existing_tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $existing_tables[] = $row[0];
}

foreach ($required_tables as $table) {
    if (in_array($table, $existing_tables)) {
        echo "   ✅ Table '$table' exists\n";
    } else {
        echo "   ❌ Table '$table' missing\n";
        $errors[] = "Missing table: $table";
    }
}
echo "\n";

// Step 9: Verify Admin User
echo "📋 Step 9: Verifying admin user...\n";
$result = $conn->query("SELECT username, email, user_type FROM users WHERE user_type = 'admin'");
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "   ✅ Admin user found:\n";
    echo "      Username: " . $admin['username'] . "\n";
    echo "      Email: " . $admin['email'] . "\n";
    echo "      Password: admin123 (default)\n";
    $success[] = "Admin user verified";
} else {
    echo "   ⚠️  Admin user not found. Creating default admin...\n";
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type, status) VALUES ('admin', 'admin@skillbridge.com', ?, 'admin', 'active')");
    $stmt->bind_param("s", $hashed_password);
    if ($stmt->execute()) {
        echo "   ✅ Default admin user created!\n";
        $success[] = "Admin user created";
    } else {
        echo "   ❌ Failed to create admin user: " . $stmt->error . "\n";
        $warnings[] = "Failed to create admin user";
    }
}
echo "\n";

// Step 10: Check/Create Upload Directories
echo "📋 Step 10: Setting up upload directories...\n";
require_once __DIR__ . '/config/config.php';

$directories = [
    UPLOAD_DIR => 'Uploads',
    RESUME_DIR => 'Resumes',
    PROFILE_DIR => 'Profiles',
    LOGO_DIR => 'Logos'
];

foreach ($directories as $dir => $name) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "   ✅ Created directory: $name\n";
            $success[] = "Created $name directory";
        } else {
            echo "   ❌ Failed to create directory: $name\n";
            $errors[] = "Failed to create $name directory";
        }
    } else {
        echo "   ✅ Directory exists: $name\n";
    }
    
    // Check write permissions
    if (is_writable($dir)) {
        echo "      ✅ Write permissions: OK\n";
    } else {
        echo "      ⚠️  Write permissions: Check required\n";
        $warnings[] = "$name directory may not be writable";
    }
}
echo "\n";

// Step 11: Check Configuration
echo "📋 Step 11: Verifying configuration...\n";
$config_check = true;

// Check BASE_URL
if (defined('BASE_URL')) {
    echo "   ✅ BASE_URL: " . BASE_URL . "\n";
    if (BASE_URL == 'http://localhost/skillbridge/') {
        echo "      ⚠️  Note: Update BASE_URL in config/config.php if using different path\n";
    }
} else {
    echo "   ❌ BASE_URL not defined\n";
    $errors[] = "BASE_URL not configured";
}

// Check file paths
if (defined('UPLOAD_DIR') && file_exists(UPLOAD_DIR)) {
    echo "   ✅ Upload directory path configured\n";
} else {
    echo "   ❌ Upload directory path issue\n";
    $errors[] = "Upload directory path problem";
}
echo "\n";

// Step 12: Summary
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                      Setup Summary                          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

if (count($success) > 0) {
    echo "✅ Successful Operations (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "   • $msg\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  Warnings (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "   • $msg\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "❌ Errors (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "   • $msg\n";
    }
    echo "\n";
    echo "⚠️  Please fix the errors above before using the application.\n\n";
} else {
    echo "🎉 Setup completed successfully!\n\n";
    echo "📝 Next Steps:\n";
    echo "   1. Update BASE_URL in config/config.php if needed\n";
    echo "   2. Start PHP server: php -S localhost:8000\n";
    echo "   3. Open browser: http://localhost:8000\n";
    echo "   4. Login with admin/admin123\n\n";
    
    echo "📚 Documentation:\n";
    echo "   - README.md - Full documentation\n";
    echo "   - INSTALLATION.md - Installation guide\n";
    echo "   - QUICK_START.md - Quick start guide\n\n";
}

$conn->close();

echo "═══════════════════════════════════════════════════════════════\n\n";
?>
