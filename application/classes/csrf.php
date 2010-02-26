<?php
defined('SYSPATH') OR die('No direct access allowed.');

class csrf
{

	public static function token()
	{
		if (($token = Session::instance()->get('csrf')) === null)
		{
			Session::instance()->set('csrf', ($token = text::random('alnum', 16)));
		}

		return $token;
	}

	public static function valid($token)
	{
		return ($token === Session::instance()->get('csrf'));
	}
}
?>