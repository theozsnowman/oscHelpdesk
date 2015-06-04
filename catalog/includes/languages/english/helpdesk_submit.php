<?php
/*
  $Id: ,v 3.00 2015/06/04

  oscHelpdesk
  http://www.snowtech.com.au

  Copyright (c) 2003 -2015 Snowtech Services

*/

global $customer_email, $customer_name, $customer_id;

define('TEXT_MAIN_SUPPORT', '<BR><p><span class="greetUser">Instant Help</span></p>
<p>Most questions can be answered immediately by browsing <a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '" style="color:#0000ff">Frequently Asked Questions</a> <a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '" style="color:#0000ff">(FAQ)</a>.</p>

<p><span class="greetUser">Contact us</span></p>
<p>Our Customer Service team is specially trained to address all your inquiries in timely manner. <p>
<hr size=1>

<p><strong>Registered customers. </strong></p><p>Please <a href="' . tep_href_link(FILENAME_SUPPORT, 'action=new', 'NONSSL') . '" style="color:#0000ff; font-size: 12px; font-weight : bold;">Submit new Inquiry</a> through Support Ticket System or <a href="' . tep_href_link(FILENAME_SUPPORT_TRACK, 'view=all', 'NONSSL') . '"  style="color:#0000ff; font-size: 12px; font-weight : bold;"> update the existing Support Ticket </a>and we\'ll reply promptly. You can suggest your question to be included into FAQ list.</p> 
<p>You can also reach us by phone using free 
<script language="javascript">
function ppW1(url) {
  window.open(url,\'popupWindow\',\'toolbar=no,location=no,directories=no,status=no,menubar=no,resizable=yes,copyhistory=no\')
}
  
	document.write(\'<a href="javascript:ppW1(\\\''. tep_href_link('callback.php', '')   . '\\\')"  style="color:#0000FF">Call back</a>\')
  
  </script>
  <noscript>
  <a href="'. tep_href_link(SUGGEST) . '" target="_blank"  style="color:#0000FF">Call Back</a>
  </noscript>

 service.</p> <p>We highly suggest to prefer  Support Tickets because this communication will be recorded and remain in our database for your convenience. While a message sent by e-mail through regular <em>Contact Us</em> form (below) can be lost or rejected by Spam filters</P>

<hr size=1>

<p><strong>Visitors.  </strong></P>
<p>You are welcom to send us a message via online contact form. Please 


<script language="javascript">
function ppW2(url) {
  window.open(url,\'popupWindow\',\'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=500,height=500,screenX=150,screenY=150,top=150,left=150\')
}
  
	document.write(\'<a href="javascript:ppW2(\\\''. tep_href_link(SUGGEST, 'fn=' . tep_output_string_protected($customer_name) . '&email=' . tep_output_string_protected($customer_email) . '&id=' . tep_output_string_protected($customer_id))   . '\\\')"  style="color:#0000ff">Click Here to contact us</a>\')
  
  </script>
  <noscript><a href="'. tep_href_link(SUGGEST) . '" target="_blank" style="color:#0000ff">Click Here to contact us<br></a></noscript>


.</p>

<hr size=1>

If you have difficulties to express your inquiry in English please use this link to translate:<br>
<a href="http://www.google.com/language_tools?hl=en" target="_blank" style="color:#0000ff">http://www.google.com/language_tools?hl=en</a>

');


define('NAVBAR_TITLE', 'Support Center');
define('HEADING_TITLE', 'Support Center <br>');
define('NAVBAR_TITLE_1', 'Track tickets');
define('NAVBAR_TITLE_3', 'Ticket Info');
define('NAVBAR_TITLE_4', 'Edit a ticket');
define('HEADING_TITLE_TRACK', 'Ticket Information');
define('HEADING_TITLE_OPTIONS', 'Alter a ticket');
define('HEADING_TITLE_DELETE', 'Delete a ticket');
define('HEADING_TICKET_HISTORY', 'Ticket History');
define('HEADING_LAST_MODIFIED', 'Last Modified');
define('HEADING_LAST_ADMIN', 'Last modified by');
define('HEADING_TICKET_INFORMATION', 'Details of ticket');
define('HEADING_TICKET_NAME', 'Ticket Submitted by');
define('HEADING_TICKET_EMAIL', 'Email Address');
define('HEADING_TICKET_ADMIN', 'Support Details');
define('HEADING_TICKET_SUPPORTER', 'Assigned to:');
define('HEADING_TICKET_DEPARTMENT', 'Category');
define('HEADING_TICKET_DOMAIN', 'Subject');
define('HEADING_TICKET_COMPANY', 'Company name');
define('TEXT_TICKET_PRIORITY', 'Ticket Priority:');
define('TEXT_NO_RESPONSE_AVAILABLE', 'No support comments available');
define('TEXT_NO_RESPONSE_DATE','');
define('TEXT_SUPPORT_DEPT', 'Category');
DEFINE('TEXT_SUPPORT_PRIORITY', 'Priority');
define('TEXT_SUPPORT_USER_NAME', 'Your name');
define('TEXT_SUPPORT_USER_EMAIL', 'Your email');
define('TEXT_SUPPORT_COMPANY' ,'Company name');
define('TEXT_SUPPORT_DOMAIN', 'Subject');
define('TEXT_SUPPORT_TEXT', 'Comments');
define('TEXT_SUCCESS', 'Thank you for submitting your support request.  Your communication has been sent to the relevant department for investigation.  You will be notified of a response via email.');
define('TEXT_NO_COMMENTS_AVAILABLE', 'No information submitted');
define('TEXT_LOCATE_ERROR', 'Sorry no tickets were found to be logged with that email address.');
define('TEXT_DISPLAY_NUMBER_OF_TICKETS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> tickets)');
define('TEXT_TICKET_NUMBER', '#');
define('TEXT_TICKET_DATE', 'Ticket submitted on:');
define('TEXT_TICKET_CLOSED', 'Ticket closed on:');
define('TEXT_SUBMITTED_BY', 'Submitted by: ');
define('TEXT_TICKET_DEPARTMENT', 'Category: ');
define('TEXT_TICKET_PRIORITY', 'Priority: ');
define('TEXT_TICKET_STATUS', 'Ticket Status: ');
define('TEXT_VIEW_TICKET', 'View');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_OLD_DEPT', 'Old Dept');
define('TABLE_HEADING_NEW_DEPT', 'Category');
define('TABLE_HEADING_OLD_ADMIN', 'Old Admin');
define('TABLE_HEADING_NEW_ADMIN', 'Assigned to');
define('TABLE_HEADING_NEW_VALUE', 'Status');
define('TABLE_HEADING_OLD_VALUE', 'Old Value');
define('TABLE_HEADING_CUSTOMER_NOTIFIED', 'Customer Notified');
define('TABLE_HEADING_DATE_ADDED', 'Date Added');
define('TEXT_AMEND_TICKET', 'Edit');
define('TEXT_DELETE_TICKET', 'Close');
define('TEXT_TICKET_SUBJECT', 'Subject');
define('TEXT_ERROR', 'No option selected');
define('TEXT_NO_ORDER_HISTORY', 'The Administrator has not yet responded to this ticket.');
define('TEXT_CANCEL_DELETE', 'Cancel');
define('TEXT_CONFIRM_DELETE', 'Delete this ticket');
define('TEXT_NO_PURCHASES', 'There are no open Tickets in your support history.');
define('TEXT_NO_CLOSED', 'There are no closed Tickets in your support history.');
define('TEXT_TICKET_REMOVAL', 'Your request to close this ticket has been completed.  Your ticket status has been updated and the assigned administrator has been informed of your request.  If you need to reopen this ticket, you may do so at any time by viewing your closed Tickets.');
define('ENTRY_NAME', 'Name:');
define('TICKET_DETAILS', 'Ticket Details');
define('ENTRY_SUBJECT', 'Ticket Subject');
define('ENTRY_PRIORITY', 'Priority');
define('ENTRY_DEPARTMENT', 'Category');
define('ENTRY_PROBLEM', 'Problem');
define('CATEGORY_ADMIN', 'Admin Information');
define('ENTRY_ASSIGN', 'Assined to');
define('ENTRY_LAST_STATUS', 'Currnet Status');
define('ENTRY_ADMIN_COMMENTS', 'Admin Comments');
define('ENTRY_LAST_MODIFIED', 'Last Modiified');
define('TEXT_TICKET_REOPEN', 'Your request to re-open this ticket has been completed.  Your ticket status has been updated and the assigned administrator has been informed of your request.');
define('TEXT_SUPPORT_FAQ', 'Suggest for FAQ');
define('TEXT_SUPPORT_ALTERNATIVE_EMAIL', 'Alternative email address');
define('TEXT_SUPPORT_ORDERS', 'Order(s) #');
define('TEXT_SUPPORT_IF_APPLICABLE', 'if applicable');
define('TEXT_FAQ_HELP', 'By selecting this option, you are asking the administrator to consider the contents of this support request for inclusion within the sites FAQ.<Br><BR>All questions are considered carefully, and if it is felt that your question raises a topic not previously covered, then it may be added to the FAQ.  Thank you for your input.');
define('HEADING_FAQ_HELP', 'Suggest for FAQ');
define('TEXT_CLOSE_WINDOW', 'Close this window');
?>
