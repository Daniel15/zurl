<?php defined('SYSPATH') or die('No direct script access.'); ?>
			<p><a href="<?php echo URL::site('') ?>">&larr; Back to your account</a></p>
			
			<div id="chart_container">
				<ul id="types">
					<li id="type_month" class="selected">Last 30 days</li>
					<li id="type_year">Last 365 days (year)</li>
				</ul>
				
				<div id="chart_area">
					<p id="loading_chart">
						<img src="res/spinner_chart.gif" alt="Loading..." title="Loading..." /><br />
						<span id="loading_text">Loading...</span>
					</p>
					<div id="chart"></div>
				</div>
			</div>
			
			<!-- JS required by the hits charts -->
			<!-- TODO: Move this to a common file used by all graphs? -->
			<script type="text/javascript" src="http://www.google.com/jsapi"></script>
			<script type="text/javascript">
				HitStats.url_id = <?php echo $url->id; ?>;
				google.load('visualization', '1', {packages:['areachart']});
				//google.load('visualization', '1', {packages:['columnchart']});
				google.setOnLoadCallback(HitStats.lib_loaded);
			</script>