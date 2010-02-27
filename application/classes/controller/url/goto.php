<?php
defined('SYSPATH') or die('No direct script access.');

/**
 * This is a separate class to get rid of small overheads (like creating a new session)
 * when going to shortened URLs. Make it a bit faster :)
 */
class Controller_Url_Goto extends Controller
{
	public function action_go($alias)
	{	
		$url = Shortener::get_url($alias);
		
		if ($url->status != 'ok')
		{
			echo Request::factory('url/invalid/' . $alias)->execute();
			die;
		}
		
		// Log a hit, and then redirect.
		// TODO: Not thread safe. We can re-sync these though.
		$url->last_hit = time();
		$url->hits++;
		$url->save();
		// Log the hit itself
		$hit = ORM::factory('hit');
		$hit->url = $url;
		$hit->date = time();
		$hit->user_agent = Request::$user_agent;
		$hit->browser = $this->request->user_agent('browser');
		$hit->browser_version = $this->request->user_agent('version');
		$hit->ip_address = $_SERVER['REMOTE_ADDR'];
		$hit->country = self::get_country($hit->ip_address);
		$hit->referrer = Request::$referrer;
		$hit->save();
		
		$this->request->redirect($url->url);
	}
	
	
	/**
	 * TODO: No clue why this is here.
	 */
	public function action_favicon($domain)
	{
		// Validate domain, just in case.
		$domain = preg_replace('~[^A-Za-z0-9\.\-]~', '', $domain);
		// Strip a .ico from the end if we have one
		$domain = preg_replace('~\.ico$~', '', $domain);
		
		$favicon_path = DOCROOT . 'favicons/' . $domain . '.ico';
		// Check if we don't already have a favicon for it
		if (!file_exists($favicon_path))
		{
			// Let's try get it
			$ctx = stream_context_create(array( 
				'http' => array( 
					'timeout' => 1
				) 
			));
			
			$icon = @file_get_contents('http://' . $domain . '/favicon.ico', 0, $ctx);
			// Didn't work?
			if ($icon == null || $icon == '')
			{
				// Let's go to a generic icon
				$this->request->redirect('res/icons/page_white.png');
			}
			
			// Let's output the icon, and save it
			file_put_contents($favicon_path, $icon);
			header('Content-Type: image/x-icon');
			echo $icon;
			die();
		}
		// We have a cached file!
		else
		{
			header('Content-Type: image/x-icon');
			readfile($favicon_path);
			die();
		}
		//	echo $domain;
	}
	
	
	private static function get_country($ip)
	{
		if (!function_exists('geoip_country_code_by_name'))
			return '';
			
		try
		{
			$result = geoip_country_code_by_name($ip);
		}
		catch (Exception $ex)
		{
			$result = '';
		}
		
		return $result;
	}
}
?>