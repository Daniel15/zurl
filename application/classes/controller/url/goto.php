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
		$hit->ip_address = $_SERVER['REMOTE_ADDR'];
		$hit->country = self::get_country($hit->ip_address);
		$hit->referrer = Request::$referrer;
		$hit->save();
		echo $url->url;
		die();
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