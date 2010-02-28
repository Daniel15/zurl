<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Url_Stats extends Controller_Template
{
	protected $auth_required = true;
	
	public function action_hits_month($id)
	{
		$num_days = 30;
		
		if (($url = $this->get_url($id)) === false)
			return;
			
		$start_date = new DateTime();
		$start_date->setTime(0, 0);
		$start_date->modify('-' . $num_days . ' days');
		
		$end_date = new DateTime();
		$end_date->setTime(0, 0);
		
		// Get some defaults - This way, we will ALWAYS have 30 days, even if there is no database
		// records for the previous 30 days.  They will default to 0.
		$defaults = array();
		for ($date = clone $start_date; $date <= $end_date; $date->modify('+1 day'))
		{
			$defaults[$date->format('Y-m-d')] = 0;
		}
			
		// Here's our logic!
		$days = DB::select(array('DATE("date")', 'date'), array('COUNT("*")', 'count'))
			->from('hits')
			->where('url_id', '=', $url->id)
			->and_where('date', '>=', $start_date->format('Y-m-d H:i:s'))
			->group_by('DATE("date")')
			->execute();
		
		$results = array();
		$total = 0;
		
		foreach ($days as $day)
		{
			$results[$day['date']] = $day['count'];
			$total += $day['count'];
		}
		
		// Overwrite the defaults with the new results.
		$results = Arr::merge($defaults, $results);
		
		die(json_encode(array(
			'error' => false,
			'title' => 'Hits to ' . $url->short_url . ' from ' . $start_date->format('Y-m-d') . ' to ' . $end_date->format('Y-m-d'), 
			'total' => $total,
			'data' => $results,
		)));
	}
	
	public function action_hits_year($id)
	{
		if (($url = $this->get_url($id)) === false)
			return;
			
		//$format = 'M Y';
		$format = 'M';
			
		$start_date = new DateTime();
		$start_date->setTime(0, 0);
		$start_date->setDate(date('Y') - 1, date('m') + 1, 1);
		
		$end_date = new DateTime();
		$end_date->setTime(0, 0);
		$end_date->setDate(date('Y'), date('m'), 1);
		
		$defaults = array();
		
		for ($date = clone $start_date; $date <= $end_date; $date->modify('+1 month'))
		{
			$defaults[$date->format($format)] = 0;
		}
			
		// Here's our logic!
		$months = DB::select(array('YEAR("date")', 'year'), array('MONTH("date")', 'month'), array('COUNT("*")', 'count'))
			->from('hits')
			->where('url_id', '=', $url->id)
			->and_where('date', '>=', $start_date->format('Y-m-d H:i:s'))
			->group_by('YEAR("date")')
			->group_by('MONTH("date")')
			->execute();
		
		$results = array();
		$total = 0;
		
		foreach ($months as $month)
		{
			//$results[$month['year'] . '-' . $month['month']] = $month['count'];
			$month_name = date($format, mktime(0, 0, 0, $month['month'], 1, $month['year']));
			$results[$month_name] = $month['count'];
			$total += $month['count'];
		}
		
		// Overwrite the defaults with the new results.
		$results = Arr::merge($defaults, $results);
		
		die(json_encode(array(
			'error' => false,
			'title' => 'Hits to ' . $url->short_url . ' from ' . $start_date->format('F Y') . ' to today',
			'total' => $total,
			'data' => $results,
		)));
	}
	
	public function action_browsers_month($id)
	{
		$num_days = 30;
			
		$start_date = new DateTime();
		$start_date->setTime(0, 0);
		$start_date->modify('-' . $num_days . ' days');
		
		return $this->browsers($id, $start_date);
	}
	
	public function action_browsers_year($id)
	{
		$start_date = new DateTime();
		$start_date->setTime(0, 0);
		$start_date->setDate(date('Y') - 1, date('m') + 1, 1);
		
		return $this->browsers($id, $start_date);
	}
	
	private function browsers($id, $start_date)
	{
		if (($url = $this->get_url($id)) === false)
			return;
			
		$end_date = new DateTime();
		$end_date->setTime(0, 0);
			
		// Here's our logic!
		$browsers = DB::select('browser', array('COUNT("*")', 'count'))
			->from('hits')
			->where('url_id', '=', $url->id)
			->and_where('date', '>=', $start_date->format('Y-m-d H:i:s'))
			->group_by('browser')
			->order_by('count', 'desc')
			->execute();
		
		$results = array();
		$total = 0;
		
		foreach ($browsers as $browser)
		{
			$icon = str_replace(' ', '_', strtolower($browser['browser']));
			
			$results[$browser['browser']] = array(
				'count' => $browser['count'],
				'text' => '<img src="res/browsers/' . $icon . '.png" width="16" height="16" title="' . $browser['browser'] . '" alt="" class="favicon" /> ' . $browser['browser']
			);
			$total += $browser['count'];
		}
		
		die(json_encode(array(
			'error' => false,
			'title' => 'Browsers to ' . $url->short_url . ' from ' . $start_date->format('Y-m-d') . ' to ' . $end_date->format('Y-m-d'), 
			'total' => $total,
			'type' => 'PieChart',
			'data' => $results,
		)));
	}
	
	public function action_referrers_month($id)
	{
		$num_days = 30;
			
		$start_date = new DateTime();
		$start_date->setTime(0, 0);
		$start_date->modify('-' . $num_days . ' days');
		
		return $this->referrers($id, $start_date);
	}
	
	public function action_referrers_year($id)
	{
		$start_date = new DateTime();
		$start_date->setTime(0, 0);
		$start_date->setDate(date('Y') - 1, date('m') + 1, 1);
		
		return $this->referrers($id, $start_date);
	}
	
	private function referrers($id, $start_date)
	{
		if (($url = $this->get_url($id)) === false)
			return;
			
		$end_date = new DateTime();
		$end_date->setTime(0, 0);
			
		// Here's our logic!
		$referrers = DB::select('referrer_domain', array('COUNT("*")', 'count'))
			->from('hits')
			->where('url_id', '=', $url->id)
			->and_where('date', '>=', $start_date->format('Y-m-d H:i:s'))
			->and_where('referrer_domain', '!=', '')
			->group_by('referrer_domain')
			->order_by('count', 'desc')
			->execute();
		
		$results = array();
		$total = 0;
		
		foreach ($referrers as $referrer)
		{
				
			$results[$referrer['referrer_domain']] = array(
				'count' => $referrer['count'], 
				'text' => '<img src="favicons/' . htmlspecialchars($referrer['referrer_domain']) . '.ico" width="16" height="16" title="' . htmlspecialchars($referrer['referrer_domain']) . '" alt="" class="favicon" /> <a href="http://' . htmlspecialchars($referrer['referrer_domain']) . '/" rel="nofollow">' . htmlspecialchars($referrer['referrer_domain']) . '</a>',
			);
			$total += $referrer['count'];
		}
		
		die(json_encode(array(
			'error' => false,
			'title' => 'Referrers to ' . $url->short_url . ' from ' . $start_date->format('Y-m-d') . ' to ' . $end_date->format('Y-m-d'), 
			'total' => $total,
			'type' => 'PieChart',
			'data' => $results,
		)));
	}
	
	public function action_countries_month($id)
	{
		$num_days = 30;
			
		$start_date = new DateTime();
		$start_date->setTime(0, 0);
		$start_date->modify('-' . $num_days . ' days');
		
		return $this->countries($id, $start_date);
	}
	
	public function action_countries_year($id)
	{
		$start_date = new DateTime();
		$start_date->setTime(0, 0);
		$start_date->setDate(date('Y') - 1, date('m') + 1, 1);
		
		return $this->countries($id, $start_date);
	}
	
	private function countries($id, $start_date)
	{
		if (($url = $this->get_url($id)) === false)
			return;
			
		$end_date = new DateTime();
		$end_date->setTime(0, 0);
		
		// Here's our logic!
		$countries = DB::select('country', array('countries.printable_name', 'country_name'), array('COUNT("*")', 'count'))
			->from('hits')
			->join('countries', 'left')->on('countries.iso', '=', 'hits.country')
			->where('url_id', '=', $url->id)
			->and_where('date', '>=', $start_date->format('Y-m-d H:i:s'))
			->group_by('country')
			->group_by('country_name')
			->order_by('count', 'desc')
			->execute();
		
		$results = array();
		$total = 0;
		
		foreach ($countries as $country)
		{
			if ($country['country_name'] == '')
				$country['country_name'] = 'Unknown';
				
			$results[$country['country_name']] = array(
				'count' => $country['count'],
				'text' => '<img src="res/flags/' . strtolower($country['country']) . '.png" alt="' . $country['country'] . '" class="favicon" /> ' . $country['country_name'] . ' (' . $country['country'] . ')',
			);
			$total += $country['count'];
		}
		
		die(json_encode(array(
			'error' => false,
			'title' => 'Countries to ' . $url->short_url . ' from ' . $start_date->format('Y-m-d') . ' to ' . $end_date->format('Y-m-d'), 
			'total' => $total,
			'type' => 'GeoMap',
			'data' => $results,
		)));
	}
	
	/**
	 * Get a URL. Returns false or shows an error if the URL is invalid, or not owned by the current user.
	 * @param int ID of the URL
	 */
	public static function get_url($id)
	{
		$url = ORM::factory('url', $id);
		if (!$url->loaded() || $url->user_id != Auth::instance()->get_user()->id)
		{
			if (Request::$is_ajax)
			{
				die(json_encode(array(
					'error' => true,
					'message' => 'Invalid URL'
				)));
			}
			else
			{
				$this->template->body = 'Invalid URL.';
			}
				
			return false;
		}
		
		return $url;
	}
}
?>