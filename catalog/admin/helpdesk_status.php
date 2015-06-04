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
        $status_id = tep_db_prepare_input($_GET['status']);
        $status_name_array = $_POST['status'];

        $languages = tep_get_languages();
        for ($i=0; $i<sizeof($languages); $i++) {
          $sql_data_array = array('title' => tep_db_prepare_input($status_name_array[$languages[$i]['id']]));

          if ($_GET['action'] == 'insert') {
            if (!tep_not_null($status_id)) {
              $next_id_query = tep_db_query("select max(status_id) as status_id from " . TABLE_HELPDESK_STATUSES . "");
              $next_id = tep_db_fetch_array($next_id_query);
              $status_id = $next_id['status_id'] + 1;
            }

            $insert_sql_data = array('status_id' => $status_id,
                                     'languages_id' => $languages[$i]['id']);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_HELPDESK_STATUSES, $sql_data_array);
          } elseif ($_GET['action'] == 'save') {
            tep_db_perform(TABLE_HELPDESK_STATUSES, $sql_data_array, 'update', "status_id = '" . tep_db_input($status_id) . "' and languages_id = '" . $languages[$i]['id'] . "'");
          }
        }

        if ($_POST['default'] == 'on') {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($status_id) . "' where configuration_key = 'DEFAULT_HELPDESK_STATUS_ID'");
        }

        tep_redirect(tep_href_link(FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page'] . '&status=' . $status_id));
        break;
      case 'deleteconfirm':
        $status_id = tep_db_prepare_input($_GET['status']);

        $status_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_HELPDESK_STATUS_ID'");
        $status = tep_db_fetch_array($status_query);
        if ($status['configuration_value'] == $status_id) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_HELPDESK_STATUS_ID'");
        }

        tep_db_query("delete from " . TABLE_HELPDESK_STATUSES . " where status_id = '" . tep_db_input($status_id) . "'");

        tep_redirect(tep_href_link(FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page']));
        break;
      case 'delete':
        $status_id = tep_db_prepare_input($_GET['status']);

        $status_query = tep_db_query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where status_id = '" . tep_db_input($status_id) . "'");
        $status = tep_db_fetch_array($status_query);

        $remove_status = true;
        if ($status_id == DEFAULT_HELPDESK_STATUS_ID) {
          $remove_status = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_HELPDESK_STATUS, 'error');
          unset($_GET['action']);
        } elseif ($status['count'] > 0) {
          $remove_status = false;
          $messageStack->add(ERROR_STATUS_USED_IN_ENTRIES, 'error');
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $statuses_query_raw = "select status_id, title from " . TABLE_HELPDESK_STATUSES . " where languages_id = '" . $languages_id . "' order by title";
  $statuses_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $statuses_query_raw, $statuses_query_numrows);
  $statuses_query = tep_db_query($statuses_query_raw);
  while ($statuses = tep_db_fetch_array($statuses_query)) {
    if (((!$_GET['status']) || ($_GET['status'] == $statuses['status_id'])) && (!$sInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
      $sInfo = new objectInfo($statuses);
    }

    if ( (is_object($sInfo)) && ($statuses['status_id'] == $sInfo->status_id) ) {
      echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page'] . '&status=' . $sInfo->status_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page'] . '&status=' . $statuses['status_id']) . '\'">' . "\n";
    }

    if (DEFAULT_HELPDESK_STATUS_ID == $statuses['status_id']) {
      echo '                <td class="dataTableContent"><b>' . $statuses['title'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $statuses['title'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent" align="right"><?php if ( (is_object($sInfo)) && ($statuses['status_id'] == $sInfo->status_id) ) { echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page'] . '&status=' . $statuses['status_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $statuses_split->display_count($statuses_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></td>
                    <td class="smallText" align="right"><?php echo $statuses_split->display_links($statuses_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (!$_GET['action']) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page'] . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_STATUS . '</b>');

      $contents = array('form' => tep_draw_form('status', FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page'] . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);

      $status_inputs_string = '';
      $languages = tep_get_languages();
      for ($i=0; $i<sizeof($languages); $i++) {
        $status_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('status[' . $languages[$i]['id'] . ']');
      }

      $contents[] = array('text' => '<br>' . TEXT_INFO_STATUSES . $status_inputs_string);
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . tep_href_link(FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_STATUS . '</b>');

      $contents = array('form' => tep_draw_form('status', FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page'] . '&status=' . $sInfo->status_id  . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);

      $status_id = tep_db_prepare_input($_GET['status']);
      $status_inputs_string = '';
      $languages = tep_get_languages();
      for ($i=0; $i<sizeof($languages); $i++) {
        $status_query = tep_db_query("select title from " . TABLE_HELPDESK_STATUSES . " where status_id = '" . tep_db_input($status_id) . "' and languages_id = '" . $languages[$i]['id'] . "'");
        $status = tep_db_fetch_array($status_query);
        $status_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('status[' . $languages[$i]['id'] . ']', $status['title']);
      }

      $contents[] = array('text' => '<br>' . TEXT_INFO_STATUSES . $status_inputs_string);
      if (DEFAULT_HELPDESK_STATUS_ID != $sInfo->status_id) $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page'] . '&status=' . $sInfo->status_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_STATUS . '</b>');

      $contents = array('form' => tep_draw_form('status', FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page'] . '&status=' . $sInfo->status_id  . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $sInfo->title . '</b>');
      if ($remove_status) $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page'] . '&status=' . $sInfo->status_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($sInfo)) {
        $heading[] = array('text' => '<b>' . $sInfo->title . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page'] . '&status=' . $sInfo->status_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_HELPDESK_STATUS, 'page=' . $_GET['page'] . '&status=' . $sInfo->status_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');

        $statuses_string = '';
        $statuses_query = tep_db_query("select hs.title as status_title, l.name as language_title, l.image, l.directory from " . TABLE_HELPDESK_STATUSES . " hs, " . TABLE_LANGUAGES . " l where hs.status_id = '" . $sInfo->status_id . "' and hs.languages_id = l.languages_id order by l.sort_order");
        while ($statuses = tep_db_fetch_array($statuses_query)) {
          $statuses_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $statuses['directory'] . '/images/' . $statuses['image'], $statuses['language_title']) . '&nbsp;' . $statuses['status_title'];
        }

        $contents[] = array('text' => $statuses_string);
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