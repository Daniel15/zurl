<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Url_Stats extends Controller_Template
{
	protected $auth_required = true;
	
	public function action_hits($id)
	{
		// Let's load the URL
		$url = ORM::factory('url', $id);
		if (!$url->loaded() || $url->user_id != $this->user->id)
		{
			$this->template->body = 'Error: Invalid URL';
			return;
		}
	}
}
?>