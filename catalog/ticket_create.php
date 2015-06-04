<?php
/*
  $Id: ,v 3.00 2015/06/04

  oscHelpdesk
  http://www.snowtech.com.au

  Copyright (c) 2003 -2015 Snowtech Services

*/

  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id') && $_GET['login']=="yes") {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }


  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_TICKET_CREATE);
  $ticket_departments = array();
  $ticket_department_array = array();
  $ticket_department_query = tep_db_query("select department_id, title from " . TABLE_HELPDESK_DEPARTMENTS);
  while ($ticket_department = tep_db_fetch_array($ticket_department_query)) {
    $ticket_departments[] = array('id' => $ticket_department['department_id'],
                               'text' => $ticket_department['title']);
    $ticket_department_array[$ticket_department['department_id']] = $ticket_department['title'];
  }
  $ticket_prioritys = array();
  $ticket_priority_array = array();
  $ticket_priority_query = tep_db_query("select priority_id, title from " . TABLE_HELPDESK_PRIORITIES . " where languages_id = '" . $languages_id . "'");
  while ($ticket_priority = tep_db_fetch_array($ticket_priority_query)) {
    $ticket_prioritys[] = array('id' => $ticket_priority['priority_id'],
                               'text' => $ticket_priority['title']);
    $ticket_priority_array[$ticket_priority['priority_id']] = $ticket_priority['title'];
  }
 
  $email = tep_db_prepare_input(trim($_POST['email']));
  $name = tep_db_prepare_input($_POST['name']);
  $subject = tep_db_prepare_input($_POST['subject']);
  $enquiry = tep_db_prepare_input($_POST['enquiry']);
  $department = tep_db_prepare_input($_POST['department']);
  $priority = tep_db_prepare_input($_POST['priority']);
  $ticket_customers_orders_id = tep_db_prepare_input($_POST['ticket_customers_orders_id']);
  
  
// Customer is logged in:  
  if (tep_session_is_registered('customer_id')) {
    $customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
    $customer = tep_db_fetch_array($customer_query);
  }

// Form was submitted
  $error = false;
  if (isset($_GET['action']) && ($_GET['action'] == 'send')) {
  // Check Name length
    if (!tep_session_is_registered('customer_id') && isset($name) && strlen($name) < TICKET_ENTRIES_MIN_LENGTH ) {
        $error = true;
        $error_name = true;
      }
    
 // Check Subject length
    if (isset($subject) && strlen($subject) < TICKET_ENTRIES_MIN_LENGTH ) {
        $error = true;
        $error_subject = true;
      }
  // Check Message length
    if (isset($enquiry) && strlen($enquiry) < TICKET_ENTRIES_MIN_LENGTH ) {
        $error = true;
        $error_enquiry = true;
      }
  // Check Email for non logged in Customers
    if (!tep_session_is_registered('customer_id') && !tep_validate_email($email)) {
      $error = true;
      $error_email = true;
    } 
    
    if ($error == false) {
	// Insert into the database
      $ticket_customers_id = '';
    // Get the customers_id
      if (tep_session_is_registered('customer_id')) {
        $ticket_customers_id = $customer_id;
      } else {
        $customerid_query = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where customers_email_address='" . tep_db_input($email) . "'");
        if ($customerid = tep_db_fetch_array($customerid_query)) $ticket_customers_id = $customerid['customers_id'] ;
      }
      // generate LInkID
      $time = mktime();
      $ticket = '';
      for ($x=3;$x<6;$x++) {
        $ticket .= substr($time,$x,1) . tep_create_random_value(1, $type = 'chars');
      }
   /*   
      $sql_data_array = array('ticket' => $ticket,
                          'ticket_customers_id' => $ticket_customers_id,
                          'ticket_customers_orders_id' => $ticket_customers_orders_id,
                          'ticket_customers_email' => $email,
                          'ticket_customers_name' => $name,
                          'ticket_subject' => $subject,
                          'ticket_status_id' => TICKET_DEFAULT_STATUS_ID,
                          'department_id' => $department,
                          'priority_id' => $priority,
                          'ticket_login_required' => TICKET_CUSTOMER_LOGIN_REQUIREMENT_DEFAULT,
                          'datestamp_last_entry' => 'now()',
                          'ticket_date_last_customer_modified' => 'now()',
                          'datestamp_comment' => 'now()');
	*/

      $sql_data_array = array('ticket' => $ticket,
						  'CustomerID'=> $ticket_customers_id,
						  'subject'=>$subject,
						  'status_id' => 1,
                          'department_id' => $department,
                          'priority_id' => $priority,
                          'datestamp_last_entry' => 'now()',
                          'datestamp_comment' => 'now()',
						  'comment'=>$enquiry
						  );

	
      tep_db_perform(TABLE_HELPDESK_TICKETS, $sql_data_array);
     
	 $insert_id = $ticket;
      
	  
      $sql_data_array = array('ticket' => $insert_id,
						  'parent_id'=>0,
                          'datestamp_local' => 'now()',
						  'datestamp' => 'now()',
                          'receiver'=>$ticket_department_array[$department],
						  'receiver_email_address'=>$ticket_department_array[$department].'@FromCustomerPanel',
						  'sender'=>$name,
						  'email_address'=>$email,
						  'subject'=>$subject,
						  'body'=>$enquiry,
						  'entry_read'=>0						  
                          );
      tep_db_perform(TABLE_HELPDESK_ENTRIES, $sql_data_array); 
	  
	  
    // Email Customer doesn't get the Message cause he should use the web
	
      $ticket_email_subject = TICKET_EMAIL_SUBJECT . $subject;
      $ticket_email_message = TICKET_EMAIL_MESAGE_HEADER . "\n\n" . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $ticket, 'SSL',false,false) . "\n\n" . TICKET_EMAIL_TICKET_NR . " " . $ticket . "\n" . TICKET_EMAIL_MESAGE_FOOTER;
      tep_mail($name, $email, $ticket_email_subject, nl2br($ticket_email_message), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
    // send emails to other people
      if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
       $ticket_email_message = TICKET_EMAIL_MESAGE_HEADER . "\n\n" . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $ticket) . "\n\n" . $enquiry . TICKET_EMAIL_MESAGE_FOOTER . "\n\n" . $enquiry;
       tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, $ticket_email_subject,nl2br($ticket_email_message), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      }
	  $redirect_link = htmlspecialchars_decode(tep_href_link(FILENAME_TICKET_CREATE, 'action=success&tlid=' . $ticket));
    tep_redirect($redirect_link);
	tep_exit();
	  }
  }

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_TICKET_CREATE, '', 'SSL'));

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
tinyMCE.init({
  mode : "textareas",
  editor_selector : "mceEditor",
  theme : "advanced",
  theme_advanced_buttons1 : "bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright, justifyfull,bullist,numlist,undo,redo,link,unlink",
  theme_advanced_buttons2 : "",
  theme_advanced_buttons3 : "",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left",
  theme_advanced_path_location : "bottom",
  extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
});
</script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0"  onload="SetFocus();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_contact_us.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  if (isset($_GET['action']) && ($_GET['action'] == 'success')) {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td rowspan=4 class="main"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_man_on_board.gif', HEADING_TITLE, '0', '0', 'align="left"') ?></td>
            <td class="main"><?php echo TEXT_SUCCESS; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_YOUR_TICKET_ID . ' ' . $_GET['tlid']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_CHECK_YOUR_TICKET . '<br><a href="' . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $_GET['tlid'], 'SSL',false,false) . '">' . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $_GET['tlid'], 'SSL',false,false) . '</a>'; ?></td>
          </tr>
          <tr>
            <td valign ="bottom" align="right"><br><a href="<?php echo tep_href_link(FILENAME_DEFAULT); ?>"><?php echo tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></a></td>
          </tr>
        </table></td>
      </tr>
<?php
  } else {
?>
      <tr>
        <td><?php echo tep_draw_form('contact_us', tep_href_link(FILENAME_TICKET_CREATE, 'action=send', 'SSL')); ?><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td width="150" class="main"><?php echo ENTRY_NAME; ?>&nbsp;</td>
            <td class="main">
<?php
    if (tep_session_is_registered('customer_id')) {
      echo tep_draw_hidden_field('name',$customer['customers_firstname'] . ' ' . $customer['customers_lastname']) . $customer['customers_firstname'] . ' ' . $customer['customers_lastname']; 
    } else {
      echo tep_draw_input_field('name', ($error ? $name : $first_name)); if ($error_name) echo ENTRY_ERROR_NO_NAME;
    }
?>
            </td>
            <td class="main" align="left" width="100%" valign="top" rowspan="2">
<?php
   if (!tep_session_is_registered('customer_id')) {
     echo  sprintf(TEXT_LOGIN, tep_href_link(FILENAME_TICKET_CREATE, 'login=yes', 'SSL'), tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')); 
   }
?>
            &nbsp;</td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_EMAIL; ?>&nbsp;</td>
            <td class="main">
<?php
    if (tep_session_is_registered('customer_id')) {
      echo tep_draw_hidden_field('email',$customer['customers_email_address']) . $customer['customers_email_address']; 
    } else {
      echo tep_draw_input_field('email', ($error ? $email : $email_address)); if ($error_email) echo ENTRY_EMAIL_ADDRESS_CHECK_ERROR; 
    }
?>
            </td>
          </tr>
<?php
    if (TICKET_SHOW_CUSTOMERS_SUBJECT == 'true') {   
?>
          <tr>
            <td class="main"><?php echo ENTRY_SUBJECT; ?>&nbsp;</td>
            <td class="main"><?php  echo tep_draw_input_field('subject', ($error ? $subject : $subject)); if ($error_subject) echo ENTRY_ERROR_NO_SUBJECT; ?></td>
            <td>&nbsp;</td>
          </tr>
<?php
    }
    if (TICKET_SHOW_CUSTOMERS_ORDER_IDS == 'true' && tep_session_is_registered('customer_id')) {     
      $customers_orders_query = tep_db_query("select orders_id, date_purchased from " . TABLE_ORDERS . " where customers_id = '" . tep_db_input($customer_id) . "'");
      if (isset($_GET['ticket_order_id'])) $ticket_preselected_order_id = $_GET['ticket_order_id'];
      $orders_array[] = array('id' => '', 'text' => ' -- ' );
      while ($customers_orders = tep_db_fetch_array($customers_orders_query)) {
        $orders_array[] = array('id' => $customers_orders['orders_id'], 'text' => $customers_orders['orders_id'] . "  (" . tep_date_short($customers_orders['date_purchased']) . ")" );
      }

?>
          <tr>
            <td class="main"><?php echo ENTRY_ORDER; ?>&nbsp;</td>
            <td class="main"><?php echo  tep_draw_pull_down_menu('ticket_customers_orders_id', $orders_array,$ticket_preselected_order_id); ?></td>
            <td>&nbsp;</td>
          </tr>

<?php
    }
    if (TICKET_CATALOG_USE_DEPARTMENT == 'true') {     
?>
          <tr>
            <td class="main"><?php echo ENTRY_DEPARTMENT; ?>&nbsp;</td>
            <td class="main"><?php echo tep_draw_pull_down_menu('department', $ticket_departments, ($department ? $department : TICKET_DEFAULT_DEPARTMENT_ID) ); ?></td>
            <td>&nbsp;</td>
          </tr>
<?php
    } else {
      echo tep_draw_hidden_field('department', TICKET_DEFAULT_DEPARTMENT_ID);
    }
    if (TICKET_CATALOG_USE_PRIORITY == 'true') {   
?>
          <tr>
            <td class="main"><?php echo ENTRY_PRIORITY; ?>&nbsp;</td>
            <td class="main"><?php echo tep_draw_pull_down_menu('priority', $ticket_prioritys, ($priority ? $priority : TICKET_DEFAULT_PRIORITY_ID) ); ?></td>
            <td>&nbsp;</td>
          </tr>
<?php
    } else {
      echo tep_draw_hidden_field('priority', TICKET_DEFAULT_PRIORITY_ID);
    }
?>
          <tr>
            <td colspan=3 class="main"><?php echo ENTRY_ENQUIRY; ?></td>
          </tr>
          <tr>
            <td colspan=3><?php echo tep_draw_textarea_field('enquiry', 'soft', 50, 15, 'mceEditor', $enquiry); ?><br><?php if ($error_enquiry) echo ENTRY_ERROR_NO_ENQUIRY; ?></td>
          </tr>
          <tr>
            <td colspan=3 class="main" align="right"><br><?php echo '<a href="' . tep_href_link(FILENAME_TICKET_VIEW, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'?>&nbsp;&nbsp;<?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></td>
          </tr>
        </table></form></td>
      </tr>
<?php
  }
?>
    </table></td>
<!-- body_text_eof //-->
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    </table></td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
