<?php
/*
  $Id: ,v 3.00 2015/06/04

  oscHelpdesk
  http://www.snowtech.com.au

  Copyright (c) 2003 -2015 Snowtech Services

*/

define('HEADING_TITLE', 'HelpDesk');

define('TABLE_HEADING_PRIORITIES', 'Priorities');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_PRIORITIES', 'Priorities:');

define('TEXT_INFO_HEADING_NEW_PRIORITY', 'New Priority');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new priority with its related data');

define('TEXT_INFO_HEADING_EDIT_PRIORITY', 'Edit Priority');
define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_HEADING_DELETE_PRIORITY', 'Delete Priority');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this helpdesk priority?');

define('ERROR_REMOVE_DEFAULT_HELPDESK_PRIORITY', 'Error: The default helpdesk priority can not be removed. Please set another helpdesk priority as default, and try again.');
define('ERROR_PRIORITY_USED_IN_ENTRIES', 'Error: This helpdesk priority is currently used in entries.');
?>