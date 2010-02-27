<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Url_Stats extends Controller_Template
{
	protected $auth_required = true;
	
	public function action_hits($id)
	{
		// Let's load the URL
		if (($url = $this->get_url($id)) === false)
			return;
			
		$this->template->title = 'Statistics for ' . $url->short_url;
		$this->template->body = $page = new View('url/stats/hits');
		$this->template->jsload = 'HitStats.init.pass(' . $url->id . ')';
		$page->url = $url;
	}
	
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
	
	/**
	 * Get a URL. Returns false or shows an error if the URL is invalid, or not owned by the current user.
	 * @param int ID of the URL
	 */
	private function get_url($id)
	{
		$url = ORM::factory('url', $id);
		if (!$url->loaded() || $url->user_id != $this->user->id)
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