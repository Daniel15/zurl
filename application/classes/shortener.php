<?php
defined('SYSPATH') OR die('No direct access allowed.');

// Note: a lot of this was copied from the OLD zURL site.
class Shortener
{
	const CHARACTERS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_';
	
	/**
	 * Convert an ID to an alias
	 */
	public static function id_to_alias($id)
	{
		$number_temp = $id;
		$output = '';
		$characters = self::CHARACTERS;
		
		// We work from the right towards the left
		while ($number_temp > 0)
		{
			// PHP doesn't seem to like self::CHARACTERS[...] for some odd reason.
			// So we have $characters temporary variable
			$output = $characters[$number_temp % 64] . $output;
			// What we have left
			$number_temp = floor($number_temp / 64);
		}	
		
		return $output;
	}
	
	public static function alias_to_id($alias)
	{
		$output = 0;
		// Loop through each character
		// Numbers go upwards, but we work from right to left
		for ($i = 0; $i < strlen($alias); $i++)
		{
			// Get the index of this character
			$character = substr($alias, (-$i - 1), 1); // LOLWUT?
			$digit = strpos(self::CHARACTERS, $character);
			
			// Add this digit to our total
			$output += (pow(64, $i) * $digit);
		}
		
		return $output;
	}
	
	public static function get_url($alias, $hostname = null)
	{
		if ($hostname == null)
			$hostname = $_SERVER['HTTP_HOST'];
			
		// Is it a custom URL?
		if (preg_match('~^c(\.dev|\.staging|\.pre)?\.zurl~', $hostname))
		{
			return Model_Url::find_by_custom_alias($alias);
		}
		// Is it a user custom URL?
		elseif (preg_match('~^([A-Za-z0-9\-_]+)(\.dev|\.staging|\.pre)?.zurl~', $hostname, $matches) && !in_array($matches[1], array('dev', 'staging', 'pre')))
		{
			return Model_Url::find_by_user_alias($alias, $matches[1]);
		}
		else
		{
			return Model_Url::find_by_alias($alias);
		}
	}
}
?>