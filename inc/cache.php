<?php

/*
 * 欢迎来到代码世界，如果你想修改多梦主题的代码，那我猜你是有更好的主意了～
 * 那么请到多梦网络（ http://www.dmeng.net/ ）说说你的想法，数以万计的童鞋们会因此受益哦～
 * 同时，你的名字将出现在多梦主题贡献者名单中，并有一定的积分奖励～
 * 注释和代码同样重要～
 * @author 多梦 @email chihyu@aliyun.com 
 */

/*
 * 缓存 @author 多梦 at 2014.06.23 
 * 
 */

//~ 菜单缓存key
function dmeng_get_nav_menu_cache_key($args){
	global $wp_query;
	$timestamp = get_option('dmeng_cached_nav_menu');
	$cache_key = 'dmeng_n_' . md5( $args->theme_location . '_' . $wp_query->query_vars_hash . $timestamp );
	return $cache_key;
}

//~ 读取菜单缓存
function dmeng_get_nav_menu_cache( $nav_menu, $args ) {

	$cache_key = dmeng_get_nav_menu_cache_key($args);
		
	$menu = get_transient( $cache_key );
	if ( false !== $menu ) {
		$nav_menu = $menu;
	}

	return $nav_menu;
}
add_filter( 'pre_wp_nav_menu', 'dmeng_get_nav_menu_cache', 10, 2 );

//~ 设置菜单缓存
function dmeng_set_nav_menu_cache( $nav_menu, $args ) {

	$cache_key = dmeng_get_nav_menu_cache_key($args);
	set_transient( $cache_key, $nav_menu.sprintf(__('<!-- cached %s -->', 'dmeng'), current_time('mysql')), 3600 );

	return $nav_menu;
}
add_filter( 'wp_nav_menu', 'dmeng_set_nav_menu_cache', 10, 2 );

//~ 更新菜单时更新菜单缓存时间
function dmeng_clear_nav_menu_cache(){
	update_option('dmeng_cached_nav_menu', current_time('timestamp'));
}
add_action( 'wp_update_nav_menu', 'dmeng_clear_nav_menu_cache' );

function dmeng_get_widget_cache_key($prefix, $object){
	return 'dmeng_'.$prefix.'_'.md5(serialize($object));
}

//~ 读取小工具缓存
function dmeng_get_widget_cache($output, $this, $prefix){
	
	if( $prefix == 'ru' && ! get_option('dmeng_cached_recent_user') ) return $output;
	
	$cache_key = dmeng_get_widget_cache_key($prefix, $this);
	
	$widget = get_transient( $cache_key );
	if ( false !== $widget ) {
		$output = $widget;
	}
	
	return $output;
}
add_filter( 'dmeng_pre_analytics', 'dmeng_get_widget_cache', 10, 3 );
add_filter( 'dmeng_pre_credit_rank', 'dmeng_get_widget_cache', 10, 3 );
add_filter( 'dmeng_pre_rank', 'dmeng_get_widget_cache', 10, 3 );
add_filter( 'dmeng_pre_recent_user', 'dmeng_get_widget_cache', 10, 3 );

//~ 设置小工具缓存
function dmeng_set_widget_cache($output, $this, $prefix){
	
	$cache_key = dmeng_get_widget_cache_key($prefix, $this);
	set_transient( $cache_key, $output.sprintf(__('<!-- cached %s -->', 'dmeng'), current_time('mysql')), 3600 );
	
	//~ 如果最近登录用户，就更新状态为缓存
	if( $prefix == 'ru' ) update_option('dmeng_cached_recent_user', 1);
	
	return $output;
}
add_filter( 'dmeng_analytics', 'dmeng_set_widget_cache', 10, 3 );
add_filter( 'dmeng_credit_rank', 'dmeng_set_widget_cache', 10, 3 );
add_filter( 'dmeng_rank', 'dmeng_set_widget_cache', 10, 3 );
add_filter( 'dmeng_recent_user', 'dmeng_set_widget_cache', 10, 3 );

//~ 清理小工具缓存
function dmeng_clear_widget_cache($this, $prefix){
	//~ $prefix 是代表这四个小工具的简称
	//~ a 是统计，cr 是积分排行榜，r 是文章排行榜，ru 是最近登录用户
	$cache_key = dmeng_get_widget_cache_key($prefix, $this);
	delete_transient( $cache_key );
}
add_action( 'dmeng_update_analytics', 'dmeng_clear_widget_cache', 10, 2 );
add_action( 'dmeng_update_credit_rank', 'dmeng_clear_widget_cache', 10, 2 );
add_action( 'dmeng_update_rank', 'dmeng_clear_widget_cache', 10, 2 );
add_action( 'dmeng_update_recent_user', 'dmeng_clear_widget_cache', 10, 2 );

//~ 有新用户登录时把最近登录用户小工具缓存状态改为0，即不缓存
function dmeng_clear_recent_user_cache(){
	update_option('dmeng_cached_recent_user', 0);
}
add_action('wp_login', 'dmeng_clear_recent_user_cache');

//~ 更新文章时清空首页分类列表缓存
function dmeng_clear_home_cat_lists(){
	delete_transient( 'dmeng_home_cat_lists' );
}
add_action( 'save_post', 'dmeng_clear_home_cat_lists' );

//~ 刷新所有缓存
function dmeng_refresh_all($object=array('rewrite','memcached','transient')){
	
	//~ 固定链接缓存
	if( in_array('rewrite', $object) ){
		global $wp_rewrite;   
		$wp_rewrite->flush_rules();
	}

	//~ 对象缓存
	if( in_array('memcached', $object) ){
		wp_cache_flush();
	}
	
	//~ transient
	if( in_array('transient', $object) ){
		global $wpdb;
		$wpdb->query( " DELETE FROM $wpdb->options WHERE `option_name` LIKE '_transient_%' AND `option_name` NOT LIKE '_transient_dmeng_email_verify_%' " );
	}
}

//~ 每隔一个小时清理一次内容缓存
add_action( 'wp', 'dmeng_refresh_all_setup_schedule' );
function dmeng_refresh_all_setup_schedule() {
	if ( ! wp_next_scheduled( 'dmeng_refresh_all_hourly_event' ) ) {
		wp_schedule_event( time(), 'hourly', 'dmeng_refresh_all_hourly_event');
	}
}
function dmeng_refresh_all_hourly_event_callback(){
	dmeng_refresh_all(array('memcached','transient'));
}
add_action( 'dmeng_refresh_all_hourly_event', 'dmeng_refresh_all_hourly_event_callback' );
