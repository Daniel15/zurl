<?php
defined('SYSPATH') or die('No direct script access.');

class Uribl
{
	// DNS blocklists we check against
	private static $dns_blocklists = array
	(
		'SURBL' => array(
			'suffix' => 'multi.surbl.org',
			'lists' => array(
				2 => 'SpamCop',
				4 => 'sa-blacklist',
				8 => 'Phishing',
				16 => 'Outblaze',
				32 => 'AbuseButler',
				64 => 'jwSpamSpy',
			)
		),
		'URIBL' => array(
			'suffix' => 'multi.uribl.com',
			'lists' => array(
				2 => 'blacklist',
				4 => 'greylist',
			)
		),
	);
	
	/**
	 * Check if a particular host name is blocked at any DNS block list
	 * @param	string		Host name to check
	 */
	public static function check($host)
	{		
		// Let's check each blocklist
		foreach (self::$dns_blocklists as $name => $blocklist)
		{
			$ip = Dns::get_ip($host . '.' . $blocklist['suffix']);
			
			// Do we have an IP? If so, it's blocked!
			if ($ip != null)
			{
				// Get the names of the data source it's from
				$ip_octets = explode('.', $ip);
				$last_octet = end($ip_octets);
				// Ignore red list for URIBL for now...
				// TODO: This is messy!
				if ($name == 'URIBL' && $last_octet == 8)
					continue;
					
				// Get the names of the lists this host is on
				$lists = array();
				foreach ($blocklist['lists'] as $list_num => $list_name)
				{
					if (($last_octet & $list_num) == $list_num)
						$lists[] = $list_name;
				}
				
				$return = $name . ' [' . implode(', ', $lists) . ']';
				Kohana::$log->add('URIBL', $host . ' denied by ' . $return);
				return $return;
			}
		}
		
		return false;
	}
	
	public static function validate(Validate $validate, $field)
	{
		$host = parse_url($validate[$field], PHP_URL_HOST);
		// If there's no host, let's just give up and assume it's fine for now... This should be caught as an invalid URL anyways.
		if ($host == '')
			return false;
			
		$check = self::check($host);
		// Not on the blocklist? That's good
		if ($check === false)
		{
			return true;
		}
			
		$validate->error('url', 'uribl', array($check));
	}
}
?>