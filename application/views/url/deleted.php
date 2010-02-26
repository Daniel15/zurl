<?php defined('SYSPATH') or die('No direct script access.'); ?>
	<div class="errors">
		<p><img src="res/icons/exclamation.png" alt="Error" width="16" height="16" /> <?php echo $message; ?></p>
		<p><?php echo !empty($message2) ? $message2 : ''; ?></p>
	</div>