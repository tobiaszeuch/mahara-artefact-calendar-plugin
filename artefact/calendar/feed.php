<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-calendar
 * @author     Angela Karl, Uwe Boettcher
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Technische Universitaet Darmstadt, Germany
 *
 */


define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', '');
define('HOME', 1);

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(dirname(dirname(dirname(__FILE__))).'/artefact/lib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/artefact/calendar/lib.php')      ;
require_once(dirname(dirname(dirname(__FILE__))).'/artefact/plans/lib.php')  ;

// offset and limit
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 1000);

$userkey = $_GET['fid'];
$user = $_GET['uid'];

if(!$userkey)
	echo get_string('missingparamid', 'error').": feed id";
else if(!$user)
	echo get_string('missingparamid', 'error').": user id";
else {
	$plans = ArtefactTypeCalendar::get_plans_of_user($user, $offset, $limit);
	$feed = ArtefactTypeCalendar::build_feed($plans, $user, $userkey);
	echo $feed;
}	

?>
