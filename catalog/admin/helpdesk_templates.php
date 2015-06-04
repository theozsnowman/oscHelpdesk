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
        $template_id = tep_db_prepare_input($_GET['template']);
        $title = tep_db_prepare_input($_POST['title']);
        $template = tep_db_prepare_input($_POST['body']);

        $sql_data_array = array('title' => $title,
                                'template' => $template);

        if ($_GET['action'] == 'insert') {
          tep_db_perform(TABLE_HELPDESK_TEMPLATES, $sql_data_array);
          $template_id = tep_db_insert_id();
        } elseif ($_GET['action'] == 'save') {
          tep_db_perform(TABLE_HELPDESK_TEMPLATES, $sql_data_array, 'update', "template_id = '" . tep_db_input($template_id) . "'");
        }

        tep_redirect(tep_href_link(FILENAME_HELPDESK_TEMPLATES, 'page=' . $_GET['page'] . '&template=' . $template_id));
        break;
      case 'deleteconfirm':
        $template_id = tep_db_prepare_input($_GET['template']);

        tep_db_query("delete from " . TABLE_HELPDESK_TEMPLATES . " where template_id = '" . tep_db_input($template_id) . "'");

        tep_redirect(tep_href_link(FILENAME_HELPDESK_TEMPLATES, 'page=' . $_GET['page']));
        break;
    }
  }
  require(DIR_WS_INCLUDES . 'template_top.php');
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" height="40">
          <tr>


<script language="javascript" type="text/javascript" src="includes/javascript/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
tinyMCE.init({
  mode : "textareas",
  editor_selector : "mceEditor",
  theme : "advanced",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left",
  theme_advanced_path_location : "bottom",
  extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
});
</script>

            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
<?php
  if ( ($_GET['action'] == 'new') || ($_GET['action'] == 'edit') ) {
    switch ($_GET['action']) {
      case 'new':
        $form_action = 'insert';
        $tInfo = new objectInfo(array());
        break;
      case 'edit':
        $form_action = 'save';
        $template_id = tep_db_prepare_input($_GET['template']);

        $template_query = tep_db_query("select template_id, title, template from " . TABLE_HELPDESK_TEMPLATES . " where template_id = '" . tep_db_input($template_id) . "'");
        $template = tep_db_fetch_array($template_query);

        $tInfo = new objectInfo($template);
        break;
    }
?>
      <?php echo tep_draw_form('template', FILENAME_HELPDESK_TEMPLATES, 'page=' . $_GET['page'] . '&template=' . $_GET['template'] . '&action=' . $form_action); ?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" class="columnLeft">
          <tr>
            <td class="smallText"><?php echo (($form_action == 'insert') ? TEXT_INSERT_TEMPLATE : TEXT_UPDATE_TEMPLATE); ?></td>
            <td align="right"><?php echo tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . tep_href_link(FILENAME_HELPDESK_TEMPLATES, 'page=' . $_GET['page'] . '&template=' . $_GET['template']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" class="columnLeft">
          <tr>
            <td class="smallText"><?php echo TEXT_TEMPLATE_TITLE; ?></td>
            <td class="smallText"><?php echo tep_draw_input_field('title', $tInfo->title); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="smallText" valign="top"><?php echo TEXT_TEMPLATE_BODY; ?></td>
            <td class="smallText"><?php echo tep_draw_textarea_field('body', 'virtual', '60', '10', 'mceEditor', $tInfo->template, 'style="width: 100%"'); ?></td>
          </tr>
        </table></td>
      </tr>
      </form>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TEMPLATES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $templates_query_raw = "select template_id, title from " . TABLE_HELPDESK_TEMPLATES . " order by title";
    $templates_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $templates_query_raw, $templates_query_numrows);
    $templates_query = tep_db_query($templates_query_raw);
    while ($templates = tep_db_fetch_array($templates_query)) {
      if (((!$_GET['template']) || ($_GET['template'] == $templates['template_id'])) && (!$tInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
        $tInfo = new objectInfo($templates);
      }

      if ( (is_object($tInfo)) && ($templates['template_id'] == $tInfo->template_id) ) {
        echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_HELPDESK_TEMPLATES, 'page=' . $_GET['page'] . '&template=' . $tInfo->template_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_HELPDESK_TEMPLATES, 'page=' . $_GET['page'] . '&template=' . $templates['template_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo $templates['title']; ?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($tInfo)) && ($templates['template_id'] == $tInfo->template_id) ) { echo tep_image(DIR_WS_ICONS . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_HELPDESK_TEMPLATES, 'page=' . $_GET['page'] . '&template=' . $templates['template_id']) . '">' . tep_image(DIR_WS_ICONS . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $templates_split->display_count($templates_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></td>
                    <td class="smallText" align="right"><?php echo $templates_split->display_links($templates_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
    if (!$_GET['action']) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_HELPDESK_TEMPLATES, 'page=' . $_GET['page'] . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
      case 'delete':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_TEMPLATE . '</b>');

        $contents = array('form' => tep_draw_form('template', FILENAME_HELPDESK_TEMPLATES, 'page=' . $_GET['page'] . '&template=' . $tInfo->template_id  . '&action=deleteconfirm'));
        $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
        $contents[] = array('text' => '<br><b>' . $tInfo->title . '</b>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_HELPDESK_TEMPLATES, 'page=' . $_GET['page'] . '&template=' . $tInfo->template_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
        if (is_object($tInfo)) {
          $heading[] = array('text' => '<b>' . $tInfo->title . '</b>');

          $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_HELPDESK_TEMPLATES, 'page=' . $_GET['page'] . '&template=' . $tInfo->template_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_HELPDESK_TEMPLATES, 'page=' . $_GET['page'] . '&template=' . $tInfo->template_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
          $contents[] = array('text' => '<br>' . TEXT_INFO_TEMPLATE . '<br>' . $tInfo->title);
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
<?php
  }
?>
          </tr>
        </table></td>
      </tr>
    </table>
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>