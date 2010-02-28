<?php defined('SYSPATH') or die('No direct script access.'); ?>
			<p><a href="<?php echo URL::site('') ?>">&larr; Back to your account</a></p>
			
			<div id="chart_container">
				<ul id="types">
					<li id="type_hits" class="selected">Hits</li>
					<li id="type_browsers">Browsers</li>
					<li id="type_referrers">Referrers</li>
					<li id="type_countries">Countries</li>
				</ul>
				
				<div id="chart_area">
					<ul id="timespans">
						<li id="time_month" class="selected">Last 30 days</li>
						<li id="time_year">Last 12 months</li>
					</ul>

					<p id="loading_chart">
						<img src="res/spinner_chart.gif" alt="Loading..." title="Loading..." /><br />
						<span id="loading_text">Loading...</span>
					</p>
					<div id="chart"></div>
				</div>
				
			
			<table id="chart_data" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th>Value</th>
						<th>Count</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
			</div>
			
			<!-- JS required by the hits charts -->
			<!-- TODO: Move this to a common file used by all graphs? -->
			<script type="text/javascript" src="http://www.google.com/jsapi"></script>
			<script type="text/javascript">
				HitStats.url_id = <?php echo $url->id; ?>;
				google.load('visualization', '1', {packages:['areachart', 'piechart', 'geomap']});
				google.setOnLoadCallback(HitStats.lib_loaded);
			</script>