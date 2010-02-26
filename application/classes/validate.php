<?php
defined('SYSPATH') or die('No direct script access.');

class Validate extends Kohana_Validate
{
	public static function uribl_check()
	{
		return false;
	}
}
?>