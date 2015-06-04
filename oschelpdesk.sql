
--
-- Database: `oscHelpdesk`
--

-- --------------------------------------------------------

--
-- Dumping data for table `configuration`
--

INSERT INTO `configuration` (`configuration_id`, `configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES
('', 'Ticket prefix', 'DEFAULT_HELPDESK_TICKET_PREFIX', 'osC:', 'Prefix sent when sending a ticket (changing it AFTER sending emails may cause emails not to be associated to their existing ticket', 999, 0, NULL, '', NULL, NULL),
('', 'Display Header in sync email output (useful for debugging)', 'DEFAULT_HELPDESK_DISPLAY_HEADER', 'true', 'If <b>true</b> a header is displayed in the output so you know a) the sync command ran and b) if emails are flagged to be deleted. Only useful for debugging, for production set to <b>false</b> for use with logging.', 999, 0, NULL, '', NULL, 'tep_cfg_select_option(array(''true'', ''false''),'),
('', 'Delete emails from server', 'DEFAULT_HELPDESK_DELETE_EMAILS', 'false', 'Delete emails from server upon download', 999, 0, '', '', NULL, 'tep_cfg_select_option(array(''true'', ''false''),'),
('', 'Open POP3 connection Read-Only', 'DEFAULT_HELPDESK_READ_ONLY', 'false', 'If true a read-only connection is made to the pop3 server. However some servers do not allow read-only connections in which case this must be true.<p>In either case, if the Delete Emails option is true a read/write connection is made.', 999, 0, NULL, '', NULL, 'tep_cfg_select_option(array(''true'', ''false''),'),
('', 'Message body size limit', 'DEFAULT_HELPDESK_BODY_SIZE_LIMIT', '0', 'If the message body is longer than this number of bytes, it will be trimmed. Set to 0 for no limit.', 999, 0, NULL, '', NULL, NULL),
('', 'Mailbox', 'DEFAULT_HELPDESK_MAILBOX', 'INBOX', 'Name of the mailbox to open', 999, 0, NULL, '', NULL, NULL),
('', 'Mark emails as seen', 'DEFAULT_HELPDESK_MARKSEEN', 'true', 'Whether or not to mark retrieved messages as seen', 999, 0, NULL, '', NULL, 'tep_cfg_select_option(array(''true'', ''false''),'),
('', 'Protocol specification', 'DEFAULT_HELPDESK_PROTOCOL_SPECIFICATION', '/POP3:110/notls', 'Mail Server Protocol specification (optional)<p>One of: /POP3 /IMAP /NNTP<p>Examples<p>/IMAP<br>/POP3:110', 999, 0, '', '', NULL, NULL),
('', 'POP3/IMAP/NNTP server', 'DEFAULT_HELPDESK_MAILSERVER', 'mail.yourdomain.com.au', 'POP3/IMAP/NNTP server to connect to, with optional port.<p>Example: mail.server.com:110<p><b>Note:</b>Some servers must have the port after the protocol!', 999, 0, '', '', NULL, NULL),
('', 'Default Helpdesk Priority For New Entries', 'DEFAULT_HELPDESK_PRIORITY_ID', '3', 'When a new entry is received, this priority will be assigned to it.', 999, 0, '', '', NULL, NULL),
('', 'Default Helpdesk Department For New Entries', 'DEFAULT_HELPDESK_DEPARTMENT_ID', '4', 'When a new entry is received, this department will be assigned to it.', 999, 0, NULL, '', NULL, NULL),
('', 'Default Helpdesk Status For New Entries', 'DEFAULT_HELPDESK_STATUS_ID', '2', 'When a new entry is received, this status will be assigned to it.', 999, 0, NULL, '', NULL, NULL);


-- --------------------------------------------------------

--
-- Table structure for table `configuration_group`
--

CREATE TABLE IF NOT EXISTS `configuration_group` (
  `configuration_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `configuration_group_title` varchar(64) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `configuration_group_description` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `sort_order` int(5) DEFAULT NULL,
  `visible` int(1) DEFAULT '1',
  PRIMARY KEY (`configuration_group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=26236 ;

--
-- Dumping data for table `configuration_group`
--

INSERT INTO `configuration_group` (`configuration_group_id`, `configuration_group_title`, `configuration_group_description`, `sort_order`, `visible`) VALUES
(999, 'Helpdesk', 'Helpdesk Configuration Group', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `helpdesk_departments`
--

CREATE TABLE IF NOT EXISTS `helpdesk_departments` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE latin1_general_ci NOT NULL,
  `email_address` varchar(96) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `name` varchar(64) COLLATE latin1_general_ci NOT NULL,
  `password` varchar(32) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`department_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `helpdesk_entries`
--

CREATE TABLE IF NOT EXISTS `helpdesk_entries` (
  `helpdesk_entries_id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket` varchar(7) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `message_id` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `ip_address` varchar(15) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `host` varchar(50) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `datestamp_local` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `receiver` varchar(50) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `receiver_email_address` varchar(96) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `sender` varchar(50) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `email_address` varchar(96) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `subject` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `body` text COLLATE latin1_general_ci NOT NULL,
  `entry_read` char(1) COLLATE latin1_general_ci DEFAULT '0',
  PRIMARY KEY (`helpdesk_entries_id`),
  KEY `idx_helpdesk_entries_ticket` (`ticket`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=2009 ;

-- --------------------------------------------------------

--
-- Table structure for table `helpdesk_priorities`
--

CREATE TABLE IF NOT EXISTS `helpdesk_priorities` (
  `priority_id` int(11) NOT NULL AUTO_INCREMENT,
  `languages_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(32) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`priority_id`,`languages_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `helpdesk_priorities`
--

INSERT INTO `helpdesk_priorities` (`priority_id`, `languages_id`, `title`) VALUES
(1, 1, 'Medium'),
(2, 1, 'High'),
(3, 1, 'Low');

-- --------------------------------------------------------

--
-- Table structure for table `helpdesk_statuses`
--

CREATE TABLE IF NOT EXISTS `helpdesk_statuses` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `languages_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(32) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`status_id`,`languages_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `helpdesk_statuses`
--

INSERT INTO `helpdesk_statuses` (`status_id`, `languages_id`, `title`) VALUES
(1, 1, 'Pending'),
(2, 1, 'Open'),
(3, 1, 'Closed'),
(4, 1, 'In Progress');

-- --------------------------------------------------------

--
-- Table structure for table `helpdesk_templates`
--

CREATE TABLE IF NOT EXISTS `helpdesk_templates` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `template` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `helpdesk_tickets`
--

CREATE TABLE IF NOT EXISTS `helpdesk_tickets` (
  `ticket` varchar(7) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `CustomerID` varchar(8) COLLATE latin1_general_ci NOT NULL,
  `subject` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `department_id` int(11) NOT NULL DEFAULT '0',
  `priority_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `datestamp_last_entry` datetime DEFAULT NULL,
  `comment` text COLLATE latin1_general_ci,
  `datestamp_comment` datetime DEFAULT NULL,
  KEY `idx_helpdesk_tickets_ticket` (`ticket`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
