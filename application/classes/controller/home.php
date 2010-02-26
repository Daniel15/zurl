<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Home extends Controller
{
	public function action_index()
	{
		echo 'yo';
		$this->request->response = 'hello, world!';
	}

} // End Welcome
