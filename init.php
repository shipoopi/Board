<?php
/**
 * @author		SilexBoard Team
 *					Cadillaxx
 * @copyright	2011 SilexBoard
 */

// Include required files
require_once('config.inc.php');
require_once('constants.inc.php');
require_once('functions.inc.php');

// Sessions
session_start();

// Connect to MySQL-Database
$connect = new mysql($CFG_Host, $CFG_User, $CFG_Password, $CFG_Database);

// default timezone (for date functions)
date_default_timezone_set('Europe/Berlin');
?>