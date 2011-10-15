<?php
/**
 * core.php template
 *
 * Copyright 2011, nojimage (http://php-tips.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @version   1.0
 * @author    nojimage <nojimage at gmail.com>
 * @copyright 2011 nojimage (http://php-tips.com/)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link    　http://php-tips.com/
 */
if (!class_exists('Security')) {
    App::uses('Security', 'Utility');
}

// genarete Security.salt
if (empty($salt) || strtolower($salt) === 'auto') {
    $salt = Security::generateAuthKey();
}

// genarete Security.cipherSeed
if (empty($cipherseed) || strtolower($cipherseed) === 'auto') {
    $cipherseed = substr(bin2hex(Security::generateAuthKey()), 0, 30);
}

echo "<?php\n";
?>
/**
* This is core configuration file.
*/

/**
 * CakePHP Debug Level:
 */
	Configure::write('debug', <?php echo $debug ?>);

/**
 * Configure the Error handler used to handle errors for your application.
 */
	Configure::write('Error', array(
		'handler' => 'ErrorHandler::handleError',
		'level' => E_ALL & ~E_DEPRECATED,
		'trace' => true
	));

/**
 * Configure the Exception handler used for uncaught exceptions.
 */
	Configure::write('Exception', array(
		'handler' => 'ErrorHandler::handleException',
		'renderer' => 'ExceptionRenderer',
		'log' => true
	));

/**
 * Application wide charset encoding
 */
	Configure::write('App.encoding', '<?php echo $encoding ?>');

/**
 * To configure CakePHP *not* to use mod_rewrite and to
 * use CakePHP pretty URLs, remove these .htaccess
 * files:
 *
 * /.htaccess
 * /app/.htaccess
 * /app/webroot/.htaccess
 *
 * And uncomment the App.baseUrl below:
 */
	//Configure::write('App.baseUrl', env('SCRIPT_NAME'));

/**
 * CakePHP prefix routes.
 */
<?php if (isset($routing_prefix)) : ?>
    Configure::write('Routing.prefixes', <?php var_export($routing_prefix) ?>);
<?php else: ?>
	//Configure::write('Routing.prefixes', array('admin'));
<?php endif; ?>

/**
 * Turn off all caching application-wide.
 *
 */
	//Configure::write('Cache.disable', true);

/**
 * Enable cache checking.
 */
<?php if (isset($cache_check) && $cache_check) : ?>
    Configure::write('Cache.check', true);
<?php else: ?>
    //Configure::write('Cache.check', true);
<?php endif; ?>

/**
 * Defines the default error type when using the log() function. Used for
 * differentiating error logging and debugging. Currently PHP supports LOG_DEBUG.
 */
	define('LOG_ERROR', 2);

/**
 * Session configuration.
 */
	Configure::write('Session', array(
		'defaults' => 'php'
	));
    Configure::write('Session.cookie', '<?php echo $session_cookie ?>');

/**
 * The level of CakePHP security.
 */
    Configure::write('Security.level', '<?php echo $security_level ?>');

/**
 * A random string used in security hashing methods.
 */
    Configure::write('Security.salt', '<?php echo $salt ?>');

/**
 * A random numeric string (digits only) used to encrypt/decrypt strings.
 */
    Configure::write('Security.cipherSeed', '<?php echo $cipherseed ?>');

/**
 * Apply timestamps with the last modified time to static assets (js, css, images).
 */
	//Configure::write('Asset.timestamp', true);
/**
 * Compress CSS output by removing comments, whitespace, repeating tags, etc.
 */
	//Configure::write('Asset.filter.css', 'css.php');

/**
 * Plug in your own custom JavaScript compressor by dropping a script in your webroot to handle the
 * output, and setting the config below to the name of the script.
 */
	//Configure::write('Asset.filter.js', 'custom_javascript_output_filter.php');

/**
 * The classname and database used in CakePHP's
 * access control lists.
 */
	Configure::write('Acl.classname', 'DbAcl');
	Configure::write('Acl.database', 'default');

/**
 * If you are on PHP 5.3 uncomment this line and correct your server timezone
 * to fix the date & time related errors.
 */
<?php if (isset($timezone)) : ?>
    date_default_timezone_set('<?php echo $timezone ?>');
<?php else: ?>
    //date_default_timezone_set('UTC');
<?php endif; ?>

/**
 * Cache Engine Configuration
 * Default settings provided below
 */

/**
 * Pick the caching engine to use.  If APC is enabled use it.
 */
$engine = 'File';
if (extension_loaded('apc') && (php_sapi_name() !== 'cli' || ini_get('apc.enable_cli'))) {
	$engine = 'Apc';
}

// In development mode, caches should expire quickly.
$duration = '+999 days';
if (Configure::read('debug') >= 1) {
	$duration = '+10 seconds';
}

/**
 * Configure the cache used for general framework caching.  Path information,
 * object listings, and translation cache files are stored with this configuration.
 */
Cache::config('_cake_core_', array(
	'engine' => $engine,
	'prefix' => 'cake_core_',
	'path' => CACHE . 'persistent' . DS,
	'serialize' => ($engine === 'File'),
	'duration' => $duration
));

/**
 * Configure the cache for model and datasource caches.  This cache configuration
 * is used to store schema descriptions, and table listings in connections.
 */
Cache::config('_cake_model_', array(
	'engine' => $engine,
	'prefix' => 'cake_model_',
	'path' => CACHE . 'models' . DS,
	'serialize' => ($engine === 'File'),
	'duration' => $duration
));
