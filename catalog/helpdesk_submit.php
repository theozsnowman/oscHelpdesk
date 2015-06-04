<?php
/*
  $Id: ,v 3.00 2015/06/04

  oscHelpdesk
  http://www.snowtech.com.au

  Copyright (c) 2003 -2015 Snowtech Services

*/

  require('includes/application_top.php');
  
  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
  
  $default_status_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_SUPPORT_TICKET_STATUS'");
  $default_status = tep_db_fetch_array($default_status_query);
  $default_admin_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_SUPPORT_ADMIN_ID'");
  $default_admin = tep_db_fetch_array($default_admin_query);
  $default_priority_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_SUPPORT_TICKET_PRIORITY'");
  $default_priority = tep_db_fetch_array($default_priority_query);
   $default_department_query = tep_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_SUPPORT_TICKET_DEPARTMENT'");
  $default_department = tep_db_fetch_array($default_department_query);


  if ($_GET['action']) {
    switch ($_GET['action']) {
      case 'insert':
      case 'update':
      //  $link_id = tep_db_prepare_input($_POST['link_id']);
        $support_dept = tep_db_prepare_input($_POST['support_dept']);
        $support_priority = tep_db_prepare_input($_POST['support_priority']);
        $support_user_name = tep_db_prepare_input($_POST['support_user_name']);
        $support_user_email = tep_db_prepare_input($_POST['support_user_email']);
//        $support_comapny = tep_db_prepare_input($_POST['support_company']);
        $support_domain = tep_db_prepare_input($_POST['support_domain']);
        //$support_domain = escape_all($support_domain) ;
        $support_domain = eregi_replace('\'','\\\'', $support_domain) ;
				$support_domain = eregi_replace('<','&lt;', $support_domain) ;
       
        
        $support_text = tep_db_prepare_input($_POST['ticket_comments']);
        $support_text = eregi_replace('\'','\\\'', $support_text) ;
				$support_text = eregi_replace('<','&lt;', $support_text) ;
        
        
//      $support_alternate_email = tep_db_prepare_input($_POST['support_alternate_email']);
        $support_orders = tep_db_prepare_input($_POST['support_orders']);
        $support_orders = eregi_replace('\'','\\\'', $support_orders) ;
				$support_orders = eregi_replace('<','&lt;', $support_orders) ;
       
        
        $support_error = false;
 // error checking goes in here
 // not present at moment
   if (!$support_error) {
   	
		if ($_GET['action'] == 'insert') {
			
      tep_db_query("
INSERT   INTO `" . TABLE_SUPPORT_TICKETS . "` SET 
`customers_domain`	= '$support_domain',
`customers_id`			= '$customer_id',
`customers_orders`	= '$support_orders',
`ticket_date`				= now();
");
  
$ticket_id = tep_db_insert_id();   	
   	
      tep_db_query("
INSERT   INTO `" . TABLE_SUPPORT_TICKETS_HISTORY . "` SET 
`ticket_id`					= '$ticket_id',
`ticket_status`			= '1',
`last_modified`			= NOW(),
`department_id`			='$support_dept',
`admin_id`					='". $default_admin['configuration_value'] . "',
`priority_id`				='$support_priority',
`ticket_comments`		='$support_text',
`submitted_by`			= 'customer',
`user_id`						= '0'");

          if ($_POST['notify'] == 'on') {
            tep_db_query("INSERT INTO " . TABLE_FAQ . " VALUES ('', '0', '0', '" . $support_text . "', '', now())");
          }
          	          
       } elseif ($_GET['action'] == 'update') {
       	
        $support_admin = tep_db_prepare_input($_POST['support_admin']);
        $ticket_id = tep_db_prepare_input($_POST['ticket_id']);

 
      tep_db_query("
INSERT   INTO `" . TABLE_SUPPORT_TICKETS_HISTORY . "` SET 
`ticket_id`					= '$ticket_id',
`ticket_status`			= '1',
`last_modified`			= NOW(),
`department_id`			='$support_dept',
`admin_id`					='". $default_admin['configuration_value'] . "',
`priority_id`				='$support_priority',
`ticket_comments`		='$support_text',
`submitted_by`			= 'customer',
`user_id`						= '0'");
       	

        // tep_db_query(TABLE_SUPPORT_TICKETS, $sql_data_array, 'updateee', 'ticket_id = \'' . $ticket_id . '\'');
       }

          // now check to see if the customer would like this question considered for the FAQ section


          // now send email to customer

            require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_SUPPORT_EMAILS);
           $email_text = EMAIL_TEXT_TICKET_OPEN;
           $email_text .= EMAIL_THANKS_OPEN . EMAIL_TEXT_OPEN . EMAIL_CONTACT_OPEN . EMAIL_WARNING_OPEN;
           tep_mail($support_user_name, $support_user_email, EMAIL_SUBJECT_OPEN . ' #' . $ticket_id, nl2br($email_text), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
          // send email to alternate address
 //          if (strlen($support_alternate_email) > 0) {
 //               $email_text = EMAIL_TEXT_TICKET_OPEN;
//                $email_text .= EMAIL_THANKS_OPEN . EMAIL_TEXT_OPEN . EMAIL_CONTACT_OPEN . EMAIL_WARNING_OPEN;
//                tep_mail($support_user_name, $support_alternate_email, EMAIL_SUBJECT_OPEN . ' #' . $ticket_id, nl2br($email_text), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
//             }
             
          // now send an email to the default administrator to let them know of new ticket
//             $default_admin_email = tep_db_query("SELECT admin_id FROM " . TABLE_SUPPORT_TICKETS . " where ticket_id = '" . $_GET['ticket_id'] . "' and customers_id = '" . $customer_id . "'");
//             $default_email = tep_db_fetch_array($default_admin_email);
             $admin_email_query = tep_db_query("SELECT support_assign_email, support_assign_name FROM " . TABLE_SUPPORT_ASSIGN . " where support_assign_id = '" . $default_admin['configuration_value'] . "' and language_id = '" . $languages_id . "'");
             $admin_email = tep_db_fetch_array($admin_email_query);
             $email_text_admin = EMAIL_TEXT_TICKET_ADMIN;
             $email_text_admin .= EMAIL_THANKS_ADMIN . EMAIL_TEXT_ADMIN . EMAIL_CONTACT_ADMIN . EMAIL_WARNING_ADMIN;
             tep_mail($admin_email['support_assign_name'], $admin_email['support_assign_email'], EMAIL_SUBJECT_UPDATE .' #' . $ticket_id, nl2br($email_text), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

          // redirect to confirmation
            tep_redirect(tep_href_link(FILENAME_SUPPORT,  'action=sent'));
        } 
        
        else {
    //      $_GET['action'] = 'new';
        }
        break;
       case 'default':
       tep_redirect(tep_href_link(FILEANME_DEFAULT));
       break;
    }
  }
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_HELPDESK_SUBMIT);
  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_HELPDESK_SUBMIT, '', 'NONSSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (getenv('HTTPS') == 'on' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">

<script language="javascript"><!--
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
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
    <td width="100%" valign="top">
    	
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_account.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="1">
          <tr>
            <td class="main"><?php   include(DIR_WS_MODULES . 'support_menu.php'); ?>	</td>
            </tr>
	<tr>
			<td><?php echo tep_draw_separator('pixel_trans.gif', '15', '15'); ?></td>
	</tr>
<?php
if ($_GET['action'] == 'sent'){
	$_GET['view'] = 'open';
         ?>
          <tr>
        <td>&nbsp;</td>
      </tr>
          <tr>
            <td class="main"><?php new infoBox(array(array('text' => TEXT_SUCCESS))); ?></td>
          </tr>
           <tr><td><?php echo tep_draw_separator('pixel_trans.gif', '20', '20'); ?></td>
					</tr>
            
		           <? include(DIR_WS_MODULES . 'support_track.php'); ?>
           
			


          <?
} else {
         $account_query = tep_db_query("SELECT customers_firstname, customers_lastname, customers_email_address FROM " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
         $account = tep_db_fetch_array($account_query);
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
      	
         <td  bgcolor="#336699"><table border="0" cellspacing="2" cellpadding="2" width="100%" bgcolor="#ffffff">
         	<?php echo tep_draw_form('submit_ticket', tep_href_link('support.php', 'action=insert', 'NONSSL')) . tep_hide_session_id() ; ?>
          <tr>
            <td class="main" align=left><strong><?php echo TEXT_SUPPORT_DEPT; ?></strong></td>
            <td class="main" width=85%><?php //echo tep_draw_input_field('link_title'); ?>
            <?
              $department_query = tep_db_query("select * from ". TABLE_SUPPORT_DEPARTMENT . " where language_id = '" . $languages_id . "' order by support_department_id");
$select_box = '<select name="support_dept"  size="1">';
    
    
    while ($department_values = tep_db_fetch_array($department_query)) {
      $select_box .= '<option value="' . $department_values['support_department_id'] . '"';
      if ($default_department['configuration_value'] ==  $department_values['support_department_id']) $select_box .= ' SELECTED';
      $select_box .= '>' . substr($department_values['support_department_name'], 0, 50) . '</option>';
    }
    $select_box .= "</select>";
    $select_box .= tep_hide_session_id();
    echo $select_box;
             ?>
            </td>
          </tr>
          <tr>
            <td class="main" align=left><strong><?php echo TEXT_SUPPORT_PRIORITY; ?></strong></td>
            <td class="main"><?php //echo tep_draw_input_field('link_url'); ?>
          <?
            $priority_query = tep_db_query("select * from ". TABLE_SUPPORT_PRIORITY . " where language_id = '" . $languages_id . "' order by support_priority_id desc");
$select_box = '<select name="support_priority"  size="1">';


    while ($priority_values = tep_db_fetch_array($priority_query)) {
      $select_box .= '<option value="' . $priority_values['support_priority_id'] . '"';
      if ($default_priority['configuration_value'] ==  $priority_values['support_priority_id']) $select_box .= ' SELECTED';
      $select_box .= '>' . substr($priority_values['support_priority_name'], 0, 50) . '</option>';
    }
    $select_box .= "</select>";
    $select_box .= tep_hide_session_id();
    echo $select_box;
          ?>
            </td>
          </tr>
          <tr>
            <td class="main" align=left><strong><?php echo TEXT_SUPPORT_USER_NAME; ?></strong>&nbsp;&nbsp;&nbsp;</td>
            <td class="main"><?php echo $account['customers_firstname'] . '&nbsp;' . $account['customers_lastname'] . tep_draw_hidden_field('support_user_name', $account['customers_firstname'] . '&nbsp;' . $account['customers_lastname']); ?></td>
          </tr>
          <tr>
            <td class="main" align=left><strong><?php echo TEXT_SUPPORT_USER_EMAIL; ?></strong>&nbsp;&nbsp;&nbsp;</td>
            <td class="main"><?php echo $account['customers_email_address'] . tep_draw_hidden_field('support_user_email', $account['customers_email_address']); ?></td>
          </tr>
<!--          <tr>
            <td class="main" align=left><strong><?php echo TEXT_SUPPORT_ALTERNATIVE_EMAIL; ?></strong>&nbsp;&nbsp;&nbsp;</td>
            <td class="main"><?php echo tep_draw_input_field('support_alternate_email'); ?></td>
          </tr> -->
          <tr>
            <td class="main" align=left><?php echo TEXT_SUPPORT_ORDERS; ?>&nbsp;&nbsp;&nbsp;</td>
            <td class="main"><input type=text name="support_orders" maxlength=100>&nbsp;<?php echo TEXT_SUPPORT_IF_APPLICABLE; ?></td>
          </tr> 
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>

          <tr>
            <td class="main" align=left><strong><?php echo TEXT_SUPPORT_DOMAIN; ?></strong>&nbsp;&nbsp;&nbsp;</td>
            <td class="main"><?php echo tep_draw_input_field('support_domain'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td valign="top" class="main" align=left><strong><?php echo TEXT_SUPPORT_TEXT; ?></strong>&nbsp;&nbsp;&nbsp;</td>
            <td class="main"><?php echo tep_draw_textarea_field('ticket_comments', 'soft', '40', '7'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" align=left><strong><?php echo TEXT_SUPPORT_FAQ; ?></strong>&nbsp;<?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_POPUP_FAQ) . '\')">[?]</a>'; ?>&nbsp;&nbsp;</td>
            <td class="main"><?php echo tep_draw_checkbox_field('notify', '', true); ?></td>
          </tr>
          <tr>
            <td class="main" colspan=2>&nbsp;</td>
          </tr>
          <tr>
            <td class="main">&nbsp;</td>
            <td class="main"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></td>
          </tr></form>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="2" class="main" valign="top" nowrap align="center"></td>
          </tr>
        </table></td>
      </tr>
<?php
}
?>



        </table></td>
      </tr>

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
