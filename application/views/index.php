<?php defined('SYSPATH') or die('No direct script access.'); ?>
			<p>Welcome to zURL.ws! We provide a free URL shortener, to make your looooooong URLs short. Tired of sending around long, manageable URLs? Need to send a long URL by text message or post it on Twitter? By entering a URL in the box below, we'll create a short URL that will never expire.</p>

<?php
if ($logged_in)
	echo $shorten;

// Are they logged in?
if (!$logged_in)
{
?>

			<h2>Accounts</h2>
			<p>Services are now no longer available with registering for an account due to abuse. Registering with us allows you to keep track of your URLs, and see hit statistics. Take a look on the <a href="about.htm">About page</a> for more.</p>

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
			<h3>6th January 2011</h3>
			<p>Due to the amount of spam and my lack of time to remove it all, you are now required to create an account in order to use zURL. Sorry for the inconvenience, but this should significantly reduce spam.</p>
			
			<h3>28th February 2010</h3>
			<p>After a long period of inactivity, a new zURL site is finally up! This adds a large number of new features to zURL, including (for registered members) hit statistics include referrals, country and web browser of people that visit your URLs, as well as hit statistics for the last 30 days and last year. Note that all old user accounts will not longer work - If you were an old user, you will have to re-register. Please send all feedback to daniel [-at-] d15.biz. Thanks!</p>
			<p>&mdash; Daniel</p>
			
			<h3>19th September 2007</h3>
			<p>Hi everyone, <br />
			zURL.ws now has a unique feature that most other URL shortening services do not provide: custom URLs! Basically, instead of using the standard, automatically-generated shortened URLs (http://zurl.ws/9), you may specify your own alias (for example, http://c.zurl.ws/wii). This allows your URLs to be more descriptive, and more easily memorised. Note that this is only available to members registered for the forum. In the future, I might implement an administration panel, where users can edit or delete any of their URLs.<p>
			<p>Also, the annoying visual verification codes are now no longer displayed to logged in users.</p>
			<p>- Daniel15</p>
			
			<h3>3rd April 2007</h3>
			<p>I'm proud to announce that a beta version of the zURL.ws website is now up! zURL.ws is another one of those services that attempt to make large URLs into nice, small ones. The main advantage over other similar sites is that urls produced by zURL.ws are very small . Note that this is just a beta, and so there aren't really that many features yet. Also, there may be a <strong>few minor bugs</strong> (please tell me if you find any bugs!). Future versions will have more features (a members area where you can see all your URLs, and possibly the ability to have custom URLs).</p>
