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
    $ticketid = $_GET['ticket_id'];

  $customer_number_query = tep_db_query("select customers_id from " . TABLE_SUPPORT_TICKETS . " where ticket_id = '". tep_db_input(tep_db_prepare_input($ticketid)) . "'");
  $customer_number = tep_db_fetch_array($customer_number_query);
  if ($customer_number['customers_id'] != $customer_id) {
    tep_redirect(tep_href_link(FILENAME_DEFAULT, '', 'SSL'));
  }


  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_SUPPORT);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_SUPPORT_TRACK, '', 'NONSSL'));
  $breadcrumb->add(NAVBAR_TITLE_3, tep_href_link(FILENAME_SUPPORT_TICKET_INFO, 'ticket_id=' . $ticketid, 'NONSSL'));

// seems everyhting is okay, no hacks, so lets run the queries

    $ticket_query = tep_db_query("
SELECT 
t.customers_id, 
t.customers_orders, 
t.customers_domain, 
t.ticket_date, 
CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS customers_name, 
c.customers_email_address, 
sh.ticket_comments, 
sh.department_id, 
sh.ticket_status, 
sh.priority_id, 
sh.admin_id, 
sh.last_modified, 
sh.submitted_by,
s.support_status_name, 
p.support_priority_name, 
d.support_department_name,
a.support_assign_name 

FROM " . TABLE_SUPPORT_TICKETS_HISTORY . "  sh, 
" . TABLE_SUPPORT_TICKETS . " t, 
" . TABLE_CUSTOMERS . " c, 
" . TABLE_SUPPORT_PRIORITY . " p, 
" . TABLE_SUPPORT_DEPARTMENT . " d, 
" . TABLE_SUPPORT_STATUS . " s,  
" . TABLE_SUPPORT_ASSIGN . " a 
WHERE sh.ticket_id = '$ticketid' AND 
t.ticket_id = sh.ticket_id AND 
c.customers_id = t.customers_id AND
sh.ticket_status = s.support_status_id AND 
sh.priority_id = p.support_priority_id AND 
sh.department_id = d.support_department_id AND 
sh.admin_id = a.support_assign_id AND 
s.language_id = '$languages_id' AND
d.language_id = '$languages_id' AND
p.language_id = '$languages_id' AND 
a.language_id = '$languages_id' 
ORDER BY sh.support_history_id DESC limit 1");



//  $ticket = tep_db_fetch_array($ticket_query);
  
	$thiscomments = '';
  $order_index = 0;  
  while ($ticket_ind = tep_db_fetch_array($ticket_query)) {
  	if ($order_index == 0){
  		$ticket = $ticket_ind;
  	}
  	if ($ticket_ind['submitted_by'] == 'customer'){
  		$thiscomments .= "<i><strong>". $order['customers_name'] . "</strong> on " . $ticket_ind['last_modified'] . "</i> <br><span style=\"color:#0000ff\">" . $ticket_ind['ticket_comments'] . "</span><p>";
  		}
    else {
    	$thiscomments .= "<i>support(". $order['user_id'] . ") on " . $ticket_ind['last_modified'] . "</i><br><span style=\"color:#ff0000\">" . $ticket_ind['ticket_comments'] . "</span><p>";}
  	
    
    $order_index ++; 
  }
  
	$ticket['ticket_comments'] = $thiscomments;


  $next_id_query = tep_db_query("select max(support_history_id) as support_history_id from " . TABLE_TICKET_HISTORY . " where ticket_id = '" . $ticketid . "'");
  $next_id = tep_db_fetch_array($next_id_query);
  $history_query = tep_db_query("SELECT * FROM " . TABLE_TICKET_HISTORY . " where ticket_id = '" . $ticketid ."' and support_history_id = '" . $next_id['support_history_id'] . "'");
  $ticket_history = tep_db_fetch_array($history_query);
  $last_admin = tep_db_query("select support_assign_name from " . TABLE_SUPPORT_ASSIGN ." where support_assign_id = '" . $ticket_history['new_support'] . "'");
  $new_admin = tep_db_fetch_array($last_admin);


if ($ticket['ticket_status']=='0'){
	$ticket_closed = '<strong>' . TEXT_TICKET_CLOSED . '</strong> ' . $ticket['last_modified'];
	
}
else {
	$ticket_closed = '&nbsp';
}

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (getenv('HTTPS') == 'on' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
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
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0"> 
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
        	<table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main">
<? include(DIR_WS_MODULES . 'support_menu.php'); ?>
              </td>
            </tr>
            <tr>
               <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
						</tr>
						<tr>
								<td>
 <!-- ticket properties  -->
			<table border=1 cellpadding=0 cellspacing=1 bgcolor="#336699" align=center width=100%>
				<tr>
          <td class="main">
			<table border=1 cellpadding=0 cellspacing=0 bgcolor="#ffffff" align=center width=100%>
          		<tr>
            		<td class="main" ><b><?php echo TEXT_TICKET_NUMBER . $ticketid; ?></b></td>
            		<td class="smallText" style="line-height: 1.5;" align=right>
<?php 
if ($ticket['ticket_status']){echo '<a href="' . tep_href_link(FILENAME_SUPPORT_TRACK, 'view=open&close=' . $ticketid, 'NONSSL') . '" style="color:#0000ff">Close ticket</a> (this will cause stop aswering to your questions)';}
else {echo '<a href="' . tep_href_link(FILENAME_SUPPORT_TRACK, 'view=closed&open=' . $ticketid, 'NONSSL') . '" style="color:#0000ff">Re-open ticket without adding comments</a>';}
?>
            			
            			
            			</td>
          		</tr>
          		<tr>
            		<td class="smallText" style="line-height: 1.5;"><strong><?php echo TEXT_TICKET_DATE . '</strong> ' . tep_date_long($ticket['ticket_date']); ?></td>
            		<td class="smallText" align="right" style="line-height: 1.5;"><strong><?php echo TEXT_TICKET_DEPARTMENT . '</strong> ' . $ticket['support_department_name'] ; ?></td>
          		</tr>
          		<tr>
            		<td class="smallText" style="line-height: 1.5;"><?php echo $ticket_closed; ?></tD>
            <td class="smallText" align=right style="line-height: 1.5;"><strong><?php echo HEADING_TICKET_SUPPORTER . '</strong> ' .  $ticket['support_assign_name']; ?></td>
         		 </tr>
          		<tr>
            		<td class="smallText" style="line-height: 1.5;"><strong><?php echo HEADING_TICKET_DOMAIN . ':</strong> ' . $ticket['customers_domain']; ?></tD>
            <td class="smallText" align=right style="line-height: 1.5;"><strong><?php echo TEXT_TICKET_PRIORITY . '</strong> ' . $ticket['support_priority_name']; ?></td>
         		 </tr>
			</table>
         </td>
				</tr>
			</table>
<!-- end ticket properties -->
        		
        		</td>
      </tr>

      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><b><?php echo HEADING_TICKET_INFORMATION; ?></b></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td width="30%" valign="top"><iframe id="messages_show" name="messages_show" scrolling="auto" width="100%" height="300" src="support_iframe.php?ticket_id=<?php echo $ticket_id; ?>" border="0">
            
<textarea readonly cols=100 rows=10 style="color:#ff0000"><?php echo ((strlen($ticket['ticket_comments']) > 0) ? nl2br($ticket['ticket_comments']) : '<i>' . TEXT_NO_COMMENTS_AVAILABLE . '</i>'); ?></textarea>
            
            
            
            </iframe></td>
            
          </tr>
        </table></td>
      </tr>
      
      
     
      
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main">
		<table cellpadding=0 cellspacing=0 border=0 width=100%>
<?php echo tep_draw_form('submit_ticket', tep_href_link('support.php', 'action=update', 'NONSSL')) . tep_hide_session_id() ; ?>		


      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?>?</td>
      </tr>
      <tr>

       <td><?php echo tep_draw_textarea_field('ticket_comments', 'soft', '100', '8', '', 'id="comments"'); ?></td>
       
      </tr>
 	
			
			<tr>
				<td class="main" align=left>
<?php		echo tep_image_submit('button_make_changes.gif', 'Add Comments', $parameters = ''); 
				echo tep_draw_hidden_field('ticket_id', $ticketid); 
				echo tep_draw_hidden_field('support_admin', $ticket['admin_id']); 
				echo tep_draw_hidden_field('support_dept', $ticket['department_id']); 
				echo tep_draw_hidden_field('support_priority', $ticket['priority_id']); 
       ?>
</td>
				<td align="right" class="main"></td>
      </tr>
    </table>

            </td>
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
