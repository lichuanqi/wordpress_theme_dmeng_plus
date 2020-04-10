<?php

/*
 * 欢迎来到代码世界，如果你想修改多梦主题的代码，那我猜你是有更好的主意了～
 * 那么请到多梦网络（ http://www.dmeng.net/ ）说说你的想法，数以万计的童鞋们会因此受益哦～
 * 同时，你的名字将出现在多梦主题贡献者名单中，并有一定的积分奖励～
 * 注释和代码同样重要～
 * @author 多梦 @email chihyu@aliyun.com 
 */

/*
 * 
 * 积分商城
 * 
 */

function dmeng_gift_init(){
    register_post_type( 'gift', array(
      'public' => true,
      'has_archive' => true,
      'exclude_from_search' => true,
      'register_meta_box_cb' => 'dmeng_register_gift_meta_box',
      'supports' => array( 'title', 'editor', 'thumbnail', 'comments' ),
      'labels'  => array(
		'name'               => __('积分换礼', 'dmeng'),
		'singular_name'      => __('积分换礼', 'dmeng'),
		'menu_name'          => __('积分换礼', 'dmeng'),
		'name_admin_bar'     => __('积分换礼', 'dmeng'),
		'add_new'            => __('添加礼品', 'dmeng'),
		'add_new_item'       => __('添加礼品', 'dmeng'),
		'new_item'           => __('新礼品', 'dmeng'),
		'edit_item'          => __('编辑礼品', 'dmeng'),
		'view_item'          => __('查看礼品', 'dmeng'),
		'all_items'          => __('全部礼品', 'dmeng'),
		'search_items'       => __('搜索礼品', 'dmeng'),
		'parent_item_colon'  => __('礼品上级：', 'dmeng'),
		'not_found'          => __('没有找到礼品', 'dmeng'),
		'not_found_in_trash' => __('回收站里没有礼品', 'dmeng'),
	)
    ) );
    //~ 礼品页重写规则
    add_rewrite_rule(
        'gift/([0-9]+)?$',
        'index.php?post_type=gift&p=$matches[1]',
        'top' );
    //~ 礼品页重写规则（文章分页）
    add_rewrite_rule(
        'gift/([0-9]+)/([0-9]+)?$',
        'index.php?post_type=gift&p=$matches[1]&page=$matches[2]',
        'top' );
    //~ 礼品页重写规则（评论分页）
    add_rewrite_rule(
        'gift/([0-9]+)/comment-page-([0-9]+)?$',
        'index.php?post_type=gift&p=$matches[1]&cpage=$matches[2]',
        'top' );
	register_taxonomy( 'gift_tag', 'gift', array(
		'label'          => __('礼品目录', 'dmeng'),
		'hierarchical'          => true,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'query_var'             => true
	) );
}
add_action( 'init', 'dmeng_gift_init' );

function dmeng_gift_permalink( $link, $post = 0 ){
    if ( $post->post_type == 'gift' && get_option('permalink_structure') ){
        return home_url( 'gift/' . $post->ID );
    } else {
        return $link;
    }
}
add_filter('post_type_link', 'dmeng_gift_permalink', 10, 2);

function dmeng_get_express_array(){
	return array( 
				1 => __('无须物流', 'dmeng'),
				2 => __('包邮', 'dmeng'),
				3 => __('邮费自付', 'dmeng')
			);
}

function dmeng_register_gift_meta_box(){
	
	foreach( array( 'info'=>__('礼品信息', 'dmeng'), 'content'=>__('礼品内容', 'dmeng') ) as $gift_key=>$gift_title ){
		add_meta_box(
			'dmeng_gift_meta_'.$gift_key,
			$gift_title,
			'dmeng_gift_meta_callback_'.$gift_key,
			'gift'
		);
	}

	function dmeng_gift_meta_callback_info($post){
		
		wp_nonce_field( 'dmeng_gift_meta_box', 'dmeng_gift_meta_box_nonce' );

		$gift = json_decode(get_post_meta( $post->ID, 'dmeng_gift_info', true ));

		if( !$gift ){
			$gift = json_decode('{"price":"99","stock":"99","max":"1","express":"1","content":"","attachment":[]}');
		}

		$credit = intval(get_post_meta( $post->ID, 'dmeng_gift_credit', true ));

		$info_items = array(
			array(
				'name' => '价格',
				'slug' => 'price',
				'value' => $gift->price,
				'excerpt' => '礼品市场价，单位是人民币“元”'
			),
			array(
				'name' => '积分',
				'slug' => 'credit',
				'value' => $credit,
				'excerpt' => '兑换需要积分，0 代表免费'
			),
			array(
				'name' => '库存',
				'slug' => 'stock',
				'value' => $gift->stock,
				'excerpt' => '礼品数量，0 代表无限量'
			),
			array(
				'name' => '最多',
				'slug' => 'max',
				'value' => $gift->max,
				'excerpt' => '每人最多兑换的数量，0 代表无限量'
			)
		);
		
		$output = '<style>
		#dmeng_gift_info label{margin-right:8px;}
		#dmeng_gift_content{margin: 0;height: 4em;width: 98%;}
		#dmeng_gift_attachment li{line-height:36px;border:1px solid #eee;}
		#dmeng_gift_attachment li label{background: #f5f5f5;padding: 5px 8px;margin: 0 8px;}
		#dmeng_gift_attachment li .delete{margin: 0 8px;border: 1px solid;padding: 3px 6px;}
		</style>
		<div id="dmeng_gift_info">';
		
		$output .= sprintf( '<p>%s</p>', __('填写信息只能是数字，文字会被转为 0', 'dmeng') );
		foreach( $info_items as $info_item ){
			$output .= sprintf( '<p><label for="%2$s">%1$s</label><input name="%2$s" id="%2$s" type="text" value="%3$s"> %4$s</p>' , $info_item['name'] , 'dmeng_gift_'.$info_item['slug'] , $info_item['value'] , $info_item['excerpt'] );
		}

		$express = dmeng_get_express_array();

		$output .= '<p><label for="dmeng_gift_express">'.__('物流', 'dmeng').'</label><select name="dmeng_gift_express" id="dmeng_gift_express">';
		
		foreach( $express as $express_key=>$express_title){
			$output .= sprintf( '<option value="%s" %s>%s</option>', $express_key, ( $gift->express==$express_key ? 'selected="selected"' : '' ), $express_title );
		}

		$output .= '</select></p>';
		
		$output .= '</div>';
		
		echo $output;

	}
	
	function dmeng_gift_meta_callback_content($post){
		
		$gift = json_decode(get_post_meta( $post->ID, 'dmeng_gift_info', true ));

		if( !$gift ){
			$gift = json_decode('{"price":"99","credit":"0","stock":"99","max":"1","express":"1","content":"","attachment":[]}');
		}
		
		echo '<p>'.__('以下内容在用户使用积分兑换后可见，礼品介绍请放在文章内容中。注意：编辑后需要保存文章才可保存', 'dmeng').'</p>';
		wp_editor( stripslashes(htmlspecialchars_decode($gift->content)), 'dmeng_gift_content', array( 'media_buttons' => false ) );
		
		$output = '<p><a class="button select_gift_attachment" href="javascript:;" data-uploader_title="'.__('选择附件', 'dmeng').'" data-uploader_button_text="'.__('添加附件', 'dmeng').'">'.__('添加附件', 'dmeng').'</a></p>';
		$output .= '<p>'.__('附件地址会被加密，请不要泄露真实地址！名称是附件的标题，所有人都可见。注意：本功能只适合小文件传输。', 'dmeng').'</p>';
		$output .= '<ul id="dmeng_gift_attachment">';
		if($gift->attachment && is_array($gift->attachment)){
			foreach($gift->attachment as $gift_attachment){
				$output .= '<li><a href="javascript:;" class="delete">'.__('删除', 'dmeng').'</a><input type="hidden" name="dmeng_gift_attachment[]" value="'.$gift_attachment.'"><label>ID</label>'.$gift_attachment.'<label>'.__('名称', 'dmeng').'</label><a href="'.get_edit_post_link($gift_attachment).'" target="_blank">'.get_the_title($gift_attachment).'</a></li>';
			}
		}
		$output .= '</ul>';
		echo $output;

?>
<script>
jQuery('#dmeng_gift_attachment .delete').live('click', function( event ){
	 jQuery( this ).parent('li').remove();
});

var file_frame;
jQuery('.select_gift_attachment').live('click', function( event ){
 
	event.preventDefault();

    if ( file_frame ) {
      file_frame.open();
      return;
    }

    file_frame = wp.media.frames.file_frame = wp.media({
      title: jQuery( this ).data( 'uploader_title' ),
      button: {
        text: jQuery( this ).data( 'uploader_button_text' ),
      },
      multiple: false
    });
 
    file_frame.on( 'select', function() {
		attachment = file_frame.state().get('selection').first().toJSON();
		jQuery('#dmeng_gift_attachment').append('<li><a href="javascript:;" class="delete"><?php _e('删除', 'dmeng');?></a><input type="hidden" name="dmeng_gift_attachment[]" value="'+attachment.id+'"><label>ID</label>'+attachment.id+'<label><?php _e('名称', 'dmeng');?></label><a href="'+attachment.editLink+'" target="_blank">'+attachment.title+'</a></li>');
    });
 
    file_frame.open();
});
</script>
<?php
	}
	

}
	function dmeng_gift_save_meta_box_data( $post_id ) {

		if ( ! isset( $_POST['dmeng_gift_meta_box_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['dmeng_gift_meta_box_nonce'], 'dmeng_gift_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		$gift_data = array();

		foreach( array('price', 'stock', 'max', 'express') as $info_item ){
			if ( isset( $_POST['dmeng_gift_'.$info_item] ) ) $gift_data[$info_item] =  intval($_POST['dmeng_gift_'.$info_item]);
		}

		if ( isset( $_POST['dmeng_gift_content'] ) ) $gift_data['content'] =  htmlspecialchars($_POST['dmeng_gift_content']);
		if ( isset( $_POST['dmeng_gift_attachment'] ) ) $gift_data['attachment'] =  (array)$_POST['dmeng_gift_attachment'];
		
		$credit = isset( $_POST['dmeng_gift_credit'] ) ? intval($_POST['dmeng_gift_credit']) : 0;
		
		update_post_meta( $post_id, 'dmeng_gift_credit', $credit);
		
		update_post_meta( $post_id, 'dmeng_gift_info', wp_slash(json_encode(wp_parse_args( $gift_data, array(
			'price' => 99,
			'credit' => $credit,
			'stock' => 99,
			'max' => 1,
			'express' => 1,
			'content' => '',
			'attachment' => array()
		) ))));

	}
	add_action( 'save_post', 'dmeng_gift_save_meta_box_data' );


//~ 添加礼品分类/最大积分/最小积分链接参数
function dmeng_gift_queryvars( $vars ){
  $vars[] = 't';
  $vars[] = 'max';
  $vars[] = 'min';
  return $vars;
}
add_filter('query_vars', 'dmeng_gift_queryvars' );

function dmeng_gift_archive_page( $query ) {
	
	global $wp_query;
	
    if( !is_admin() && $query->is_main_query() && is_post_type_archive('gift') ) {
		
       $query->set( 'posts_per_page', intval(get_option('dmeng_gift_num', 12)) );
       
       $gift_status[] = 'publish';
       if( intval(get_option('dmeng_is_gift_future', 1)) ) $gift_status[] = 'future';
       $query->set( 'post_status', $gift_status );
       
		if( !empty($wp_query->query_vars['t']) ){
			$terms[] = intval($wp_query->query_vars['t']);
			$query->set( 'tax_query', array(
				array(
					'taxonomy' => 'gift_tag',
					'field' => 'id',
					'terms' => $terms
				)
			) );
		}
		
       return;
    }

}
add_action( 'pre_get_posts', 'dmeng_gift_archive_page' );

function dmeng_gift_archive_page_posts_where($where){
	 global $wp_query, $wpdb;
	 $max =  $min = 0;
	 if(isset($wp_query->query_vars['max'])) $max = intval($wp_query->query_vars['max']);
	 if(isset($wp_query->query_vars['min'])) $min = intval($wp_query->query_vars['min']);

    if( !is_admin() && is_main_query() && is_post_type_archive('gift') && ($max || $min) ){
		$where .= " AND ( ($wpdb->postmeta.meta_key = 'dmeng_gift_credit' ";
		if($max) $where .= " AND ( $wpdb->postmeta.meta_value + 0 ) <= $max ";
		if($min) $where .= " AND ( $wpdb->postmeta.meta_value + 0 ) >= $min ";
		$where .= " ) ) ";
	}
	return $where;
}
add_filter( 'posts_where' , 'dmeng_gift_archive_page_posts_where' );

function dmeng_gift_archive_page_posts_join($join) {
    global $wp_query, $wpdb;

    if( !is_admin() && is_main_query() && is_post_type_archive('gift') && (!empty($wp_query->query_vars['max']) || !empty($wp_query->query_vars['min'])) ){
        $join .= "LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
    }

    return $join;
}
add_filter('posts_join', 'dmeng_gift_archive_page_posts_join');

function dmeng_gift_archive_page_posts_groupby($groupby) {
   global $wp_query, $wpdb;
   
    if( !is_admin() && is_main_query() && is_post_type_archive('gift') && (!empty($wp_query->query_vars['max']) || !empty($wp_query->query_vars['min'])) ){
        $groupby = "{$wpdb->posts}.ID";
    }
    
    return $groupby;
}
add_filter( 'posts_groupby', 'dmeng_gift_archive_page_posts_groupby' );

function dmeng_gift_nav_class($classes, $item){
	if( is_singular('gift') || is_post_type_archive('gift') ){
		$gift_url = array(
			home_url( '?post_type=gift' ),
			get_post_type_archive_link('gift')
		);
		if( in_array($item->url, $gift_url) && !in_array('active', $classes) ){
			$classes[] = 'active';
		}
	}
     return $classes;
}
add_filter('nav_menu_css_class' , 'dmeng_gift_nav_class' , 10 , 2);

//~ 查询兑换信息
function get_dmeng_user_gifts( $uid=0, $count=0, $limit=0, $offset=0 ){

	$uid = intval($uid);
	
	if( !$uid ) return;

	global $wpdb;
	$table_name = $wpdb->prefix . 'dmeng_meta';
	
	if($count){
		$check = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE `meta_key` LIKE 'exchange_%' AND user_id='$uid' " );
	}else{
		$check = $wpdb->get_results( "SELECT meta_key,meta_value FROM $table_name WHERE `meta_key` LIKE 'exchange_%' AND user_id='$uid' ORDER BY meta_id DESC LIMIT $offset,$limit" );
	}

	if(isset($check))
		return $check;

	return 0;

}

//~ 添加兑换信息到dmeng_meta
function dmeng_exchange($pid, $uid){
	return add_dmeng_meta( 'exchange_'.$pid, current_time('mysql'), intval($uid) );
}

//~ 兑换 ajax callback
function dmeng_exchange_ajax_callback(){

	do_action( 'dmeng_before_ajax', false, false );

	$pid = intval($_POST['post_id']);
	
	$current_user = wp_get_current_user();
	$uid = $current_user->ID;
	
	if( $pid==0 || $uid==0 ) return;

	$data = array();

	$buyers = (array)json_decode(get_post_meta( $pid, 'dmeng_gift_buyers', true ));
	
	$gift = json_decode(get_post_meta( $pid, 'dmeng_gift_info', true ));
	$max = intval($gift->max);
	$stock = intval($gift->stock);
	
	if($stock!=0 && $stock<= count($buyers)){
		
		$data['error'] = __('库存不足！', 'dmeng');
		
	}else{

		$count_buyers = array_count_values($buyers);
		$exchange_num = isset($count_buyers[$uid]) ? intval($count_buyers[$uid]) : 0;

		if( $max!=0 && $max<= $exchange_num){
			
			$data['error'] = __('你已达到最大兑换数，不能再兑换！', 'dmeng');
			
		}else{
			
			$credit = intval(get_user_meta($uid, 'dmeng_credit', true));
			$gift_credit = intval($gift->credit);
			if($gift_credit > $credit){
				
				$data['error'] = __('你的积分余额不足！', 'dmeng');
				
			}else{

				$buyers[] = $uid;
				
				$gift_title = esc_html(get_the_title($pid));
				
				update_post_meta( $pid, 'dmeng_gift_buyers', json_encode($buyers) );
				if( $gift_credit > 0 ) dmeng_credit_to_void( $uid , $gift_credit, sprintf(__('花费了%1$s积分兑换%2$s','dmeng') ,$gift_credit, $gift_title) );
				dmeng_exchange( $pid, $uid );
				
				//~ 通知内容
				$m_headline = sprintf( __('恭喜，你已成功兑换<a href="%1$s" target="_blank">%2$s</a>','dmeng'), get_permalink($pid), $gift_title );
				//~ 发邮件通知
				if(is_email($current_user->dmeng_verify_email)){
					$m_content = '<h3>'.sprintf( __('%1$s，你好！','dmeng'), $current_user->display_name ).'</h3><p>'.$m_headline.'<br/>'.sprintf( __('温馨提示：%s','dmeng'), get_option('dmeng_gift_tips', __('兑换成功后请留意信息通知，如有兑换后可见的内容可直接查看。', 'dmeng')) ).'</p>';
					dmeng_send_email( $current_user->dmeng_verify_email, strip_tags($m_headline), $m_content );
				}

				//~ 站内信息通知
				add_dmeng_message( $uid, 'unread', current_time('mysql'), __('礼品兑换通知', 'dmeng'), $m_headline);

				$data['success'] = __('兑换成功！', 'dmeng');

			}
		}
	
	}
	
	header('Content-type: application/json');
	echo json_encode($data);
	
	die();
}
add_action( 'wp_ajax_dmeng_exchange_ajax', 'dmeng_exchange_ajax_callback' );

function dmeng_get_exchange_key($user_id, $gift_id, $time){
	return $user_id .'-'. $gift_id .'-'. strtotime($time) .'-'. substr( wp_hash( $user_id . $gift_id . $time . NONCE_KEY ), -12, 10 );
}

function dmeng_gift_api_template(){
	if( !empty($_GET['api']) && $_GET['api']=='check' && is_post_type_archive('gift') ){
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php if ( function_exists( 'language_attributes' ) && function_exists( 'is_rtl' ) ) language_attributes(); else echo "dir='$text_direction'"; ?>>
<head>
	<meta name="robots" content="none">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php _e('兑换信息查询', 'dmeng'); ?></title>
	<style type="text/css">
		html {
			background: #f1f1f1;
		}
		body {
			background: #fff;
			color: #444;
			font-family: "Open Sans", sans-serif;
			margin: 2em auto;
			padding: 1em 2em;
			max-width: 700px;
			-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
			box-shadow: 0 1px 3px rgba(0,0,0,0.13);
		}
		h1 {
			border-bottom: 1px solid #dadada;
			clear: both;
			color: #666;
			font: 24px "Open Sans", sans-serif;
			margin: 30px 0 20px 0;
			padding: 0;
			padding-bottom: 7px;
		}
		body {
			margin-top: 50px;
		}
		p {
			font-size: 14px;
			line-height: 1.5;
			margin: 25px 0 20px;
		}
		ul li {
			margin-bottom: 10px;
			font-size: 14px ;
		}
		a {
			color: #21759B;
			text-decoration: none;
		}
		a:hover {
			color: #D54E21;
		}
		.button {
			background: #f7f7f7;
			border: 1px solid #cccccc;
			color: #555;
			display: inline-block;
			text-decoration: none;
			font-size: 13px;
			line-height: 26px;
			height: 28px;
			margin: 0;
			padding: 0 10px 1px;
			cursor: pointer;
			-webkit-border-radius: 3px;
			-webkit-appearance: none;
			border-radius: 3px;
			white-space: nowrap;
			-webkit-box-sizing: border-box;
			-moz-box-sizing:    border-box;
			box-sizing:         border-box;

			-webkit-box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0,0,0,.08);
			box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0,0,0,.08);
		 	vertical-align: top;
		}

		.button.button-large {
			height: 29px;
			line-height: 28px;
			padding: 0 12px;
		}

		.button:hover,
		.button:focus {
			background: #fafafa;
			border-color: #999;
			color: #222;
		}

		.button:focus  {
			-webkit-box-shadow: 1px 1px 1px rgba(0,0,0,.2);
			box-shadow: 1px 1px 1px rgba(0,0,0,.2);
		}

		.button:active {
			background: #eee;
			border-color: #999;
			color: #333;
			-webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
		 	box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
		}

	</style>
</head>
<body>

<?php

		$tips = array(
			'text' => __('请输入唯一识别码进行查询，格式如：1-62-1417634028-62ec07f451', 'dmeng'),
			'color' => '#444'
		);
		
		$code = !empty($_POST['code']) ? wp_slash(trim($_POST['code'])) : '';

if( isset($_POST['_wp_nonce']) ){
	if ( wp_verify_nonce( trim($_POST['_wp_nonce']), 'check-nonce' ) ){

		$explode = explode('-', $code);
					
		if( !empty($code) && count($explode)!=4 ){
				$tips = array(
					'text' => __('错误：唯一识别码格式不正确', 'dmeng'),
					'color' => '#a94442'
					);
		}
		
		$data = array();
		
		if( count($explode)==4 ){
			$exdate = date( 'Y-m-d H:i:s', $explode[2] );
			if( $code == dmeng_get_exchange_key( $explode[0], $explode[1], $exdate ) ){
				if(dmeng_meta_exists($explode[0], 'exchange_'.$explode[1], $exdate)){
	
						$gift_info = json_decode(get_post_meta( $explode[1], 'dmeng_gift_info', true ));
						if( $gift_info ){
							
							$user = get_userdata($explode[0]);
							if( $user==false ){
								
							$tips = array(
								'text' => __('错误：没有找到对应的用户', 'dmeng'),
								'color' => '#a94442'
								);
								
							}else{
								$tips = array(
									'text' => __('兑换信息如下', 'dmeng'),
									'color' => '#3c763d'
									);
								
								$data[] = __('兑换人：', 'dmeng') . '（<a href="'.get_author_posts_url( $explode[0] ).'" target="_blank">' .__('个人主页', 'dmeng').'</a>）' . 
													( is_email($user->dmeng_verify_email) ? $user->dmeng_verify_email : __('没有验证邮箱', 'dmeng') );
								
								$data[] = __('兑换时间：', 'dmeng') . $exdate;
								
								$data[] = __('礼品标题：', 'dmeng') . '<a href="'.get_permalink( $explode[1] ).'" target="_blank">' . esc_html(get_the_title($explode[1])).'</a>';
									
								$data[] = __('市场价格：', 'dmeng') . sprintf("%.2f", intval($gift_info->price));
								
								$credit = intval($gift_info->credit)==0 ? __('免费', 'dmeng') : intval($gift_info->credit);
								$data[] = __('所需积分：', 'dmeng') . $credit;
								
								$express = dmeng_get_express_array();
								$data[] = __('物流配送：', 'dmeng') . $express[intval($gift_info->express)];
							}
						}else{
							$tips = array(
								'text' => __('错误：没有找到对应的礼品信息', 'dmeng'),
								'color' => '#a94442'
								);
						}
						
				}else{

				$tips = array(
					'text' => __('错误：没有找到对应的兑换信息', 'dmeng'),
					'color' => '#a94442'
					);
				}
			}else{
				$tips = array(
					'text' => __('错误：唯一识别码没有通过验证，你确定这正确吗？', 'dmeng'),
					'color' => '#a94442'
					);
			}
		}
	}else{
				$tips = array(
					'text' => __('错误：身份验证无法通过，请重试！', 'dmeng'),
					'color' => '#a94442'
					);
	}
}

$check_url = add_query_arg('api', 'check', get_post_type_archive_link( 'gift' ) );
?>

	<h1><?php _e('兑换信息查询', 'dmeng');?></h1>

	<form action="<?php echo $check_url;?>" method="POST">
		
		<input type="hidden" name="_wp_nonce" value="<?php echo wp_create_nonce('check-nonce');?>">
		
		<input type="text" size="25" name="code" id="code" class="button" value="<?php echo $code;?>" autocomplete="off" required> 
		<button type="submit" class="button"><?php _e('查询', 'dmeng');?></button>
		<a href="<?php echo $check_url;?>" class="button"><?php _e('刷新本页', 'dmeng');?></a>
		<p style="color:<?php echo $tips['color'];?>"><?php echo $tips['text'];?></p>
		
		<?php
		
			if(!empty($data)) echo '<ul><li>'.join('</li><li>', $data).'</li></ul>';
		
		?>
		<p><?php _e('本功能主要用于校验兑换礼品用户身份，每个识别码都是唯一的，私密的。', 'dmeng');?></p>
		<p><a href="<?php echo home_url();?>"><?php echo __('&laquo; 返回', 'dmeng').get_bloginfo('name');?></a></p>
		
	</form>
	
</body>
</html>
<?php
	die();
	}
}
add_action('template_redirect', 'dmeng_gift_api_template');
