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

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_HELPDESK);

  $error = false;
  if ($_GET['action'] == 'send') {
    if (tep_validate_email(trim($_POST['email']))) {
      $department = $_POST['department'];
      if (!$department) $department = DEFAULT_HELPDESK_DEPARTMENT_ID;

      $department_query = tep_db_query("select email_address, name from " . TABLE_HELPDESK_DEPARTMENTS . " where department_id = '" . $department . "'");
      $department = tep_db_fetch_array($department_query);

      tep_mail($department['name'], $department['email_address'], $_POST['subject'], $_POST['enquiry'], $_POST['name'], $_POST['email'], '');
      tep_redirect(tep_href_link(FILENAME_HELPDESK, 'action=success', 'NONSSL'));
    } else {
      $error = true;
    }
  }

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_HELPDESK));
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
    <td width="100%" valign="top"><?php echo tep_draw_form('contact_us', tep_href_link(FILENAME_CONTACT_US, 'action=send')); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'littleman_info.gif', HEADING_TITLE); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  if ($_GET['action'] == 'success') {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_man_on_board.gif', HEADING_TITLE, '0', '0', 'align="left"') . TEXT_SUCCESS; ?></td>
          </tr>
          <tr>
            <td align="right"><br><a href="<?php echo tep_href_link(FILENAME_DEFAULT, '', 'NONSSL'); ?>"><?php echo tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></a></td>
          </tr>
        </table></td>
      </tr>
<?php
  } else {
    $departments_array = array();
    $departments_query = tep_db_query("select department_id, title from " . TABLE_HELPDESK_DEPARTMENTS . " order by title");
    while ($departments = tep_db_fetch_array($departments_query)) {
      $departments_array[] = array('id' => $departments['department_id'], 'text' => $departments['title']);
    }

?>
      <?php echo tep_draw_form('contact', tep_href_link(FILENAME_CONTACT_US, 'action=send')); ?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" class="boxedTable">
          <tr>
            <td class="main"><?php echo TEXT_SELECT_DEPARTMENT; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo tep_draw_pull_down_menu('department', $departments_array, DEFAULT_HELPDESK_DEPARTMENT_ID); ?></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_NAME; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo tep_draw_input_field('name', (($error == true) ? $_POST['name'] : $first_name)); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_EMAIL; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo tep_draw_input_field('email', (($error == true) ? $_POST['email'] : $email_address)); if ($error) echo ENTRY_EMAIL_ADDRESS_CHECK_ERROR; ?></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_SUBJECT; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo tep_draw_input_field('subject', $_POST['subject']); ?></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_ENQUIRY; ?></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_textarea_field('enquiry', 'virtual', 20, 10, 'mceEditor', $_POST['enquiry']); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></td>
      </tr>
      </form>
<?php
  }
?>
    </table></form></td>
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