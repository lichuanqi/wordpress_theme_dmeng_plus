<?php

/*
 * 欢迎来到代码世界，如果你想修改多梦主题的代码，那我猜你是有更好的主意了～
 * 那么请到多梦网络（ http://www.dmeng.net/ ）说说你的想法，数以万计的童鞋们会因此受益哦～
 * 同时，你的名字将出现在多梦主题贡献者名单中，并有一定的积分奖励～
 * 注释和代码同样重要～
 * @author 多梦 @email chihyu@aliyun.com 
 */
 
/*
 * 主题设置页面 - 数据记录 @author 多梦 at 2014.06.23 
 * 
 */

function dmeng_options_log_page(){

  if( isset($_POST['action']) && sanitize_text_field($_POST['action'])=='clear' && wp_verify_nonce( trim($_POST['_wpnonce']), 'check-nonce' ) ) :


  endif;
  
	//~ dmeng_settings_error('updated',__('缓存已清理','dmeng'));

	$tab = 'credit';
	if( isset($_GET['tab'])){
		if(in_array($_GET['tab'], array('credit','exchange','repm','search','vote'))) $tab = $_GET['tab'];
	}
	$dmeng_tabs = array(
		'credit' => __('积分', 'dmeng'),
		'exchange' => __('积分换礼', 'dmeng'),
		'repm' => __('私信', 'dmeng'),
		'search' => __('搜索量', 'dmeng'),
		'vote' => __('投票', 'dmeng')
	);
	$tab_output = '<h2 class="nav-tab-wrapper">';
	foreach( $dmeng_tabs as $tab_key=>$tab_name ){
		$tab_output .= sprintf('<a href="%s" class="nav-tab%s">%s</a>', remove_query_arg( array('delete', '_wp_nonce'), add_query_arg('tab', $tab_key) ), $tab_key==$tab ? ' nav-tab-active' : '', $tab_name);
	}
	$tab_output .= '</h2>';
	

	?>
<div class="wrap">
	<h2><?php _e('多梦主题部分数据记录','dmeng');?></h2>
	<form method="post">
		<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
		<?php echo $tab_output;?>
<div style="border:1px solid #e5e5e5;padding:15px;background:#fff;margin:15px 0;">

		<?php
		
	global $wpdb;
	
	$offset = isset($_POST['offset']) ? intval($_POST['offset'])-1 : 0;
	$limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
	
	$offset = $offset<0 ? 0 : $offset;
	$limit = $limit<1 ? 1 : $limit;
	
	$count = 0;
	printf(__('正在查看从第%1$s条到第%2$s条数据', 'dmeng'), $offset+1, $offset+$limit);
	
	$output = '<p>'.__('没有找到数据','dmeng').'</p>';
	
if($tab=='credit'){ 
	
	$table_name = $wpdb->prefix . 'dmeng_message';
	
	$sql_where = "FROM $table_name WHERE msg_type='credit'";
	
	$count =  $wpdb->get_var( "SELECT count(msg_id) ".$sql_where );
	
	$messages = $wpdb->get_results( "SELECT user_id,msg_date,msg_title ".$sql_where." ORDER BY msg_id DESC LIMIT $offset,$limit" );
	if( $messages ){
		$output = '';
		foreach( $messages as $message ){
			$output .= '<p>'.sprintf( __('%1$s 在 %2$s %3$s', 'dmeng'), 
									'<a href="'.dmeng_get_user_url('credit', $message->user_id).'" target="_blank">'.get_the_author_meta('display_name', $message->user_id).'</a>',
									$message->msg_date, 
									$message->msg_title ).
									'</p>';
		}
	}
	
}

if($tab=='exchange'){ 
	
	$table_name = $wpdb->prefix . 'dmeng_meta';
	
	$sql_where = "FROM $table_name WHERE meta_key LIKE 'exchange_%'";
	
	$count =  $wpdb->get_var( "SELECT count(meta_id) ".$sql_where );
	
	$results = $wpdb->get_results( "SELECT user_id,meta_key,meta_value ".$sql_where." ORDER BY meta_id DESC LIMIT $offset,$limit" );

	if( $results ){
		$output = get_option('dmeng_is_gift_open') ? '<p><a href="'.add_query_arg('api', 'check', get_post_type_archive_link( 'gift' ) ).'" target="_blank">'.__('点击进入兑换信息查询（使用唯一识别码）', 'dmeng').'</a></p>' : '';
		
		foreach( $results as $item ){
			$gift_id = explode('_', $item->meta_key);
			$output .= '<p>'.sprintf( __('%1$s 在 %2$s 兑换了 %3$s，唯一识别码是 %4$s', 'dmeng'), 
									'<a href="'.dmeng_get_user_url('gift', $item->user_id).'" target="_blank">'.get_the_author_meta('display_name', $item->user_id).'</a>',
									$item->meta_value, 
									'<a href="'.get_permalink($gift_id[1]).'" target="_blank">'.esc_html(get_the_title($gift_id[1])).'</a>', 
									dmeng_get_exchange_key($item->user_id, $gift_id[1], $item->meta_value) ).
									'</p>';
		}
	}
	
}

if($tab=='repm'){ 
	
	$table_name = $wpdb->prefix . 'dmeng_message';
	
	$sql_where = "FROM $table_name WHERE msg_type='repm' OR msg_type='unrepm'";
	
	$count =  $wpdb->get_var( "SELECT count(msg_id) ".$sql_where );
	
	$messages = $wpdb->get_results( "SELECT user_id,msg_date,msg_title,msg_content ".$sql_where." ORDER BY msg_id DESC LIMIT $offset,$limit" );
	if( $messages ){
		$output = '';
		foreach( $messages as $message ){
			$repm = json_decode($message->msg_title);
			$output .= '<p>'.sprintf( __('%1$s %2$s 给 %3$s 发私信：%4$s', 'dmeng'),
									$message->msg_date,
									'<a href="'.dmeng_get_user_url('message', $repm->from).'" target="_blank">'.get_the_author_meta('display_name', $repm->from).'</a>',
									'<a href="'.dmeng_get_user_url('message', $repm->pm).'" target="_blank">'.get_the_author_meta('display_name', $repm->pm).'</a>',
									htmlspecialchars_decode($message->msg_content) ).
									'</p>';
		}
	}
	
}

if($tab=='search'){ 
	
	$table_name = $wpdb->prefix . 'dmeng_tracker';
	
	$sql_where = "FROM $table_name WHERE type='search'";
	
	if( !empty($_GET['delete']) && !empty($_GET['_wp_nonce']) && wp_verify_nonce( trim($_GET['_wp_nonce']), 'delete-pid' ) ){
		$delete_key = urldecode($_GET['delete']);
		$wpdb->query(" DELETE FROM $table_name WHERE pid='$delete_key' ");
	}
	
	$count =  $wpdb->get_var( "SELECT count(ID) ".$sql_where );
	
	$results = $wpdb->get_results( "SELECT pid,traffic ".$sql_where." ORDER BY traffic+0 DESC LIMIT $offset,$limit" );
	if( $results ){
		$output = '<p>'.__('请注意：因为小工具有缓存，所以删除记录并不会马上反映在小工具中。手动更新小工具缓存：重新点击一下保存小工具即可。', 'dmeng').'</p>';
		foreach( $results as $item ){
			$output .= '<p>'.sprintf( __('（关键词）%1$s （搜索次数） %2$s 次 （操作）<a href="%3$s">删除这个记录</a>', 'dmeng'), $item->pid, $item->traffic, add_query_arg(array('delete'=>urlencode($item->pid), '_wp_nonce'=>wp_create_nonce('delete-pid') )) ).'</p>';
		}
	}
	
}

if($tab=='vote'){ 
	
	$table_name = $wpdb->prefix . 'dmeng_meta';
	
	$sql_where = "FROM $table_name WHERE user_id>0 AND meta_key LIKE 'vote_%'";
	
	$count =  $wpdb->get_var( "SELECT count(meta_id) ".$sql_where );
	
	$results = $wpdb->get_results( "SELECT user_id,meta_key,meta_value ".$sql_where." ORDER BY meta_id DESC LIMIT $offset,$limit" );

	if( $results ){
		
		$output = '';
		
		foreach( $results as $item ){
			
			$info = explode('_', $item->meta_key);
			
			$vote = $item->meta_value=='up' ? __('顶', 'dmeng') : __('踩', 'dmeng');
			
			if( $info[1]=='post' ){
				$url = get_permalink($info[2]);
				$title = get_the_title($info[2]);
			}else{
				$url = get_comment_link($info[2]);
				$comment = get_comment($info[2]);
				$comment_author = $comment->user_id ? get_the_author_meta('display_name', $comment->user_id) : $comment->comment_author;
				$title = sprintf( __('%s的评论', 'dmeng'), $comment_author);
			}
			
			$output .= '<p>'.sprintf( __('%1$s %2$s了 %3$s', 'dmeng'), 
									'<a href="'.dmeng_get_user_url('like', $item->user_id).'" target="_blank">'.get_the_author_meta('display_name', $item->user_id).'</a>',
									$vote, 
									'<a href="'.$url.'" target="_blank">'.esc_html($title).'</a>').
									'</p>';
		}
	}
	
}

echo $output;
	?>
	<br>
	<form method="GET">
		<?php printf(__('共有%s条数据。', 'dmeng'), $count);?> <?php _e('从','dmeng');?> <input type="text" name="offset" size="5" value="<?php echo $offset+1;?>"> <?php _e('开始，显示','dmeng');?> <input type="text" name="limit" size="5" value="<?php echo $limit;?>"> <?php _e('条','dmeng');?> <button type="submit" class="button"><?php _e('提交','dmeng');?></button>
	</form>
	
</div>
</div>
	<?php
}
