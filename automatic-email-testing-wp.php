<?php
/*
 * Plugin Name: Automatic Email Testing for WP
 * Description: Allow website to test email server automatically and report.
 * Version: 1.4.7
 * Author: WebBuddy
 * Author URI: https://webbuddy.sg
 * Text Domain: automatic-email-testing-wp
*/

defined( 'ABSPATH' ) || exit; 

// to embed css style
add_action( 'admin_enqueue_scripts', 'aet_stylesheet' );

function aet_stylesheet() {
    // Respects SSL, Style.css is relative to the current file
    wp_register_style( 'style', plugins_url('style.css', __FILE__) );
    wp_enqueue_style( 'style' );
}

// set up menu inside dashboard
function aet_menu() {
    add_submenu_page(
        'options-general.php',
        __( 'Automatic Email Testing for WP', 'automatic-email-testing-wp' ),
        __( 'Automatic Email Testing for WP', 'automatic-email-testing-wp' ),
        'manage_options',
        'automatic-email-testing-wp',
        'aet_contents',
        99
    );
}
add_action( 'admin_menu', 'aet_menu' );


function aet_contents() {
    ?>
    <p style="float:left;"><img style="margin-right:7px;" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/icon-128x128.png'; ?>" width="48px" height="48px;"></p>
    <div style="padding-top:10px;"><h1> <?php esc_html_e( 'Automatic Email Testing for WP', 'automatic-email-testing-wp' ); ?> </h1></div><p style="clear:both;"></p>
    <form method="POST" action="options.php">
    <?php
    $aet_email= get_option( 'email_setting_field' );
    if (!filter_var($aet_email, FILTER_VALIDATE_EMAIL) && $aet_email != NULL){
        echo '<p style="color:red;">' . __( 'Please enter a valid email address!', 'automatic-email-testing-wp' ) . '</p>';
    }     
    
    
    // Only display table log if it is not empty
    if(get_option('aet_log')!=""){
        
        echo '<h2 style="margin-top:1.5em;">'  . __( 'Email Test Log', 'automatic-email-testing-wp' ) . '</h2>';
        
        echo"<div align=\"center;\" ><table class=\"aet\">";
        echo"<tr class=\"aet\">";
        echo '<td class="aet"><b>' . __( 'Date & Time', 'automatic-email-testing-wp' ) . '</b></td>';
        
        echo '<td class="aet"><b>' . __( 'Email Send Status', 'automatic-email-testing-wp' ) . '</b></td>';
        echo '<td class="aet"><b>' . __( 'Remarks', 'automatic-email-testing-wp' ) . '</b></td></tr>';

        $aet_data= explode(",", get_option('aet_log'));

        //count the array item and if more than 3, then clean up and re-save the variable option key
        if(count($aet_data) >3){
        //if array items more than 3, then explode using limit
         $clear_aet_log= explode(",", get_option('aet_log'),4);
        // array pop the last item
        array_pop($clear_aet_log);
        //then implode back
        update_option("aet_log" , implode("," , $clear_aet_log ));
        $aet_data= explode(",", get_option('aet_log'));
        } 


            foreach ($aet_data as $key => $value){
                echo "<tr class=\"aet\">";
                $aet_sdata= explode("-", $value);
           
                echo "<td class=\"aet\">";
                //echo date("F j, Y, g:i a", $aet_sdata[0]);    // display the time and date
                echo $aet_sdata[0];
                echo "</td>";
                    echo "<td class=\"aet\">";
                    if ($aet_sdata[1]=="s")  _e( 'Successful =)', 'automatic-email-testing-wp' );
                    elseif ($aet_sdata[1]=="f") 
                    echo '<span style="color:red;">' . __( 'Failed!', 'automatic-email-testing-wp' )  . '</span>'; 
                    
                    echo "</td>";
                        echo "<td class=\"aet\">";
                        if ($aet_sdata[2]=="n") echo "";
                        else //echo "$aet_sdata[2]" ; 
                        echo '<a href="https://webbuddy.sg/steps-to-troubleshoot-emails-not-sending-wordpress-website/" target="_blank">' . __('Troubleshoot Guide','automatic-email-testing-wp') . '</a>';
                        echo "</td>";
       
                 echo "</tr>";
            }


        echo"</table></div><br>";
        
        $aet_sched = wp_next_scheduled('aet_cron');
        echo "Next test email will be sent after " . wp_date('F j Y g:i a', $aet_sched);
        echo ".<p></p><div style=\"padding-top:20px;\"></div>";
}
    
    settings_fields( 'auto-email-test-settings' );
    do_settings_sections( 'auto-email-test-settings' );
    if (! wp_next_scheduled ( 'aet_cron' )) submit_button('Test and Automate!');
    else submit_button('Save Changes');
    ?>
    </form>
    <?php
    echo '<p>' . __('*The first test email will be triggered once you click the above button. Thereafter, the test email will be automatically sent every 24 hours (approx).', 'automatic-email-testing-wp')  . '</p>';
    echo '<br><div style="border: dashed 2px #666666;border-radius:7px;padding:20px;"><b>' . __('DO YOU KNOW?','automatic-email-testing-wp') . '</b><br><br>';
    echo 'You will not receive the daily email testing report using this free version if your email server fails. Want to receive automated email notification even when your email server fails? <a href="https://payhip.com/b/L4KeS" target="_blank">Check out our PRO version here!</a></div>';
              
}

// Register required settings using wp option api
add_action( 'admin_init', 'aet_settings_init' );

function aet_settings_init() {

    add_settings_section(
        'email_setting_section',
        __( 'Email Settings', 'aet' ),
        'aet_section_callback_function',
        'auto-email-test-settings'
    );         
    

		add_settings_field(
		   'email_setting_field',
		   __( 'Email Address', 'aet' ),
		   'aet_setting_markup',
		   'auto-email-test-settings',
		   'email_setting_section'
		);
		
	

		register_setting( 'auto-email-test-settings', 'email_setting_field' ); 		
}

//Displays the message together with the field for usres to enter email
function aet_section_callback_function() {
    echo '<p style="margin-bottom:0px;">' . __('What email address do you want the daily test report to be sent to?', 'automatic-email-testing-wp') . '</p>' ;
}

function aet_setting_markup(){
    ?>
    <input type="text" id="1" name="email_setting_field" value="<?php if(get_option( 'email_setting_field' )!=NULL) echo get_option( 'email_setting_field' ); ?>" >
    
   
    <?php
}

$aet_email= sanitize_email(get_option( 'email_setting_field' ));

// add action to catch mailing errors
add_action( 'wp_mail_failed', 'aet_mail_error', 10, 1 );

function aet_mail_error( $aetwp_error ) {
     update_option('aet_mail_error' , json_encode($aetwp_error,true));         
}   

if (filter_var($aet_email, FILTER_VALIDATE_EMAIL)) {
    
  function aet_deactivate() {
    wp_clear_scheduled_hook( 'aet_cron' );
    
}
 
add_action('init', function() {
    add_action( 'aet_cron', 'aet_run_cron' );
    register_deactivation_hook( __FILE__, 'aet_deactivate' );
 
    if (! wp_next_scheduled ( 'aet_cron' )) {
        wp_schedule_event( time(), 'daily', 'aet_cron' );
    }
});


function aet_run_cron() {
  $to = sanitize_email(get_option( 'email_setting_field' ));
	$subject = 'Email Testing Report for '  . get_home_url() . ' (' . wp_date("F j") .')' ;
	$body = 'Hello! <br><br>We just tested the email server for  ' . get_home_url() . 
	' and we are happy to report that it is working well today. <br><br> There is no further action needed on your part.'
	. '<br><br>Tomorrow, you should receive this email again around this time. <br><br>If you didn\'t receive any email from us, please login to your WordPress dashboard (Settings >> Automatic Email Testing for WP) to check the email log.'
	. '<br><br>Thank you for using our plugin, <br>WebBuddy.sg'
  . '<br><br>P.S. If you like our plugin, please help us <a href="https://wordpress.org/plugins/automatic-email-testing-for-wp/" target="_blank">write a review here!</a>'
  . '<br><br>------------------------'
  . '<br><br>Want to be automatically notified even when your email server fails? <a href="https://payhip.com/b/L4KeS" target="_blank">Check out our PRO version here!</a>' ;
  $headers = array('Content-Type: text/html; charset=UTF-8'); 
	$sent = wp_mail( $to, $subject, $body, $headers );   
	
	// check if email sent successfully
	if($sent){
      
      // check if log is empty, if empty, save directly
      // of not empty, then append to it
      if(get_option('aet_log')!="") $aet_new_value = wp_date("F j Y g:i a") . "-" . "s" . "-" . "n" . "," . get_option( 'aet_log' );
      else $aet_new_value = wp_date("F j Y g:i a") . "-" . "s" . "-" . "n" ;
      	       
	}else{
      if(get_option('aet_log')!="")   $aet_new_value = wp_date("F j Y g:i a") . "-" . "f" . "-" . get_option( 'aet_mail_error' ) . "," . get_option( 'aet_log' );
      else $aet_new_value = wp_date("F j Y g:i a") . "-" . "f" . "-" . get_option( 'aet_mail_error' ) ;
      do_action ('__aet_sent_fail');  
  }  
	update_option( 'aet_log' , $aet_new_value);   
	
}

}

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wp_automatic_email_testing_settings_link' );

// create the plugin action links - 'Settings' so user can click on it and get to the plugin setting page fast

function wp_automatic_email_testing_settings_link( $links ) {
	// Build and escape the URL.
	$url = esc_url( add_query_arg(
		'page',
		'automatic-email-testing-wp',
		get_admin_url() . 'options-general.php'
	) );
	// Create the link.
	$settings_link = "<a href='$url'>" . __( 'Settings' , 'automatic-email-testing-wp' ) . '</a>';
	// Adds the link to the end of the array.
	  array_push(
		$links,
		$settings_link
	); 
	return $links;
	
}

?>