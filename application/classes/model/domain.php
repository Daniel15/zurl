<?php
defined('SYSPATH') OR die('No direct access allowed.');

class Model_Domain extends ORM
{
	protected $_belongs_to = array('user' => array());
	
	/**
	 * Get the next domain URL ID for this domain
	 */
	public function next_url_id()
	{
		return DB::select(array('IFNULL(MAX("domain_url_id"), 0) + 1', 'domain_url_id'))
			->from('urls')
			->where('domain_id', '=', $this->id)
			->execute($this->_db)
			->get('domain_url_id');
	}
}

?>