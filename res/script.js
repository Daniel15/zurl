/*
 * zURL.ws JavaScript file
 * (c) 2010 Daniel15 (http://dan.cx/)
 */
 

/**
 * JS used on the URL listing page
 */
var Listing = 
{
	/**
	 * Initialise the page
	 */
	init:  function()
	{
		// Get all of the delete links
		$$('img.delete').addEvent('click', Listing.del);
	},
	
	/**
	 * Delete a URL
	 */
	del: function()
	{
		var row = this.getParent().getParent()
		var id = row.id.substring(4);
		
		if (!confirm('Are you sure you want to delete this URL?'))
			return;
			
		this.src = 'res/spinner.gif';
		// Send the request
		var myRequest = new Request({
			method: 'post',
			url: site_url + 'url/delete/' + id,
			onSuccess: function(data_text)
			{
				var response = JSON.decode(data_text);
				// Was there an error?
				if (response.error)
				{
					alert('An error occurred while deleting that URL: ' + response.message);
					return;
				}
			}
		}).send();
		
		row.highlight('#FFFFFF', '#FF0000').get('tween').chain(function() { row.dispose() });
		$('url_count').set('html', $('url_count').get('html') - 1);
	},
};

/**
 * JS used on the shorten page
 */
var Shorten = 
{
	init: function()
	{
		if ($('type_custom'))
		{
			$$('#types input').addEvent('click', Shorten.change_type);
			
			Shorten.change_type();
		}
	},
	
	change_type: function()
	{
		if ($('type_custom').checked)
		{
			$('alias_p').setStyle('display', '');
			$('prefix').set('html', 'c');
			if ($('domain_custom_p'))
				$('domain_custom_p').setStyle('display', 'none');
		}
		else if ($('type_user').checked)
		{
			$('alias_p').setStyle('display', '');
			$('prefix').set('html', $('current_user').get('text'));
			if ($('domain_custom_p'))
				$('domain_custom_p').setStyle('display', 'none');
		}
		else if ($('type_domain_custom') && $('type_domain_custom').checked)
		{
			$('alias_p').setStyle('display', 'none');
			$('domain_custom_p').setStyle('display', '');
		}
		else
		{
			$('alias_p').setStyle('display', 'none');
			if ($('domain_custom_p'))
				$('domain_custom_p').setStyle('display', 'none');
		}
	}
}

/**
 * JS used after a URL is shortened
 */
var Shortened =
{
	clipboard: null,
	
	/**
	 * Initialise the page
	 */
	init: function()
	{
		ZeroClipboard.setMoviePath(site_url + '/res/ZeroClipboard.swf');
		this.clipboard = new ZeroClipboard.Client();
		this.clipboard.setHandCursor(true);
		this.clipboard.setText($('shortened').get('text'));
		this.clipboard.glue('copy_clipboard');
		
		// Make the "Shorten Another" link work
		$('shorten_another_show').addEvent('click', function()
		{
			this.destroy();
			var shorten = $('shorten_another');
			shorten.setStyle('display', 'block');
			shorten.fade('hide');
			shorten.fade('in');
			return false;
		});
	}
};

/**
 * JS used on the registration page
 */
var Register = 
{
	/**
	 * Initialise the page
	 */
	init: function()
	{
		$('username').addEvent('blur', Register.check_name);
	},
	
	check_name: function()
	{
		var check = $('username_check');
		
		check.set('html', '<img src="res/spinner.gif" alt="Loading..." title="Loading..." />');
		// We need a username, HURR DURR
		if (this.value == '')
		{
			check.set('html', 'Please enter a username!');
			return;
		}
		
		// Check if there's any disallowed characters
		else if (this.value.test('[^A-Za-z0-9\-_]'))
		{
			check.set('html', 'The username contains invalid characters');
			return;
		}
			
		var myRequest = new Request({
			method: 'post',
			url: base_url + 'account/check_username',
			data: 'username=' + this.value,
			onSuccess: function(data_text)
			{
				// JSON decode the data
				var data = JSON.decode(data_text);
				// Is it available?
				if (data.available)
				{
					check.set('html', '<img src="res/icons/accept.png" alt="This username is available." title="This username is available." />');
					$('submit').disabled = false;
				}
				else
				{
					check.set('html', '<img src="res/icons/cancel.png" /> This username is not available');
					$('submit').disabled = true;
				}
			}
		}).send();
	}
}
 
/**
 * Stuff used on every page
 */
var Page = 
{
	/**
	 * Initialise the page
	 */
	init: function()
	{
		// Get the timezone for the login forms
		var timezone = -new Date().getTimezoneOffset() / 60;
		
		// Do we have a login link?
		if ($('head_login_link'))
		{
			$('head_login_link').addEvent('click', function()
			{
				var form = $('head_login_form');
				$('head_login_prompt').setStyle('display', 'none');
				form.fade('hide');
				form.setStyle('display', 'block');
				form.fade('in');
				return false;
			});
			$('head_timezone').value = timezone;
		}
		
		// Do we have a login form?
		var timezoneEl = $('timezone');
		if (timezoneEl)
			timezoneEl.value = timezone;
		// Do we have any form fields that have to be filled out?
		$$('input.no-value').addEvent('focus', Page.field_focus);
	},
	
	/**
	 * Remove the default text from a field when it is focused 
	 */
	field_focus: function()
	{
		if (this.retrieve('default') == null)
			this.store('default', this.value);
		
		if (this.value == this.retrieve('default'))
		{
			this.removeClass('no-value');
			this.value = '';
			this.addEvent('blur', Page.field_blur);
		}
	},
	
	/**
	 * Add the default text back to the field if it's empty 
	 */
	field_blur: function()
	{
		// Only set this back if it's still empty
		if (this.value == '')
		{
			this.addClass('no-value');
			this.value = this.retrieve('default');
			this.addEvent('focus', Page.field_focus);
		}
	}
};

window.addEvent('domready', Page.init);

/*********** Begin statistics stuff **************/

var HitStats = 
{
	url_id: null,
	type: 'hits',
	timespan: 'month',
	
	init: function()
	{
		// Attach the tab click handlers
		$$('ul#types li').addEvent('click', HitStats.type_click);
		$$('ul#timespans li').addEvent('click', HitStats.timespan_click);
	},
	
	lib_loaded: function()
	{
		// Do we have a type?
		if (window.location.hash)
		{
			var hash = window.location.hash.substring(1).split('_');
			HitStats.type = hash[0];
			HitStats.timespan = hash[1];
		}
		
		HitStats.get_graph();
	},
	
	type_click: function()
	{
		HitStats.type = this.id.substring(5);
		// Update the graph
		HitStats.get_graph();
	},
	
	timespan_click: function()
	{
		HitStats.timespan = this.id.substring(5);
		// Update the graph
		HitStats.get_graph();
	},
	
	get_graph: function()
	{	
		$('loading_chart').setStyle('display', 'block');
		$('chart').set('html', '');
		$('loading_text').set('html', 'Getting data...');
	
		window.location.hash = HitStats.type + '_' + HitStats.timespan;
		
		// Update the selected tabs
		$$('ul#types li').removeClass('selected');
		$$('ul#timespans li').removeClass('selected');
		$('type_' + HitStats.type).addClass('selected');
		$('time_' + HitStats.timespan).addClass('selected');
		
		// Get the data table, hide it, and delete all rows
		var data_table = $('chart_data');
		data_table.setStyle('display', 'none');
		
		for (var i = data_table.tBodies[0].rows.length; i; i--)
		{
			data_table.tBodies[0].deleteRow(i - 1);
		}
		
		var myRequest = new Request(
		{
			method: 'post',
			url: base_url + 'url_stats/' + HitStats.type + '_' + HitStats.timespan + '/' + HitStats.url_id,
			onSuccess: function(data_text)
			{
				var data_table = $('chart_data');
				// JSON decode the data
				var response = JSON.decode(data_text);
				// Was there an error?
				if (response.error)
				{
					alert('An error occurred while getting the graph data: ' + response.message);
					return;
				}
				
				// Add all the data to the graph
				var data = $H(response.data);
				var chartData = new google.visualization.DataTable();
				chartData.addColumn('string', 'Date');
				chartData.addColumn('number', 'Hits');
				chartData.addRows(data.getLength());
				
				var i = -1;
				
				data.each(function(val, label)
				{
					chartData.setValue(++i, 0, label);
					var data_row = new Element('tr');
					
					// If we just have a number, use that. Otherwise, it's an object with count and text.
					if (typeof val != 'object')
					{
						chartData.setValue(i, 1, +val);
						new Element('td', {'html': label}).inject(data_row);
						new Element('td', {'html': val}).inject(data_row);
					}
					else
					{
						chartData.setValue(i, 1, +val.count);
						new Element('td', {'html': val.text}).inject(data_row);
						new Element('td', {'html': val.count}).inject(data_row);
					}
					
					
					data_row.inject(data_table.tBodies[0]);
				});
				
				$('loading_chart').setStyle('display', 'none');
				
				type = response.type ? response.type : 'AreaChart'
					
				//var chart = new google.visualization.ColumnChart(document.getElementById('chart'));
				// Make a graph based on the type we need
				var chart = new google.visualization[type](document.getElementById('chart'));
				var chartSettings = 
				{
					width: '100%',
					//height: '100%',
					height: '400px',
					//is3D: true,
					legend: response.type == 'PieChart' ? 'right' : 'none',
					title: response.title
				};
				
				if (type == 'LineChart')
				{
					chartSettings.colors = ['#199e99'];
				}
				
				chart.draw(chartData, chartSettings);
				data_table.setStyle('display', 'table');
			}
		}).send();
	}
};