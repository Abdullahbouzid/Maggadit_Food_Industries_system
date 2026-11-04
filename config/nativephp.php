<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App Version Name
    |--------------------------------------------------------------------------
    |
    | This is the human-readable version of your app (e.g. "1.0.0"). It is
    | used as the versionName in Android builds and may be displayed in
    | the app or console to determine the current app release version.
    |
    */

    'version' => env('NATIVEPHP_APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | App Version Code
    |--------------------------------------------------------------------------
    |
    | This is the internal numeric version code used for Play Store builds.
    | It must increase with every release. This is used as versionCode in
    | Android builds and is required for publishing updates to the store.
    |
    */

    'version_code' => env('NATIVEPHP_APP_VERSION_CODE', 1),

    /*
    |--------------------------------------------------------------------------
    | App ID
    |--------------------------------------------------------------------------
    |
    | This is the unique ID of your application used by Android to identify
    | the app package. It is typically written in reverse domain format,
    | such as "com.nativephp.app".
    |
    */

    'app_id' => env('NATIVEPHP_APP_ID'),

    /*
    |--------------------------------------------------------------------------
    | Deeplink Scheme
    |--------------------------------------------------------------------------
    |
    | The deep link scheme to use for opening your app from URLs. For
    | example, using the scheme "nativephp" allows links like:
    | nativephp://some/path to open the app directly.
    |
    */

    'deeplink_scheme' => env('NATIVEPHP_DEEPLINK_SCHEME'),

    /*
    |--------------------------------------------------------------------------
    | Deeplink Host
    |--------------------------------------------------------------------------
    |
    | The domain name to associate with verified HTTPS links and NFC tags.
    | This allows URLs like https://your-host.com/path to open your app
    | when tapped from an NFC tag or clicked from the browser.
    |
    */

    'deeplink_host' => env('NATIVEPHP_DEEPLINK_HOST'),

    /*
    |--------------------------------------------------------------------------
    | Start URL
    |--------------------------------------------------------------------------
    |
    | The initial URL/path to load when the app starts. This should be a
    | path relative to the app root (e.g., "/dashboard", "/onboarding").
    | If not set, the app will load the root path ("/").
    |
    */

    //     'start_url' => env('NATIVEPHP_START_URL', '/'),
    'start_url' => env('NATIVEPHP_START_URL', 'https://blue-beaver-923812.hostingersite.com/mobile/login'),
    /*|--------------------------------------------------------------------------
    | Development Team (iOS)
    |--------------------------------------------------------------------------
    |
    | The Apple Developer Team ID to use for code signing iOS apps. This is
    | automatically detected from your installed certificates, but you can
    | override it here if needed. Find your Team ID in your Apple Developer
    | account under Membership details.
    |
    */
    'development_team' => env('NATIVEPHP_DEVELOPMENT_TEAM'),

    /*
    |--------------------------------------------------------------------------
    | iOS Permission Strings (Info.plist Overrides)
    |--------------------------------------------------------------------------
    |
    | Override iOS Info.plist usage descriptions provided by plugins. Anything
    | you set here is applied AFTER all plugin manifests are merged, so it
    | always wins — useful when multiple plugins claim the same key (e.g.
    | mobile-camera and mobile-scanner both set NSCameraUsageDescription) and
    | you want a single explicit string for App Store review.
    |
    | Android has no equivalent: permission rationale is shown by app code at
    | runtime, not declared in the manifest, so this block is iOS-only.
    |
    */

    'permissions' => [
        // 'NSCameraUsageDescription' => 'Used to take a profile photo.',
        // 'NSMicrophoneUsageDescription' => 'Used to record audio with your videos.',
        // 'NSPhotoLibraryUsageDescription' => 'Used to select photos for your post.',
        'push_notifications' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Keys to Clean Up
    |--------------------------------------------------------------------------
    |
    | These are keys that will be removed from the .env file during app
    | bundling to prevent secrets or development credentials from being
    | leaked. Wildcards are supported (e.g. AWS_* or *_SECRET).
    |
    */

    'cleanup_env_keys' => [
        'AWS_*',
        'GITHUB_*',
        'DO_SPACES_*',
        '*_SECRET',
    ],

    /*
    |--------------------------------------------------------------------------
    | Files to Exclude Before Bundling
    |--------------------------------------------------------------------------
    |
    | These files and folders will be removed before the final bundle is
    | built for production. You may use glob/wildcard patterns here to
    | skip unnecessary assets like logs, sessions, or temp data.
    |
    */

    'cleanup_exclude_files' => [
        'storage/framework/sessions',
        'storage/framework/cache',
        'storage/framework/testing',
        'storage/logs/laravel.log',
        'storage/logs/*.log',
        'vendor/*/tests',
        'vendor/*/test',
        'vendor/*/Tests',
        'vendor/*/docs',
        'vendor/*/examples',
        'vendor/*/example',
        'vendor/*/*.md',
        'vendor/*/*.txt',
        'vendor/*/README*',
        'vendor/*/CHANGELOG*',
        'vendor/*/LICENSE*',
        'vendor/*/composer.json',
        'vendor/symfony/*/Tests',
        'vendor/nikic/php-parser/lib/PHPParser/Tests',
        'vendor/doctrine/*/tests',
        'vendor/doctrine/*/Tests',
        'vendor/phar-io/manifest',
        'vendor/phar-io/version',
        'vendor/sebastian/*/resources',
        'vendor/sebastian/*/tests',
        'vendor/sebastian/*/Tests',
        'vendor/mockery/mockery/library/Mockery/Tests',
        'vendor/phpunit/*/tests',
        'vendor/phpunit/*/Tests',
        'vendor/fakerphp/faker/src/Faker/Provider/*/tests',
        'vendor/guzzlehttp/*/tests',
        'vendor/guzzlehttp/*/Tests',
        'vendor/ramsey/uuid/tests',
        'vendor/nesbot/carbon/tests',
        'vendor/nesbot/carbon/Tests',
        'vendor/psr/*/tests',
        'vendor/league/*/tests',
        'vendor/league/*/Tests',
        'vendor/league/*/resources',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOSqlite',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/SQLSrv',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/OCI8',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/IBM_DB2',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOMySql',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOPostgreSQL',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOOCI',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDOSQLSRV',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/PDODBlib',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Platforms/*Sqlite*',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Platforms/*SQLServer*',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Platforms/*Oracle*',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Platforms/*PostgreSQL*',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Platforms/*MySQL*',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Schema/Visitors/DropSchemaSqlCollector.php',
        'vendor/doctrine/dbal/lib/Doctrine/DBAL/Schema/Visitors/CreateSchemaSqlCollector.php',
        'vendor/monolog/monolog/src/Monolog/Handler/*Test*',
        'vendor/illuminate/redis/Illuminate/Redis/Tests',
        'vendor/illuminate/mail/Illuminate/Mail/Tests',
        'vendor/illuminate/notifications/Illuminate/Notifications/Tests',
        'vendor/illuminate/queue/Illuminate/Queue/Tests',
        'vendor/illuminate/events/Illuminate/Events/Tests',
        'vendor/illuminate/broadcasting/Illuminate/Broadcasting/Tests',
        'vendor/illuminate/bus/Illuminate/Bus/Tests',
        'vendor/illuminate/pipeline/Illuminate/Pipeline/Tests',
        'vendor/illuminate/console/Illuminate/Console/Tests',
        'vendor/illuminate/validation/Illuminate/Validation/Tests',
        'vendor/illuminate/auth/Illuminate/Auth/Tests',
        'vendor/illuminate/session/Illuminate/Session/Tests',
        'vendor/illuminate/cache/Illuminate/Cache/Tests',
        'vendor/illuminate/filesystem/Illuminate/Filesystem/Tests',
        'vendor/illuminate/hashing/Illuminate/Hashing/Tests',
        'vendor/illuminate/translation/Illuminate/Translation/Tests',
        'vendor/illuminate/pagination/Illuminate/Pagination/Tests',
        'vendor/illuminate/database/Illuminate/Database/Tests',
        'vendor/illuminate/support/Illuminate/Support/Tests',
        'vendor/illuminate/collections/Illuminate/Collections/Tests',
        'vendor/illuminate/container/Illuminate/Container/Tests',
        'vendor/illuminate/contracts/Illuminate/Contracts/Tests',
        'vendor/illuminate/macroable/Illuminate/Macroable/Tests',
        // Exclude large directories that shouldn't be in the bundle
        'nativephp',
        'nativephp/**',
        'node_modules',
        'node_modules/**',
        '.git',
        '.git/**',
        'apk-output',
        'apk-output/**',
        '*.apk',
        '*.log',
        '*.sqlite',
        '*.db',
    ],

    /*
    |--------------------------------------------------------------------------
    | Runtime Configuration
    |--------------------------------------------------------------------------
    |
    | Controls the persistent PHP runtime behavior. In 'persistent' mode,
    | Laravel boots once and the kernel is reused across requests (~5-30ms
    | per dispatch instead of ~200-300ms). Falls back to 'classic' mode
    | (full init/shutdown per request) if persistent boot fails.
    |
    */

    'runtime' => [
        'mode' => 'classic', // 'classic' or 'persistent' - using classic to avoid "incomplete object" errors with stdClass objects
        'reset_instances' => true,
        'gc_between_dispatches' => false,
    ],

    'android' => [
        'gradle_jdk_path' => env('NATIVEPHP_GRADLE_PATH'),
        'android_sdk_path' => env('NATIVEPHP_ANDROID_SDK_LOCATION'),
        'emulator_path' => env('ANDROID_EMULATOR'),
        '7zip-location' => env('NATIVEPHP_7ZIP_LOCATION', 'C:\\Program Files\\7-Zip\\7z.exe'),

        /*
        |--------------------------------------------------------------------------
        | Android SDK Versions
        |--------------------------------------------------------------------------
        |
        | Configure the Android SDK versions for your app build. These control
        | which Android versions your app can run on and which APIs are available.
        |
        | compile_sdk: The SDK version used to compile your app (latest features)
        | min_sdk:     The minimum Android version your app supports
        | target_sdk:  The SDK version your app is designed and tested for
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Status Bar Style
        |--------------------------------------------------------------------------
        |
        | Set the color of the status bar and navigation bar icons.
        | Options: 'auto'  - Auto-detect from system theme (recommended)
        |          'light' - Light/white icons
        |          'dark'  - Dark icons
        |
        */
        'status_bar_style' => 'auto',

        /*
        |--------------------------------------------------------------------------
        | Android Build Configuration
        |--------------------------------------------------------------------------
        |
        | These options control how your Android app is built and optimized.
        | The defaults maintain current behavior while allowing customization
        | for production builds, debugging, and app store optimization.
        |
        */
        'build' => [
            // R8/ProGuard Configuration - optimized for production
            'minify_enabled' => env('NATIVEPHP_ANDROID_MINIFY_ENABLED', true),
            'shrink_resources' => env('NATIVEPHP_ANDROID_SHRINK_RESOURCES', true),
            'obfuscate' => env('NATIVEPHP_ANDROID_OBFUSCATE', true),

            // Debug Symbol Configuration - disabled for production build
            'debug_symbols' => env('NATIVEPHP_ANDROID_DEBUG_SYMBOLS', 'NONE'),
            'generate_mapping_files' => env('NATIVEPHP_ANDROID_MAPPING_FILES', false),
            'mapping_file_path' => env('NATIVEPHP_ANDROID_MAPPING_PATH', 'build/outputs/mapping/reten/'),

            // ProGuard Rules - configured for production
            'keep_line_numbers' => env('NATIVEPHP_ANDROID_KEEP_LINE_NUMBERS', false),
            'keep_source_file' => env('NATIVEPHP_ANDROID_KEEP_SOURCE_FILE', false),
            'custom_proguard_rules' => env('NATIVEPHP_ANDROID_CUSTOM_PROGUARD_RULES', []),

            // Build Performance - using Gradle defaults
            'parallel_builds' => env('NATIVEPHP_ANDROID_PARALLEL_BUILDS', true),
            'incremental_builds' => env('NATIVEPHP_ANDROID_INCREMENTAL_BUILDS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the NativePHP development server that allows hot
    | reloading of mobile applications during development.
    |
    */

    'server' => [
        // HTTP server port for serving the app
        'http_port' => env('NATIVEPHP_HTTP_PORT', 3000),

        // WebSocket server port for hot reload communication
        'ws_port' => env('NATIVEPHP_WS_PORT', 8081),

        // Service name advertised on the network
        'service_name' => env('NATIVEPHP_SERVICE_NAME', 'NativePHP Server'),

        // Service type for mDNS advertisement
        'service_type' => '_http._tcp',

        // Public directory to serve (relative to Laravel root)
        'public_path' => env('NATIVEPHP_PUBLIC_PATH', 'public'),

        // Build output directory (where the ZIP will be created)
        'build_path' => env('NATIVEPHP_BUILD_PATH', 'storage/app/native-build'),

        // Automatically open browser with QR code when server starts
        'open_browser' => env('NATIVEPHP_OPEN_BROWSER', true),

        // Watch these directories for changes
        'watch_paths' => [
            'app',
            'resources',
            'routes',
            'public/build',
        ],

        // File extensions to watch for changes
        'watch_extensions' => ['php', 'blade.php', 'js', 'css', 'ts', 'vue', 'json'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Hot Reload Configuration
    |--------------------------------------------------------------------------
    */
    'hot_reload' => [
        'watch_paths' => [
            'app',
            'resources',
            'routes',
            'config',
            'public',
        ],

        'exclude_patterns' => [
            '\.git',
            'storage',
            'tests',
            'nativephp',
            'credentials',
            'node_modules',
            '\.swp',
            '\.tmp',
            '~',
            '\.log',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | App Store Connect API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for uploading apps to App Store Connect using the API.
    | These values are used for automated uploads during the package process.
    | Store sensitive data in environment variables for security.
    |
    */
    'app_store_connect' => [
        'api_key' => env('APP_STORE_API_KEY'),
        'api_key_id' => env('APP_STORE_API_KEY_ID'),
        'api_issuer_id' => env('APP_STORE_API_ISSUER_ID'),
        'app_name' => env('APP_STORE_APP_NAME'),
    ],

    /*
    |--------------------------------------------------------------------------
    | iPad Support
    |--------------------------------------------------------------------------
    |
    | Enable or disable iPad support for your iOS app. When enabled, your app
    | will support iPad devices and all iPad orientations (portrait, upside down,
    | landscape left, and landscape right) as required by Apple's App Store
    | guidelines. When disabled, your app will be iPhone-only.
    |
    | Note: Once an app is deployed to the App Store with iPad
    | support you cannot revoke this action.
    |
    */
    'ipad' => false,

    /*
    |--------------------------------------------------------------------------
    | Device Orientation Support
    |--------------------------------------------------------------------------
    |
    | Configure which orientations your app supports on different devices.
    | This will be applied during the build process to set appropriate
    | constraints in Info.plist (iOS) and AndroidManifest.xml (Android).
    |
    | For iPhone and Android, you can configure specific orientations.
    | For iPad, when enabled above, all orientations are automatically supported
    | as required by Apple's App Store guidelines.
    |
    | If all orientations are false for iPhone, the build will fail with a
    | helpful error message. If all orientations are false for Android, the
    | build will fail with a helpful error message.
    |
    */
    'orientation' => [
        'iphone' => [
            'portrait' => true,
            'upside_down' => false,
            'landscape_left' => false,
            'landscape_right' => false,
        ],
        'android' => [
            'portrait' => true,
            'upside_down' => false,
            'landscape_left' => false,
            'landscape_right' => false,
        ],
    ],
];
