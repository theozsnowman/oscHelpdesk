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
      case 'reply_confirm':
        $ticket = tep_db_prepare_input($_GET['ticket']);
        $id = tep_db_prepare_input($_GET['id']);
        $status_id = tep_db_prepare_input($_POST['status']);
        $from_name = tep_db_prepare_input($_POST['from_name']);
        $from_email_address = tep_db_prepare_input($_POST['from_email_address']);
        $to_name = tep_db_prepare_input($_POST['to_name']);
        $to_email_address = tep_db_prepare_input($_POST['to_email_address']);
        $subject = tep_db_prepare_input($_POST['subject']);
        $body = tep_db_prepare_input($_POST['body']);

        $sql_data_array = array('ticket' => $ticket,
                                'parent_id' => $id,
                                'message_id' => '',
                                'ip_address' => getenv('SERVER_ADDR'),
                                'host' => getenv('SERVER_NAME'),
                                'datestamp_local' => 'now()',
                                'datestamp' => 'now()',
                                'receiver' => $to_name,
                                'receiver_email_address' => $to_email_address,
                                'sender' => $from_name,
                                'email_address' => $from_email_address,
                                'subject' => $subject,
                                'body' => $body,
                                'entry_read' => '1');

        tep_db_perform(TABLE_HELPDESK_ENTRIES, $sql_data_array);

        $sql_data_array = array('status_id' => $status_id,
                                'datestamp_last_entry' => 'now()');

        tep_db_perform(TABLE_HELPDESK_TICKETS, $sql_data_array, 'update', "ticket = '" . tep_db_input($ticket) . "'");

        tep_mail($to_name, $to_email_address, $subject, $body, $from_name, $from_email_address);

        $messageStack->add_session(SUCCESS_REPLY_PROCESSED, 'success');
        tep_redirect(tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=view'));
        break;
      case 'save_entry':
        $department_id = tep_db_prepare_input($_POST['department']);
        $status_id = tep_db_prepare_input($_POST['status']);
        $priority_id = tep_db_prepare_input($_POST['priority']);

        $ticket = tep_db_prepare_input($_POST['ticket']);
        $id = tep_db_prepare_input($_GET['id']);
        $from_name = tep_db_prepare_input($_POST['from_name']);
        $from_email_address = tep_db_prepare_input($_POST['from_email_address']);
        $to_name = tep_db_prepare_input($_POST['to_name']);
        $to_email_address = tep_db_prepare_input($_POST['to_email_address']);
        $subject = tep_db_prepare_input($_POST['subject']);
        $body = tep_db_prepare_input($_POST['body']);

        $entry_query = tep_db_query("select ticket, datestamp_local from " . TABLE_HELPDESK_ENTRIES . " where helpdesk_entries_id = '" . tep_db_input($id) . "'");
        $entry = tep_db_fetch_array($entry_query);

        $sql_data_array = array('department_id' => $department_id,
                                'status_id' => $status_id,
                                'priority_id' => $priority_id);

        if ($entry['ticket'] == $ticket) {
          $new_ticket = false;

          tep_db_perform(TABLE_HELPDESK_TICKETS, $sql_data_array, 'update', "ticket = '" . tep_db_input($ticket) . "'");
        } else {
          $new_ticket = true;

          $check_query = tep_db_query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . tep_db_input($ticket) . "'");
          $check = tep_db_fetch_array($check_query);

          if ($check['count'] > 0) {
            $ticket_date_query = tep_db_query("select datestamp_last_entry from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . tep_db_input($ticket) . "'");
            $ticket_date = tep_db_fetch_array($ticket_date_query);

            if ($entry['datestamp_local'] > $ticket_date['datestamp_last_entry']) {
              $sql_data_array['datestamp_last_entry'] = $entry['datestamp_local'];
            }

            tep_db_perform(TABLE_HELPDESK_TICKETS, $sql_data_array, 'update', "ticket = '" . tep_db_input($ticket) . "'");
          } else {
            $sql_data_array['ticket'] = $ticket;
            $sql_data_array['datestamp_last_entry'] = $entry['datestamp_local'];

            tep_db_perform(TABLE_HELPDESK_TICKETS, $sql_data_array);
          }
        }

        $sql_data_array = array('ticket' => $ticket,
                                'receiver' => $to_name,
                                'receiver_email_address' => $to_email_address,
                                'sender' => $from_name,
                                'email_address' => $from_email_address,
                                'subject' => $subject,
                                'body' => $body);

        if ($new_ticket == true) $sql_data_array['parent_id'] = '0';

        tep_db_perform(TABLE_HELPDESK_ENTRIES, $sql_data_array, 'update', "helpdesk_entries_id = '" . tep_db_input($id) . "'");

        $check_query = tep_db_query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $entry['ticket'] . "'");
        $check = tep_db_fetch_array($check_query);

        $ticket_exists = true;
        if ($check['count'] < 1) {
           $ticket_exists = false;
          tep_db_query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $entry['ticket'] . "'");
        }

        $messageStack->add_session(SUCCESS_ENTRY_UPDATED, 'success');
        tep_redirect(tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $ticket . '&action=view&id=' . $_GET['id']));
        break;
      case 'delete_confirm':
        $ticket = tep_db_prepare_input($_GET['ticket']);
        $id = tep_db_prepare_input($_GET['id']);
        $whole = tep_db_prepare_input($_POST['whole']);

        if ($whole == 'true') {
          tep_db_query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . tep_db_input($ticket) . "'");
          tep_db_query("delete from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . tep_db_input($ticket) . "'");

          $messageStack->add_session(SUCCESS_WHOLE_THREAD_REMOVED, 'success');
        } else {
          tep_db_query("delete from " . TABLE_HELPDESK_ENTRIES . " where helpdesk_entries_id = '" . tep_db_input($id) . "'");

          $check_query = tep_db_query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . tep_db_input($ticket) . "'");
          $check = tep_db_fetch_array($check_query);

          if ($check['count'] > 0) {
            tep_redirect(tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=view'));
          } else {
            tep_db_query("delete from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . tep_db_input($ticket) . "'");
          }

          $messageStack->add_session(SUCCESS_ENTRY_REMOVED, 'success');
        }

        tep_redirect(tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page']));
        break;
      case 'updatestatus':
        $ticket = tep_db_prepare_input($_GET['ticket']);
        $department_id = tep_db_prepare_input($_POST['department']);
        $status_id = tep_db_prepare_input($_POST['status']);
        $priority_id = tep_db_prepare_input($_POST['priority']);

        tep_db_query("update " . TABLE_HELPDESK_TICKETS . " set department_id = '" . tep_db_input($department_id) . "', status_id = '" . tep_db_input($status_id) . "', priority_id = '" . tep_db_input($priority) . "' where ticket = '" . tep_db_input($ticket) . "'");

        $messageStack->add_session(SUCCESS_TICKET_UPDATED, 'success');
        tep_redirect(tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $ticket . '&action=view'));
        break;
      case 'view':
        $ticket = tep_db_prepare_input($_GET['ticket']);

        $check_query = tep_db_query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . tep_db_input($ticket) . "'");
        $check = tep_db_fetch_array($check_query);

        if ($check['count'] < 1) {
          $messageStack->add_session(ERROR_TICKET_DOES_NOT_EXIST, 'error');
          tep_redirect(tep_href_link(FILENAME_HELPDESK, 'ticket=' . $ticket));
        }
        break;
      case 'updatecomment':
        $ticket = tep_db_prepare_input($_GET['ticket']);
        $comment = tep_db_prepare_input($_POST['comment']);

        $sql_data_array = array('comment' => $comment,
                                'datestamp_comment' => 'now()');

        tep_db_perform(TABLE_HELPDESK_TICKETS, $sql_data_array, 'update', "ticket = '" . tep_db_input($ticket) . "'");

        $messageStack->add_session(SUCCESS_COMMENT_UPDATED, 'success');
        tep_redirect(tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=view'));
        break;
    }
  }

  if (isset($_GET['statusfilter'])) {
    if (!tep_session_is_registered('_statusfilter')) tep_session_register('_statusfilter');
    $_statusfilter = $_GET['statusfilter'];
  }
  if ($_statusfilter == '0') tep_session_unregister('_statusfilter');

  if (isset($_GET['priorityfilter'])) {
    if (!tep_session_is_registered('_priorityfilter')) tep_session_register('_priorityfilter');
    $_priorityfilter = $_GET['priorityfilter'];
  }
  if ($_priorityfilter == '0') tep_session_unregister('_priorityfilter');

  if (isset($_GET['departmentfilter'])) {
    if (!tep_session_is_registered('_departmentfilter')) tep_session_register('_departmentfilter');
    $_departmentfilter = $_GET['departmentfilter'];
  }
  if ($_departmentfilter == '0') tep_session_unregister('_departmentfilter');

  if (isset($_GET['entryfilter'])) {
    if (!tep_session_is_registered('_entryfilter')) tep_session_register('_entryfilter');
    $_entryfilter = $_GET['entryfilter'];
  }
  if ($_entryfilter == '0') tep_session_unregister('_entryfilter');

  $statuses_array = array();
  $priorities_array = array();
  $departments_array = array();
  $entries_array = array(array('id' => '0', 'text' => TEXT_ALL_ENTRIES),
                         array('id' => '1', 'text' => TEXT_ONLY_NEW_ENTRIES));

  if (!$_GET['action']) {
    $statuses_array[] = array('id' => '0', 'text' => TEXT_ALL_STATUSES);
    $priorities_array[] = array('id' => '0', 'text' => TEXT_ALL_PRIORITIES);
    $departments_array[] = array('id' => '0', 'text' => TEXT_ALL_DEPARTMENTS);
  }

  $statuses_query = tep_db_query("select status_id, title from " . TABLE_HELPDESK_STATUSES . " where languages_id = '" . $languages_id . "' order by title");
  while ($statuses = tep_db_fetch_array($statuses_query)) {
    $statuses_array[] = array('id' => $statuses['status_id'], 'text' => $statuses['title']);
  }

  $priorities_query = tep_db_query("select priority_id, title from " . TABLE_HELPDESK_PRIORITIES . " where languages_id = '" . $languages_id . "' order by title");
  while ($priorities = tep_db_fetch_array($priorities_query)) {
    $priorities_array[] = array('id' => $priorities['priority_id'], 'text' => $priorities['title']);
  }

  $departments_query = tep_db_query("select department_id, title from " . TABLE_HELPDESK_DEPARTMENTS . " order by title");
  while ($departments = tep_db_fetch_array($departments_query)) {
    $departments_array[] = array('id' => $departments['department_id'], 'text' => $departments['title']);
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
  theme_advanced_buttons1 : "bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright, justifyfull,bullist,numlist,undo,redo,link,unlink",
  theme_advanced_buttons2 : "",
  theme_advanced_buttons3 : "",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left",
  theme_advanced_path_location : "bottom",
  extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
});
</script>

            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', '1', HEADING_IMAGE_HEIGHT); ?></td>
<?php
  if (!isset($_GET['action'])) {
?>
            <td class="smallText"><?php echo tep_draw_form('ticket', FILENAME_HELPDESK, '', 'get') . tep_draw_hidden_field('page', $_GET['page']) . TEXT_TICKET_NUMBER . '&nbsp;' . tep_draw_input_field('ticket', '', 'size="10" maxlength="7"') . tep_draw_hidden_field('action', 'view') . '</form>'; ?></td>
            <td class="smallText"><?php echo tep_draw_form('ticket', FILENAME_HELPDESK, '', 'get') . TEXT_DEPARTMENT . '&nbsp;' . tep_draw_pull_down_menu('departmentfilter', $departments_array, $_departmentfilter, 'onChange="this.form.submit();"' . ((isset($_departmentfilter) && $_departmentfilter != '0') ? ' style="background-color: #fedecb;"' : '')) . '</form>'; ?></td>
            <td align="right"><?php echo tep_draw_form('filter', FILENAME_HELPDESK, '', 'get'); ?><table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="smallText" align="right"><?php echo TEXT_STATUS . '&nbsp;' . tep_draw_pull_down_menu('statusfilter', $statuses_array, $_statusfilter, 'onChange="this.form.submit();"' . ((isset($_statusfilter) && $_statusfilter != '0') ? ' style="background-color: #fedecb;"' : '')); ?></td>
              </tr>
              <tr>
                <td class="smallText" align="right"><?php echo TEXT_PRIORITY . '&nbsp;' . tep_draw_pull_down_menu('priorityfilter', $priorities_array, $_priorityfilter, 'onChange="this.form.submit();"' . ((isset($_priorityfilter) && $_priorityfilter != '0') ? ' style="background-color: #fedecb;"' : '')); ?></td>
              </tr>
              <tr>
                <td class="smallText" align="right"><?php echo TEXT_ENTRIES . '&nbsp;' . tep_draw_pull_down_menu('entryfilter', $entries_array, $_entryfilter, 'onChange="this.form.submit();"' . ((isset($_entryfilter) && $_entryfilter != '0') ? ' style="background-color: #fedecb;"' : '')); ?></td>
              </tr>
            </table></form></td>
<?php
  } elseif ($_GET['subaction'] == 'reply') {
    $templates_array = array(array('id' => '', 'text' => TEXT_PLEASE_SELECT));
    $templates_query = tep_db_query("select template_id, title from " . TABLE_HELPDESK_TEMPLATES . " order by title");
    while ($templates = tep_db_fetch_array($templates_query)) {
      $templates_array[] = array('id' => $templates['template_id'], 'text' => $templates['title']);
    }
?>
            <td class="smallText" align="right"><?php echo tep_draw_form('ticket', FILENAME_HELPDESK, '', 'get') . tep_draw_hidden_field('page', $_GET['page']) . tep_draw_hidden_field('ticket', $_GET['ticket']) . tep_draw_hidden_field('action', 'view') . tep_draw_hidden_field('id', $_GET['id']) . tep_draw_hidden_field('subaction', 'reply') . TEXT_TEMPLATES . ' ' . tep_draw_pull_down_menu('template', $templates_array, $_GET['template'], 'onChange="this.form.submit();"') . '</form>'; ?></td>
<?php
  }
?>
          </tr>
        </table></td>
      </tr>
<?php
  if ($_GET['action'] == 'view') {
    $ticket = tep_db_prepare_input($_GET['ticket']);
    $id = tep_db_prepare_input($_GET['id']);

    if (isset($id)) {
      $entry_query = tep_db_query("select he.helpdesk_entries_id, he.ticket, ifnull(he.host, he.ip_address) as host, he.datestamp_local, he.datestamp, he.receiver, he.receiver_email_address, he.sender, he.email_address, he.subject, he.body, he.entry_read, ht.department_id, ht.status_id, ht.priority_id from " . TABLE_HELPDESK_ENTRIES . " he left join " . TABLE_HELPDESK_TICKETS . " ht on (he.ticket = ht.ticket) where he.ticket = '" . tep_db_input($ticket) . "' and he.helpdesk_entries_id = '" . tep_db_input($id) . "'");
    } else {
      $entry_query = tep_db_query("select he.helpdesk_entries_id, he.ticket, ifnull(he.host, he.ip_address) as host, he.datestamp_local, he.datestamp, he.receiver, he.receiver_email_address, he.sender, he.email_address, he.subject, he.body, he.entry_read, ht.department_id, ht.status_id, ht.priority_id from " . TABLE_HELPDESK_ENTRIES . " he left join " . TABLE_HELPDESK_TICKETS . " ht on (he.ticket = ht.ticket) where he.ticket = '" . tep_db_input($ticket) . "' and he.parent_id = '0'");
    }
    $entry = tep_db_fetch_array($entry_query);

// mark entry as read
    if ($entry['entry_read'] != '1') {
      tep_db_query("update " . TABLE_HELPDESK_ENTRIES . " set entry_read = '1' where ticket = '" . $entry['ticket'] . "' and helpdesk_entries_id = '" . $entry['helpdesk_entries_id'] . "'");
    }

    $department_query = tep_db_query("select hd.title, hd.email_address, hd.name from " . TABLE_HELPDESK_DEPARTMENTS . " hd, " . TABLE_HELPDESK_TICKETS . " ht where ht.ticket = '" . tep_db_input($ticket) . "' and ht.department_id = hd.department_id");
    $department = tep_db_fetch_array($department_query);

    if ($_GET['subaction'] == 'reply') {
      $template = '';
      if ($_GET['template']) {
        $template_id = tep_db_prepare_input($_GET['template']);
        $template_query = tep_db_query("select template from " . TABLE_HELPDESK_TEMPLATES . " where template_id = '" . tep_db_input($template_id) . "'");
        $template = tep_db_fetch_array($template_query);

        $template = $template['template'];
      }

      $subject = $entry['subject'];
      if (!strstr($subject, '['. DEFAULT_HELPDESK_TICKET_PREFIX . $entry['ticket'] . ']')) {
        $subject = '[' .DEFAULT_HELPDESK_TICKET_PREFIX. $entry['ticket'] . '] ' . $subject;
      }
      $subject = 'RE: ' . $subject;
?>
      <?php echo tep_draw_form('reply', FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&id=' . $_GET['id'] . '&action=reply_confirm'); ?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" class="columnLeft">
          <tr>
            <td class="smallText"><?php echo TEXT_SEND_INTRO; ?></td>
            <td align="right"><?php echo tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" class="columnLeft">
          <tr>
            <td class="smallText"><?php echo TEXT_STATUS; ?></td>
            <td class="smallText"><?php echo tep_draw_pull_down_menu('status', $statuses_array, $entry['status_id']); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="smallText"><?php echo TEXT_FROM_NAME; ?></td>
            <td class="smallText"><?php echo tep_draw_input_field('from_name', $department['title']); ?></td>
          </tr>
          <tr>
            <td class="smallText"><?php echo TEXT_FROM_EMAIL_ADDRESS; ?></td>
            <td class="smallText"><?php echo tep_draw_input_field('from_email_address', $department['email_address']); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="smallText"><?php echo TEXT_TO_NAME; ?></td>
            <td class="smallText"><?php echo tep_draw_input_field('to_name', $entry['sender']); ?></td>
          </tr>
          <tr>
            <td class="smallText"><?php echo TEXT_TO_EMAIL_ADDRESS; ?></td>
            <td class="smallText"><?php echo tep_draw_input_field('to_email_address', $entry['email_address']); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="smallText"><?php echo TEXT_SUBJECT; ?></td>
            <td class="smallText"><?php echo tep_draw_input_field('subject', $subject); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="smallText" valign="top"><?php echo TEXT_BODY; ?></td>
            <td class="smallText"><?php echo tep_draw_textarea_field('body', 'virtual', '120', '20', 'mceEditor', $template); ?></td>
          </tr>
        </table></td>
      </tr>          
      </form>
<?php
    } elseif ($_GET['subaction'] == 'edit') {
?>
      <?php echo tep_draw_form('edit', FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&id=' . $_GET['id'] . '&action=save_entry'); ?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" class="columnLeft">
          <tr>
            <td class="smallText"><?php echo TEXT_UPDATE_INTRO; ?></td>
            <td align="right"><?php echo tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="smallText"><?php echo TEXT_DEPARTMENT; ?></td>
            <td class="smallText"><?php echo tep_draw_pull_down_menu('department', $departments_array, $entry['department_id']); ?></td>
            <td class="smallText"><?php echo TEXT_STATUS; ?></td>
            <td class="smallText"><?php echo tep_draw_pull_down_menu('status', $statuses_array, $entry['status_id']); ?></td>
            <td class="smallText"><?php echo TEXT_PRIORITY; ?></td>
            <td class="smallText"><?php echo tep_draw_pull_down_menu('priority', $priorities_array, $entry['priority_id']); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" class="columnLeft">
          <tr>
            <td class="smallText"><?php echo TEXT_TICKET_NUMBER; ?></td>
            <td class="smallText"><?php echo tep_draw_input_field('ticket', $entry['ticket'], 'maxlength="7"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="smallText"><?php echo TEXT_FROM_NAME; ?></td>
            <td class="smallText"><?php echo tep_draw_input_field('from_name', $entry['sender']); ?></td>
          </tr>
          <tr>
            <td class="smallText"><?php echo TEXT_FROM_EMAIL_ADDRESS; ?></td>
            <td class="smallText"><?php echo tep_draw_input_field('from_email_address', $entry['email_address']); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="smallText"><?php echo TEXT_TO_NAME; ?></td>
            <td class="smallText"><?php echo tep_draw_input_field('to_name', $entry['receiver']); ?></td>
          </tr>
          <tr>
            <td class="smallText"><?php echo TEXT_TO_EMAIL_ADDRESS; ?></td>
            <td class="smallText"><?php echo tep_draw_input_field('to_email_address', $entry['receiver_email_address']); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="smallText"><?php echo TEXT_SUBJECT; ?></td>
            <td class="smallText"><?php echo tep_draw_input_field('subject', $entry['subject']); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="smallText" valign="top"><?php echo TEXT_BODY; ?></td>
            <td class="smallText"><?php echo tep_draw_textarea_field('body', 'virtual', '120', '20', 'mceEditor', $entry['body']); ?></td>
          </tr>
        </table></td>
      </tr>          
      </form>
<?php
    } elseif ($_GET['subaction'] == 'delete') {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" class="columnLeft">
          <tr><?php echo tep_draw_form('delete', FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&id=' . $_GET['id'] . '&action=delete_confirm'); ?>
            <td class="smallText"><?php echo TEXT_DELETE_INTRO; ?></td>
            <td class="smallText"><?php echo tep_draw_checkbox_field('whole', 'true') . '&nbsp;' . TEXT_DELETE_WHOLE_THREAD; ?></td>
            <td align="right"><?php echo tep_image_submit('button_confirm.gif', IMAGE_CONFIRM) . '&nbsp;<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </form></tr>
        </table></td>
      </tr>
<?php
    } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td><?php echo '<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id'] . '&subaction=reply') . '">' . tep_image_button('button_reply.gif', IMAGE_REPLY) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id'] . '&subaction=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id'] . '&subaction=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>'; ?></td>
            <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr><?php echo tep_draw_form('ticket', FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=updatestatus'); ?>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="smallText"><?php echo TEXT_DEPARTMENT; ?></td>
            <td class="smallText"><?php echo tep_draw_pull_down_menu('department', $departments_array, $entry['department_id']); ?></td>
            <td class="smallText"><?php echo TEXT_STATUS; ?></td>
            <td class="smallText"><?php echo tep_draw_pull_down_menu('status', $statuses_array, $entry['status_id']); ?></td>
            <td class="smallText"><?php echo TEXT_PRIORITY; ?></td>
            <td class="smallText"><?php echo tep_draw_pull_down_menu('priority', $priorities_array, $entry['priority_id']); ?></td>
            <td class="smallText"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php
    }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0" class="columnLeft">
          <tr>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText"><b><?php echo $entry['subject']; ?></b></td>
                <td class="smallText" align="right"><b><?php echo '[' . $entry['ticket'] . ']'; ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText"><?php echo TEXT_TO; ?></td>
                <td class="smallText"><?php echo $entry['receiver'] . ' (' . $entry['receiver_email_address'] . ')'; ?></td>
              </tr>
              <tr>
                <td class="smallText"><?php echo TEXT_FROM; ?></td>
                <td class="smallText"><?php echo $entry['sender'] . ' (' . $entry['email_address'] . ') (' . $entry['host'] . ')'; ?></td>
              </tr>
              <tr>
                <td class="smallText"><?php echo TEXT_DATE; ?></td>
                <td class="smallText"><?php echo $entry['datestamp_local'] . ' (' . TEXT_REMOTE . ' ' . $entry['datestamp'] . ')'; ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php 
/*
          <tr>
            <td class="smallText"><?php echo nl2br($entry['body']); ?></td>
          </tr>
*/
?>
<tr>
  <td class="smallText"><?php echo (stristr($entry['body'],'<br>') || stristr($entry['body'],'<p>') ? $entry['body']: nl2br($entry['body'])); ?></td>
</tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td><?php echo '<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id'] . '&subaction=reply') . '">' . tep_image_button('button_reply.gif', IMAGE_REPLY) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id'] . '&subaction=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=view&id=' . $entry['helpdesk_entries_id'] . '&subaction=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>'; ?></td>
            <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
    $threads_query = tep_db_query("select helpdesk_entries_id, ticket, subject, sender, datestamp_local, datestamp, entry_read from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . tep_db_input($ticket) . "' order by datestamp_local");
    if (tep_db_num_rows($threads_query) > 1) {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SUBJECT; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SENDER; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_DATE; ?></td>
          </tr>
<?php
      while ($threads = tep_db_fetch_array($threads_query)) {
        if ($entry['helpdesk_entries_id'] == $threads['helpdesk_entries_id']) {
          echo '                  <tr class="dataTableRowSelected">' . "\n";
        } else {
          echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $threads['ticket'] . '&id=' . $threads['helpdesk_entries_id'] . '&action=view') . '\'">' . "\n";
        }

        $entry_icon = (($threads['entry_read'] != '1') ? tep_image(DIR_WS_ICONS . 'unread.gif', ICON_UNREAD) : tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW));
?>
            <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $threads['ticket'] . '&id=' . $threads['helpdesk_entries_id'] . '&action=view') . '">' . $entry_icon . '</a>&nbsp;' . $threads['subject']; ?></td>
            <td class="dataTableContent"><?php echo $threads['sender']; ?></td>
            <td class="dataTableContent" align="right"><?php echo $threads['datestamp_local']; ?></td>
          </tr>
<?php
      }
?>
        </table></td>
      </tr>
<?php
    }

    if (!isset($_GET['subaction'])) {
      $internal_query = tep_db_query("select comment, datestamp_comment from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . tep_db_input($ticket) . "'");
      $internal = tep_db_fetch_array($internal_query);
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <?php echo tep_draw_form('internal', FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $_GET['ticket'] . '&action=updatecomment'); ?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" class="columnLeft">
          <tr>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText"><b><?php echo TEXT_INTERNAL_COMMENTS; ?></b></td>
<?php
      if (tep_not_null($internal['datestamp_comment'])) {
        echo '              <td class="smallText" align="right"><b>' . TEXT_LAST_UPDATE . '</b> ' . $internal['datestamp_comment'] . '</td>' . "\n";
      }
?>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td class="smallText"><?php echo tep_draw_textarea_field('comment', 'virtual', '120', '10', 'mceEditor', $internal['comment']); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td align="right"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>
      </tr>
      </form>
<?php
    }
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TICKET; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SUBJECT; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SENDER; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAST_POST; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRIORITY; ?>&nbsp;</td>
              </tr>
<?php
    if ( (tep_session_is_registered('_entryfilter')) && ($_entryfilter != '0') ) {
      $unread_array = array();
      $unread_entries_query = tep_db_query("select distinct he.ticket from " . TABLE_HELPDESK_ENTRIES . " he, " . TABLE_HELPDESK_TICKETS . " ht where he.entry_read = '0' and he.ticket = ht.ticket order by ht.datestamp_last_entry desc limit " . MAX_DISPLAY_SEARCH_RESULTS);
      while ($unread_entries = tep_db_fetch_array($unread_entries_query)) {
        $unread_array[] = $unread_entries['ticket'];
      }
    }

    $entries_query_raw = "select ht.ticket, hd.email_address, ht.datestamp_last_entry, hs.title as status, hp.title as priority from " . TABLE_HELPDESK_TICKETS . " ht, " . TABLE_HELPDESK_STATUSES . " hs, " . TABLE_HELPDESK_PRIORITIES . " hp, " . TABLE_HELPDESK_DEPARTMENTS . " hd where ht.status_id = hs.status_id and hs.languages_id = '" . $languages_id . "' and ht.priority_id = hp.priority_id and hp.languages_id = '" . $languages_id . "' and ht.department_id = hd.department_id";
    if ( (tep_session_is_registered('_departmentfilter')) && ($_departmentfilter != '0') ) {
      $departmentfilter = tep_db_prepare_input($_departmentfilter);
      $entries_query_raw .= " and ht.department_id = '" . tep_db_input($departmentfilter) . "'";
    }
    if ( (tep_session_is_registered('_statusfilter')) && ($_statusfilter != '0') ) {
      $statusfilter = tep_db_prepare_input($_statusfilter);
      $entries_query_raw .= " and ht.status_id = '" . tep_db_input($statusfilter) . "'";
    }
    if ( (tep_session_is_registered('_priorityfilter')) && ($_priorityfilter != '0') ) {
      $priorityfilter = tep_db_prepare_input($_priorityfilter);
      $entries_query_raw .= " and ht.priority_id = '" . tep_db_input($priorityfilter) . "'";
    }
    if ( (tep_session_is_registered('_entryfilter')) && ($_entryfilter != '0') ) {
      $entries_query_raw .= " and ht.ticket in ('" . implode("', '", $unread_array) . "')";
    }
    $entries_query_raw .= " order by ht.datestamp_last_entry desc";
    $entries_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $entries_query_raw, $entries_query_numrows);
    $entries_query = tep_db_query($entries_query_raw);
    while ($entries = tep_db_fetch_array($entries_query)) {
      $ticket_query = tep_db_query("select helpdesk_entries_id, sender, subject from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $entries['ticket'] . "' and parent_id = '0'");
      $ticket = tep_db_fetch_array($ticket_query);

      $postings_query = tep_db_query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $entries['ticket'] . "' and helpdesk_entries_id != '" . $ticket['helpdesk_entries_id'] . "'");
      $postings = tep_db_fetch_array($postings_query);

      $unread_query = tep_db_query("select count(*) as count from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $entries['ticket'] . "' and entry_read = '0'");
      $unread = tep_db_fetch_array($unread_query);

      $last_post_query = tep_db_query("select email_address from " . TABLE_HELPDESK_ENTRIES . " where ticket = '" . $entries['ticket'] . "' order by datestamp_local desc limit 1");
      $last_post = tep_db_fetch_array($last_post_query);

      if (((!$_GET['ticket']) || (@$_GET['ticket'] == $entries['ticket'])) && (!$tInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
        $tInfo = new objectInfo(array_merge($entries, $ticket));
      }

      if ( (is_object($tInfo)) && ($entries['ticket'] == $tInfo->ticket) ) {
        echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $tInfo->ticket . '&action=view') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $entries['ticket']) . '\'">' . "\n";
      }

      $entry_icon = (($unread['count'] > 0) ? tep_image(DIR_WS_ICONS . 'unread.gif', ICON_UNREAD) : tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW));

      if ($entries['email_address'] == $last_post['email_address']) {
        $entry_icon .= tep_image(DIR_WS_ICONS . 'outgoing.gif', ICON_OUTGOING);
      } else {
        $entry_icon .= tep_image(DIR_WS_ICONS . 'incoming.gif', ICON_INCOMING);
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_HELPDESK, 'page=' . $_GET['page'] . '&ticket=' . $entries['ticket'] . '&action=view') . '">' . $entry_icon . '</a>&nbsp;' . $entries['ticket']; ?></td>
                <td class="dataTableContent"><?php echo $ticket['subject'] . ' (' . $postings['count'] . ')'; ?></td>
                <td class="dataTableContent"><?php echo $ticket['sender']; ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_date_short($entries['datestamp_last_entry']); ?></td>
                <td class="dataTableContent" align="right"><?php echo $entries['status']; ?></td>
                <td class="dataTableContent" align="right"><?php echo $entries['priority']; ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $entries_split->display_count($entries_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></td>
                    <td class="smallText" align="right"><?php echo $entries_split->display_links($entries_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
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