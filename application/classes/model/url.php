<?php
defined('SYSPATH') OR die('No direct access allowed.');

class Model_Url extends ORM
{
	protected $_belongs_to = array('user' => array());
	
	// Todo: Move validation rules from controller to here!
	protected $_rules = array
	(
	
	);
	
	/**
	 * Override get so we can magically produce the alias and short URL
	 */
	public function __get($name)
	{
		// If we're getting the alias, generate it.
		if ($name == 'alias')
		{
			return Shortener::id_to_alias($this->id);
		}
		elseif ($name == 'short_url')
		{
			return self::get_short_url($this->id, $this->type, $this->custom_alias, $this->user_id, $this->domain_id, $this->domain_url_id);
		}
		elseif ($name == 'preview_url')
		{
			return self::get_preview_url($this->id, $this->type, $this->custom_alias, $this->user_id, $this->domain_id, $this->domain_url_id);
		}
		
		return parent::__get($name);
	}
	
	/**
	 * Override set so we can set custom properties. When the URL is changed, 
	 * we also change the domain.
	 */
	public function __set($name, $value)
	{
		if ($name == 'url')
		{
			$this->url_domain = str_replace('www.', '', parse_url($value, PHP_URL_HOST));
		}
		
		return parent::__set($name, $value);
	}	
	
	public static function find_by_alias($alias)
	{
		return ORM::factory('url', Shortener::alias_to_id($alias));
	}
	
	public static function find_by_custom_alias($alias)
	{
		return ORM::factory('url')
			->where('type', '=', 'custom')
			->where('custom_alias', '=', $alias)
			->order_by('id', 'desc')
			->find();
	}
	
	public static function find_by_user_alias($alias, $username)
	{
		return ORM::factory('user', array('username' => $username))
			->urls
				->where('type', '=', 'user')
				->where('custom_alias', '=', $alias)
				->order_by('id', 'desc')
				->find();
	}
	
	/**
	 * Find a URL on a custom domain, either by custom alias, or "normal" alias.
	 * @param	string	Alias in the URL
	 * @param	int		Domain ID
	 * @returns The URL, or null if not found
	 */
	public static function find_by_domain($alias, $domain)
	{
		return ORM::factory('url')
			->where('domain_id', '=', $domain)
			->and_where_open()
				->or_where_open()
					->where('type', '=', 'domain_custom')
					->where('custom_alias', '=', $alias)
				->or_where_close()
				->or_where_open()
					->where('type', '=', 'domain')
					->where('domain_url_id', '=', Shortener::alias_to_id($alias))
				->or_where_close()
			->and_where_close()
			->order_by('id')
			->find();
	}
	
	/**
	 * Get the shortened URL for a URL
	 */
	//public static function get_short_url($id)
	// TODO: This is UUUUGLY!
	///public static function get_short_url($row)
	public static function get_short_url($id, $type, $custom_alias, $user_id, $domain_id = null, $domain_url_id = null)
	{		
		if (strstr($_SERVER['HTTP_HOST'], 'dev.zurl'))
			$base = 'dev.zurl.ws:82';
		elseif (strstr($_SERVER['HTTP_HOST'], 'staging.zurl'))
			$base = 'staging.zurl.ws';
		elseif (strstr($_SERVER['HTTP_HOST'], 'pre.zurl'))
			$base = 'pre.zurl.ws';
		else
			$base = 'zurl.ws';
			
		switch ($type)
		{
			case 'custom';
				return 'http://c.' . $base . '/' . $custom_alias;
				break;
				
			case 'user':
				$user = ORM::factory('user', $user_id);
				return 'http://' . $user->username . '.' . $base . '/' . $custom_alias;
				break;
				
			case 'domain_custom':
				$domain = ORM::factory('domain', $domain_id);
				return 'http://' . $domain->domain . '/' . $custom_alias;
				break;
				
			case 'domain':
				$domain = ORM::factory('domain', $domain_id);
				return 'http://' . $domain->domain . '/' . Shortener::id_to_alias($domain_url_id);
				break;
				
			default:
				return 'http://' . $base . '/' . Shortener::id_to_alias($id);
				break;
		}
	}
	
	/**
	 * Get the preview URL for a URL
	 * TODO: Remove duplication with get_short_url above
	 */
	public static function get_preview_url($id, $type, $custom_alias, $user_id, $domain_id, $domain_url_id = null)
	{
		switch ($type)
		{
			case 'custom';
				return 'http://c.' . $_SERVER['HTTP_HOST'] . '/p/' . $custom_alias;
				break;
				
			case 'user':
				$user = ORM::factory('user', $user_id);
				return 'http://' . $user->username . '.' . $_SERVER['HTTP_HOST'] . '/p/' . $custom_alias;
				break;
				
			case 'domain_custom':
				$domain = ORM::factory('domain', $domain_id);
				return 'http://' . $domain->domain . '/p/' . $custom_alias;
				break;
				
			case 'domain':
				$domain = ORM::factory('domain', $domain_id);
				return 'http://' . $domain->domain . '/p/' . Shortener::id_to_alias($domain_url_id);
				break;
				
			default:
				return 'http://' . $_SERVER['HTTP_HOST'] . '/p/' . Shortener::id_to_alias($id);
				break;
		}
	}
	
	/**
	 * Count the number of URLs a user has
	 */
	public function count_user($user, $only_active = true)
	{
		$this->_build(Database::SELECT);

		$records = $this->_db_builder->from($this->_table_name)
			->select(array('COUNT("*")', 'records_found'))
			->where('user_id', '=', $user);
			
		if ($only_active)
			$records->and_where('status', '=', 'ok');
			
		$records = $records->execute($this->_db)
			->get('records_found');

		//$this->_reset();

		// Return the total number of records in a table
		return $records;
	}
	
	/**
	 * Check if a custom URL is available
	 */
	public function check_custom_alias_available($value)
	{
		return !(bool) DB::select(array('COUNT("*")', 'total_count'))
			->from($this->_table_name)
			->where('type', '=', 'custom')
			->and_where('status', '=', 'ok')
			->and_where('custom_alias', '=', $value)
			->execute($this->_db)
			->get('total_count');
	}
}

?>