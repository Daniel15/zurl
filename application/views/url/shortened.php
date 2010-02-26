<?php defined('SYSPATH') or die('No direct script access.'); ?>

			<p>Your original URL was:</p>
			<blockquote><?php echo htmlspecialchars($original); ?></blockquote>
			
			<p>Your nice shortened URL:</p>
			<blockquote>
				<span id="shortened"><?php echo $shortened; ?></span>
				<span id="copy_clipboard">Copy to Clipboard</span>
			</blockquote>
			
			<p>Give your recipient confidence with a preview URL. Before they redirect, this URL will show them where they'll be redirected to (<a href="<?php echo $preview; ?>">show me</a>):</p>
			<blockquote><?php echo $preview; ?></blockquote>
			
			<a href="<?php echo URL::site('url/shorten'); ?>" id="shorten_another_show">Shorten another?</a>
			<div id="shorten_another">
				<?php echo $shorten; ?>
			</div>