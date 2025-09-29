<?php
// QuikAPI bootstrap: autoload, env, config, container

// PSR-4 autoload via Composer if present
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
}

// Lightweight fallback autoloader (if Composer not yet installed)
spl_autoload_register(function ($class) {
    $prefix = 'QuikAPI\\';
    $baseDir = __DIR__ . DIRECTORY_SEPARATOR;
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // not our namespace
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// Global container
if (!isset($GLOBALS['quikapi'])) {
    $GLOBALS['quikapi'] = [
        'config' => [
            'cors' => [
                'allow_origin' => getenv('CORS_ALLOW_ORIGIN') ?: '*',
                'allow_methods' => getenv('CORS_ALLOW_METHODS') ?: 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                'allow_headers' => getenv('CORS_ALLOW_HEADERS') ?: 'Content-Type, Authorization, X-Requested-With',
                'allow_credentials' => getenv('CORS_ALLOW_CREDENTIALS') ?: 'true',
                'max_age' => getenv('CORS_MAX_AGE') ?: '86400'
            ],
        ],
    ];
}

// Load env if Dotenv is available
if (class_exists('Dotenv\\Dotenv')) {
    $root = dirname(__DIR__);
    if (is_file($root . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable($root);
        $dotenv->safeLoad();
    }
}

// Setup Eloquent ORM if illuminate/database is installed
if (class_exists('Illuminate\\Database\\Capsule\\Manager')) {
    $capsule = new Illuminate\Database\Capsule\Manager();
    $dsn = getenv('DB_DSN');
    if ($dsn) {
        $capsule->addConnection([
            'driver' => str_starts_with($dsn, 'mysql:') ? 'mysql' : 'pgsql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'database' => getenv('DB_NAME') ?: '',
            'username' => getenv('DB_USER') ?: '',
            'password' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'sticky' => true,
            // Fallback if DSN only provided
            'driver_options' => [],
        ]);
    }
    $capsule->setEventDispatcher(new Illuminate\Events\Dispatcher(new Illuminate\Container\Container()));
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
}

// Setup Validator if illuminate/validation is installed
if (class_exists('Illuminate\\Validation\\Factory')) {
    $translator = new Illuminate\Translation\Translator(new Illuminate\Translation\ArrayLoader(), 'en');
    $validator = new Illuminate\Validation\Factory($translator);
    $GLOBALS['quikapi']['validator'] = $validator;
}
