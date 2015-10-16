<?php
defined('_JEXEC') or die;

// Defines
define('LINKR_VERSION', 237);
define('LINKR_VERSION_READ', '2.3.7');
define('LINKR_VERSION_INC', ((@$_SERVER['HTTP_HOST'] == 'localhost' || @$_SERVER['SERVER_NAME'] == 'localhost') ? time() : LINKR_VERSION));
define('LINKR_ASSETS', JURI::root() .'components/com_linkr/assets/');

define('LINKR_MAIL', 'dev@l33p.com');

define('LINKR_URL', 'http://j.l33p.com/linkr/about');
define('LINKR_URL_HOME', 'http://j.l33p.com/linkr');
define('LINKR_URL_SUPPORT', 'http://j.l33p.com/linkr/support');
define('LINKR_URL_FAQS', 'http://j.l33p.com/linkr/faqs');
define('LINKR_URL_DOCUMENTATION', 'http://j.l33p.com/linkr/docs');
define('LINKR_URL_TRANSLATION', 'http://j.l33p.com/linkr/translations');
define('LINKR_URL_DOWNLOAD', 'http://joomlacode.org/gf/project/linkr2/frs/');
define('LINKR_URL_API', 'http://j.l33p.com/api/linkr/intro');
define('LINKR_URL_API_EG', 'http://j.l33p.com/api/linkr/tutorials');
