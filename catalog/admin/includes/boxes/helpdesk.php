<?php
/*
  $Id: ,v 3.00 2015/06/04

  oscHelpdesk
  http://www.snowtech.com.au

  Copyright (c) 2003 -2015 Snowtech Services

*/

  $contents = (			   		   tep_admin_jqmenu(FILENAME_HELPDESK,BOX_HELPDESK_ENTRIES, 'TOP') .
                                   tep_admin_jqmenu(FILENAME_HELPDESK_DEPARTMENTS,BOX_HELPDESK_DEPARTMENTS, 'TOP') .
                                   tep_admin_jqmenu(FILENAME_HELPDESK_TEMPLATES,BOX_HELPDESK_TEMPLATES, 'TOP') .
                                   tep_admin_jqmenu(FILENAME_HELPDESK_STATUS,BOX_HELPDESK_STATUSES, 'TOP') .
                                   tep_admin_jqmenu(FILENAME_HELPDESK_PRIORITIES,BOX_HELPDESK_PRIORITIES, 'TOP') .
                                   tep_admin_jqmenu(FILENAME_HELPDESK_POP3,BOX_HELPDESK_POP3, 'TOP'));
  print_r($contents);
?>