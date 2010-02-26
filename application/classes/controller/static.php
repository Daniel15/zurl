<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Static extends Controller_Template
{
	public function action_index($page)
	{
		$this->template->title = ucfirst($page);
		$this->template->body = new View('static/' . $page);
	}
}

?>