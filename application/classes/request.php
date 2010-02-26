<?php 
defined('SYSPATH') or die('No direct script access.');
class Request extends Kohana_Request
{
	public function execute()
	{
		parent::execute();
		
		if ($this->response)
		{
			// Get the total memory and execution time
			$total = array(
				'{memory_usage}'   => number_format((memory_get_peak_usage() - KOHANA_START_MEMORY) / 1024, 2) . 'KB',
				'{execution_time}' => number_format(microtime(TRUE) - KOHANA_START_TIME, 5) . ' seconds');
		 
			// Insert the totals into the response
			$this->response = strtr((string) $this->response, $total);
		}
		
		return $this;
	}
}
?>