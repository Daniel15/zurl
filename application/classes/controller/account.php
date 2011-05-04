<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Account extends Controller_Template
{
	protected $secure_actions = array('index', 'settings', 'logout', 'urls');
	
	public function action_index()
	{
		$this->request->redirect('account/settings');
	}
	
	public function action_settings()
	{
		$this->request->redirect('');
		/*$this->template->title = 'Account Settings';
		$page = $this->template->body = new View('account/settings');
		$page->errors = array();*/
	}
	
	public function action_login()
	{
		$this->template->title = 'Log in';
		$page = $this->template->body = new View('account/login');
		$user = ORM::factory('user');
		$timezone = Arr::get($_POST, 'timezone');
		
		// No POST data?
		if (!$_POST)
		{
			$page->errors = array();
			return;
		}
		// If we have POST data, try to log the user in.
		elseif (!$user->login($_POST))
		{
			$page->errors = $_POST->errors('login');
			return;
		}
		
		// If we're here, they logged in successfully!
		// Check if they submitted a timezone (via the JS)
		if ($timezone != '')
		{
			$user->timezone = $timezone;
			$user->save();
		}
		$this->session->set('top_message', 'You have been logged in. Welcome back!');
		$this->request->redirect('');
	}
	
	public function action_logout()
	{
		$this->auth->logout();
		$this->session->set('top_message', 'You have been logged out.');
		$this->request->redirect('');
	}
	
	public function action_register()
	{
		// If they're logged in, we don't want to go to this page!
		if ($this->logged_in)
			Request::instance()->redirect('');
			
		$this->template->title = 'Register';
		$this->template->jsload = 'Register.init';
		$page = $this->template->body = new View('account/register');
		$page->captcha = Recaptcha::get_html();
		$page->errors = array();
		$page->values = array('username' => '', 'email' => '');
		
		/* Shutdown Registration Temporarily
		// Did the user post the form?
		if ($_POST)
		{
			$user = ORM::factory('user');
			$user->values($_POST);
			// Add the CAPTCHA validation
			$user->validate()
				->callback('recaptcha_challenge_field', 'Recaptcha::validate');
			// TODO: This seems very messy, but it seems validate() doesn't check it. :(
			if (!csrf::valid($_POST['token']))
			{
				die('Token is invalid.');
			}
			
			if ($user->check())
			{
				// Do we have a timezone?
				$timezone = Arr::get($_POST, 'timezone');
				if ($timezone != '')
				{
					$user->timezone = $timezone;
				}
				
				$user->save();
				$user->add('roles', ORM::factory('role', array('name' => 'login')));
				$user->login($_POST);
				$this->session->set('top_message', 'Welcome to zURL! Your account has been created :)');
				$this->request->redirect('');
			}
			
			// If we're here, it failed, so we still have to show the page.
			// Let's grab the errors
			$page->errors = $user->validate()->errors('register');
			$page->values = $user->validate();
		}*/
	}
	
	public function action_check_username()
	{
		if (empty($_POST['username']))
			die();
			
		// Check that this username is available
		$user = ORM::factory('user', array('username' => $_POST['username']));
		if ($user->loaded())
		{
			die(json_encode(array(
				'available' => false,
			)));		
		}
		
		die(json_encode(array(
			'available' => true,
		)));
	}
	
	public function action_urls()
	{
		$this->template->title = 'My URLs';
		$this->template->jsload = 'Listing.init';
		
		// Get the URLs
		$benchmark = Profiler::start('zURL', 'Get user URLs');
		$count = ORM::factory('url')->count_user($this->user);
		$pagination = Pagination::factory(array(
			'total_items' => $count,
			'items_per_page' => 25
		));
		
		$urls = ORM::factory('url')
			->where('user_id', '=', $this->user)
			->and_where('status', '=', 'ok')
			->order_by('id', 'desc')
			->limit($pagination->items_per_page)
			->offset($pagination->offset)
			->find_all();
		Profiler::stop($benchmark);
		
		$benchmark = Profiler::start('zURL', 'Get user hits');
		// Let's get the 10 most recent visitors, too.
		/*$visitors = ORM::factory('hit')
			->where('';*/
		$visits = DB::select('hits.*', array('UNIX_TIMESTAMP("hits.date")', 'date'), 'urls.*', array('countries.printable_name', 'country_name'))
			->from('hits')
			->join('urls')->on('urls.id', '=', 'hits.url_id')
			->join('users')->on('users.id', '=', 'urls.user_id')
			->join('countries', 'left')->on('countries.iso', '=', 'hits.country')
			->where('users.id', '=', $this->user)
			->order_by('hits.id', 'desc')
			->limit(20)
			->as_object()
			->execute();
		
		Profiler::stop($benchmark);
		
		$page = $this->template->body = new View('account/urls');
		$page->urls = $urls;
		$page->count = $count;
		$page->pagination = $pagination->render();
		$page->visits = $visits;
	}
}
?>