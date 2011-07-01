<?php defined('SYSPATH') or die('No direct script access.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<base href="<?php echo URL::base('', 'http'); ?>" />
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="res/layout.css" />
	<link rel="stylesheet" type="text/css" href="res/style.css" />
	<title><?php echo !empty($title) ? $title . ' &mdash; ' : ''; ?>zURL, a free URL shortener</title>
	<!-- TODO: Compress these down to one file -->
	<script type="text/javascript" src="res/mootools-1.2.4-core-yc.js"></script>
	<script type="text/javascript" src="res/ZeroClipboard.js"></script>
	<script type="text/javascript" src="res/script.js"></script>
	<script type="text/javascript">
		var site_url = '<?php echo URL::site('', 'http'); ?>';
		var base_url = '<?php echo URL::base('', 'http'); ?>';<?php
if (!empty($jsload))
{
	echo '
		window.addEvent(\'domready\', ', $jsload, ');';
}
?>

	</script>
</head>

<body<?php if (!empty($sidebar)) echo ' class="has-sidebar"'?>>
	<!-- Here come the IE hacks -->
	<!--[if IE]><div class="ie"><![endif]-->
	<!--[if IE 7]><div class="ie7"><![endif]-->
	<!--[if IE 6]><div class="ie6"><![endif]-->
	<div id="header">
		<div id="head_login">
<?php if (!$logged_in): ?>
			<p id="head_login_prompt">Not logged in. <a id="head_login_link" href="<?php echo URL::site('account/login'); ?>">Log in</a><?php if(Kohana::config('app.allow_registration')): ?> or <a href="<?php echo URL::site('account/register'); ?>">register</a><?php endif; ?>.</p>
			<form id="head_login_form" action="<?php echo URL::site('account/login'); ?>" method="post">
				Log in: 
				<fieldset>
					<input type="text" name="username" value="Username" class="no-value" />
					<input type="password" name="password" value="Password" class="no-value" />
					<input type="submit" value="Login" />
					<input type="hidden" name="timezone" id="head_timezone" />
				</fieldset>					
			</form>
<?php else: ?>
			<p id="head_login_prompt">Logged in as <a href="<?php echo URL::site('account'); ?>"><span id="current_user"><?php echo $user->username; ?></span></a>. <a href="<?php echo URL::site('account/logout'); ?>">Log out.</a></p>
<?php endif; ?>
		</div>
		<h1><a href="/">zURL</a></h1>
		<h2><?php echo !empty($title) ? $title : 'Untitled Page'; ?></h2>
	</div>
	<ul id="nav">
		<li><a href="<?php echo URL::site(''); ?>"><img src="res/icons/house.png" alt="Home" width="16" height="16" /> Home</a></li>
		<!--li><a href="<?php echo URL::site('account/url'); ?>"><img src="res/icons/link.png" alt="URLs" width="16" height="16" /> My URLs</a></li-->
		<li><a href="<?php echo URL::site('about.htm'); ?>"><img src="res/icons/book.png" alt="About" width="16" height="16" /> About zURL</a></li>
		<li><a href="<?php echo URL::site('url/complaint'); ?>"><img src="res/icons/bomb.png" alt="Report Spam" width="16" height="16" /> Report a spam URL</a></li>
<?php if (!$logged_in): ?>
<?php if (Kohana::config('app.allow_registration')): ?>		<li><a href="<?php echo URL::site('account/register'); ?>"><img src="res/icons/user_add.png" alt="Register" width="16" height="16" /> Register</a></li><?php endif; ?>
<?php endif; ?>
	</ul>
	<div id="container">
		<div id="body">
			<noscript><p class="errors"><img src="res/icons/exclamation.png" alt="Error" width="16" height="16" /> Please enabled JavaScript for added awesomeness. Some of the features on zURL work best with JavaScript enabled.</p></noscript>
<?php
// Do we have a top message?
if (!empty($top_message))
{
	echo '
			<p id="top_message"><img src="res/icons/information.png" alt="Information" width="16" height="16" /> ', $top_message, '</p>';
}

echo $body; 
?>
		</div>
<?php
// Do we have a sidebar?
if (!empty($sidebar))
{
	echo '
		<div id="sidebar">', $sidebar, '</div>';
}
?>
	</div>
	<div id="footer">
		<p>&copy; 2010 <a href="http://dan.cx/">Daniel15</a>. Powered by zURL and <a href="http://kohanaphp.com/">Kohana</a> 3.0.3. Page created in {execution_time}<!-- using {memory_usage} memory-->. All times are in 
<?php
$timezone = Date::get_timezone();
if ($timezone < 0)
	echo 'GMT', $timezone;
elseif ($timezone == 0)
	echo 'GMT';
else
	echo 'GMT+', $timezone;
?>. <a href="<?php echo URL::site('about.htm'); ?>">Terms of Service</a> | <a href="<?php echo URL::site('url/complaint'); ?>">Report abuse</a> </p>	
	</div>
	<!--[if IE]></div></div><![endif]-->
	<?php if (!IN_PRODUCTION) echo View::factory('profiler/stats'); ?>
</body>
</html>
