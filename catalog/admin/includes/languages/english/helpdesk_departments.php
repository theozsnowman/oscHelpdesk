<?php
/*
  $Id: ,v 3.00 2015/06/04

  oscHelpdesk
  http://www.snowtech.com.au

  Copyright (c) 2003 -2015 Snowtech Services

*/

define('HEADING_TITLE', 'HelpDesk');

define('TABLE_HEADING_DEPARTMENTS', 'Departments');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_DEPARTMENT', 'Department:');
define('TEXT_INFO_EMAIL_ADDRESS', 'E-Mail Address:');
define('TEXT_INFO_NAME', 'Name:');
define('TEXT_INFO_PASSWORD', 'Password:');

define('TEXT_INFO_HEADING_NEW_DEPARTMENT', 'New Department');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new department with its related data');

define('TEXT_INFO_HEADING_EDIT_DEPARTMENT', 'Edit Department');
define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');

define('TEXT_INFO_HEADING_DELETE_DEPARTMENT', 'Delete Department');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this helpdesk department?');

define('ERROR_REMOVE_DEFAULT_HELPDESK_DEPARTMENT', 'Error: The default helpdesk department can not be removed. Please set another helpdesk department as default, and try again.');
define('ERROR_DEPARTMENT_USED_IN_ENTRIES', 'Error: This helpdesk department is currently used in entries.');
?>