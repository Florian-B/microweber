<?php
include_once (MW_APP_PATH . 'functions' . DIRECTORY_SEPARATOR . 'api.php');
include_once (MW_APP_PATH . 'functions' . DIRECTORY_SEPARATOR . 'common.php');


if (MW_IS_INSTALLED == true) {
    if (!defined('MW_TABLE_PREFIX')) {
        $pre = c('table_prefix');

        define('MW_TABLE_PREFIX', $pre);

    }

} elseif (isset($_POST['table_prefix'])) {

    if (!defined('MW_TABLE_PREFIX')) {

        define('MW_TABLE_PREFIX', strip_tags($_POST['table_prefix']));

    }

} else {

    /*
        if (!defined('MW_TABLE_PREFIX') and !isset($_REQUEST['autoinstall'])) {

            define('MW_TABLE_PREFIX', null);

        } else if (!defined('MW_TABLE_PREFIX')) {
            $pre = c('table_prefix');

            define('MW_TABLE_PREFIX', $pre);

        }*/


    $autoinstall_cli = getopt("i:");

    /*
    You can install MW from the command line if you have predefined your db access in the config
    just run php -e full_path_to/index.php -i=yes
    */
    if (isset($autoinstall_cli) and $autoinstall_cli != false and !empty($autoinstall_cli)) {

        $autoinstall_cli_val = array_pop($autoinstall_cli);
        if (trim(strtolower($autoinstall_cli_val)) == 'yes') {
            $cli_autoinstall = true;
        }
    }

    if (isset($cli_autoinstall) and $cli_autoinstall != false) {
        $cfg = MW_CONFIG_FILE;

        if (is_file($cfg) and is_readable($cfg)) {
            require ($cfg);
            if (is_array($config) and isset($config['db']) and is_array($config['db'])) {
                // if(!isset($config['installed']) or $config['installed'] != 'yes')){
                $autoinstall = $config;
                if (!isset($config['is_installed'])) {
                    $autoinstall['is_installed'] = 'no';
                }
                if (isset($config['table_prefix'])) {
                    $autoinstall['table_prefix'] = $config['table_prefix'];
                    if (!defined('MW_TABLE_PREFIX') and isset($autoinstall['table_prefix']) and !isset($_REQUEST['table_prefix'])) {

                        define('MW_TABLE_PREFIX', (trim($autoinstall['table_prefix'])));

                    }
                    if (!defined('MW_TABLE_PREFIX') and isset($_REQUEST['table_prefix'])) {

                        define('MW_TABLE_PREFIX', (trim($_REQUEST['table_prefix'])));

                    }
                } else {
                    $autoinstall['table_prefix'] = '';
                }
                if (isset($autoinstall['db']['dbname'])) {
                    $autoinstall['dbname'] = $autoinstall['db']['dbname'];
                }


                if (isset($autoinstall['db']['host'])) {
                    $autoinstall['db_host'] = $autoinstall['db']['host'];
                }
                if (isset($autoinstall['db']['user'])) {
                    $autoinstall['db_user'] = $autoinstall['db']['user'];
                }
                if (isset($autoinstall['db']['pass'])) {
                    $autoinstall['db_pass'] = $autoinstall['db']['pass'];
                }

                if (!defined('MW_INSTALL_FROM_CONFIG')) {

                    define('MW_INSTALL_FROM_CONFIG', true);
                    mw_var('mw_autoinstall', $autoinstall);

                }

                // }
            }


        }

    }


    if (!defined('MW_TABLE_PREFIX') and !isset($_REQUEST['autoinstall'])) {

        define('MW_TABLE_PREFIX', null);

    } else if (!defined('MW_TABLE_PREFIX') and isset($_REQUEST['table_prefix'])) {

        define('MW_TABLE_PREFIX', trim($_REQUEST['table_prefix']));

    } else if (!defined('MW_TABLE_PREFIX')) {
        $pre = c('table_prefix');

        define('MW_TABLE_PREFIX', $pre);

    }

}


$c_id = 'mw_init_all';
$cache_content_init = mw('option')->get_static('is_installed', 'mw_system');

if (MW_IS_INSTALLED == true) {
    if ($cache_content_init != 'yes') {
        event_trigger('mw_db_init_default');
        event_trigger('mw_db_init_options');
        event_trigger('mw_db_init_users');
    }
    $curent_time_zone = mw('option')->get('time_zone', 'website');
    if ($curent_time_zone != false and $curent_time_zone != '') {
        $default_time_zone = date_default_timezone_get();

        if ($default_time_zone != $curent_time_zone) {

            date_default_timezone_set($curent_time_zone);
        }

    }
}


if (MW_IS_INSTALLED == true) {

    if ($cache_content_init != 'yes') {
        event_trigger('mw_db_init_modules');
    }

}
include_once (MW_APP_PATH . 'functions' . DIRECTORY_SEPARATOR . 'media.php');
include_once (MW_APP_PATH . 'functions' . DIRECTORY_SEPARATOR . 'language.php');
include_once (MW_APP_PATH . 'functions' . DIRECTORY_SEPARATOR . 'updates.php');

if (defined('MW_IS_INSTALLED') and MW_IS_INSTALLED == true and function_exists('get_all_functions_files_for_modules')) {
    $module_functions = get_all_functions_files_for_modules();
    if ($module_functions != false) {
        if (is_array($module_functions)) {
            foreach ($module_functions as $item) {
                if (is_file($item)) {

                    include_once ($item);
                }
            }
        }
    }
    if (MW_IS_INSTALLED == true) {

        if (($cache_content_init) == false) {
            event_trigger('mw_db_init');

            $installed = array();
            $installed['option_group'] = ('mw_system');
            $installed['option_key'] = ('is_installed');
            $installed['option_value'] = 'yes';
            mw('option')->save_static($installed);

        }

    }
}





