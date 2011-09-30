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
    require LIBS . 'security.php';
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

<?php if (isset($timezone)) : ?>
date_default_timezone_set('<?php echo $timezone ?>');
<?php endif; ?>

/**
 * CakePHP Debug Level:
 */
Configure::write('debug', <?php echo $debug ?>);

/**
 * CakePHP Log Level:
 */
Configure::write('log', <?php echo $log ?>);

/**
 * Application wide charset encoding
 */
Configure::write('App.encoding', '<?php echo $encoding ?>');

<?php if (isset($routing_prefix)) : ?>
/**
 * Prefix routes.
 */
 Configure::write('Routing.prefixes', <?php var_export($routing_prefix) ?>);
<?php endif; ?>

/**
 * Turn off all caching application-wide.
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
 * The preferred session handling method.
 */
Configure::write('Session.save', 'php');

/**
 * The model name to be used for the session model.
 */
//Configure::write('Session.model', 'Session');

/**
 * The name of the table used to store CakePHP database sessions.
 */
//Configure::write('Session.table', 'cake_sessions');

/**
 * The DATABASE_CONFIG::$var to use for database session handling.
 */
//Configure::write('Session.database', 'default');

/**
 * The name of CakePHP's session cookie.
 */
Configure::write('Session.cookie', '<?php echo $session_cookie ?>');

/**
 * Session time out time (in seconds).
 */
Configure::write('Session.timeout', '120');

/**
 * If set to false, sessions are not automatically started.
 */
Configure::write('Session.start', true);

/**
 * When set to false, HTTP_USER_AGENT will not be checked in the session.
 */
Configure::write('Session.checkAgent', true);

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
 * The classname and database used in CakePHP's access control lists.
 */
Configure::write('Acl.classname', 'DbAcl');
Configure::write('Acl.database', 'default');

/**
 * Cache Engine Configuration
 */
Cache::config('default', array('engine' => 'File'));
