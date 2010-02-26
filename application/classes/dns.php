<?php
defined('SYSPATH') or die('No direct script access.');

class Dns
{
	public static function get_ip($host)
	{
		if (PHP_OS == 'WINNT')
			$dig = 'c:/apps/dig';
		else
			$dig = '/usr/bin/dig';
		
		$host = exec($dig . ' +short ' . escapeshellarg($host));
		return $host == '' ? false : $host;
	}
}
?>