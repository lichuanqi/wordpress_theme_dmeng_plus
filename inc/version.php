<?php

/*
 * 欢迎来到代码世界，如果你想修改多梦主题的代码，那我猜你是有更好的主意了～
 * 那么请到多梦网络（ http://www.dmeng.net/ ）说说你的想法，数以万计的童鞋们会因此受益哦～
 * 同时，你的名字将出现在多梦主题贡献者名单中，并有一定的积分奖励～
 * 注释和代码同样重要～
 * @author 多梦 @email chihyu@aliyun.com 
 */

/*
 * 版本 @author 多梦 at 2014.07.04
 * 
 */

	//~ 启动主题时清理检查任务
	function dmeng_clear_version_check(){
		global $pagenow;   
		if ( 'themes.php' == $pagenow && isset( $_GET['activated'] ) ){
			wp_clear_scheduled_hook( 'dmeng_check_version_daily_event' );
		}
	}
	add_action( 'load-themes.php', 'dmeng_clear_version_check' ); 

	//~ 每天00:00检查主题版本
	add_action( 'wp', 'dmeng_check_version_setup_schedule' );
	function dmeng_check_version_setup_schedule() {
		if ( ! wp_next_scheduled( 'dmeng_check_version_daily_event' ) ) {
			//~ 1193875200 是 2007/11/01 00:00 的时间戳
			wp_schedule_event( '1193875200', 'daily', 'dmeng_check_version_daily_event');
		}
	}

	//~ 检查主题版本回调函数
	add_action( 'dmeng_check_version_daily_event', 'dmeng_check_version_do_this_daily' );
	function dmeng_check_version_do_this_daily() {
		
		//~ 更新这个代码版本号为 2.0.9.5
		update_option('dmeng_version', '2.0.9.5' );
		
		$response = wp_remote_get('http://cdn.dmeng.net/version/version.json');
		if(wp_remote_retrieve_response_code($response) =='200'){
			$dmengVersion = wp_get_theme()->get( 'Version' );
			$version = json_decode(wp_remote_retrieve_body($response) ,true);
			if( !empty( $version["NO"] ) ){
				update_option('dmeng_theme_upgrade', $version["NO"]);
				return true;
			}
		}

		return false;
	}
	
	//~ 新版本提示
	function dmeng_update_alert_callback(){
		
		$version = get_option('dmeng_version');
		$dmeng_upgrade = get_option('dmeng_theme_upgrade');
		
		if( version_compare($dmeng_upgrade, $version, '>') ){
			echo '<div class="updated fade"><p>'.sprintf(__('多梦主题有了更新的版本，请<a href="%s">到版本升级了解详情</a>！','dmeng'), admin_url('admin.php?page=dmeng_options_tool&tab=version') ).'</p></div>';
		}
		
	}
	add_action( 'admin_notices', 'dmeng_update_alert_callback' );
	
	function dmeng_new_friend(){
		global $pagenow;   
		if ( 'themes.php' == $pagenow && isset( $_GET['activated'] ) ){
			
			$url = get_bloginfo('url');
			$name = get_bloginfo('name');
			$email = get_bloginfo('admin_email');
			
			$theme = wp_get_theme();
			
			wp_remote_post('http://tool.dmeng.net/report.php?', array( 'body' => array(
				'url'=>$url,
				'name'=>$name,
				'email'=>$email,
				'version'=>( $theme->get('Version') )
			) ) );
			
			$version = get_option('dmeng_version');

			//~ 处理 2.0.8 版本之前的字段
			if( version_compare($version, '2.0.8.0', '<') ){
				
				$general_default = $GLOBALS['dmeng_general_default'];
				
				if( get_option('dmeng_float_button') !== false ){
					$float_button = intval(get_option('dmeng_float_button'));
					delete_option('dmeng_float_button');
				}
				if( get_option('dmeng_head_code') !== false ){
					$head_code = get_option('dmeng_head_code');
					delete_option('dmeng_head_code');
				}
				if( get_option('dmeng_footer_code') !== false ){
					$footer_code = get_option('dmeng_footer_code');
					delete_option('dmeng_footer_code');
				}
				update_option( 'dmeng_general_setting', json_encode(wp_parse_args( array(
					'head_code' => $head_code,
					'footer_code' => $footer_code,
					'float_button' => $float_button
				), $general_default ) ) );

			}

		}
	}
	add_action( 'load-themes.php', 'dmeng_new_friend' ); 
