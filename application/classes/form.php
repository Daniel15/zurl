<?php
defined('SYSPATH') or die('No direct script access.');

class Form extends Kohana_Form
{
	public static function show_errors($errors)
	{
		// Any errors? 
		if (empty($errors))
			return;
			
		echo '
		<div class="errors">
			<p><img src="res/icons/exclamation.png" alt="Error" width="16" height="16" /> ';

		// Only one error?
		if (count($errors) == 1)
		{
			echo current($errors), '</p>';
		}
		else
		{
			echo '
			Some errors were encountered:</p>
			<ul>';

			foreach ($errors as $field => $error)
			{
				echo '
				<li><strong>', $field, '</strong>: ', $error, '</li>';
			}
			echo '
			</ul>';
		}

		echo '
		</div>';
	}
	
	public static function timezone($name, $selected = NULL, array $attributes = NULL)
	{
		$timezones = DateTimeZone::listIdentifiers();
		$options = array(
			'America' => array(),
			'Australia' => array(),
			'Asia' => array(),
			'Atlantic' => array(),
			'Europe' => array(),
			'Indian' => array(),
			'Pacific' => array(),
		);
		
		foreach ($timezones as $timezone)
		{
			if (preg_match('~^(America|Australia|Asia|Atlantic|Europe|Indian|Pacific)/(.+)$~', $timezone, $matches))
			{
				$options[$matches[1]][$timezone] = $matches[2];
			}
		}
		
		//print_r($options);
		
		return Form::select($name, $options, $selected, $attributes);
	//public static function select($name, array $options = NULL, $selected = NULL, array $attributes = NULL)
	
	}
	
}
?>