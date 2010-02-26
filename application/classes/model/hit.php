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
		
		return parent::__set($name, $value);
	}
}
?>