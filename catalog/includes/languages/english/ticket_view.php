<?php
/*
  $Id: ,v 3.00 2015/06/04

  oscHelpdesk
  http://www.snowtech.com.au

  Copyright (c) 2003 -2015 Snowtech Services

*/

define('HEADING_TITLE', 'View Support Ticket');
define('NAVBAR_TITLE', 'View Support Ticket');

define('TEXT_SUCCESS', 'We received a new update on a support enquiry.');
define('TEXT_YOUR_TICKET_ID','Ticket Nr:');
define('TEXT_CHECK_YOUR_TICKET','You can view the ticket at following link:');
define('TICKET_EMAIL_SUBJECT', 'New Support Ticket: ');
define('TICKET_EMAIL_SUBJECT_RESPONSE','Response to ');

define('TICKET_EMAIL_MESAGE_HEADER'," ");
define('TICKET_EMAIL_MESAGE_FOOTER',"Please login to admin panel to answer this enquiry");
define('TICKET_EMAIL_TICKET_NR',"Enquiry Ticket Number");

define('TABLE_HEADING_NR','TicketNr');
define('TABLE_HEADING_SUBJECT','Subject');
define('TABLE_HEADING_STATUS','Status');
define('TABLE_HEADING_DEPARTMENT','Department');
define('TABLE_HEADING_PRIORITY','Priority');
define('TABLE_HEADING_CREATED','opened');
define('TABLE_HEADING_LAST_MODIFIED','last Change');

define('TEXT_TICKET_BY', 'from');
define('TEXT_COMMENT','Reply:');
define('TEXT_DATE','Date:');
define('TEXT_DEPARTMENT','Department:');
define('TEXT_DISPLAY_NUMBER_OF_TICKETS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> tickets)');

define('TEXT_PRIORITY','Priority:');
define('TEXT_OPENED', 'opened:');
define('TEXT_STATUS', 'Status:');
define('TEXT_FROM', 'From:');
define('TEXT_TICKET_NR','Ticket No.:');
define('TEXT_CUSTOMERS_ORDERS_ID','OrderID:');
define('TEXT_VIEW_TICKET_NR','Please enter your Ticket no:');
define('TICKET_EMAIL_SUBJECT_RESPONSE','Response to ');
define('TICKET_WARNING_ENQUIRY_TOO_SHORT','Error: Your Enquiry is too short. It must be at least ' . TICKET_ENTRIES_MIN_LENGTH . ' Signs');
define('TICKET_MESSAGE_UPDATED','Your Ticket has been updated');

define ('TEXT_VIEW_TICKET_LOGIN','<a href="%s">To view your ticket, you have to log in here</a>');

define('TICKET_SHOW_CUSTOMERS_SUBJECT',true);
define('TICKET_CATALOG_USE_STATUS',true);
define('TICKET_CATALOG_USE_DEPARTMENT',true);
define('TICKET_CATALOG_USE_PRIORITY',true);

define ('BOX_HEADING_TICKET','Support Ticket');  
define ('BOX_TICKET_GENERATE','Open Support Ticket');
define ('BOX_TICKET_VIEW','View Ticket');

define ('ENTRY_SUBJECT','Subject: ');
define ('ENTRY_DEPARTMENT','Department: ');
define ('ENTRY_PRIORITY','Priority: ');
?>