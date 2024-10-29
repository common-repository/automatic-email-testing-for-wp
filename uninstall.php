<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// delete value inside option settings
delete_option('aet_log');
delete_option( 'email_setting_field');

?>