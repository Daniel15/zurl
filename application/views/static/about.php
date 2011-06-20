<?php defined('SYSPATH') or die('No direct script access.'); ?>

			<h2>So what exactly is zURL?</h2>
			<p>zURL is a URL shortener. It lets you convert your long links into nice, manageable short links. </p>
			<h2>An Example</h2>
			<p>
				Turn this URL:<br />
				<tt>http://www.amazon.com/Nintendo-RVLSWCUSZ-Wii/dp/B0009VXBAQ/ref=pd_bbs_sr_1/102-2807968-1014504?ie=UTF8&amp;s=videogames&amp;qid=1175580663&amp;sr=8-1</tt><br /><br />
				into this nice, tiny URL:<br />
				<tt>http://zurl.ws/1</tt>
			</p>
			
			<h2>Free Membership</h2>
			<p>In order to use the zURL shortening service you must create an account, registering has a number of advantages, including <a href="about.htm#custom">custom URLs</a> and <a href="about.htm#hits">hit statistics</a></p>
			
			<h3 id="custom">Custom URLs</h3>
			<p>
				Custom URLs allow you to specify the short URL to use, rather than using a generic one. For example, you may turn this URL:
				<tt>http://www.amazon.com/Nintendo-RVLSWCUSZ-Wii/dp/B0009VXBAQ/ref=pd_bbs_sr_1/102-2807968-1014504?ie=UTF8&amp;s=videogames&amp;qid=1175580663&amp;sr=8-1</tt><br /><br />
				into this nice, tiny, descriptive URL:<br />
				<tt>http://c.zurl.ws/wii</tt>
			</p>
			
			<p>There are two types of custom URLs: normal custom URLs, and user URLs. Custom URLs:</p>
			<ul>
				<li>Begin with http://c.zurl.ws/</li>
				<li>Are nearly as short as normal zURL links</li>
				<li>Are available on a first-come, first-served basis. In other words, once a user has used an alias, no other user will be able to use it (unless they delete it).</li>
				<li>Example: <a href="http://c.zurl.ws/wii">http://c.zurl.ws/wii</a></li>
			</ul>
			
			<p>User URLs:</p>
			<ul>
				<li>Begin with http://<strong><?php echo $logged_in ? Auth::instance()->get_user()->username : 'username'; ?></strong>.zurl.ws/</li>
				<li>Are a bit longer than normal zURL links, depending on how long your username is</li>
				<li>Will most likely be available, even if the normal custom URL is taken</li>
				<li>Allows you to easily see the URL is owned by you, without clicking on it</li>
				<li>Example: <a href="http://dan.zurl.ws/fb">http://dan.zurl.ws/fb</a></li>
			</ul>
			
			<h3>Hit Statistics</h3>
			<p>Registered users also get advanced statistics on who is visiting their URLs. This includes referrers (what site they come from), and countries.</p> 
			
			<h2>Note</h2>
			<p>zURL is NOT a place to post your spam or affiliate links. We have a strong stance against spam, and any spam links will be DELETED. If you have been sent a spam zURL link, please <a href="<?php echo URL::site('url/complaint'); ?>">report it</a>.</p>
