<?php defined('SYSPATH') or die('No direct script access.'); ?>
			<p>Welcome to zURL.ws! We provide a free URL shortener, to make your looooooong URLs short. Tired of sending around long, manageable URLs? Need to send a long URL by text message or post it on Twitter? By entering a URL in the box below, we'll create a short URL that will never expire.</p>

<?php
echo $shorten;

// Are they logged in?
if (!$logged_in)
{
?>

			<h2>Accounts</h2>
			<p>The basic URL shortening service is available without registration. However, registering for an account allows you to keep track of your URLs, and see hit statistics. Take a look on the <a href="about.htm">About page</a> for more.</p>

<?php
}
else
{
	echo '
			<h2>Your Account</h2>
', $account;
}
?>
			
			<h2>News</h2>
			<h3>?? 2010</h3>
			<p>After a long period of inactivity, a new zURL site is finally up! This adds a large number of new features to zURL, including advanced statistics and other stuff that will be documented when the site is finished.</p>
			