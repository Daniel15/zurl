<?php
defined('SYSPATH') or die('No direct script access.');

// Based off ideas at http://kerkness.ca/wiki/doku.php?id=using_the_auth_module_in_your_controllers
abstract class Controller_Template extends Kohana_Controller_Template
{
	// If set to true, show the main template if a request is internal (HMVC)
	// If set to an array, show the main template if the action is in the array
	protected $internal_template = false;
	
	// Some useful stuff
	protected $session;
	protected $internal = false;
	protected $auth;
	protected $user;
	protected $logged_in;
	protected $auth_required = false;
	protected $auth_role = array('login');
	protected $secure_actions = array();
	
	public function before()
	{		
		// Is this an internal request (HMVC)?
		if ($this->request != Request::instance())
		{
			$this->internal = true;
			if ($this->internal_template === false
				|| (is_array($this->internal_template) && !in_array($this->request->action, $this->internal_template)))
			{
				$this->template = 'template_internal';
			}
		}
		// AJAX request? No template needed
		elseif (Request::$is_ajax)
		{
			//$this->auto_render = false;
			$this->template = 'template_ajax_html';
		}
		
		// Call the parent::before (load the template, etc.)
		parent::before();
		
		// Set our helpful things
		$this->session = Session::instance();
		$this->auth = Auth::instance();
		$this->user = $this->auth->get_user();
		
		// Check if we should be logged in
		if (($this->auth_required || in_array(Request::instance()->action, $this->secure_actions))
			&& !$this->auth->logged_in($this->auth_role))
		{
			Request::instance()->redirect('account/login');
		}

		// Only set these on a non-AJAX request
		//if (!Request::$is_ajax)
		//{
			$this->template->body = '';
			$this->template->logged_in = $this->logged_in = $this->auth->logged_in('login');
			$this->template->user = $this->user;
			// Check if we have a top message...
			// Why is there no flashdata in Kohana 3?? O_O
			if (($top_message = $this->session->get('top_message', null)) != null)
			{
				$this->template->top_message = $top_message;
				$this->session->delete('top_message');
			}
		//}
	}
	
	public function after()
	{
		// If we've got a view, feed it the logged_in variable
		if ($this->auto_render && $this->template->body instanceof View)
			$this->template->body->logged_in = $this->logged_in;
			
		return parent::after();
	}
}
?>