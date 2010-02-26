<?php
defined('SYSPATH') OR die('No direct access allowed.');

class Model_Complaint extends ORM
{
	protected $_belongs_to = array('url' => array());
}

?>