<!-- Template for internal HMVC requests-->
<?php
defined('SYSPATH') or die('No direct script access.');
if (!empty($jsload))
{
	echo '
		<script type="text/javascript">window.addEvent(\'domready\', ', $jsload, ');</script>';
}


echo $body;
?>
<!-- End template for internal HMVC requests-->