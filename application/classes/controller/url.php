<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Url extends Controller_Template
{
	protected $internal_template = array('invalid');
	protected $secure_actions = array('delete');
	
	// Only allow [rate] URLs in [period] seconds before showing a CAPTCHA to guests
	const RATE = 4;
	const RATE_PERIOD = 60;
	
	public function action_index()
	{
		// Wrong domain? Redirect to the correct one.
		if (!in_array($_SERVER['HTTP_HOST'], array('zurl.ws', 'www.zurl.ws', 'dev.zurl.ws', 'dev.zurl.ws:82')))
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
		// TODO: move these validation rules into the MODEL!
		$post = Validate::factory($_POST)
			->rule(true, 'not_empty')
			->filter(true, 'trim')
			->rule('token', 'csrf::valid')
			->rule('url', 'url')
			//->callback('url', 'Uribl::validate')
			;
		
		// If they're logged in, they might have chosen a custom type
		if ($this->logged_in)
		{
			$post->rule('type', 'Controller_Url::validate_type_member');
		
			if (in_array(Arr::get($_POST, 'type'), array('custom', 'user')))
			{
				$post
					->rule('alias', 'not_empty')
					->filter('alias', 'strtolower')
					->rule('alias', 'regex', array('~^[a-z0-9\-_]+$~'));
					
				if ($_POST['type'] == 'custom')
					$post->callback('alias', 'Controller_Url::custom_alias_available');
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
			if (!$this->logged_in && self::exceeded_rate_limit())
			{
				$page->shorten->captcha = Recaptcha::get_html();
			}

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
		$url = Shortener::get_url(substr($url_pieces['path'], 1), $url_pieces['host']);

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

		mail('zurl@d15.biz', '[URL ' . $url->id . '] ' . $post['url'], $email, 'From: "zURL Abuse" <abuse@zurl.ws>');
		
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
	
	public function action_test()
	{
		for ($i = 0; $i < 9000; $i++)
		{
			$j = Shortener::alias_to_id(Shortener::id_to_alias($i));
			if ($i != $j)
			{
				echo 'MISMATCH. ', $i, ' != ', $j, '<br />';
			}
		}
	}
	
	/**
	 * Check if the current user should be rate limited
	 */
	private static function exceeded_rate_limit()
	{
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
		return in_array($type, array('standard', 'custom', 'user'));
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
}
