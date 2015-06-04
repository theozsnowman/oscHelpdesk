<?php
/*
  $Id: ,v 3.00 2015/06/04

  oscHelpdesk
  http://www.snowtech.com.au

  Copyright (c) 2003 -2015 Snowtech Services

*/

    require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  if ($_GET['action']) {
    switch ($_GET['action']) {
      case 'insert':
      case 'save':
        $department_id = tep_db_prepare_input($_GET['department']);
        $title = tep_db_prepare_input($_POST['title']);
        $email_address = tep_db_prepare_input($_POST['email_address']);
        $name = tep_db_prepare_input($_POST['name']);
        $password = tep_db_prepare_input($_POST['password']);

        $sql_data_array = array('title' => $title,
                                'email_address' => $email_address,
                                'name' => $name,
                                'password' => $password);

        if ($_GET['action'] == 'insert') {
          tep_db_perform(TABLE_HELPDESK_DEPARTMENTS, $sql_data_array);
          $department_id = tep_db_insert_id();
        } elseif ($_GET['action'] == 'save') {
          tep_db_perform(TABLE_HELPDESK_DEPARTMENTS, $sql_data_array, 'update', "department_id = '" . tep_db_input($department_id) . "'");
        }

        if ($_POST['default'] == 'on') {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($department_id) . "' where configuration_key = 'DEFAULT_HELPDESK_DEPARTMENT_ID'");
        }

        tep_redirect(tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page'] . '&department=' . $department_id));
        break;
      case 'deleteconfirm':
        $department_id = tep_db_prepare_input($_GET['department']);

        $department_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_HELPDESK_DEPARTMENT_ID'");
        $department = tep_db_fetch_array($department_query);
        if ($department['configuration_value'] == $department_id) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_HELPDESK_DEPARTMENT_ID'");
        }

        tep_db_query("delete from " . TABLE_HELPDESK_DEPARTMENTS . " where department_id = '" . tep_db_input($department_id) . "'");

        tep_redirect(tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page']));
        break;
      case 'delete':
        $department_id = tep_db_prepare_input($_GET['department']);

        $check_query = tep_db_query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where department_id = '" . tep_db_input($department_id) . "'");
        $check = tep_db_fetch_array($check_query);

        $remove_department = true;
        if ($department_id == DEFAULT_HELPDESK_DEPARTMENT_ID) {
          $remove_department = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_HELPDESK_DEPARTMENT, 'error');
          unset($_GET['action']);
        } elseif ($check['count'] > 0) {
          $remove_department = false;
          $messageStack->add(ERROR_DEPARTMENT_USED_IN_ENTRIES, 'error');
          unset($_GET['action']);
        }
        break;
    }
  }
  require(DIR_WS_INCLUDES . 'template_top.php');
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" height="40">
          <tr>

            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_DEPARTMENTS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $departments_query_raw = "select department_id, title, email_address, name, password from " . TABLE_HELPDESK_DEPARTMENTS . " order by title";
  $departments_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $departments_query_raw, $departments_query_numrows);
  $departments_query = tep_db_query($departments_query_raw);
  while ($departments = tep_db_fetch_array($departments_query)) {
    if (((!$_GET['department']) || ($_GET['department'] == $departments['department_id'])) && (!$dInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
      $dInfo = new objectInfo($departments);
    }

    if ( (is_object($dInfo)) && ($departments['department_id'] == $dInfo->department_id) ) {
      echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page'] . '&department=' . $dInfo->department_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page'] . '&department=' . $departments['department_id']) . '\'">' . "\n";
    }

    if (DEFAULT_HELPDESK_DEPARTMENT_ID == $departments['department_id']) {
      echo '                <td class="dataTableContent"><b>' . $departments['title'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $departments['title'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent" align="right"><?php if ( (is_object($dInfo)) && ($departments['department_id'] == $dInfo->department_id) ) { echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page'] . '&department=' . $departments['department_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $departments_split->display_count($departments_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></td>
                    <td class="smallText" align="right"><?php echo $departments_split->display_links($departments_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (!$_GET['action']) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page'] . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
                  </tr>

<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($_GET['action']) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_DEPARTMENT . '</b>');

      $contents = array('form' => tep_draw_form('departments', FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page'] . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_DEPARTMENT . '<br>' . tep_draw_input_field('title'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_EMAIL_ADDRESS . '<br>' . tep_draw_input_field('email_address'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_PASSWORD . '<br>' . tep_draw_input_field('password'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_NAME . '<br>' . tep_draw_input_field('name'));
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_DEPARTMENT . '</b>');

      $contents = array('form' => tep_draw_form('departments', FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page'] . '&department=' . $dInfo->department_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_DEPARTMENT . '<br>' . tep_draw_input_field('title', $dInfo->title));
      $contents[] = array('text' => '<br>' . TEXT_INFO_EMAIL_ADDRESS . '<br>' . tep_draw_input_field('email_address', $dInfo->email_address));
      $contents[] = array('text' => '<br>' . TEXT_INFO_PASSWORD . '<br>' . tep_draw_input_field('password'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_NAME . '<br>' . tep_draw_input_field('name', $dInfo->name));
      if (DEFAULT_HELPDESK_DEPARTMENT_ID != $dInfo->department_id) $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page'] . '&department=' . $dInfo->department_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_DEPARTMENT . '</b>');

      $contents = array('form' => tep_draw_form('departments', FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page'] . '&department=' . $dInfo->department_id  . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $dInfo->title . '</b>');
      if ($remove_department) $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page'] . '&department=' . $dInfo->department_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($dInfo)) {
        $heading[] = array('text' => '<b>' . $dInfo->title . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page'] . '&department=' . $dInfo->department_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_HELPDESK_DEPARTMENTS, 'page=' . $_GET['page'] . '&department=' . $dInfo->department_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_DEPARTMENT . '<br>' . $dInfo->title);
        $contents[] = array('text' => '<br>' . TEXT_INFO_EMAIL_ADDRESS . '<br>' . $dInfo->email_address);
        $contents[] = array('text' => '<br>' . TEXT_INFO_PASSWORD . '<br>' . $dInfo->password);
        $contents[] = array('text' => '<br>' . TEXT_INFO_NAME . '<br>' . $dInfo->name);
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
          </tr>
        </table></td>
      </tr>
    </table>
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>