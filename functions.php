<?php

/*
 * 欢迎来到代码世界，如果你想修改多梦主题的代码，那我猜你是有更好的主意了～
 * 那么请到多梦网络（ http://www.dmeng.net/ ）说说你的想法，数以万计的童鞋们会因此受益哦～
 * 同时，你的名字将出现在多梦主题贡献者名单中，并有一定的积分奖励～
 * 注释和代码同样重要～
 * @author 多梦 @email chihyu@aliyun.com 
 */

/*
 * 模板函数 @author 多梦 at 2014.06.19 
 * 
 */

//~ 全局设置
$dmeng_general_default = array(
		'head_code' => '',
		'head_css' => '',
		'footer_code' => '',
		'header_profile' => 1,
		'navbar_searchform' => 1,
		'float_button' => 1,
		'instantclick' => 0,
		'only_first_cat' => 0,
		'qrcode' => 1,
		'speedup' => array('css', 'js', 'bootstrap', 'instantclick', 'prettify', 'grey_png', 'look'),
);
$dmeng_general_setting = json_decode(get_option('dmeng_general_setting'), true);
$dmeng_general_setting = wp_parse_args( $dmeng_general_setting,  $dmeng_general_default);

function dmeng_setting( $name='' ){
	global $dmeng_general_setting;
	$value = isset($dmeng_general_setting[$name]) ? $dmeng_general_setting[$name] : false;
	return $value;
}

function dmeng_speed_url( $path='' ){
	return apply_filters('dmeng_speed_url', 'http://s.dmeng.net' . $path );
}

function dmeng_script_uri( $name = '' , $speed = 'auto' ){
	
	$uri = apply_filters('dmeng_script_uri', false, $name);
	
	if( $uri !== false ) 
		return $uri;
		
	if( $speed == 'auto' )
		$speed = in_array( ( strpos($name, 'bootstrap') !== false ? 'bootstrap' : $name ) ,dmeng_setting('speedup') );

	$dmeng_script_file_name = 'dmeng-2.0.9.5';

	switch( $name ){

		case 'look' :
			$path = $speed ? '/look/' : '/images/look/';
			break;

		case 'js' :
			$path = $speed ? '/dmeng2.0/js/'.$dmeng_script_file_name.'.js' : '/js/'.$dmeng_script_file_name.'.js';
			break;
			
		case 'bootstrap_css' :
			$path = $speed ? '/bootstrap/3.3.1/css/bootstrap.min.css' : '/css/bootstrap.min.css';
			break;
			
		case 'bootstrap_js' :
			$path = $speed ? '/bootstrap/3.3.1/js/bootstrap.min.js' : '/js/bootstrap.min.js';
			break;
			
		case 'instantclick' :
			$path = $speed ? '/instantclick/3.1.0/instantclick.min.js' : '/js/instantclick.min.js';
			break;
			
		case 'prettify' :
			$path = $speed ? '/google-code-prettify/prettify.js' : '/google-code-prettify/prettify.js';
			break;
			
		case 'grey_png' :
			$path = $speed ? '/grey.png' : '/images/grey.png';
			break;
			
		default :
			$path = $speed ? '/dmeng2.0/css/'.$dmeng_script_file_name.'.css' : '/css/'.$dmeng_script_file_name.'.css';

	}
	
	$dir = $speed ? dmeng_speed_url() : get_stylesheet_directory_uri();

	return $dir . $path;
	
}

function dmeng_enqueue_scripts(){

	wp_register_script( 'bootstrap',  dmeng_script_uri('bootstrap_js'), array( 'jquery' ), '3.3.1' );
	wp_register_script( 'dmeng',  dmeng_script_uri('js'), array( 'jquery', 'bootstrap' ), '2.0.9.5' );
	wp_register_script( 'google_code_prettify',  dmeng_script_uri('prettify'), array(), '4-Mar-2013' );
	
	wp_register_style( 'bootstrap',  dmeng_script_uri('bootstrap_css'), array(), '3.3.1' );
	wp_register_style( 'dmeng',  dmeng_script_uri('css'), array( 'bootstrap' ), '2.0.9.5' );

	wp_enqueue_script('dmeng');

	wp_enqueue_style('dmeng');
	
	if( intval(get_option('dmeng_google_code_prettify',0)) ) wp_enqueue_script('google_code_prettify');

}
add_action('wp_enqueue_scripts', 'dmeng_enqueue_scripts' );

/*
 * 移除WordPress版本信息和默认的canonical链接 @author 多梦 at 2014.06.19 
 * 
 */
remove_action( 'wp_head', 'wp_generator' ); 
remove_action( 'wp_head', 'rel_canonical' );
 
 /* 
 * 通过 after_setup_theme 添加启用多梦主题后要执行的动作 @author 多梦 at 2014.06.19 
 * 
 */
function dmeng_setup() {
	//~ 载入本地化语言文件
	load_theme_textdomain( 'dmeng', get_template_directory() . '/languages' );
	//~ 注册菜单
	register_nav_menus( array(
		'header_menu' => __( '头部菜单', 'dmeng' ),
		'header_right_menu' => __( '头部右侧菜单', 'dmeng' ),
		'link_menu' => __( '链接菜单', 'dmeng' ),
	) );
}
add_action( 'after_setup_theme', 'dmeng_setup' );

//~ 添加文章缩略图 @author 多梦 at 2014.09.04
add_theme_support( 'post-thumbnails', array( 'post', 'gift' ) );
set_post_thumbnail_size( 220, 146, true );

function dmeng_get_the_thumbnail( $size = 'post-thumbnail' ) {
	$post_thumbnail = (array)json_decode(get_option('dmeng_post_thumbnail','{"on":"1","suffix":"?imageView2/1/w/220/h/146/q/100"}'));
	$post_thumbnail_on = intval($post_thumbnail['on']);
	$post_thumbnail_suffix = $post_thumbnail['suffix'];
	if(!in_array($post_thumbnail_on,array(1,2))) return;
	$image_url = '';
	if ( has_post_thumbnail() ) {
		$image_url = wp_get_attachment_image_src( get_post_thumbnail_id() , $size);
		$image_url = $image_url[0];
	} else {
		if($post_thumbnail_on==2){
			global $post, $posts;
			$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
			if($output) $image_url = $matches[1][0];
		}
	}
	if($image_url){
		$image_url = $post_thumbnail_suffix ? $image_url.$post_thumbnail_suffix : $image_url;
		return apply_filters('dmeng_post_thumbnail', $image_url);
	}
}

//~ 自定义头部
add_theme_support( 'custom-header', array(
	'default-image'          => get_bloginfo('template_url').'/images/screenshot_64.png',
	'random-default'         => false,
	'width'                  => 64,
	'height'                 => 64,
	'flex-height'            => true,
	'flex-width'             => true,
	'default-text-color'     => '444444',
	'header-text'            => true,
	'uploads'                => true,
	'admin-preview-callback' => 'dmeng_custom_header_admin_preview'
) );
//~ 自定义背景
add_theme_support( 'custom-background' );

function dmeng_custom_header_admin_preview(){
		echo '<style>
#masthead{font: normal 14px/24px "Microsoft Yahei","冬青黑体简体中文 w3","宋体";float:left;width:100%;}
#masthead .header-logo{margin:0 15px 0 0;}
#masthead .header-logo{float:left;}
#masthead .header-text{color:#444}
#masthead .header-text .name{margin:5px 0 5px;font-size: 24px;font-weight: 500;line-height: 1.1;}
#masthead .header-text .name a{color:#444;text-decoration:none;}
#masthead .header-text .name a:hover{text-decoration:underline;}
#masthead .header-text .description{opacity: 0.9;}
</style>
<div id="masthead">' . dmeng_custom_header().'</div>';
}

function dmeng_custom_header(){
	$logo_html = $header_text = '';
	 
	if( get_header_image() ){
		$custom_header = get_custom_header();

		$logo_data = array();
		$logo_data['url'] = $custom_header->url ? $custom_header->url : get_theme_support( 'custom-header', 'default-image');
		$logo_data['width'] = $custom_header->width ? $custom_header->width : get_theme_support( 'custom-header', 'width');
		$logo_data['height'] = $custom_header->height ? $custom_header->height : get_theme_support( 'custom-header', 'height');

		$logo_html = sprintf(
									'<a href="%4$s" rel="home"><img src="%1$s" width="%2$s" height="%3$s" alt="%5$s" /></a>',
									$logo_data['url'],
									$logo_data['width'],
									$logo_data['height'],
									esc_url(home_url('/')),
									get_bloginfo('name')
								);

		$logo_html = '<div class="header-logo">'.$logo_html.'</div>';
	}

	if(display_header_text()){
		$textcolor = get_header_textcolor();
		$textcolor = in_array($textcolor, array('444', '444444')) ? '' : ' style="color:#'.$textcolor.'"';
		$header_text = '<div class="header-text">';
		$header_text .= sprintf('<div class="name"><a href="%1$s" rel="home" id="name"%2$s>%3$s</a></div>', esc_url( home_url( '/' ) ), $textcolor, get_bloginfo( 'name' ));
		$header_text .= '<div class="description" id="desc"'.$textcolor.'>'.get_bloginfo('description').'</div>';
		$header_text .= '</div>';
	}
	
	return $logo_html . $header_text;
}

//~ 登录用户浏览站点时不显示工具栏 @author 多梦 at 2014.06.19 
add_filter('show_admin_bar', '__return_false');

/*
 * 通过 widgets_init 动作定义侧边栏和小工具 @author 多梦 at 2014.06.19 
 * 
 */
function dmeng_widgets_init() {
	register_sidebar( array(
		'name' => __( '主侧边栏', 'dmeng' ),
		'id' => 'sidebar-1',
		'description' => __( '主要的侧边栏', 'dmeng' ),
		'before_widget' => '<aside id="%1$s" class="'.apply_filters('dmeng_widget_panel_class', 'panel panel-default widget clearfix %2$s').'">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="panel-heading widget-title">',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( '底部边栏', 'dmeng' ),
		'id' => 'sidebar-2',
		'description' => __( '显示在底部', 'dmeng' ),
		'before_widget' => '<aside id="%1$s" class="widget clearfix footer-widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="panel-heading widget-title">',
		'after_title' => '</h3>',
	) );
}
add_action( 'widgets_init', 'dmeng_widgets_init' );

//~ 去除功能小工具的WordPress版权链接
function dmeng_widget_meta_poweredby($link){
	return;
}
add_filter('widget_meta_poweredby','dmeng_widget_meta_poweredby');
 
function dmeng_get_current_page_url(){
	$ssl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true:false;
    $sp = strtolower($_SERVER['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port  = $_SERVER['SERVER_PORT'];
    $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
    $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
    return $protocol . '://' . $host . $port . $_SERVER['REQUEST_URI'];
}

//~ 保存提示
function dmeng_settings_error($type='updated',$message=''){
	$type = $type=='updated' ? 'updated' : 'error';
	if(empty($message)) $message = $type=='updated' ?  __('设置已保存。','dmeng') : __('保存失败，请重试。，','dmeng');
    add_settings_error(
        'dmeng_settings_message',
        esc_attr( 'dmeng_settings_updated' ),
		$message,
		$type
    );
    settings_errors( 'dmeng_settings_message' );
}

//~ 载入 Bootstrap 菜单类
require_once( get_template_directory() . '/inc/bootstrap_navwalker.php' );
//~ 载入用户页面
require_once( get_template_directory() . '/inc/user-page.php' );
//~ 载入文章/页面相关信息面板
require_once( get_template_directory() . '/inc/post-meta.php' );
//~ 载入自定义小工具
require_once( get_template_directory() . '/inc/widget.php' );
//~ 载入积分
require_once( get_template_directory() . '/inc/credit.php' );
//~ 载入评论列表
require_once( get_template_directory() . '/inc/commentlist.php' );
//~ 载入评论meta
require_once( get_template_directory() . '/inc/comment-meta.php' );
//~ 载入安全验证码
require_once( get_template_directory() . '/inc/nonce.php' );
//~ 载入流量统计
require_once( get_template_directory() . '/inc/tracker.php' );
//~ 载入meta（主要用于投票）
require_once( get_template_directory() . '/inc/meta.php' );
//~ 载入投票
require_once( get_template_directory() . '/inc/vote.php' );
//~ 载入提示信息
require_once( get_template_directory() . '/inc/message.php' );
//~ 载入设置页面
require_once( get_template_directory() . '/inc/settings.php' );
//~ 载入开放平台登录
require_once( get_template_directory() . '/inc/open.php' );
//~ 载入邮件
require_once( get_template_directory() . '/inc/mail.php' );
//~ 载入最近用户
require_once( get_template_directory() . '/inc/recent-user.php' );
//~ 载入短代码
require_once( get_template_directory() . '/inc/shortcode.php' );
//~ 载入SEO
require_once( get_template_directory() . '/inc/seo.php' );
//~ 载入广告
require_once( get_template_directory() . '/inc/adsense.php' );
//~ 载入版本
require_once( get_template_directory() . '/inc/version.php' );
//~ 载入积分换礼
if( intval(get_option('dmeng_is_gift_open', 0)) ){
	require_once( get_template_directory() . '/inc/gift.php' );
}

//~ 载入缓存
require_once( get_template_directory() . '/inc/cache.php' );

function dmeng_get_avatar( $id , $size='40' , $type='' , $lazy=true ){

	if($type==='qq'){
		
		$O = array(
			'ID'=>get_option('dmeng_open_qq_id'),
			'KEY'=>get_option('dmeng_open_qq_key')
		);
		
		$U = array(
			'ID'=>get_user_meta( $id, 'dmeng_qq_openid', true ),
			'TOKEN'=>get_user_meta( $id, 'dmeng_qq_access_token', true )
		);
		
		if( $O['ID'] && $O['KEY'] && $U['ID'] && $U['TOKEN'] ){
			$avatar_url = 'http://q.qlogo.cn/qqapp/'.$O['ID'].'/'.$U['ID'].'/100';
		}
		
	}else if($type==='weibo'){
		
		$O = array(
			'KEY'=>get_option('dmeng_open_weibo_key'),
			'SECRET'=>get_option('dmeng_open_weibo_secret')
		);

		$U = array(
			'ID'=>get_user_meta( $id, 'dmeng_weibo_openid', true ),
			'TOKEN'=>get_user_meta( $id, 'dmeng_weibo_access_token', true )
		);
		
		if( $O['KEY'] && $O['SECRET'] && $U['ID'] && $U['TOKEN'] ){
			$avatar_url = 'http://tp1.sinaimg.cn/'.$U['ID'].'/180/1.jpg';
		}
		
	}else{

		$avatar_url = "http://secure.gravatar.com/avatar/" . md5(strtolower( ( is_email($id) ? $id : get_the_author_meta('user_email', $id) ) )) . "?d=" . get_option('avatar_default') . "&r=" . get_option('avatar_rating') . "&s=" . $size;

	}
	
	return $lazy ? '<img src="'.dmeng_script_uri('grey_png').'" data-original="'.$avatar_url.'" class="avatar" width="'.$size.'" height="'.$size.'" />' :  '<img src="'.$avatar_url.'" class="avatar" width="'.$size.'" height="'.$size.'" />';
}

function dmeng_replace_avatar( $avatar, $id_or_email, $size ){

	 if ( is_numeric($id_or_email) ) {

		$user_id = $id_or_email;
	
	} elseif ( is_object($id_or_email) ) {

		$user_id = empty( $id_or_email->user_id ) ? $id_or_email->comment_author_email : $id_or_email->user_id;
			
	}else{
		
		$user_id = email_exists($id_or_email);
		
	}

	//~ 已注册用户使用 dmeng_get_avatar 函数调用头像
	if( is_numeric($user_id) ){
		$dmeng_avatar = get_user_meta( $user_id, 'dmeng_avatar', true );
		$avatar = dmeng_get_avatar( $user_id, $size, $dmeng_avatar, ( is_single() ? 1 : 0 ) );
	}

	//~ 替换为 https 的域名
	$avatar = str_replace(array("www.gravatar.com", "0.gravatar.com", "1.gravatar.com", "2.gravatar.com"), "secure.gravatar.com", $avatar);

	return $avatar;

}
add_filter( 'get_avatar' , 'dmeng_replace_avatar' , 10 , 3 );

function dmeng_get_avatar_type($user_id){
	$id = (int)$user_id;
	if($id===0) return;
	$avatar = get_user_meta($id,'dmeng_avatar',true);
	if( $avatar=='qq' && dmeng_is_open_qq($id) ) return 'qq';
	if( $avatar=='weibo' && dmeng_is_open_weibo($id) ) return 'weibo';
	return 'default';
}

//~ 因为使用了 dmeng_get_avatar 调用头像，为防止后台默认头像选择示例都显示成固定的头像，替换下
function dmeng_default_avatar_select($avatar_list){
	
	global $avatar_defaults;
	
	$avatar = explode('<br />', $avatar_list );
	
	$content = '';
	
	$i = 0;
	foreach( $avatar_defaults as $default_key=>$default_value ){
		$content .= preg_replace( '/<img\s+src="([^"]+)"[^>]+>/i', get_avatar('email@example.com', 32, $default_key), $avatar[$i] ) . '<br />'; 
		$i++;
	}
	return $content;
}
add_filter('default_avatar_select', 'dmeng_default_avatar_select');
/*
 * 作者/发布时间/评论/分类等相关信息 @author 多梦 at 2014.06.20 
 * 
 */
function dmeng_post_meta(){

	$output = '<div class="entry-meta">';

	//~ 字体设置按钮
	if( is_single() || is_page() )
		$output .= apply_filters('dmeng_post_meta_set_font', '<div class="entry-set-font"><span id="set-font-small" class="disabled">A<sup>-</sup></span><span id="set-font-big">A<sup>+</sup></span></div>');
	
	$output .= apply_filters('dmeng_post_meta_author', '<span class="glyphicon glyphicon-user"></span><a href="'.esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ).'" itemprop="author">'.get_the_author().'</a>');

	$output .= apply_filters('dmeng_post_meta_date', '<span class="glyphicon glyphicon-calendar"></span><time class="entry-date" title="'.sprintf( __('发布于 %1$s 最后编辑于 %2$s ', 'dmeng'), get_the_time('Y-m-d H:i:s'), get_the_modified_time('Y-m-d H:i:s') ).'" datetime="'.get_the_date( 'c' ).'"  itemprop="datePublished">'.get_the_date().'</time>');
	
	$output .= apply_filters('dmeng_post_meta_comments_number', '<span class="glyphicon glyphicon-comment"></span><a href="'.get_permalink().'#comments" itemprop="discussionUrl" itemscope itemtype="http://schema.org/Comment"><span itemprop="interactionCount">'.get_comments_number().'</span></a>');
	
	$traffic = get_dmeng_traffic('single', get_the_ID());

	$output .= apply_filters('dmeng_post_meta_traffic', '<span class="glyphicon glyphicon-eye-open"></span>'.sprintf( __( '%s 次浏览', 'dmeng' ) , ( is_singular() ? '<span data-num-views="true">'.$traffic.'</span>' : $traffic) ));

	//~ 如果是文章页则输出分类和标签，因为只有文章才有～
	if( get_post_type()=='post' ) {
		
		if( apply_filters('dmeng_post_meta_cat_show', true) ){
			$categories = get_the_category();
			if($categories){
				foreach($categories as $category) {
					$cats[] = '<a href="'.get_category_link( $category->term_id ).'" rel="category" itemprop="articleSection">'.$category->name.'</a>';
				}
				$output .= apply_filters('dmeng_post_meta_cat', '<span class="glyphicon glyphicon-folder-open"></span>' . join(' | ',$cats) );
			}
		}
		
		if( apply_filters('dmeng_post_meta_tag_show', true) ){
			$tags = get_the_tag_list('<span class="glyphicon glyphicon-tags"></span>',' | ');
			if($tags) $output .= apply_filters('dmeng_post_meta_tag', '<span itemprop="keywords">'.$tags.'</span>');
		}
	}

	$output .= '</div>';
	
	echo $output;

}

function dmeng_post_footer(){
	global $post;
	$post_excerpt = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
	$post_excerpt = str_replace(array("\t", "\r\n", "\r", "\n"), "", strip_tags($post_excerpt)); 

	echo '<div class="entry-footer clearfix" role="toolbar">';
		
		dmeng_vote_html('post',get_the_ID()); dmeng_breadcrumb_html(get_the_ID(),' › ');
		
	echo '</div>';

}

/*
 * 版权声明
 * 
 */

function dmeng_post_copyright($post_id){
	
	$post_id = (int)$post_id;
	if(!$post_id) return;

	if( (int)get_option('dmeng_copyright_status_all',1)===1 && (int)get_post_meta( $post_id, 'dmeng_copyright_status', true )!==9 ){
		$cc = get_post_meta( $post_id, 'dmeng_copyright_content', true );
		$cc = empty($cc) ? get_option('dmeng_copyright_content_default',sprintf(__('原文链接：%s，转发请注明来源！','dmeng'),'<a href="{link}" rel="author">{title}</a>')) : $cc;
		$cc = stripcslashes(htmlspecialchars_decode($cc));
		if($cc){
			
		?><div class="entry-details" itemprop="copyrightHolder" itemtype="http://schema.org/Organization" itemscope>
			<details>
				<summary><?php 
					if($cc){
						$cc = str_replace(array( '{name}', '{url}', '{title}', '{link}'), array(get_bloginfo('name'), home_url('/'), get_the_title($post_id), get_permalink($post_id)), $cc);
						echo $cc;
						}
				?></summary>
			</details>
	</div><?php
		}
	}
}

function dmeng_breadcrumb_output($url,$name){
	return '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="'.esc_url($url).'" title="'.$name.'" itemprop="url"><span itemprop="title">'.$name.'</span></a></span>';
}

function dmeng_get_category_parents( $id, $separator='', $visited = array() ) {
	$chain = '';
	$parent = get_term( $id, 'category' );
	if ( is_wp_error( $parent ) )
		return $parent;

	$name = $parent->name;

	if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
		$visited[] = $parent->parent;
		$chain .= dmeng_get_category_parents( $parent->parent, $separator, $visited );
	}

	$chain .= dmeng_breadcrumb_output( get_category_link( $parent->term_id ), $name).$separator;

	return $chain;
}

function dmeng_breadcrumb_html($post_id,$separator){
	$path[] = dmeng_breadcrumb_output( home_url('/'), get_bloginfo('name'));
	if( get_post_type($post_id)=='post' ) {
		$cats_id = array();
		$categories = get_the_category($post_id);
		if($categories){
			foreach($categories as $category) {
				if(!in_array($category->term_id,$cats_id)){
					if ( $category->parent ){
						$path[] = dmeng_get_category_parents( $category->parent, $separator );
						$cats_id[] = $category->parent;
					}
					$path[] = dmeng_breadcrumb_output( get_category_link( $category->term_id ), $category->name);
					$cats_id[] = $category->term_id;
				}
			}
		}
	}
	if( is_singular() && !is_single() && !is_page() ){
		$post_type = get_post_type();
		$post_type_obj = get_post_type_object( $post_type );
		$path[] = dmeng_breadcrumb_output( get_post_type_archive_link( $post_type ), $post_type_obj->labels->singular_name);
	}
	$path[] = dmeng_breadcrumb_output( get_permalink($post_id), get_the_title($post_id));
	echo join( $separator ,$path);
}

//~ 编辑器样式
function dmeng_mce_css($mce_css){
	if ( ! empty( $mce_css ) ) $mce_css .= ',';
	$mce_css .= dmeng_script_uri('bootstrap_css').','.dmeng_script_uri('css');
	return $mce_css;
}
add_filter( 'mce_css', 'dmeng_mce_css');

//规定摘要字数
function dmeng_excerpt_length( $length ) {
	return 120;
}
add_filter( 'excerpt_length', 'dmeng_excerpt_length', 999 );
//改变摘要结束省略符号
function dmeng_excerpt_more( $more ) {
	return ' ...';
}
add_filter('excerpt_more', 'dmeng_excerpt_more'); 
function dmeng_password_form() {
    global $post;
    $label = 'pwbox-'.( empty( $post->ID ) ? rand() : $post->ID );
    $o = '<form action="' . esc_url( add_query_arg('action','postpass',wp_login_url()) ) . '" method="post" class="form-inline"><ul class="list-inline"><li><input name="post_password" id="' . $label . '" type="password" class="form-control" placeholder="'.__('请输入密码 …','dmeng').'"></li><li><button type="submit" class="btn btn-default" id="searchsubmit">'.__('提交','dmeng').'</button></li></ul><div class="help-block">' . __( '这是一篇受密码保护的文章，您需要提供访问密码。','dmeng' ) . '</div></form>';
    return $o;
}
add_filter( 'the_password_form', 'dmeng_password_form' );
//文章归档分页导航
function dmeng_paginate($wp_query=''){
	if(empty($wp_query)) global $wp_query;
	$pages = $wp_query->max_num_pages;
	if ( $pages >= 2 ):
		$big = 999999999;
		$paginate = paginate_links( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => max( 1, get_query_var('paged') ),
			'total' => $pages,
			'type' => 'array'
		) );
		echo '<ul class="pagination" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">';
		foreach ($paginate as $value) {
			echo '<li itemprop="name">'.$value.'</li>';
		}
		echo '</ul>';
	endif;
}
//文章页上一篇下一篇导航
function dmeng_post_nav(){

	$previous = get_adjacent_post( false, '', true );
	$next = get_adjacent_post( false, '', false );

	if ( ( ! $next && ! $previous ) || is_attachment() ) {
		return;
	}

	?><nav class="pager" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
			<?php
				previous_post_link( '<li class="previous">%link</li>', __( '<span class="text-muted">上一篇：</span> <span itemprop="name">%title</span>', 'dmeng' ), true );
				next_post_link( '<li class="next">%link</li>', __( '<span class="text-muted">下一篇：</span> <span itemprop="name">%title</span>', 'dmeng' ), true );
			?>
	</nav><!-- .navigation --><?php
}
//文章内容分页导航
function dmeng_post_page_nav($echo=true){

	return wp_link_pages( array(
		'before'      => '<nav class="pager" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement" data-instant><span>'.__('分页','dmeng').'</span>',
		'after'       => '</nav>',
		'link_before' => '<span itemprop="name">',
		'link_after'  => '</span>',
		'pagelink' => __('%','dmeng'),
		'echo' => $echo
	) );

}
//评论分页导航
function dmeng_paginate_comments($post_id='',$current='',$max=''){

	global $wp_rewrite;

	if ( !$post_id && ( !is_singular() || !get_option('page_comments') ) )
		return;

	$post_link = $post_id ? get_permalink($post_id) : get_permalink();
	$page = $current ? $current : get_query_var('cpage');
	if ( !$page )
		$page = 1;
	$max_page = $max ? $max : get_comment_pages_count();
	$defaults = array(
		'base' => add_query_arg( 'cpage', '%#%', $post_link ),
		'format' => '',
		'total' => $max_page,
		'current' => $page,
		'echo' => false,
		'add_fragment' => '#comments',
		'mid_size' => 4,
		'prev_next' => false,
	);
	if ( $wp_rewrite->using_permalinks() ){
		$defaults['base'] = user_trailingslashit(trailingslashit($post_link) . 'comment-page-%#%', 'commentpaged');
	}
	
	$page_links = paginate_links( $defaults );

	if ( $max_page >= 2 )
		return '<ul id="pagination-comments" role="navigation" data-max-page="'.$max_page.'" data-no-instant>'.$page_links.'</ul>';
	
}
// 文章目录
function dmeng_article_index($content) {
	$post_index = (int)get_option('dmeng_post_index_all',1);
	if( in_array($post_index,array(1,2,3) ) && ( is_single() || is_page() ) ){
		if( $post_index===2 && is_page() ) return $content;
		if( $post_index===3 && is_single() ) return $content;
		$matches = array();  
		$index_li = $ol = $depth_num = '';
		if(preg_match_all("/<h([2-6]).*?\>(.*?)<\/h[2-6]>/is", $content, $matches)) {

			//~ $matches[0] 是原标题，包括标签，如<h2>标题</h2>
			//~ $matches[1] 是标题层级，如<h2>就是“2”
			//~ $matches[2] 是标题内容，如<h2>标题</h2>就是“标题”
			
			foreach( $matches[1] as $key=>$level ) {

				if( $ol && intval($ol)<$level){
					$index_li .= '<ul>';
					$depth_num = intval($depth_num)+1;
				}

				if( $ol && intval($ol)>$level ){
					$index_li .= '</li>'.str_repeat('</ul></li>', intval($depth_num));
					$depth_num = 0;
				}
				
				$content = str_replace($matches[0][$key], '<h'.$level.' id="'.($key+1).'">'.$matches[2][$key].'</h'.$level.'>', $content);
				if( $ol && intval($ol)==$level) $index_li .= '</li>';
				$index_li .= '<li><a href="#'.($key+1).'">'.$matches[2][$key].'</a>';

				if(($key+1)==count($matches[1])) $index_li .= '</li>'.str_repeat('</ul></li>', intval($depth_num));

				$ol = $level;
			}
			$content = '<div class="article_index"><h5>'.__('文章目录','dmeng').'<span class="caret"></span></h5><ul>' . $index_li . '</ul></div>' . $content;
		}
	}
    return $content;
}
add_filter( "the_content", "dmeng_article_index" );

// canonical
function dmeng_canonical_url(){

	switch(TRUE){
		
		case is_home() :
		case is_front_page() :
			$url = home_url('/');
		break;
		
		case is_single() :
			$url = get_permalink();
		break;
		
		case is_tax() :
		case is_tag() :
		case is_category() :
			$term = get_queried_object(); 
			$url = get_term_link( $term, $term->taxonomy ); 
		break;
		
		case is_post_type_archive() :
			$url = get_post_type_archive_link( get_post_type() ); 
		break;
		
		case is_author() : 
			$url = get_author_posts_url( get_query_var('author'), get_query_var('author_name') ); 
		break;
		
		case is_year() : 
			$url = get_year_link( get_query_var('year') ); 
		break;
		
		case is_month() : 
			$url = get_month_link( get_query_var('year'), get_query_var('monthnum') ); 
		break;
		
		case is_day() : 
			$url = get_day_link( get_query_var('year'), get_query_var('monthnum'), get_query_var('day') ); 
		break;
		
		default :
			$url = dmeng_get_current_page_url();
	}
	
    if ( get_query_var('paged') > 1 ) { 
		global $wp_rewrite; 
		if ( $wp_rewrite->using_permalinks() ) { 
			$url = user_trailingslashit( trailingslashit( $url ) . trailingslashit( $wp_rewrite->pagination_base ) . get_query_var('paged'), 'archive' ); 
		} else { 
			$url = add_query_arg( 'paged', get_query_var('paged'), $url ); 
		}
	}
	
	return $url;

}

function dmeng_get_redirect_uri(){
	if( isset($_GET['redirect_uri']) ) return urldecode($_GET['redirect_uri']);
	if( isset($_GET['redirect_to']) ) return urldecode($_GET['redirect_to']);
	if( isset($_GET['redirect']) ) return urldecode($_GET['redirect']);
	if( isset($_SERVER['HTTP_REFERER']) ) return urldecode($_SERVER['HTTP_REFERER']);
	return home_url();
}

//~ 文章状态从其他到已发布时给作者添加积分和发送邮件通知
function dmeng_publish_post( $new_status, $old_status, $post ){

	if( $new_status == $old_status || $new_status != 'publish' || $post->post_type != 'post' ) return;

	$rec_post_num = (int)get_option('dmeng_rec_post_num','5');
	$rec_post_credit = (int)get_option('dmeng_rec_post_credit','50');
	$rec_post = (int)get_user_meta( $post->post_author, 'dmeng_rec_post', true );
	
	if( $rec_post<$rec_post_num && $rec_post_credit ){
	
		update_dmeng_credit( $post->post_author , $rec_post_credit , 'add' , 'dmeng_credit' , sprintf(__('获得文章投稿奖励%1$s积分','dmeng') ,$rec_post_credit) );
		
	}
	
	update_user_meta( $post->post_author, 'dmeng_rec_post', $rec_post+1);
	
	//~ 发送邮件
	$user_email = get_user_meta($post->post_author, 'dmeng_verify_email', true);
	if( is_email( $user_email )){
		$email_title = sprintf(__('你在%1$s上有新的文章发表','dmeng'),get_bloginfo('name'));
		$email_content = sprintf(__('<h3>%1$s，你好！</h3><p>你的文章%2$s已经发表，快去看看吧！</p>','dmeng'), get_user_by( 'id', $post->post_author )->display_name, '<a href="'.get_permalink($post->ID).'" target="_blank">'.$post->post_title.'</a>');
		dmeng_send_email( $user_email , $email_title, $email_content );
	}

}
add_action(  'transition_post_status', 'dmeng_publish_post', 10, 3 );

//~ 发表评论时给作者添加积分
function dmeng_comment_add_credit($comment_id, $comment_object){
	
	$user_id = $comment_object->user_id;
	
	if($user_id){
		
		$rec_comment_num = (int)get_option('dmeng_rec_comment_num','50');
		$rec_comment_credit = (int)get_option('dmeng_rec_comment_credit','5');
		$rec_comment = (int)get_user_meta( $user_id, 'dmeng_rec_comment', true );
		
		if( $rec_comment<$rec_comment_num && $rec_comment_credit ){
			update_dmeng_credit( $user_id , $rec_comment_credit , 'add' , 'dmeng_credit' , sprintf(__('获得评论回复奖励%1$s积分','dmeng') ,$rec_comment_credit) );
			update_user_meta( $user_id, 'dmeng_rec_comment', $rec_comment+1);
		}
	}
}
add_action('wp_insert_comment', 'dmeng_comment_add_credit' , 99, 2 );

function dmeng_before_delete_post( $post_id ){
	
	global $wpdb;
	$table_tracker = $wpdb->prefix . 'dmeng_tracker';
	
	//~ 删除该文章的浏览数据
	$wpdb->query( " DELETE FROM $table_tracker WHERE type='single' AND pid='$post_id' " );
}
add_action( 'before_delete_post', 'dmeng_before_delete_post' );

function dmeng_delete_user( $user_id ) {
	
	global $wpdb;
	$table_message = $wpdb->prefix . 'dmeng_message';
	$table_meta = $wpdb->prefix . 'dmeng_meta';
	
	//~ 删除该用户的消息数据
	$wpdb->query( " DELETE FROM $table_message WHERE user_id='$user_id' " );
	
	//~ 更新投票数据为游客投票
	$wpdb->query( " UPDATE $table_meta SET user_id = 0 WHERE user_id='$user_id' " );
	
		//~ 发送邮件通知
		$user_email = get_user_meta($post->post_author, 'dmeng_verify_email', true);
		if( is_email( $user_email )){
			$email_title = sprintf(__('你在%1$s上的账号已被注销','dmeng'), get_bloginfo('name'));
			$email_content = sprintf(__('<h3>%1$s，你好！</h3><p>你在%2$s上的账号已被注销！</p>','dmeng'), get_user_by( 'id', $user_id )->display_name, get_bloginfo('name'));
			//~ wp_schedule_single_event( time() + 10, 'dmeng_send_email_event', array( $user_email , $email_title, $email_content ) );
			dmeng_send_email( $user_email , $email_title, $email_content );
		}
	
}
add_action( 'delete_user', 'dmeng_delete_user' );

function dmeng_strip_tags($data){
		return esc_html($data);
}
add_filter( "pre_comment_content", "dmeng_strip_tags" );

function dmeng_get_look(){
	$text = array('[呵呵]', '[嘻嘻]', '[哈哈]', '[可爱]', '[可怜]', '[挖鼻屎]', '[吃惊]', '[害羞]', '[挤眼]', '[闭嘴]', '[鄙视]', '[爱你]', '[泪]', '[偷笑]', '[亲亲]', '[生病]', '[太开心]', '[懒得理你]', '[右哼哼]', '[左哼哼]', '[嘘]', '[衰]', '[委屈]', '[吐]');
	$file = array('hehe.gif', 'xixi.gif', 'haha.gif', 'keai.gif', 'kelian.gif', 'wabishi.gif', 'chijing.gif', 'haixiu.gif', 'jiyan.gif', 'bizui.gif', 'bishi.gif', 'aini.gif', 'lei.gif', 'touxiao.gif', 'qinqin.gif', 'shengbing.gif', 'taikaixin.gif', 'landelini.gif', 'youhengheng.gif', 'zuohengheng.gif', 'xu.gif', 'shuai.gif', 'weiqu.gif', 'tu.gif');
	return array( 'text'=>$text, 'file'=>$file);
}

function dmeng_replace_comment_text($content){

	$look = dmeng_get_look();
	
	$format = ( is_admin() && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) ? '<img class="look" src="%2$s" width="22" height="22" />' : '<img class="look" src="%1$s" data-original="%2$s" width="22" height="22" />';

	foreach( $look['file'] as $file ){
		$html[] = sprintf($format, dmeng_script_uri('grey_png'), dmeng_script_uri('look').$file);
	}

	$content = str_replace($look['text'], $html, $content);
	return $content;
}
add_filter('get_comment_text', 'dmeng_replace_comment_text');

function dmeng_remove_open_sans_from_wp_core() {
	wp_deregister_style( 'open-sans' );
	wp_register_style( 'open-sans', false );
	wp_enqueue_style('open-sans','');
}
add_action( 'init', 'dmeng_remove_open_sans_from_wp_core' );

//~ 上一页下一页和页码的分页导航
function dmeng_pager($current, $max){

	$paged = intval($current);
	$pages = intval($max);
	if($pages<2) return '';

	$pager = '<div class="dmeng-pager clearfix">';

		$pager .= '<div class="btn-group">';
		
			if($paged>1) $pager .= '<a class="btn btn-default" href="' . add_query_arg('page',$paged-1) . '">'.__('上一页','dmeng').'</a>';
			if($paged<$pages) $pager .= '<a class="btn btn-default" href="' . add_query_arg('page',$paged+1) . '">'.__('下一页','dmeng').'</a>';
			
		$pager .= '</div>';
	
		if ($pages>2 ){
			$pager .= '<div class="btn-group pull-right"><select class="form-control pull-right" onchange="document.location.href=this.options[this.selectedIndex].value;">';
				for( $i=1; $i<=$pages; $i++ ){
					$class = $paged==$i ? 'selected="selected"' : '';
					$pager .= sprintf('<option %s value="%s">%s</option>', $class, add_query_arg('page',$i), sprintf(__('第 %s 页', 'dmeng'), $i));
				}
			$pager .= '</select></div>';
		}
	
	$pager .= '</div>';
	
	return $pager;
}

//~ 高亮关键词
function dmeng_highlight_keyword($key, $content){
	$key = addcslashes(trim($key),'\/');
	if(!empty($key)){
		$keys = implode('|', explode(' ', $key));
		$content = preg_replace('/(' . $keys .')/iu', '<em>\0</em>', $content);
	}
	return $content;
}
//~ 在小工具应用短代码
add_filter( 'widget_text', 'do_shortcode' );

//~ 七牛镜像回源UA访问时返回503，拒绝抓取页面（防止镜像可能导致的降权）
function dmeng_redirect_qiniu_spider(){
	if( strpos($_SERVER['HTTP_USER_AGENT'], 'qiniu-imgstg-spider') !== false) {

		header('HTTP/1.1 503 Service Temporarily Unavailable');
		echo 'poor guy';

		//~ $url = parse_url(home_url());
		//~ $host = $url['host'];
		//~ echo "<script> var d = document.domain; var h = window.location.href; var u = h.replace(d, '".$host."'); if(d!=='".$host."'){ window.location.href=u; } </script>";

		exit;
	}
}
add_action('init', 'dmeng_redirect_qiniu_spider');

function dmeng_wp_footer(){

	$script_output = '';

	$general_setting = $GLOBALS['dmeng_general_setting'];
	$instantclick = $general_setting['instantclick'];
	
	//~ 浮动按钮
	if( intval($general_setting['float_button']) ){
		$btn_array = array(
			array(
				'id' => 'goTop',
				'title' => __('去顶部','dmeng'),
				'html' => '<span class="glyphicon glyphicon-arrow-up"></span>'
			),
			array(
				'id' => 'refresh',
				'title' => __('刷新','dmeng'),
				'html' => '<span class="glyphicon glyphicon-repeat"></span>'
			)
		);
		if ( is_single() || is_page() ) {
			$btn_array[] = array(
					'id' => 'goComments',
					'title' => __('评论','dmeng'),
					'html' => '<span class="glyphicon glyphicon-align-justify"></span>'
			);
		}
		if( intval($general_setting['qrcode']) ){
			$btn_array[] = array(
				'id' => 'pageQrcode',
				'title' => __('本页二维码','dmeng'),
				'html' => '<span class="glyphicon glyphicon-qrcode"></span><img class="qrcode" width="130" height="130" src="'.apply_filters('dmeng_qrcode', 'http://open.dmeng.net/qrcode.png?text='.urlencode(esc_url(dmeng_get_current_page_url())) ).'">'
			);
		}
		$btn_array[] = array(
			'id' => 'goBottom',
			'title' => __('去底部','dmeng'),
			'html' => '<span class="glyphicon glyphicon-arrow-down"></span>'
		);
		$btn_output = '<div class="btn-group-vertical" id="floatButton">';
		foreach( $btn_array as $btn ){
			
			$btn_attr = '';
			foreach( $btn as $btn_key=>$btn_value ){
				if($btn_key!='html') $btn_attr .= $btn_key.'="'.$btn_value.'" ';
			}
			$btn_output .= '<button type="button" class="btn btn-default" '.$btn_attr.'>'.$btn['html'].'</button>';
		}
		$btn_output .= '</div>';
		$script_output .= $btn_output;
	}
	
	//~ 多梦主题预设参数
	$script[] = sprintf("var ajaxurl = '%s'", addcslashes(admin_url( '/admin-ajax.php' ), '/') );
	$script[] = sprintf("var isUserLoggedIn = %s", intval(is_user_logged_in()) );
	if(!is_user_logged_in()){
		$script[] = sprintf("var loginUrl = '%s'", addcslashes(wp_login_url(dmeng_get_current_page_url()), '/') );
	}else{
		$script[] = sprintf("var dmengFriend = %s", json_encode(array('title'=>__('请同时按下 Ctrl + C 复制推广链接', 'dmeng'), 'url'=>add_query_arg('fid', get_current_user_id(),dmeng_canonical_url()) )) );
	}
	$script[] = sprintf("var dmengPath = '%s/'", addcslashes(get_bloginfo('template_url'), '/') );
	if( !is_admin() && !is_preview() ) $script[] =  sprintf("var dmengTracker = %s", json_encode(dmeng_tracker_param()) );
	
	$script[] =  sprintf("var dmengInstant = %s", ( $instantclick ? 1 : 0 ) );
	
	$script[] =  sprintf("var dmengTips = %s", json_encode(array('success'=>__('操作成功', 'dmeng'), 'error'=>__('操作失败', 'dmeng'), 'tryagain'=>__('请重试', 'dmeng') ) ) );
	
	$script[] =  sprintf("var dmengCodePrettify = %s", ( ( is_singular() && intval(get_option('dmeng_google_code_prettify',0)) ) ? 1 : 0 ) );

	$script_output .= '<script>'.join(';', $script).';</script>';
	
	//~ 预加载
	if( $instantclick ){
		$instantclick = $instantclick=="mousedown" ? "'mousedown'" : intval($instantclick);
		$script_output .= '<script src="'.dmeng_script_uri('instantclick').'" data-no-instant></script><script data-no-instant>InstantClick.on("change",function(){dmengready()});InstantClick.init('.$instantclick.');</script>';
	}
	
	//~ 百度分享
	if( is_singular() ) $script_output .= '<script src="http://bdimg.share.baidu.com/static/api/js/share.js?cdnversion='.floor(time()/3600).'"></script>';
	
	echo $script_output;
}
add_action('wp_footer', 'dmeng_wp_footer');

function dmeng_wp_head(){
	$general_setting = $GLOBALS['dmeng_general_setting'];
	$head_css = $general_setting['head_css'];
	$head_css = stripslashes(htmlspecialchars_decode($head_css));
	if($head_css) echo '<style type="text/css">'.$head_css.'</style>';
}
add_action('wp_head', 'dmeng_wp_head');

//~ 添加格式
function dmeng_mce_buttons_2( $buttons ) {
	array_unshift( $buttons, 'styleselect' );
	return $buttons;
}
add_filter('mce_buttons_2', 'dmeng_mce_buttons_2');
function dmeng_mce_before_init_insert_formats( $init_array ) {  

	$style_formats = array(  
		array(  
			'title' => __('绿色警告框', 'dmeng'),  
			'block' => 'div',  
			'classes' => 'alert alert-success',
			'wrapper' => true,
		),  
		array(  
			'title' => __('蓝色警告框', 'dmeng'),  
			'block' => 'div',  
			'classes' => 'alert alert-info',
			'wrapper' => true,
		),  
		array(  
			'title' => __('黄色警告框', 'dmeng'),  
			'block' => 'div',  
			'classes' => 'alert alert-warning',
			'wrapper' => true,
		),  
		array(  
			'title' => __('红色警告框', 'dmeng'),  
			'block' => 'div',  
			'classes' => 'alert alert-danger',
			'wrapper' => true,
		), 
		array(  
			'title' => __('巨幕', 'dmeng'),  
			'block' => 'div',  
			'classes' => 'jumbotron',
			'wrapper' => true,
		), 
		array(  
			'title' => __('默认标签', 'dmeng'),  
			'block' => 'span',  
			'classes' => 'label label-default',
			'wrapper' => true,
		), 
		array(  
			'title' => __('深蓝标签', 'dmeng'),  
			'block' => 'span',  
			'classes' => 'label label-primary',
			'wrapper' => true,
		), 
		array(  
			'title' => __('绿色标签', 'dmeng'),  
			'block' => 'span',  
			'classes' => 'label label-success',
			'wrapper' => true,
		), 
		array(  
			'title' => __('蓝色标签', 'dmeng'),  
			'block' => 'span',  
			'classes' => 'label label-info',
			'wrapper' => true,
		), 
		array(  
			'title' => __('黄色标签', 'dmeng'),  
			'block' => 'span',  
			'classes' => 'label label-warning',
			'wrapper' => true,
		), 
		array(  
			'title' => __('红色标签', 'dmeng'),  
			'block' => 'span',  
			'classes' => 'label label-danger',
			'wrapper' => true,
		), 
	);  

	$init_array['style_formats'] = json_encode( $style_formats );  

	return $init_array;  
} 
add_filter( 'tiny_mce_before_init', 'dmeng_mce_before_init_insert_formats' );  

//~ 个人资料页更新函数
function dmeng_profile_pass_update($id, $email, $pass1, $pass2){
	
	//~ 注释掉代码，不允许在前台资料页更改邮件地址
	//~ if( ! is_email($email) ){
		//~ return __('请输入一个有效的电子邮件地址！！！','dmeng');
	//~ }
	//~ 
	//~ $exists_id = email_exists($email);
	//~ if( $exists_id && $exists_id != $id  ){
		//~ return sprintf(__('这个电子邮件地址（%s）已经被使用，请换一个。','dmeng'),  $email);
	//~ }
	
	$data = array();
	$data['ID'] = $id;
	//~ $data['user_email'] = $email;
	
	if( !empty($pass1) && !empty($pass2) ){
		if( $pass1 !== $pass2 ){
			return __('两次输入的密码不一致！！！','dmeng');
		}
		
		$data['user_pass'] = sanitize_text_field($pass1);
	}

	$user_id = wp_update_user( $data );
	
	if ( ! is_wp_error( $user_id ) ){
		return __('安全信息已更新','dmeng');
	}
	
	return false;
}

//~ 多梦 ajax 通用 check nonce
function dmeng_ajax_check_nonce($action, $query_arg){
	if( !check_ajax_referer( ( $action ? $action : 'check-nonce' ), ( $query_arg ? $query_arg : 'wp_nonce' ), false ) ){
		die( 'NonceIsInvalid' );
	}
}
add_action('dmeng_before_ajax', 'dmeng_ajax_check_nonce', 10, 2);

//~ 当附件有密码时，不要提供预览内容
function dmeng_private_attachment($p){
	if( post_password_required() ) $p = '';
	return $p;
}
add_filter('prepend_attachment', 'dmeng_private_attachment');
//~ 附件添加密码设置
function dmeng_add_attachment_field( $form_fields, $post ) {
    $field_value = $post->post_password;
    $form_fields['post_password'] = array(
        'value' => $field_value ? $field_value : '',
        'label' => __( '密码', 'dmeng' ),
        'helps' => __( '设置密码访问，无需加密留空即可' )
    );
    return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'dmeng_add_attachment_field', 10, 2 );
function dmeng_save_attachment_field($post, $attachment){
	if( isset( $attachment['post_password'] ) ){
		$post['post_password'] = $attachment['post_password'];
	}
	return $post;
}
add_filter('attachment_fields_to_save', 'dmeng_save_attachment_field', 10, 2);

//~ 注册登录用户名检查
function dmeng_check_blacklist($username){
	
	$black_list = trim(get_option('dmeng_black_list'), '|');

	if( !empty($black_list) ){

		$black_list = explode('|', $black_list);
			
		if( in_array($username, $black_list) ) return true;

	}
	
	return false;
}

function dmeng_check_authentication($username){

	if( dmeng_check_blacklist($username) ){
		wp_die( sprintf( __( '该%s已被系统保留，不能用于注册或登录！', 'dmeng' ), ( is_email($username) ? __( '邮箱地址', 'dmeng' ) : __( '用户名', 'dmeng' ) ) ), '', array( 'response'=>503, 'back_link'=>true ) );
	}

}
add_action ('wp_authenticate' , 'dmeng_check_authentication');
add_action ('register_post' , 'dmeng_check_authentication');

//~ 自动隐藏评论中邮箱
function dmeng_auto_hide_comment_email($content, $comment){
	if( intval(get_option('dmeng_hide_comment_email', 1)) ){
		if( !current_user_can('moderate_comments') && !current_user_can('edit_post', $comment->comment_post_ID) && get_current_user_id()!=$comment->user_id ){
			$content = preg_replace('#([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '<abbr title="'.__('只有评论/文章作者或更高权限的用户才可见','dmeng').'">'.__('邮箱已被自动隐藏','dmeng').'</abbr>', $content);
		}
	}
	return $content;
}
add_filter( 'get_comment_text', 'dmeng_auto_hide_comment_email', 10, 2 );

function dmeng_home_setting(){
	return json_decode(get_option('dmeng_home_setting','{"cat":"[]","cat_list":"2","cat_desc":"","post":"1","post_title":"","ignore_sticky_posts":"1","sticky_posts_title":"{title}","post_exclude":"[]"}'));
}

//~ 首页文章列表内容定制
function dmeng_home_posts( $query ) {
    if ( $query->is_home() && $query->is_main_query() ) {
		
		$home_setting = dmeng_home_setting();
		$home_post = intval($home_setting->post);
		
		if($home_post){

			$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

			$home_ignore_sticky_posts = intval($home_setting->ignore_sticky_posts);
			$home_post = intval($home_setting->post);
			$home_post_exclude = (array)$home_setting->post_exclude;

			$query->set( 'ignore_sticky_posts', $home_ignore_sticky_posts);
			
			//~ 如果置顶文章单独显示在分类上面了，这下面的列表就排除置顶文章吧～
			if($home_ignore_sticky_posts===2)
				$query->set( 'post__not_in', get_option( 'sticky_posts' ));
			
			$query->set( 'paged', $paged);
			
			if($home_post_exclude)
				$query->set( 'category__not_in', $home_post_exclude);

			if($home_post===1)
				$query->set( 'orderby', 'date');
			
			if($home_post===2)
				$query->set( 'orderby', 'modified');
				
			if($home_post===3)
				$query->set( 'orderby', 'comment_count');
				
		}
    }
}
add_action( 'pre_get_posts', 'dmeng_home_posts' );

function dmeng_only_first_cat_callback($value){
	
	if( ( is_home() || is_archive() ) && !is_category() && dmeng_setting('only_first_cat') ){
	
		if( current_filter()=='dmeng_post_meta_cat_show' )
			return false;
			
		if( current_filter()=='dmeng_the_title' ){
			$category = get_the_category(); 
			if($category) $value = '<span class="first-cat"><a href="'.get_category_link( $category[0]->term_id ).'">'.$category[0]->cat_name . '<span class="glyphicon glyphicon-chevron-right"></span></a></span>' . $value;
		}

	}
	
	return $value;
}
add_filter( 'dmeng_post_meta_cat_show', 'dmeng_only_first_cat_callback' );
add_filter( 'dmeng_the_title', 'dmeng_only_first_cat_callback' );

/**
 * 将评论中包含黑名单内容时移入回收站改为禁止提交
 * https://www.ilxtx.com/wordpress-automatically-refuse-spam-comments.html
 */
function lxtx_fuck_spam_comments($comment) {
    if (wp_blacklist_check($comment['comment_author'], $comment['comment_author_email'], $comment['comment_author_url'], $comment['comment_content'], $comment['comment_author_IP'], $comment['comment_agent'])) {
        header("Content-type: text/html; charset=utf-8");
        wp_die('不好意思，您的评论违反了本站的评论规则！');
    } else {
        return $comment;
    }
}
add_filter('preprocess_comment', 'lxtx_fuck_spam_comments');