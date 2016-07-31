<?php
/*Plugin Name: Time Tagger
Plugin URI: http://www.envisionyourwebsite.com
Description: Time Tagger. 
Author: Envision Your Website
Version: 1.0
Author URI: http://www.envisionyourwebsite.com
Copyright 2014 envisionyourwebsite.com  (email : tgarner@envisionyourwebsite.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	 
define ( 'TIMETAGGER_PLUGIN_URL', plugin_dir_url(__FILE__));

if( !class_exists( 'timetagger' ) )  
{
  class timetagger{ 
  	
	function timetagger(){ 
			global $wpdb;
			
  			#Add Settings Panel
			add_action( 'admin_menu', array($this, 'time_tagger_setting') );
			add_action( 'admin_head', array($this, 'time_tagger_icon') );
			
			add_action( 'user_register', array( $this, 'add_user_tois' ) );

			add_action( 'wp_print_scripts', array( $this, 'enqueue_scripts' ) );
			
			add_action( 'wp_ajax_timetagger_save', array( $this, 'timetagger_save_callback' ) );
			
					
			//INSTALL TABLE
			register_activation_hook( __FILE__, array($this, 'timetaggerInstall') );
			
			$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."timetagger_infusionsettings");
			$results =$results[0];
			$is_applicationname = $results->app_name;
			$is_api_key = $results->app_key;
			define ('IS_APPLICATION_NAME1',$is_applicationname);
			define ('IS_API_KEY1', $is_api_key);

	}
			
	function enqueue_scripts() {
    wp_enqueue_style('font-awesome-min', TIMETAGGER_PLUGIN_URL.'css/font-awesome.css');
	 wp_enqueue_style('time-tagger-style', TIMETAGGER_PLUGIN_URL.'css/timetagger-style.css');
	
	}
	

	 
 
	 
	
	function time_tagger_setting()
		{
			$page_title = 'Time Tagger';
			$menu_title = 'Time Tagger';
			$capability= 10;
			$menu_slug = 'timetagger';
			$function = array($this, 'timetagger_setup');
			$position = 90;
			$icon_url = 'icon';
			add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position ); 
			/*
			$page_title = 'Time Tagger1';
			$menu_title = 'Time Tagger1';
			$capability= 10;
			$menu_slug = 'timetagger1';
			$function = array($this, 'cron_function');
			$position = 80;
			$icon_url = 'icon';
			add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position ); 
			*/
		}
		
		
		
	function time_tagger_icon(){
		
	}
	function cron_function(){
		if(IS_APPLICATION_NAME1 != '' && IS_API_KEY1 != '')
			{
			require_once("is/isdk.php");
			$myApp = new iSDK;
			if ($myApp->cfgCon("connectionName")) 
				{
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."timetagger_infusionuser");
		
		
		foreach($results as $result){
			$user_id = $result->user_id;
			$infusion_id = $result->infusion_id;
			$user_info =get_userdata($user_id);		
			$data = $user_info->data;
			$registered_date =  $data->user_registered;
			$crons = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."timetagger_tagsto_apply WHERE time !=0");
			$current_date = date('Y-m-d H:i:s');
			$diff = abs( strtotime($current_date ) - strtotime( $registered_date ) );
			
			if(sprintf("%d",intval( $diff / 86400 )) != '0'){
			if(sprintf("%d",intval( $diff / 86400 )) == '1'){
				$days= sprintf("%02d ", intval( $diff / 86400 ));
			}else{
				$days= sprintf("%02d ", intval( $diff / 86400 ));
			}
			}
			if(intval( ( $diff % 86400 ) / 3600) != '0'){
				if(intval( ( $diff % 86400 ) / 3600) == '1'){
					$hours= sprintf("%02d ", intval( ( $diff % 86400 ) / 3600));
				}else{
					$hours= sprintf("%02d ", intval( ( $diff % 86400 ) / 3600));
				}
			}
			if(intval( ( $diff / 60 ) % 60 ) != '0'){
				if(intval( ( $diff / 60 ) % 60 ) == '1'){
					$mins= sprintf("%02d", intval( ( $diff / 60 ) % 60 ));
				}else{
					$mins=sprintf("%02d", intval( ( $diff / 60 ) % 60 ));
				}
			}
					
		
			foreach($crons as $cron){

				if($cron->schedule == 'minutes' && $mins == $cron->time){
					$myApp->grpAssign($infusion_id, $cron->tag_id);
				}
				if($cron->schedule == 'hours' && $hours == $cron->time){
					$myApp->grpAssign($infusion_id, $cron->tag_id);
				}
				if($cron->schedule == 'days' && $days == $cron->time){
					$myApp->grpAssign($infusion_id, $cron->tag_id);
				}
				
			}
			
			
			
		 }
		 }
		}
							
	} 
	function timetagger_setup(){
		require ("timetagger_setup.php");
	} 
	function add_user_tois($user_id){
		global $wpdb;
		if ( isset( $_REQUEST['action'] ) && 'createuser' == $_REQUEST['action'] ) {
			 $user_info = get_userdata($user_id);
			 
			 $data = $user_info->data;
			  
			 if(IS_APPLICATION_NAME1 != '' && IS_API_KEY1 != '')
				{
				require_once("is/isdk.php");
				$myApp = new iSDK;
				if ($myApp->cfgCon("connectionName")) 
					{
						$conDat = array('FirstName' => $data->user_login,
										'Website' => $data->user_url,
										'Email'     => $data->user_email);
						
						$conID = $myApp->addCon($conDat); 
						
						$qry="INSERT INTO `".$wpdb->prefix ."timetagger_infusionuser` SET 
						user_id='".mysql_escape_string($user_id)."',
						infusion_id='".mysql_escape_string($conID)."'";
						$wpdb->query($qry); 
						$qry = "SELECT tag_id FROM `".$wpdb->prefix ."timetagger_tagsto_apply` WHERE `time` = 0";
						$tags = $wpdb->get_results($qry); 						
						$tags =$tags[0];
						$tagId = $tags->tag_id;
						$myApp->grpAssign($conID, $tagId);
					}
				}
		}
	}
	
	function timetaggerInstall()
	{
		global $wpdb;

		$sql1= "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."timetagger_infusionsettings` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `app_name` varchar(225) CHARACTER SET latin1 NOT NULL,
		  `app_key` text CHARACTER SET latin1 NOT NULL,
		  `status` int(1) NOT NULL,
		  `date_created` datetime NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		$wpdb->query($sql1);
		
		$sql2= "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."timetagger_infusionuser` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `user_id` int(10) NOT NULL,
		  `infusion_id` int(10) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		$wpdb->query($sql2);
		
		$sql3= "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."timetagger_tagsto_apply` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `time` int(10) NOT NULL,
		  `schedule` varchar(100) CHARACTER SET latin1 NOT NULL,
		  `tag_id` int(10) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		
		$wpdb->query($sql3);
	}
		
	function timetagger_save_callback()	
	{

	global $wpdb;
	$app_name = $_POST['is_applicationname'];  
	$app_key = $_POST['is_api_key'];
	$status = $_POST['is_status']; 
	$id = $_POST['id'];
	if($id == ''){
		$qry="INSERT INTO `".$wpdb->prefix ."timetagger_infusionsettings` SET 
						app_name='".mysql_escape_string($app_name)."',
						app_key='".mysql_escape_string($app_key)."',
						status='".$status."',
						date_created='".date('Y-m-d H:i:s')."'";
						$wpdb->query($qry); 

	}
	else
	{
		$qry="UPDATE `".$wpdb->prefix ."timetagger_infusionsettings` SET 
					app_name='".mysql_escape_string($app_name)."',
					app_key='".mysql_escape_string($app_key)."',
					status='".$status."',
					date_created='".date('Y-m-d H:i:s')."' WHERE id = '".$id."'";
					$wpdb->query($qry); 
	}
	$qry = "TRUNCATE TABLE `".$wpdb->prefix ."timetagger_tagsto_apply`";
	$wpdb->query($qry);
	if(isset($_POST['cron'])){
		$cron = $_POST['cron'];
		$count = count($cron['time']);
		$cron_arr =array();
		$cron_schedule = array();
		for($c=0;$c<$count;$c++){
			$cron_arr = array($cron['time'][$c],$cron['schedule'][$c],$cron['tag'][$c]);
			$cron_schedule[] = $cron_arr;
			
		}
		
		foreach($cron_schedule as $cron_val){
			$qry="INSERT INTO `".$wpdb->prefix ."timetagger_tagsto_apply` SET 
					time='".mysql_escape_string($cron_val[0])."',
					schedule='".mysql_escape_string($cron_val[1])."',
					tag_id='".mysql_escape_string($cron_val[2])."'";
					$wpdb->query($qry);
		}
				
	}
	if(isset($_POST['default_tag'])){
		$tag = $_POST['default_tag'];
		$days = 0;
		$qry="INSERT INTO `".$wpdb->prefix ."timetagger_tagsto_apply` SET 
					time='".mysql_escape_string(0)."',
					schedule='".mysql_escape_string(0)."',
					tag_id='".mysql_escape_string($tag)."'";
					$wpdb->query($qry);
		
	}
	
	
	die();
	} 
	
 	 }
  
  
}


if( class_exists('timetagger') )
	$timetaggerobj = new timetagger();
	
	
    add_filter( 'cron_schedules', 'add_cron_intervals' );
	
     
    function add_cron_intervals( $interval ) {
     
  	 $interval['minutes_1'] = array('interval' => 60, 'display' => 'Once 1 minutes');

    	return $interval;
    }
     
    add_action( 'cron_hook', 'cron_exec' );
     
    if( !wp_next_scheduled( 'cron_hook' ) ) {
    wp_schedule_event( time(), 'minutes_1', 'cron_hook' );
    }
    
    function cron_exec() {	
	
	$timetaggerobj = new timetagger();
	//wp_mail( 'raja@envisionyourwebsite.com', 'The subject','before message' );
	$timetaggerobj->cron_function();
  
    }	
	 
	
	register_deactivation_hook( __FILE__, 'deactivate' );
     
    function deactivate() {
    $timestamp = wp_next_scheduled( 'cron_hook' );
    wp_unschedule_event($timestamp, 'cron_hook' );
    }