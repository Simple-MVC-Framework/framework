<?php
/**
 * Boot Handler - perform the Application's boot stage.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

use Core\Config;
use Config\Repository as ConfigRepository;
use Foundation\AliasLoader;
use Foundation\Application;
use Http\Request;
use Http\RequestProcessor;
use Support\Facades\Facade;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

//--------------------------------------------------------------------------
// Set PHP Error Reporting Options
//--------------------------------------------------------------------------

error_reporting(-1);

//--------------------------------------------------------------------------
// Use Internally The UTF-8 Encoding
//--------------------------------------------------------------------------

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('utf-8');
}

//--------------------------------------------------------------------------
// Setup Patchwork UTF-8 Handling
//--------------------------------------------------------------------------

Patchwork\Utf8\Bootup::initMbstring();

//--------------------------------------------------------------------------
// Create New Application
//--------------------------------------------------------------------------

$app = new Application();

$app->instance('app', $app);

//--------------------------------------------------------------------------
// Detect The Application Environment
//--------------------------------------------------------------------------

$env = $app->detectEnvironment(array(
    'local'       => array('darkstar'),
    'testing'     => array('your-testing-machine-name'),
    'development' => array('your-development-machine-name'),
    'production'  => array('your-production-machine-name'),
));

//--------------------------------------------------------------------------
// Bind Paths
//--------------------------------------------------------------------------

$paths = array(
    'base'    => ROOTDIR,
    'app'     => APPDIR,
    'public'  => PUBLICDIR,
    'storage' => STORAGE_PATH,
);

$app->bindInstallPaths($paths);

//--------------------------------------------------------------------------
// Check For The Test Environment
//--------------------------------------------------------------------------

if (isset($unitTesting)) {
    $app['env'] = $env = $testEnvironment;
}

//--------------------------------------------------------------------------
// Load The Framework Facades
//--------------------------------------------------------------------------

Facade::clearResolvedInstances();

Facade::setFacadeApplication($app);

//--------------------------------------------------------------------------
// Register Facade Aliases To Full Classes
//--------------------------------------------------------------------------

$app->registerCoreContainerAliases();

//--------------------------------------------------------------------------
// Load The Configuration
//--------------------------------------------------------------------------

require app_path() .'Config.php';

// Load the Modules configuration.
$modules = Config::get('modules');

foreach ($modules as $module) {
    $path = app_path() .'Modules' .DS .$module .DS .'Config.php';

    if (is_readable($path)) require $path;
}

//--------------------------------------------------------------------------
// Register The Config Manager
//--------------------------------------------------------------------------

$app->instance('config', $config = new ConfigRepository(
    $app->getConfigLoader()
));

//--------------------------------------------------------------------------
// Register Application Exception Handling
//--------------------------------------------------------------------------

$app->startExceptionHandling();

if ($env != 'testing') ini_set('display_errors', 'Off');

//--------------------------------------------------------------------------
// Set The Default Timezone
//--------------------------------------------------------------------------

$config = $app['config']['app'];

date_default_timezone_set($config['timezone']);

//--------------------------------------------------------------------------
// Register The Alias Loader
//--------------------------------------------------------------------------

$aliases = $config['aliases'];

AliasLoader::getInstance($aliases)->register();

//--------------------------------------------------------------------------
// Enable HTTP Method Override
//--------------------------------------------------------------------------

Request::enableHttpMethodParameterOverride();

//--------------------------------------------------------------------------
// Enable Trusting Of X-Sendfile Type Header
//--------------------------------------------------------------------------

BinaryFileResponse::trustXSendfileTypeHeader();

//--------------------------------------------------------------------------
// Register The Core Service Providers
//--------------------------------------------------------------------------

$providers = $config['providers'];

$app->getProviderRepository()->load($app, $providers);

//--------------------------------------------------------------------------
// Register Again The Config Manager, If Is Need
//--------------------------------------------------------------------------

if(APPCONFIG_STORE == 'database') {
    // Get the Database Connection instance.
    $connection = $app['db']->connection();

    // Get a fresh Config Loader instance.
    $loader = $app->getConfigLoader();

    // Setup the Database Connection on Config Loader.
    $loader->setConnection($connection);

    // Refresh the Application's Config instance
    $app->instance('config', $config = new ConfigRepository(
        $loader
    ));

    // Make the Facade to refresh its information.
    Facade::clearResolvedInstance('config');
}

//--------------------------------------------------------------------------
// Register Booted Start Files
//--------------------------------------------------------------------------

$app->booted(function() use ($app, $env, $modules)
{

//--------------------------------------------------------------------------
// Load The Application Start Script
//--------------------------------------------------------------------------

$path = $app['path'] .DS .'Boot' .DS .'Global.php';

if (is_readable($path)) require $path;

//--------------------------------------------------------------------------
// Load The Environment Start Script
//--------------------------------------------------------------------------

$path = $app['path'] .DS .'Boot' .DS .'Environment' .DS .ucfirst($env) .'.php';

if (is_readable($path)) require $path;

//--------------------------------------------------------------------------
// Load The Application Routes
//--------------------------------------------------------------------------

$routes = $app['path'] .DS .'Routes.php';

if (is_readable($routes)) require $routes;

// Load the Routes defined on Modules.
foreach ($modules as $module) {
    $path = app_path() .'Modules' .DS .$module .DS .'Routes.php';

    if (is_readable($path)) require $path;
}

});

//--------------------------------------------------------------------------
// Create The Request And Decrypt Its Cookies
//--------------------------------------------------------------------------

$request = Request::createFromGlobals();

$config = $app['config']['session'];

if($config['encrypt'] == true) {
    RequestProcessor::handle($app, $request);
}

//--------------------------------------------------------------------------
// Execute The Application
//--------------------------------------------------------------------------

$app->run($request);
