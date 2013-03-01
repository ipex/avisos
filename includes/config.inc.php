<?php
/**copyright**/

/* define system variables */

define( 'RL_DS', DIRECTORY_SEPARATOR );

//debug manager, set true for enable or false for desable
define( 'RL_DEBUG', false );
define( 'RL_DB_DEBUG', false );
define( 'RL_MEMORY_DEBUG', false );
define( 'RL_AJAX_DEBUG', false );

// mysql credentials
define( 'RL_DBPORT', '3306' );
define( 'RL_DBHOST', 'localhost' );
define( 'RL_DBUSER', 'xavisos_kolla' );
define( 'RL_DBPASS', 'TkwJAzZgRX)+' );
define( 'RL_DBNAME', 'xavisos_bolivia' );
define( 'RL_DBPREFIX', 'bo_' );

// system paths
define( 'RL_DIR', '' );
define( 'RL_ROOT', '/home/xavisos/public_html' . RL_DS . RL_DIR );
define( 'RL_INC', RL_ROOT . 'includes' . RL_DS );
define( 'RL_CLASSES', RL_INC . 'classes' . RL_DS );
define( 'RL_CONTROL', RL_INC . 'controllers' . RL_DS );
define( 'RL_LIBS', RL_ROOT . 'libs' . RL_DS );
define( 'RL_TMP', RL_ROOT . 'tmp' . RL_DS );
define( 'RL_UPLOAD', RL_TMP . 'upload' . RL_DS );
define( 'RL_FILES', RL_ROOT . 'files' . RL_DS );
define( 'RL_PLUGINS', RL_ROOT . 'plugins' . RL_DS );
define( 'RL_CACHE', RL_TMP . 'cache_632153163' . RL_DS );

// system URLs
define( 'RL_URL_HOME', 'http://www.avisos.com.bo/' );
define( 'RL_FILES_URL', RL_URL_HOME . 'files/' );
define( 'RL_LIBS_URL', RL_URL_HOME . 'libs/' );
define( 'RL_PLUGINS_URL', RL_URL_HOME . 'plugins/' );

// libraries variables
define( 'RL_AJAX', RL_LIBS . 'ajax' . RL_DS );
define( 'RL_SMARTY', RL_LIBS . 'smarty' . RL_DS );

//system admin paths
define( 'ADMIN', 'xadmin' );
define( 'ADMIN_DIR', ADMIN . RL_DS );
define( 'RL_ADMIN', RL_ROOT . ADMIN . RL_DS );
define( 'RL_ADMIN_CONTROL', RL_ADMIN . 'controllers' . RL_DS );

/* define system variables end */
