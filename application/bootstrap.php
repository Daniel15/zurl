<?php defined('SYSPATH') or die('No direct script access.');

// Some ideas based off http://github.com/shadowhand/wingsc/blob/master/application/bootstrap.php

//-- Environment setup --------------------------------------------------------

/**
 * Set the default time zone.
 *
 * @see  http://docs.kohanaphp.com/about.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Australia/Melbourne');

/**
 * Set the default locale.
 *
 * @see  http://docs.kohanaphp.com/about.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://docs.kohanaphp.com/about.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');


/**
 * Set the production status based on domain
 */
define('IN_PRODUCTION', $_SERVER['HTTP_HOST'] != 'dev.zurl.ws:82');

//-- Configuration and initialization -----------------------------------------

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
Kohana::init(array
(
	'base_url' => 'http://dev.zurl.ws:82/',
	'index_file' => '',
	'profiling' => !IN_PRODUCTION,
	'caching' => IN_PRODUCTION,
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Kohana_Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Kohana_Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	 'auth'       => MODPATH.'auth',       // Basic authentication
	// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
	'database'   => MODPATH.'database',   // Database access
	// 'image'      => MODPATH.'image',      // Image manipulation
	'orm'        => MODPATH.'orm',        // Object Relationship Mapping
	 'pagination' => MODPATH.'pagination', // Paging of results
	// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
	'recaptcha'  => MODPATH.'recaptcha',
	));

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
// If we're in development, or don't have cached routes
if (!IN_PRODUCTION || !Route::cache())
{
	Route::set('static', '<page>.htm')
		-> defaults(array(
			'controller' => 'static'
		));
		
	Route::set('favicons', 'favicons/<domain>', array('domain' => '[A-Za-z0-9\.\-]+'))
		-> defaults(array(
			'controller' => 'url_goto',
			'action'     => 'favicon',
		));

		/*default*/
	Route::set('controllers', '(<controller>(/<action>(/<id>)))',
		array(
			'controller' => '(url|url_goto|static|account|url_stats)',
		))
		->defaults(array(
			'controller' => 'url',
			'action'     => 'index',
		));
		
	/*
	
	Route::set('controllers', '(<controller>(/<action>(/<id>)))',
		array(
			'controller' => '(url|urlgoto|static)',
		))
		->defaults(array(
			'controller' => 'url',
			'action'     => 'index',
		));
	Route::set('account_sub', 'account/(<controller>(/<action>(/<id>(/<id2>))))',
		array(
			'controller' => '(url|url_stats)',
		))
		->defaults(array(
			'directory'  => 'account',
			'controller' => 'index',
			'action'     => 'index',
		));
		
	Route::set('account', 'account(/<action>(/<id>))')
		->defaults(array(
			'controller' => 'account',
			'action'     => 'index',
		));*/
		
	Route::set('url_preview', 'p/<url>')
		->defaults(array(
			'controller' => 'url',
			'action' => 'preview',
		));
		
	Route::set('shortened_urls', '<url>')
		->defaults(array(
			'controller' => 'url_goto',
			'action' => 'go',
		));
	
	// Cache the routes if we're in production
	if (IN_PRODUCTION)
		Route::cache(true);
}

/**
 * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
 * If no source is specified, the URI will be automatically detected.
 */
 
/*echo $request = Request::instance()
	->execute()
	->send_headers()
	->response;*/

$request = null;

try
{
	$request = Request::instance();
	$request->execute();
}
catch (Exception $ex)
{
	// If in dev, just re-throw it
	if (!IN_PRODUCTION)
		throw $ex;
		
	// If we're in production, we have to show a nice error message instead.
	// Log the error
	Kohana::$log->add(Kohana::ERROR, Kohana::exception_text($ex));
	// If the error was with getting a route, we won't have anything here yet. 
	if ($request == null)
		$request = Request::factory('');
	// Now let's just load the error page.
	$request->status = 500;
	$request->response = new View('template');
	$request->response->title = 'Error';
	// This doesn't really matter right now...
	$request->response->logged_in = false;
	$request->response->body = $page = new View('error');
	$page->message = $ex->getMessage();
}

echo $request->send_headers()->response;
