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
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_TICKET_VIEW);
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

  $ticket_statuses = array();
  $ticket_status_array = array();
  $ticket_status_query = tep_db_query("select status_id, title from " . TABLE_HELPDESK_STATUSES . " where languages_id = '" . $languages_id . "'");
  while ($ticket_status = tep_db_fetch_array($ticket_status_query)) {
    $ticket_statuses[] = array('id' => $ticket_status['status_id'],
                               'text' => $ticket_status['title']);
    $ticket_status_array[$ticket_status['status_id']] = $ticket_status['title'];
  }
  
  $enquiry = tep_db_prepare_input($_POST['enquiry']);
  $status = tep_db_prepare_input($_POST['status']);
  $priority = tep_db_prepare_input($_POST['priority']);
  $department = tep_db_prepare_input($_POST['department']);
  if (isset($_POST['tlid'])) $tlid =  tep_db_prepare_input($_POST['tlid']);
  if (isset($_GET['tlid'])) $tlid =  tep_db_prepare_input($_GET['tlid']);
  if (strlen($tlid) < 6) unset($tlid);
// Form was submitted
  $error = false;
  if (isset($_GET['action']) && ($_GET['action'] == 'send') && isset($tlid) ) {
  // Check Message length
    if (isset($enquiry) && strlen($enquiry) < TICKET_ENTRIES_MIN_LENGTH ) {
        $error = true;
        $_GET['error_message']=TICKET_WARNING_ENQUIRY_TOO_SHORT;
    }
    if ($error == false) {
      $ticket_id_query = tep_db_query("select ticket from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . tep_db_input($tlid) . "'");
      $ticket_id = tep_db_fetch_array($ticket_id_query);
      if ($ticket_id['ticket']) {
          if (tep_session_is_registered('customer_id')) 
					{
					$customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
					$customer = tep_db_fetch_array($customer_query);
					$customer_name = $customer['customers_firstname']." ".$customer['customers_lastname'];
					$customer_email = $customer['customers_email_address'];
					}
		else
					{
					//$customer_name = 
					//$customer_email = 
					}
		// Now fetching the email address of the department
			 
			 if(isset($department))
				{
				$department_query = tep_db_query("select * from " . TABLE_HELPDESK_DEPARTMENTS . " where department_id = " . $department);
				$departmentdata = tep_db_fetch_array($department_query);			
				$Department_Email = isset($departmentdata['email_address'])?$departmentdata['email_address']:$ticket_department_array[$department].'@FromCustomerPanel';
				}
			
	if (TICKET_ALLOW_CUSTOMER_TO_CHANGE_STATUS == 'false' && TICKET_CUSTOMER_REPLY_STATUS_ID > 0 ) $status = TICKET_CUSTOMER_REPLY_STATUS_ID;
        $subject = TICKET_EMAIL_SUBJECT_RESPONSE." [osC:".$ticket_id['ticket']."] ".$_POST['subject_ticket'];

        $sql_data_array = array('ticket' => $ticket_id['ticket'],
                         /* 'status_id' => $status,
                          'priority_id' => $priority,
                          'department_id' => $department,*/
                          'receiver'=>$ticket_department_array[$department],
						  'receiver_email_address'=>$Department_Email,
						  'datestamp' => 'now()',
						  'datestamp_local'=>'now()',
						  'sender'=> $customer_name,
						  'email_address'=>$customer_email,
					          'subject'=>$subject,
                          //'ticket_customer_notified' => '0',
                          //'ticket_edited_by' => $ticket_id['ticket_customers_name'],
                          'body' => $enquiry);
        tep_db_perform(TABLE_HELPDESK_ENTRIES, $sql_data_array);         
        $sql_data_array = array('status_id' => $status,
                          'priority_id' => $priority,
                          'department_id' => $department,
                          'datestamp_last_entry' => 'now()');       
        tep_db_perform(TABLE_HELPDESK_TICKETS, $sql_data_array, 'update', 'ticket = \'' . $ticket_id['ticket'] . '\'');        
		$messageStack->add('ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST', 'success');
		$_GET['info_message']=TICKET_MESSAGE_UPDATED;
		
		
		// Now Sending An Email To The Concerned Department


	$ticket_email_subject = TICKET_EMAIL_SUBJECT_RESPONSE . $subject;
      	$ticket_email_message = TICKET_EMAIL_MESAGE_HEADER . "\n\n" . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $ticket_id['ticket'], 'SSL',false,false) . "\n\n" . TICKET_EMAIL_TICKET_NR . " " . $ticket . "\n" . TICKET_EMAIL_MESAGE_FOOTER;
      	tep_mail($ticket_department_array[$department], $Department_Email, $ticket_email_subject, nl2br($ticket_email_message), $customer_name, $customer_email);
		       
      }
    }  
  }

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_TICKET_VIEW, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<?php echo TICKET_STYLESHEET; ?>
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
// Show spezific Ticket  
  if (!isset($tlid)) {
  ?>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <?php echo tep_draw_form('ticket_view', tep_href_link(FILENAME_TICKET_VIEW, 'action=send'), 'get') . "\n"; ?>
          <tr>
            <td class="main" align="left"><?php echo TEXT_VIEW_TICKET_NR; ?>&nbsp;</td>
            <td class="main" align="left"><?php echo tep_draw_input_field('tlid'); ?></td>
            <td><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE);  ?></td>
          </tr></form>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
<?php
    if (tep_session_is_registered('customer_id')) {
      $customers_tickets_raw = "select * from " . TABLE_HELPDESK_TICKETS . " where CustomerID = '" . tep_db_prepare_input($customer_id) . "' order by datestamp_last_entry desc";
      $customers_tickets_split = new splitPageResults($customers_tickets_raw, MAX_DISPLAY_SEARCH_RESULTS);
      if ($customers_tickets_split->number_of_rows > 0 ) {
?>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="5">
          <tr>
            <td class="infoBoxHeading" align="left"><?php echo TABLE_HEADING_NR; ?></td>
<?php 
      if (TICKET_SHOW_CUSTOMERS_SUBJECT == 'true') { echo '            <td class="infoBoxHeading" align="left">' . TABLE_HEADING_SUBJECT . '</td>'; }
      if (TICKET_CATALOG_USE_STATUS == 'true') {     echo '            <td class="infoBoxHeading">' . TABLE_HEADING_STATUS . '</td>'; }
      if (TICKET_CATALOG_USE_DEPARTMENT == 'true') { echo '            <td class="infoBoxHeading">' . TABLE_HEADING_DEPARTMENT . '</td>'; }
      if (TICKET_CATALOG_USE_PRIORITY == 'true') {   echo '            <td class="infoBoxHeading">' . TABLE_HEADING_PRIORITY . '</td>'; }
?>
           <td class="infoBoxHeading" align="right"><?php echo TABLE_HEADING_CREATED; ?></td>
      
          </tr>              
<?php
        $customers_tickets_query = tep_db_query ($customers_tickets_split->sql_query);
        $number_of_tickets = 0;
        while ($customers_tickets = tep_db_fetch_array($customers_tickets_query)) {
          $number_of_tickets++;
          if (($number_of_tickets / 2) == floor($number_of_tickets / 2)) {
            echo '         <tr class="productListing-even">' . "\n";
          } else {
           echo '          <tr class="productListing-odd">' . "\n";
          }
?>
            <td class="smallText" align="left"><?php echo '<a href="' . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $customers_tickets['ticket'], 'SSL') . '">' . $customers_tickets['ticket'] . '</a>'; ?></td>
<?php
          if (TICKET_SHOW_CUSTOMERS_SUBJECT == 'true') { echo '            <td class="smallText" align="left"><a href="' . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $customers_tickets['ticket'], 'SSL') . '">' . $customers_tickets['subject'] . '</a></td>'; }
          if (TICKET_CATALOG_USE_STATUS == 'true') {     echo '            <td class="smallText">' . $ticket_status_array[$customers_tickets['status_id']] . '</td>'; }
          if (TICKET_CATALOG_USE_DEPARTMENT == 'true') { echo '            <td class="smallText">' . $ticket_department_array[$customers_tickets['department_id']] . '</td>'; }
          if (TICKET_CATALOG_USE_PRIORITY == 'true') {   echo '            <td class="smallText">' . $ticket_priority_array[$customers_tickets['priority_id']] . '</td>'; }

?>
            <td class="smallText" align="right"><?php echo tep_date_short($customers_tickets['datestamp_comment']); ?></td>
            
          </tr>
<?php
        }
?>
<?php 
  if ($customers_tickets_split->number_of_rows > 0) {
?>
          <tr>
            <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText"><?php echo $customers_tickets_split->display_count(TEXT_DISPLAY_NUMBER_OF_TICKETS); ?></td>
                <td class="smallText" align="right"><?php echo TEXT_RESULT_PAGE; ?> <?php echo $customers_tickets_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td colspan="6" class="main" align="right">
<?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, tep_get_all_get_params(array('order_id')), 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'?></td>
          </tr>
          <tr>
<?php
  }
?>
        </table></td>
      </tr>
<?php
      }
    }
  }
  if (isset($tlid)) {
       $ticket_query = tep_db_query("select * from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . tep_db_input($tlid) . "'");
      $ticket = tep_db_fetch_array($ticket_query);
    // Check if Customer is allowed to view ticket:
      if ($ticket['ticket_customers_id'] > 1 && $ticket['ticket_login_required']=='1' && !tep_session_is_registered('customer_id') ) {
          // Customer must be logged in to view ticket:
?>
      <tr>
        <td align="center"><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo sprintf(TEXT_VIEW_TICKET_LOGIN, tep_href_link(FILENAME_TICKET_VIEW, 'login=yes&tlid=' . $tlid, 'SSL'), tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')); ?></td>
          </tr>
        </table></td>
      </tr>      
<?php  
      } else {
      // Customer is allowed to view ticket
        $ticket_status_query = tep_db_query("select * from " . TABLE_HELPDESK_ENTRIES . " where ticket = '". tep_db_input($ticket['ticket']) . "'");
      
?>
      <tr>
        <td><table class="InfoBox" width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan=2 class="InfoBoxHeading" align="left"><b><?php echo $ticket['subject']; ?></b></td>
          </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
          <tr>
            <td class="SmallText" colspan=2 align="left">

			<?php 
        echo '<b>' . TEXT_OPENED . '</b> ' . tep_date_short($ticket['datestamp_last_entry']) . "<br>";
        echo '<b>' . TEXT_TICKET_NR . '</b>&nbsp;' . $ticket['ticket']. "<br>";
		    if (TICKET_SHOW_CUSTOMERS_SUBJECT == 'true') { echo '<b>' . TABLE_HEADING_SUBJECT . '</b>&nbsp;' . $ticket['subject']. "<br>";	
			 }
		if (TICKET_CATALOG_USE_DEPARTMENT == 'true') { echo '<b>' . TEXT_DEPARTMENT . '</b>&nbsp;' . $ticket_department_array[$ticket['department_id']]. "<br>";}
		 if (TICKET_CATALOG_USE_PRIORITY == 'true') { echo '<b>' . TEXT_PRIORITY . '</b>&nbsp;' . $ticket_priority_array[$ticket['priority_id']]. "<br>";}
		if (TICKET_CATALOG_USE_STATUS == 'true') { echo '<b>' . TEXT_STATUS . '</b>&nbsp;' .$ticket_status_array[$ticket['status_id']]. "<br>";}
       // As of now Customer Order ID not being stored anywhere
	   // if ($ticket['ticket_customers_orders_id'] > 0) echo '<br><b>' . TEXT_CUSTOMERS_ORDERS_ID . '</b>&nbsp;' . $ticket['ticket_customers_orders_id'] . '<br>';
?>
            </td>
          </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>  
<?php     
        while ($ticket_status = tep_db_fetch_array($ticket_status_query)) {
?>
          <tr>
            <td class="SmallText" width="15%">
<?php
          echo '<b>' . $ticket_status['ticket_edited_by'] . '</b><br></br>';
          echo TEXT_DATE . '&nbsp;' .  tep_date_short($ticket_status['datestamp']) . '<br>';
          echo TEXT_FROM . '&nbsp;' .  $ticket_status['sender'] . '('.$ticket_status['email_address']. ')<br>'; 
		  $ticket_last_used_status = $ticket['status_id'];
          $ticket_last_used_department = $ticket['department_id'];
          $ticket_last_used_priority = $ticket['priority_id'];
?>
            </td>
            <td align=left bgcolor="f0f0f0" class="SmallText"><?php echo nl2br($ticket_status['body']); ?></td>
          </tr>
<tr><td><br></td></tr>  

<?php
        }
        echo tep_draw_form('ticket_view', tep_href_link(FILENAME_TICKET_VIEW, 'action=send', 'SSL')); 
        echo tep_draw_hidden_field('subject_ticket',$ticket['subject']);
        echo tep_draw_hidden_field('tlid',$tlid);
?>
          <tr>
            <td class="SmallText" valign="top">
<?php 
        echo '<b>' . TEXT_COMMENT . '</b><br><br><br>';
        if (TICKET_CATALOG_USE_STATUS == 'true' && TICKET_ALLOW_CUSTOMER_TO_CHANGE_STATUS == 'true') {
          echo TEXT_STATUS . '&nbsp;' . tep_draw_pull_down_menu('status', $ticket_statuses, ($ticket_last_used_status ? $ticket_last_used_status : TICKET_DEFAULT_STATUS_ID) ) . "<br><br>";
        } 
		
		else {
           echo tep_draw_hidden_field('status', ($ticket_last_used_status ? $ticket_last_used_status : TICKET_DEFAULT_STATUS_ID) );
        }
        //if (TICKET_CATALOG_USE_DEPARTMENT == 'true' && TICKET_ALLOW_CUSTOMER_TO_CHANGE_DEPARTMENT == 'true') {
		if(0) {
          echo TEXT_DEPARTMENT . '&nbsp;' . tep_draw_pull_down_menu('department', $ticket_departments, ($ticket_last_used_department ? $ticket_last_used_department : TICKET_DEFAULT_DEPARTMENT_ID) ) . "<br><br>";
        } else {
           echo tep_draw_hidden_field('department', ($ticket_last_used_department ? $ticket_last_used_department : TICKET_DEFAULT_DEPARTMENT_ID) );
        }
        //if (TICKET_CATALOG_USE_PRIORITY == 'true' && TICKET_ALLOW_CUSTOMER_TO_CHANGE_PRIORITY == 'true') {
		if(0) {
          echo TEXT_PRIORITY . '&nbsp;' . tep_draw_pull_down_menu('priority', $ticket_prioritys, ($ticket_last_used_priority ? $ticket_last_used_priority : TICKET_DEFAULT_PRIORITY_ID) ) . "<br><br>";
        } else {
          echo tep_draw_hidden_field('priority', ($ticket_last_used_priority ? $ticket_last_used_priority : TICKET_DEFAULT_PRIORITY_ID) );
        }
?>
            </td>
            <td  class="SmallText" ><?php echo tep_draw_textarea_field('enquiry', 'soft', 50, 15, 'mceEditor'); ?></td>
          </tr>
          <tr>
            <td colspan=2 class="main" align="right">
<?php echo '<a href="' . tep_href_link(FILENAME_TICKET_VIEW, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'?>&nbsp;&nbsp;
<?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></td>
          </tr>
          </form>
        </table></td>
      </tr> 
<?php
    }
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