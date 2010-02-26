<?php defined('SYSPATH') or die('No direct script access.'); ?>

			<p>Welcome back, <?php echo Auth::instance()->get_user()->username; ?>. You currently have <span id="url_count"><?php echo $count; ?></span> URLs in your account.</p>
<?php if ($count != 0) : ?>
			<!-- Yes, inline styles. Must remove these eventually. -->
			<table cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th style="width: 3.5em"></th>
						<th class="date">Date Added</th>
						<th class="shorturl">Short URL</th>
						<th>Long URL</th>
						<th style="width: 1em">Hits</th>
						<th class="datetime">Last Hit</th>
					</tr>
				</thead>
				<tbody>
<?php
foreach ($urls as $url)
{
	echo '
					<tr id="url_', $url->id, '">
						<td>
							<img src="res/icons/bin_closed.png" alt="Delete" title="Delete" class="icon delete" width="16" height="16" />
							<!--a href="', Url::site('account/url/stats/' . $url->id), '"><img src="res/icons/chart_bar.png" alt="Graphs" title="Graphs" class="icon"  width="16" height="16" /></a-->
						</td>
						<td>', Date::format($url->created_date, false), '</td>
						<td><a href="', $url->short_url, '">', $url->short_url, '</a></td>
						<td><img src="http://', htmlspecialchars($url->url_domain), '/favicon.ico" width="16" height="16" title="', htmlspecialchars($url->url_domain), '" alt="" class="favicon" /> ',  htmlspecialchars($url->url), '</td>
						<td>', $url->hits, '</td>
						<td>', $url->last_hit == 0 ? 'Never' : Date::format($url->last_hit, true), '</td>
					</tr>';
}
?>
				</tbody>
			</table>
<?php endif; ?>
			
			
			<?php echo $pagination; ?>
			
			<h2>Recent Visitors</h2>
<?php if (count($visits) == 0) : ?>
				<p>Your URLs haven't had any visitors yet!</p>
<?php else: ?>
			<!-- Yes, inline styles. Must remove these eventually. -->
			<table cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th class="datetime">Date</th>
						<th class="shorturl">Short URL</th>
						<th>Long URL</th>
						<th>Referrer</th>
						<th>Country</th>
					</tr>
				</thead>
				<tbody>
<?php
foreach ($visits as $visit)
{
	$short_url = Model_Url::get_short_url($visit->id, $visit->type, $visit->custom_alias, $visit->user_id);
	
	echo '
				<tr>
					<td>', Date::format($visit->date, true), '</td>
					<td><a href="', $short_url, '">', $short_url, '</a></td>
					<td><img src="http://', htmlspecialchars($visit->url_domain), '/favicon.ico" width="16" height="16" title="', htmlspecialchars($visit->url_domain), '" alt="" /> ', htmlspecialchars($visit->url), '</td>
					<td>';
					
	if ($visit->referrer_domain == null)
	{
		echo 'None (direct)';
	}
	else
	{
		echo '<img src="http://', htmlspecialchars($visit->referrer_domain), '/favicon.ico" width="16" height="16" title="', htmlspecialchars($visit->referrer_domain), '" alt="" class="favicon" /> <a href="', htmlspecialchars($visit->referrer), '" rel="nofollow">', htmlspecialchars($visit->referrer_domain), '</a>';
	}
	
	echo '</td>
					<td>';
	if ($visit->country == null)
	{
		echo 'Unknown';
	}
	else
	{
		echo '<img src="res/flags/', strtolower($visit->country), '.png" alt="', $visit->country, '" /> ', $visit->country_name, ' (', $visit->country, ')';
	}
	
	echo '</td>
				</tr>';
}
?>
				</tbody>
			</table>
<?php endif; ?>