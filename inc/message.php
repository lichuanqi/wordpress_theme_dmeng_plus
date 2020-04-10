<?php

/*
 * 欢迎来到代码世界，如果你想修改多梦主题的代码，那我猜你是有更好的主意了～
 * 那么请到多梦网络（ http://www.dmeng.net/ ）说说你的想法，数以万计的童鞋们会因此受益哦～
 * 同时，你的名字将出现在多梦主题贡献者名单中，并有一定的积分奖励～
 * 注释和代码同样重要～
 * @author 多梦 @email chihyu@aliyun.com 
 */

/*
 * 通知和提示信息 @author 多梦 at 2014.06.23 
 * 
 */

//~ 后台提示信息
function dmeng_admin_notices_action() {
	global $pagenow;
	
	$message = __('多梦主题提示 : ','dmeng');
	
	if ( 'options-discussion.php' == $pagenow ){
		
		if(isset( $_GET['settings-updated'] )){
			update_option('thread_comments',1);
		}
		$message .= __('评论嵌套是必须选择的，无法改变！','dmeng');
		$type = 'error';
	}
	
	if( !empty($message) && !empty($type) ) add_settings_error( 'dmeng_message_admin', esc_attr( 'settings_updated' ), $message, $type );
}
add_action( 'admin_notices', 'dmeng_admin_notices_action' );

add_action('admin_menu', 'dmeng_add_help_tab');
function dmeng_add_help_tab() {
    add_action( 'admin_head-nav-menus.php', 'dmeng_nav_menus_help_tab' );
}

function dmeng_nav_menus_help_tab(){
	
    $screen = get_current_screen();

    $screen->add_help_tab( array(
        'id'	=> 'dmeng_nav_menus_help_tab',
        'title'	=> __('图标和按钮效果', 'dmeng'),
        'content'	=> '<p>' . __( '请您留意：菜单项图标和按钮效果属于多梦主题专属功能，这代表着当你切换到其他主题，图表和按钮效果将不复存在。', 'dmeng' ) . '</p>'
								. '<p>' . __( '首先，请您缩小“帮助”选项卡，并打开“显示选项”在“显示菜单高级属性”中勾上“CSS类”项，然后在编辑菜单项时填入以下内容：', 'dmeng' ) . '</p>'
								. '<p>' . __( '添加图标：在允许 Bootstrap 免费使用的200个 <a href="http://cdn.dmeng.net/glyphicons.html" target="_blank">Glyphicons 字体图标</a> 中挑选出你喜欢的图标，复制图标类名到”CSS类“里的第一个即可（必须是在前面第一个），如相机图标则是 <span style="color: #339966;">glyphicon-camera</span> ，以此类推。', 'dmeng' ) . '</p>'
								. '<p>' . __( '添加按钮：根据 Bootstrap 的按钮CSS类名填写到”CSS类“中即可，如：', 'dmeng' ) . '</p>'
								. __( '<ul><li>默认按钮（白底黑字）是 <span style="color: #555;">btn btn-default</span></li><li>基本按钮（蓝底白字）是 <span style="color: #428bca;">btn btn-primary</span></li><li>成功按钮（绿底白字）是 <span style="color: #5cb85c;">btn btn-success</span></li><li>信息按钮（青底白字）是 <span style="color: #5bc0de;">btn btn-info</span></li><li>警告按钮（黄底白字）是 <span style="color: #f0ad4e;">btn btn-warning</span></li><li>危险按钮（红底白字）是 <span style="color: #d9534f;">btn btn-danger</span></li></ul> ', 'dmeng' )
								. '<p>' . __( '如果既需要前置图标也需要按钮效果，请这样填写”CSS类“：<span style="color: #339966;">glyphicon-camera btn btn-defaul</span> （图标CSS类名在第一个）', 'dmeng' ) . '</p>'
								. '<p>' . __( '更详细的使用教程请看：<a href="http://www.dmeng.net/wordpress-navigation-menus.html" target="_blank">WordPress导航菜单设置</a>', 'dmeng' ) . '</p>'
    ) );

}

/*
 * 添加数据库表 @author 多梦 at 2014.07.04
 * 
 * msg_id 自动增长主键
 * user_id 用户ID
 * msg_type 类型
 * msg_date 日期
 * msg_title 标题
 * msg_content 内容
 * 
 */

function dmeng_message_install_callback(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'dmeng_message';   
    if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) :   
		$sql = " CREATE TABLE `$table_name` (
			`msg_id` int NOT NULL AUTO_INCREMENT, 
			PRIMARY KEY(msg_id),
			`user_id` int,
			`msg_type` varchar(20),
			`msg_date` datetime,
			`msg_title` tinytext,
			`msg_content` text
		) CHARSET=utf8;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');   
			dbDelta($sql);   
    endif;
}
function dmeng_message_install(){
    global $pagenow;   
    if ( is_admin() && 'themes.php' == $pagenow && isset( $_GET['activated'] ) )
        dmeng_message_install_callback();
}
add_action( 'load-themes.php', 'dmeng_message_install' ); 

/*
 * 
 * 添加消息
 * 
 */

function add_dmeng_message( $uid=0, $type='', $date='', $title='', $content='' ){

	$uid = intval($uid);
	$title = sanitize_text_field($title);
	
	if( !$uid || empty($title) ) return;

	$type = $type ? sanitize_text_field($type) : 'unread';
	$date = $date ? $date : current_time('mysql');
	$content = htmlspecialchars($content);
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'dmeng_message';

	if( $wpdb->insert( $table_name, array( 'user_id'=> $uid, 'msg_type'=> $type, 'msg_date'=> $date, 'msg_title'=> $title, 'msg_content'=> $content ) ) )
		return $wpdb->insert_id;
	
	return 0;
	
}

/*
 * 
 * 删除消息
 * 
 */

function delete_dmeng_message( $id, $uid=0, $type='', $title='' ){

	$id = intval($id);
	$uid = intval($uid);

	if( !$id && !$uid ) return;

	global $wpdb;
	$table_name = $wpdb->prefix . 'dmeng_message';

	$where = array();
	if($id) $where['msg_id'] = $id;
	if($uid) $where['user_id'] = $uid;
	if($type) $where['msg_type'] = $type;
	if($title) $where['msg_title'] = $title;

	return $wpdb->delete( $table_name, $where );

}

//~ 添加消息的定时器
add_action( 'add_dmeng_message_event', 'add_dmeng_message', 10, 5 );

/*
 * 
 * 更新状态
 * 
 */

function update_dmeng_message_type( $id=0, $uid=0, $type='' ){

	$id = intval($id);
	$uid = intval($uid);

	if( ( !$id || !$uid) || empty($type) ) return;

	global $wpdb;
	$table_name = $wpdb->prefix . 'dmeng_message';

	if( $id===0 ){
		$sql = " UPDATE $table_name SET msg_type = '$type' WHERE user_id = '$uid' ";
	}else{
		$sql = " UPDATE $table_name SET msg_type = '$type' WHERE msg_id = '$id' ";
	}

	if($wpdb->query( $sql ))
		return 1;
	
	return 0;
	
}

//~ 更新状态的定时器
add_action( 'update_dmeng_message_type_event', 'update_dmeng_message_type', 10, 3 );

/*
 * 
 * 获取消息（积分消息除外）
 * 
 */

function get_dmeng_message( $uid=0 , $count=0, $where='', $limit=0, $offset=0 ){
	
	$uid = intval($uid);
	
	if( !$uid ) return;

	global $wpdb;
	$table_name = $wpdb->prefix . 'dmeng_message';
	
	if($count){
		if($where) $where = " AND $where";
		$check = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE user_id='$uid' $where" );
	}else{
		$check = $wpdb->get_results( "SELECT msg_id,msg_type,msg_date,msg_title,msg_content FROM $table_name WHERE user_id='$uid' AND $where ORDER BY (CASE WHEN msg_type LIKE 'un%' THEN 1 ELSE 0 END) DESC, msg_date DESC LIMIT $offset,$limit" );
	}
	if($check)	return $check;

	return 0;

}

/*
 * 
 * 获取用户的积分消息
 * 
 */

function get_dmeng_credit_message( $uid=0 , $limit=0, $offset=0 ){
	
	$uid = intval($uid);
	
	if( !$uid ) return;

	global $wpdb;
	$table_name = $wpdb->prefix . 'dmeng_message';
	
	$check = $wpdb->get_results( "SELECT msg_id,msg_date,msg_title FROM $table_name WHERE msg_type='credit' AND user_id='$uid' ORDER BY msg_date DESC LIMIT $offset,$limit" );

	if($check)	return $check;

	return 0;

}

/*
 * 
 * 获取私信
 * 
 */

function get_dmeng_pm( $pm=0, $from=0, $count=false, $single=false, $limit=0, $offset=0 ){
	
	$pm = intval($pm);
	$from = intval($from);
	
	if( !$pm || !$from ) return;

	global $wpdb;
	$table_name = $wpdb->prefix . 'dmeng_message';
	
	$title_sql = $single ? "msg_title='{\"pm\":$pm,\"from\":$from}'" : "( msg_title='{\"pm\":$pm,\"from\":$from}' OR msg_title='{\"pm\":$from,\"from\":$pm}' )";
	
	if($count){
		$check = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE ( msg_type='repm' OR msg_type='unrepm' ) AND $title_sql" );
	}else{
		$check = $wpdb->get_results( "SELECT msg_id,msg_date,msg_title,msg_content FROM $table_name WHERE ( msg_type='repm' OR msg_type='unrepm' ) AND $title_sql ORDER BY msg_date DESC LIMIT $offset,$limit" );
	}
	if($check)	return $check;

	return 0;

}

/*
 * 
 * 删除信息AJAX
 * 
 */
 
function dmeng_delete_message_callback(){

	if( empty($_POST['id']) ) die();

	do_action( 'dmeng_before_ajax', intval($_POST['id']), false );

	if( empty($_POST['pm']) )
		delete_dmeng_message(intval($_POST['id']), get_current_user_id());
	else
		delete_dmeng_message(intval($_POST['id']), intval($_POST['pm']), false, '{"pm":'.intval($_POST['pm']).',"from":'.get_current_user_id().'}');

	die();
}
add_action( 'wp_ajax_dmeng_delete_message', 'dmeng_delete_message_callback' );
