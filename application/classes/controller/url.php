<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Url extends Controller_Template
{
	protected $internal_template = array('invalid');
	protected $secure_actions = array('delete', 'stats');
	
	// Only allow [rate] URLs in [period] seconds before showing a CAPTCHA to guests
	const RATE = 3;
	const RATE_PERIOD = 60;
	
	public function action_index()
	{
		// Wrong domain? Redirect to the correct one.
		if (!in_array($_SERVER['HTTP_HOST'], array('zurl.ws', 'www.zurl.ws', 'dev.zurl.ws', 'dev.zurl.ws:82', 'staging.zurl.ws', 'pre.zurl.ws')))
			$this->request->redirect('http://zurl.ws' . $_SERVER['REQUEST_URI']);
			
		$this->template->title = 'Welcome to zURL';
		$page = $this->template->body = View::factory('index');
		// Load the news
		//$this->template->sidebar = new View('includes/news');
		
		// Rest of the page.			
		$page->shorten = Request::factory('url/shorten')->execute();
		
		// If we're logged in, we need our listing
		if ($this->logged_in)
			$page->account = Request::factory('account/urls')->execute()->response;
	}
	
	public function action_shorten()
	{
		// TODO: Clean up this function, it's ugly :(
		
		// TODO: move these validation rules into the MODEL!
		$post = Validate::factory($_POST)
			->rule(true, 'not_empty')
			->filter(true, 'trim')
			->rule('token', 'csrf::valid')
			->rule('url', 'url')
			->callback('url', 'Uribl::validate')
			;
		
		// If they're logged in, they might have chosen a custom type
		if ($this->logged_in)
		{
			$post->rule('type', 'Controller_Url::validate_type_member');
		
			// Custom or user URL?
			if (in_array(Arr::get($_POST, 'type'), array('custom', 'user')))
			{
				$post
					->rule('alias', 'not_empty')
					->filter('alias', 'strtolower')
					->rule('alias', 'regex', array('~^[a-z0-9\-_]+$~'));
			
				if ($_POST['type'] == 'custom')
					$post->callback('alias', 'Controller_Url::custom_alias_available');
			}
			// Domain - Custom URL?
			elseif (Arr::get($_POST, 'type') == 'domain_custom')
			{
				$post
					->rule('domain_custom', 'Controller_Url::validate_domain')
					->rule('domain_alias', 'not_empty')
					->filter('domain_alias', 'strtolower')
					->rule('domain_alias', 'regex', array('~^[a-z0-9\-_]+$~'));
			}
			elseif (Arr::get($_POST, 'type') == 'domain')
			{
				$post
					->rule('domain', 'Controller_Url::validate_domain');
			}
		}
		else
		{
			$post->rule('type', 'Controller_Url::validate_type_guest'); 
			// If they've exceeded rate limits, CAPTCHAs ahoy
			if (self::exceeded_rate_limit())
				$post->callback('recaptcha_challenge_field', 'Recaptcha::validate');
		}
			
		// Haven't posted yet, or is it invalid?
		if (!$post->check())
		{
			$this->template->title = $post['url'] != null ? 'Error shortening URL!' : 'Shorten URL';
			$this->template->jsload = 'Shorten.init';
			$page = $this->template->body = new View('url/shorten');
			$page->shorten = new View('includes/url/shorten');
			$page->shorten->errors = $post->errors('shorten');
			$page->shorten->values = $post;
			$this->session->set('passed_captcha', true);
			
			if (!$this->logged_in && self::exceeded_rate_limit())
				$page->shorten->captcha = Recaptcha::get_html();
			
			// Check if they have any custom domains
			if ($this->logged_in)
				$page->shorten->domains = $this->user->domain_list();

			$page->shorten->logged_in = $this->logged_in;
			return;
		}
		
		// Check if we already have a shortened URL for this.
		//$url = ORM::factory('url', array('url' => $post['url']));
		$url = ORM::factory('url')
			->where('url', '=', $post['url'])
			->and_where('status', '=', 'ok');
		if ($this->logged_in)
		{
			$url->and_where('user_id', '=', $this->user);
			$url->and_where('type', '=', $post['type']);
			// For domain URLs, need to check the domain too
			if ($post['type'] == 'domain')
				$url->and_where('domain_id', '=', $post['domain']);
			elseif ($post['type'] == 'domain_custom')
				$url->and_where('domain_id', '=', $post['domain_custom']);
		}
		
		$url->find();
			
		// Don't have one? We have to make it.
		if (!$url->loaded())
		{		
			$url = ORM::factory('url');
			$url->url = $post['url'];
			$url->created_date = time();
			$url->created_ip = $_SERVER['REMOTE_ADDR'];
			$url->created_ip2 = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;
			if ($this->logged_in)
			{
				$url->user = $this->user;
				$url->type = $post['type'];
				if (in_array($url->type, array('custom', 'user')))
					$url->custom_alias = $post['alias'];
				// Custom domain, custom alias
				elseif ($url->type == 'domain_custom')
				{
					$url->custom_alias = empty($post['domain_alias']) ? null : $post['domain_alias'];
					$url->domain_id = $post['domain_custom'];
				}
				// Custom domain, "normal" alias
				elseif ($url->type == 'domain')
				{
					$domain = ORM::factory('domain', $post['domain']);
					$url->domain_id = $domain->id;
					// Domain_url_id is a unique ID for this domain on this URL.
					$url->domain_url_id = $domain->next_url_id();
				}
			}
				
			$url->save();
		}
		
		$page = new View('url/shortened');
		$page->original = $url->url;
		$page->shortened = $url->short_url;
		$page->preview = $url->preview_url;
		
		/*$page->shorten = new View('includes/url/shorten');
		if (!$this->logged_in)
			$page->shorten->captcha = Recaptcha::get_html();
		$page->shorten->logged_in = $this->logged_in;
		$page->shorten->errors = array();*/
		
		// Clear out the POST data, so the internal request doesn't think we're POSTing again.
		$_POST = array();
		$page->shorten = Request::factory('url/shorten')->execute();
		$this->template->title = 'URL shortened';
		$this->template->body = $page;
		$this->template->jsload = 'Shortened.init';
	}
	
	public function action_invalid($alias)
	{
		$this->request->status = 404;
		$this->template->title = 'URL not available';
		//$url = Model_Url::find_by_alias($alias);
		$url = Shortener::get_url($alias);
		$page = $this->template->body = new View('url/deleted');
		
		// Why's it not around?
		switch ($url->status)
		{
			case 'deleted_user':
				$page->message = 'This URL has been deleted by the user.';
				break;
			case 'deleted_msg':
				$page->message = 'This URL has been deleted due to abuse:';
				$page->message2 = $url->delete_message;
				break;
			case 'deleted_bl':
				$page->message = 'This URL has been deleted due to abuse - Blocked in URL blocklist';
				$page->message2 = $url->delete_message;
				break;
			// Anything else, provide a generic message
			default:
				$page->message = 'URL not found';
				break;
		}
	}
	
	public function action_delete($id)
	{
		// Check that the current user owns this!
		$url = ORM::factory('url', $id);
		if (!$url->loaded() || $url->user_id != $this->user->id)
		{
			die(json_encode(array(
				'error' => true,
				'message' => 'You don\'t own that URL. '
			)));
		}
		
		// Delete it
		//$url->delete();
		$url->status = 'deleted_user';
		$url->save();
		die(json_encode(array(
			'error' => false,
			'url_id' => $url->id
		)));
	}
	
	public function action_complaint()
	{		
		$this->template->title = 'Report a spam URL';
		$post = Validate::factory($_POST)
			->filter(true, 'trim')
			->filter('reason', 'Security::xss_clean')
			->callback('recaptcha_challenge_field', 'Recaptcha::validate')
			->rule('url', 'not_empty')
			->rule('url', 'url')
			->rule('reason', 'not_empty')
			->rule('reason', 'min_length', array(5))
			->rule('email', 'email');
		
		// Validate the submission
		if (!$post->check())
		{
			$page = $this->template->body = new View('url/complaint');
			$page->captcha = Recaptcha::get_html();
			$page->errors = $post->errors('report');
			$page->values = $post;
			return;
		}
		
		// Let's create the complaint report
		$complaint = ORM::factory('complaint');
		$complaint->date = time();
		$complaint->comments = $post['reason'];
		$complaint->email = $post['email'];
		
		// Try to find the URL they're talking about
		$url_pieces = parse_url($post['url']);
		try
		{
			$url = Shortener::get_url(substr($url_pieces['path'], 1), $url_pieces['host']);
		}
		catch (Exception $ex)
		{
			// Use a dummy URL
			$url = Model::factory('url');
		}

		// Is it a valid URL?
		if ($url != false && $url->loaded())
		{
			$complaint->url = $url;
			
			// Set this URL's last complaint date
			$url->last_complaint = $complaint->date;
			$url->complaints++;
			$url->save();
		}
		// What you talkin' about willis?
		else
		{
			$complaint->unknown_url = $post['url'];
		}
		$complaint->save();
		
		$email = 'URL: ' . $post['url'] . '
Reason: ' . $post['reason'] . '
Email: ' . $post['email'];

		if ($url->loaded())
		{
			$email .= '
URL ID: ' . $url . '
Number of complaints: ' . $url->complaints;
		}
		
		$headers = array('From: "zURL Abuse" <abuse@zurl.ws>');
		if (!empty($post['email']))
			$headers[] = 'Reply-To: ' . $post['email'];

		mail('zurl@dan.cx', '[URL ' . $url->id . '] ' . $post['url'], $email, implode("\r\n", $headers), '-fabuse@zurl.ws');
		
		$page = $this->template->body = new View('url/complaint_received');
	}
	
	public function action_preview($alias)
	{
		$url = Shortener::get_url($alias);
		
		if ($url->status != 'ok')
		{
			echo Request::factory('url/invalid/' . $alias)->execute();
			die;
		}
		
		$this->template->title = 'Preview of URL http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$page = $this->template->body = new View('url/preview');
		$page->url = $url;
	}
	
	/**
	 * Little page for stats - The actual work is done in the stats controller which is loaded via AJAX
	 */
	public function action_stats($id)
	{
		// Let's load the URL
		if (($url = Controller_Url_Stats::get_url($id)) === false)
			return;
			
		$this->template->title = 'Statistics for ' . $url->short_url;
		$this->template->body = $page = new View('url/stats');
		$this->template->jsload = 'HitStats.init.pass(' . $url->id . ')';
		$page->url = $url;
	}
	
	/**
	 * Check if the current user should be rate limited
	 */
	private static function exceeded_rate_limit()
	{
		return true; // Temporary, to prevent spam
		
		if (!Session::instance()->get('passed_captcha', false))
			return true;
			
		$count = DB::select(array('COUNT("*")', 'total_count'))
			->from('urls')
			->where('created_ip', '=', $_SERVER['REMOTE_ADDR'])
			->and_where('created_date', '>', time() - self::RATE_PERIOD)
			->execute()
			->get('total_count');
			
		return $count >= self::RATE;
	}
	
	public static function validate_type_member($type)
	{
		return in_array($type, array('standard', 'custom', 'user', 'domain', 'domain_custom'));
	}
	
	public static function validate_type_guest($type)
	{
		return $type == 'standard';
	}
	
	public static function custom_alias_available(Validate $validate, $field)
	{
		if (!ORM::factory('url')->check_custom_alias_available($validate[$field]))
			$validate->error($field, 'alias_available', array($validate[$field]));
	}
	
	/**
	 * Validate whether the current user owns the specified domain
	 */
	public static function validate_domain($domain)
	{
		return ORM::factory('domain', $domain)->user->id == Auth::instance()->get_user()->id;
	}
}
