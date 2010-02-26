<?php
defined('SYSPATH') or die('No direct script access.');

class Date
{
	/**
	 * Format a date using the user's timezone
	 */
	public static function format($timestamp, $show_time = true)
	{
		// Are we showing the time, or just the date?
		$format = $show_time ? 'Y-m-d g:i:s A' : 'Y-m-d';
		$timezone = self::get_timezone();
		
		return gmdate($format, $timestamp + ($timezone * 3600));
	}
	
	/**
	 * Get the current user's timezone
	 */
	public static function get_timezone()
	{
		$timezone = null;
		
		if (Auth::instance()->logged_in())
			$timezone = Auth::instance()->get_user()->timezone;
		
		return $timezone == null ? 0 : $timezone;
	}
}
?>