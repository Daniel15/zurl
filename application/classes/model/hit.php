<?php
defined('SYSPATH') OR die('No direct access allowed.');

class Model_Hit extends ORM
{
	protected $_belongs_to = array('url' => array());
	
	public function __set($name, $value)
	{
		if ($name == 'referrer')
		{
			$this->referrer_domain = str_replace('www.', '', parse_url($value, PHP_URL_HOST));
		}
		// If it's a date, we have to convert it to MySQL's date format
		elseif ($name == 'date')
		{
			return parent::__set($name, date('Y-m-d H:i:s', $value));
		}
		
		return parent::__set($name, $value);
	}
}
?>