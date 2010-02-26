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
			//$('alias_p').setStyle('display', 'none');
			$('type_standard').addEvent('click', Shorten.change_type);
			$('type_custom').addEvent('click', Shorten.change_type);
			$('type_user').addEvent('click', Shorten.change_type);
			
			Shorten.change_type();
		}
	},
	
	change_type: function()
	{
		if ($('type_custom').checked)
		{
			$('alias_p').setStyle('display', '');
			$('prefix').set('html', 'c');
		}
		else if ($('type_user').checked)
		{
			$('alias_p').setStyle('display', '');
			$('prefix').set('html', $('current_user').get('text'));
		}
		else
		{
			$('alias_p').setStyle('display', 'none');
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