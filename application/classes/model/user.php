<?php
defined('SYSPATH') or die('No direct access allowed.');

class Model_User extends Model_Auth_User
{
	// Validation rules
	protected $_rules = array(
		'username' => array(
			'not_empty'  => NULL,
			// Changed the min_length
			'min_length' => array(1),
			'max_length' => array(32),
			'regex'      => array('/^[-\pL\pN_.]++$/uD'),
		),
		'password' => array(
			'not_empty'  => NULL,
			'min_length' => array(5),
			'max_length' => array(42),
		),
		'password_confirm' => array(
			'matches'    => array('password'),
		),
		'email' => array(
			'not_empty'  => NULL,
			'min_length' => array(4),
			'max_length' => array(127),
			'email'      => NULL,
		),
	);
	
	protected $_has_many = array
	(
		'user_tokens' => array('model' => 'user_token'),
		'roles'       => array('model' => 'role', 'through' => 'roles_users'),
		'urls'        => array(),
		'domains'     => array(),
	);
	
	/**
	 * Get the custom domains this user can use
	 */
	public function domain_list()
	{
		$output = array();
		
		$domains = $this->domains->find_all();
		foreach ($domains as $domain)
			$output[$domain->id] = $domain->domain;

		return $output;
	}
}
?>