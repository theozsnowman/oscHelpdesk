<?php
/*
  $Id: ,v 3.00 2015/06/04

  oscHelpdesk
  http://www.snowtech.com.au

  Copyright (c) 2003 -2015 Snowtech Services

*/

  function osc_create_random_string() {
    $ascii_from = 50; // 2
    $ascii_to = 90; // Z
    $exclude = array(58, 59, 60, 61, 62, 63, 64, 73, 79);
    mt_srand((double)microtime() * 1000000);
    $string = '';
    $i = 0;
    while ($i < 7) {
      $randnum = mt_rand($ascii_from, $ascii_to);
      if (!in_array($randnum, $exclude)) {
        $string .= chr($randnum);
        $i++;
      }
    }
    return $string;
  }
  
  function parse_output(&$obj, &$parts) {
      $ctype = $obj->ctype_primary . '/' . $obj->ctype_secondary;
  
      switch ($ctype) {
      case 'text/plain':
        if (!empty($obj->disposition)AND $obj->disposition == 'attachment') {
          $names = split(';', $obj->headers["content-disposition"]);
  
          $names = split('=', $names[1]);
          $aux['name'] = $names[1];
          $aux['content-type'] = $obj->headers["content-type"];
          $aux['part'] = $i;
          $parts['attachments'][] = $aux;
        } else {
          $parts['text'][] = $obj->body;
        }
  
        break;
  
      case 'text/html':
        if (!empty($obj->disposition)AND $obj->disposition == 'attachment') {
          $names = split(';', $obj->headers["content-disposition"]);
  
          $names = split('=', $names[1]);
          $aux['name'] = $names[1];
          $aux['content-type'] = $obj->headers["content-type"];
          $aux['part'] = $i;
          $parts['attachments'][] = $aux;
        } else {
          $parts['html'][] = $obj->body;
        }
  
        break;
  
      default:
        $names = split(';', $obj->headers["content-disposition"]);
  
        $names = split('=', $names[1]);
        $aux['name'] = $names[1];
        $aux['content-type'] = $obj->headers["content-type"];
        $aux['part'] = $i;
        $parts['attachments'][] = $aux;
      }
  }  
  
  function osc_parse_mime_decode_output(&$obj, &$parts){
    if (!empty($obj->parts)) {
      for ($i=0; $i<count($obj->parts); $i++) {
        parse_output($obj->parts[$i], $parts);
      }
    } else {
      $ctype = $obj->ctype_primary.'/'.$obj->ctype_secondary;
      switch ($ctype) {
        case 'text/plain':
          if (!empty($obj->disposition) AND $obj->disposition == 'attachment') {
            $parts['attachments'][] = $obj->body;
          } else {
            $parts['text'][] = $obj->body;
          }
          break;
        case 'text/html':
          if (!empty($obj->disposition) AND $obj->disposition == 'attachment') {
            $parts['attachments'][] = $obj->body;
          } else {
            $parts['html'][] = $obj->body;
          }
          break;
        default:
          $parts['attachments'][] = $obj->body;
      }
    }
  }
    
  $store_query = tep_db_query("select configuration_key, configuration_value from configuration where configuration_key in ('STORE_OWNER', 'STORE_OWNER_EMAIL_ADDRESS', 'DEFAULT_HELPDESK_STATUS_ID', 'DEFAULT_HELPDESK_PRIORITY_ID', 'DEFAULT_HELPDESK_DEPARTMENT_ID')");
  while ($store = tep_db_fetch_array($store_query)) {
    define($store['configuration_key'], $store['configuration_value']);
  }
  
?>