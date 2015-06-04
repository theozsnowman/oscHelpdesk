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
        $priority_id = tep_db_prepare_input($_GET['priority']);
        $priority_name_array = $_POST['priority'];

        $languages = tep_get_languages();
        for ($i=0; $i<sizeof($languages); $i++) {
          $sql_data_array = array('title' => tep_db_prepare_input($priority_name_array[$languages[$i]['id']]));

          if ($_GET['action'] == 'insert') {
            if (!tep_not_null($priority_id)) {
              $next_id_query = tep_db_query("select max(priority_id) as priority_id from " . TABLE_HELPDESK_PRIORITIES . "");
              $next_id = tep_db_fetch_array($next_id_query);
              $priority_id = $next_id['priority_id'] + 1;
            }

            $insert_sql_data = array('priority_id' => $priority_id,
                                     'languages_id' => $languages[$i]['id']);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_HELPDESK_PRIORITIES, $sql_data_array);
          } elseif ($_GET['action'] == 'save') {
            tep_db_perform(TABLE_HELPDESK_PRIORITIES, $sql_data_array, 'update', "priority_id = '" . tep_db_input($priority_id) . "' and languages_id = '" . $languages[$i]['id'] . "'");
          }
        }

        if ($_POST['default'] == 'on') {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($priority_id) . "' where configuration_key = 'DEFAULT_HELPDESK_PRIORITY_ID'");
        }

        tep_redirect(tep_href_link(FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page'] . '&priority=' . $priority_id));
        break;
      case 'deleteconfirm':
        $priority_id = tep_db_prepare_input($_GET['priority']);

        $priority_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_HELPDESK_PRIORITY_ID'");
        $priority = tep_db_fetch_array($priority_query);
        if ($priority['configuration_value'] == $priority_id) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_HELPDESK_PRIORITY_ID'");
        }

        tep_db_query("delete from " . TABLE_HELPDESK_PRIORITIES . " where priority_id = '" . tep_db_input($priority_id) . "'");

        tep_redirect(tep_href_link(FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page']));
        break;
      case 'delete':
        $priority_id = tep_db_prepare_input($_GET['priority']);

        $priority_query = tep_db_query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where priority_id = '" . tep_db_input($priority_id) . "'");
        $priority = tep_db_fetch_array($priority_query);

        $remove_priority = true;
        if ($priority_id == DEFAULT_HELPDESK_PRIORITY_ID) {
          $remove_priority = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_HELPDESK_PRIORITY, 'error');
          unset($_GET['action']);
        } elseif ($priority['count'] > 0) {
          $remove_priority = false;
          $messageStack->add(ERROR_PRIORITY_USED_IN_ENTRIES, 'error');
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRIORITIES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $priorities_query_raw = "select priority_id, title from " . TABLE_HELPDESK_PRIORITIES . " where languages_id = '" . $languages_id . "' order by title";
  $priorities_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $priorities_query_raw, $priorities_query_numrows);
  $priorities_query = tep_db_query($priorities_query_raw);
  while ($priorities = tep_db_fetch_array($priorities_query)) {
    if (((!$_GET['priority']) || ($_GET['priority'] == $priorities['priority_id'])) && (!$pInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
      $pInfo = new objectInfo($priorities);
    }

    if ( (is_object($pInfo)) && ($priorities['priority_id'] == $pInfo->priority_id) ) {
      echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page'] . '&priority=' . $pInfo->priority_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page'] . '&priority=' . $priorities['priority_id']) . '\'">' . "\n";
    }

    if (DEFAULT_HELPDESK_PRIORITY_ID == $priorities['priority_id']) {
      echo '                <td class="dataTableContent"><b>' . $priorities['title'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $priorities['title'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent" align="right"><?php if ( (is_object($pInfo)) && ($priorities['priority_id'] == $pInfo->priority_id) ) { echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page'] . '&priority=' . $priorities['priority_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $priorities_split->display_count($priorities_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></td>
                    <td class="smallText" align="right"><?php echo $priorities_split->display_links($priorities_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (!$_GET['action']) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page'] . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_PRIORITY . '</b>');

      $contents = array('form' => tep_draw_form('priority', FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page'] . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);

      $priority_inputs_string = '';
      $languages = tep_get_languages();
      for ($i=0; $i<sizeof($languages); $i++) {
        $priority_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('priority[' . $languages[$i]['id'] . ']');
      }

      $contents[] = array('text' => '<br>' . TEXT_INFO_PRIORITIES . $priority_inputs_string);
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . tep_href_link(FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_PRIORITY . '</b>');

      $contents = array('form' => tep_draw_form('priority', FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page'] . '&priority=' . $pInfo->priority_id  . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);

      $priority_id = tep_db_prepare_input($_GET['priority']);
      $priority_inputs_string = '';
      $languages = tep_get_languages();
      for ($i=0; $i<sizeof($languages); $i++) {
        $priority_query = tep_db_query("select title from " . TABLE_HELPDESK_PRIORITIES . " where priority_id = '" . tep_db_input($priority_id) . "' and languages_id = '" . $languages[$i]['id'] . "'");
        $priority = tep_db_fetch_array($priority_query);
        $priority_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('priority[' . $languages[$i]['id'] . ']', $priority['title']);
      }

      $contents[] = array('text' => '<br>' . TEXT_INFO_PRIORITIES . $priority_inputs_string);
      if (DEFAULT_HELPDESK_PRIORITY_ID != $pInfo->priority_id) $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page'] . '&priority=' . $pInfo->priority_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_PRIORITY . '</b>');

      $contents = array('form' => tep_draw_form('priority', FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page'] . '&priority=' . $pInfo->priority_id  . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $pInfo->title . '</b>');
      if ($remove_priority) $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page'] . '&priority=' . $pInfo->priority_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($pInfo)) {
        $heading[] = array('text' => '<b>' . $pInfo->title . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page'] . '&priority=' . $pInfo->priority_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_HELPDESK_PRIORITIES, 'page=' . $_GET['page'] . '&priority=' . $pInfo->priority_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');

        $priorities_string = '';
        $priorities_query = tep_db_query("select hp.title as priority_title, l.name as language_title, l.image, l.directory from " . TABLE_HELPDESK_PRIORITIES . " hp, " . TABLE_LANGUAGES . " l where hp.priority_id = '" . $pInfo->priority_id . "' and hp.languages_id = l.languages_id order by l.sort_order");
        while ($priorities = tep_db_fetch_array($priorities_query)) {
          $priorities_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $priorities['directory'] . '/images/' . $priorities['image'], $priorities['language_title']) . '&nbsp;' . $priorities['priority_title'];
        }

        $contents[] = array('text' => $priorities_string);
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