<?php
/*
  $Id: ,v 3.00 2015/06/04

  oscHelpdesk
  http://www.snowtech.com.au

  Copyright (c) 2003 -2015 Snowtech Services

*/

  include('includes/configure.php');
 // require('includes/application_top.php');

// some code to solve compatibility issues
  require(DIR_WS_FUNCTIONS . 'compatibility.php');

// include the list of project filenames
  require(DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
  require(DIR_WS_INCLUDES . 'database_tables.php');

// include the database functions
  require(DIR_WS_FUNCTIONS . 'database.php');

// make a connection to the database... now
  tep_db_connect() or die('Unable to connect to database server!');


// set application wide parameters
  $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = tep_db_fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
  }

// define our general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');

    include(DIR_WS_CLASSES . 'language.php');
    $lng = new language();

// define our localization functions
  require(DIR_WS_FUNCTIONS . 'localization.php');


;
  include(DIR_WS_INCLUDES . 'classes/mime_decode.php');

  include_once( DIR_WS_FUNCTIONS . 'helpdesk.php' );

  echo "Helpdesk - Mail retrieval system version 0.1\n\n";

  if(DEFAULT_HELPDESK_DELETE_EMAILS=='true') echo "Emails will be deleted from the server\n\n";
      
  $account_query = tep_db_query("select email_address, password from " . TABLE_HELPDESK_DEPARTMENTS);
  while( $account = tep_db_fetch_array($account_query) ) {

    $username = $account['email_address'];
    $password = $account['password'];
    
    echo "\nProcessing mails for account " .$username . "\n";    

    if(DEFAULT_HELPDESK_DELETE_EMAILS=='true') {
      $mode = CL_EXPUNGE;
    } else {
      $mode = OP_READONLY;
    }

    if ($conn = imap_open('{'.DEFAULT_HELPDESK_MAILSERVER.DEFAULT_HELPDESK_PROTOCOL_SPECIFICATION.'}'.$mailbox, $username, $password, CL_EXPUNGE)) {
      $params = array();
      $params['decode_headers'] = true;
      $params['crlf']           = "\r\n";
      $params['include_bodies'] = true;
      $params['decode_bodies']  = true;
      if ($msgCount = imap_num_msg($conn)) {
        echo 'Found ' . $msgCount . ' new messages';  
        for($i = 1; $i <= $msgCount; $i++) {
                 imap_delete($conn, $i);  
        
          $header = imap_fetchheader($conn, $i, FT_PREFETCHTEXT);
          if (DEFAULT_HELPDESK_MARKSEEN=='true') {
            $body = imap_body($conn, $i);
          } else {
            $body = imap_body($conn, $i, FT_PEEK);
          }
          
          $params['input'] = $header.$body;
          $output = Mail_mimeDecode::decode($params);                  
          
          // Some mail servers and clients use special messages for holding mailbox data; ignore that message if it exists.
          if ($message->headers['subject'] != "DON'T DELETE THIS MESSAGE -- FOLDER INTERNAL DATA") {
    
            // Does the message have an attachment?
            if (strtolower($message->ctype_primary) == "multipart") {
              $body = trim($message->parts[0]->body);
              $attachCount = count($message->parts) - 1;
              $attachSize  = 0;
              
              for($p = 1; $p < count($message->parts); $p++)
                $attachSize += strlen($message->parts[$p]->body);
              
              $body .= "    [Message contains 1 attachment. (".translateSize($attachSize).")]";
            } else {
              $body = trim($message->body);
            }
            
            if (DEFAULT_HELPDESK_BODY_SIZE_LIMIT && strlen($body) > DEFAULT_HELPDESK_BODY_SIZE_LIMIT)
              $body = substr($body, 0, DEFAULT_HELPDESK_BODY_SIZE_LIMIT).'...';
            
            $body = nl2br($body);                    
          }
          
          $parts = array();
          osc_parse_mime_decode_output($output, $parts);
          
          if(strlen($parts['html'][0])>0)
{
  $field_body = trim($parts['html'][0]);
}
else
{
        // get rid of things that will confuse php
        $field_body = trim($parts['text'][0]);
}
          
          if (empty($output->headers['date'])) {
            $field_date = date("Y-m-d H:i:s");
          } else {
            $field_date = date("Y-m-d H:i:s", strtotime($output->headers['date'], time()));
          }
        
          if (preg_match('/([0-9]+/\.[0-9]+/\.[0-9]+/\.[0-9]+)/', $output->headers['received'], $regs)) {
            $field_ip = $regs[1];
          } else {
            $field_ip = '';
          }
          $field_host = @gethostbyaddr($field_ip);
        
          if (empty($output->headers['from'])) {
            $field_from = '';
            $field_from_email_address = '';
          } else {
            if (preg_match('"/([^"]+)" <([^>]+)>/', $output->headers['from'], $regs)) {
              $field_from = trim($regs[1]);
              $field_from_email_address = trim($regs[2]);
            } elseif (preg_match('/([^<]+)<([^>]+)>/', $output->headers['from'], $regs)) {
              $field_from = trim($regs[1]);
              $field_from_email_address = trim($regs[2]);
            } elseif (substr($output->headers['from'], 0, 1) == '<') {
              $field_from = substr($output->headers['from'], 1, -1);
              $field_from_email_address = $field_from;
            } else {
              $field_from = $output->headers['from'];
              $field_from_email_address = $field_from;
            }
          }
          
          if (empty($output->headers['to'])) {
            $field_to = '';
            $field_to_email_address = '';
          } else {
            if (preg_match('"/([^"]+)/" <([^>]+)>', $output->headers['to'], $regs)) {
              $field_to = trim($regs[1]);
              $field_to_email_address = trim($regs[2]);
            } elseif (preg_match('/([^<]+)<([^>]+)>/', $output->headers['to'], $regs)) {
              $field_to = trim($regs[1]);
              $field_to_email_address = trim($regs[2]);
            } elseif (substr($output->headers['to'], 0, 1) == '<') {
              $field_to = substr($output->headers['to'], 1, -1);
              $field_to_email_address = $field_to;
            } else {
              $field_to = $output->headers['to'];
              $field_to_email_address = $field_to;
            }
          }
        
          $field_message_id = trim($output->headers['message-id']);
        
          $ticket = false;
          $parent_id = '0';
          $status_id = DEFAULT_HELPDESK_STATUS_ID;
          $priority_id = DEFAULT_HELPDESK_PRIORITY_ID;
          $department_id = DEFAULT_HELPDESK_DEPARTMENT_ID;
          $departments_query = tep_db_query("select department_id from " . TABLE_HELPDESK_DEPARTMENTS . " where email_address = '" . $username . "'");
          $departments = tep_db_fetch_array($departments_query);
          
          if (tep_db_num_rows($departments_query)) {
            $department_id = $departments['department_id'];
          }
          else
          {
            echo '    Warning - department ' . $username . ' could not be found, using default value';
          }
        
          $field_subject = trim($output->headers['subject']);
          
          // check if the email already in the database
          
          $existing_query = tep_db_query("select message_id from " . TABLE_HELPDESK_ENTRIES . " where message_id='" . addslashes($field_message_id) . "'");
          if(!tep_db_num_rows($existing_query)) {
          
            // check for existing ticket number
            if (preg_match('^.*/\['.DEFAULT_HELPDESK_TICKET_PREFIX.'([A-Z0-9]{7})/\].*$/', $field_subject, $regs)) {
              $ticket = $regs[1];
              $ticket_info_query = tep_db_query("select he.parent_id, ht.department_id, ht.status_id, ht.priority_id from " . TABLE_HELPDESK_ENTRIES . " he left join " . TABLE_HELPDESK_TICKETS . " ht on (he.ticket = ht.ticket) where he.ticket = '" . $ticket . "' order by he.parent_id desc limit 1");
                  
              if (tep_db_num_rows($ticket_info_query)) {
                $ticket_info = tep_db_fetch_array($ticket_info_query);
                $parent_id = $ticket_info['parent_id'];
                $status_id = $ticket_info['status_id'];
                $priority_id = $ticket_info['priority_id'];
                $department_id = $ticket_info['department_id'];
              }
            } else {
              while (true) {
                $ticket = osc_create_random_string();
          
                $check_query = tep_db_query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $ticket . "'");
                $check = tep_db_fetch_array($check_query);
          
                if ($check['count'] < 1) break;
              }
            }
          
            $check_query = tep_db_query("select count(*) as count from " . TABLE_HELPDESK_TICKETS . " where ticket = '" . $ticket . "'");
            $check = tep_db_fetch_array($check_query);
          
            if ($check['count'] < 1) {
              tep_db_query("insert into " . TABLE_HELPDESK_TICKETS . " (ticket, department_id, priority_id, status_id, datestamp_last_entry) values ('" . $ticket . "', '" . $department_id . "', '" . DEFAULT_HELPDESK_PRIORITY_ID . "', '" . DEFAULT_HELPDESK_STATUS_ID . "', now())");
            }
          
            tep_db_query("insert into " . TABLE_HELPDESK_ENTRIES . " (helpdesk_entries_id, ticket, parent_id, message_id, ip_address, host, datestamp_local, datestamp, receiver, receiver_email_address, sender, email_address, subject, body, entry_read) values ('', '" . $ticket . "', '" . $parent_id . "', '" . addslashes($field_message_id) . "', '" . addslashes($field_ip) . "', '" . addslashes($field_host) . "', now(), '" . addslashes($field_date) . "', '" . addslashes($field_to) . "', '" . addslashes($field_to_email_address) . "', '" . addslashes($field_from) . "', '" . addslashes($field_from_email_address) . "', '" . addslashes($field_subject) . "', '" . addslashes($field_body) . "', '0')");
      
          }
                  echo "Messages before delete: " . $conn->Nmsgs . "\n\n";
        }

      }     
      else
      {
        echo "No mail found on the server\n";        
      }
    }
//echo "[". DATETIME ."] [ERROR] Unable to connect: ". implode(' ### ', imap_errors()) ."".CRLF;
    imap_close($conn);
  } 
?>