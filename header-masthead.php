<?php
/*
 * 欢迎来到代码世界，如果你想修改多梦主题的代码，那我猜你是有更好的主意了～
 * 那么请到多梦网络（ http://www.dmeng.net/ ）说说你的想法，数以万计的童鞋们会因此受益哦～
 * 同时，你的名字将出现在多梦主题贡献者名单中，并有一定的积分奖励～
 * 注释和代码同样重要～
 * @author 多梦 @email chihyu@aliyun.com 
 */

$general_setting = $GLOBALS['dmeng_general_setting'];
$header_profile_display = intval($general_setting['header_profile']);

$header_content = '';

$custom_header = dmeng_custom_header();

if( $custom_header ){
	
	$header_content = '<div class="container header-content"><div class="row">';
	$header_content .= '<div class="'.( $header_profile_display ? 'col-lg-8 col-md-7 col-sm-6 col-xs-12' : 'col-lg-12 col-md-12 col-sm-12 col-xs-12' ).'">'.$custom_header.'</div>';
	
	if($header_profile_display){
	
	$profile_li = '';

	$current_user = wp_get_current_user();

	if( $current_user->exists() ){
		$author_url = get_edit_profile_url($current_user->ID);
		$avatar_url = dmeng_get_avatar( $current_user->ID , '54' , dmeng_get_avatar_type($current_user->ID), false );
		
		$profile_li .= '<li class="clearfix">'.sprintf(__('<a href="%1$s" class="name" title="%2$s">%2$s</a>，你好！', 'dmeng'), get_edit_profile_url($current_user->ID), $current_user->display_name) . 
			'<a href="javascript:;" class="friend">推广 &raquo;</a>' . 
			( current_user_can( 'manage_options' ) ? '<a href="'.admin_url().'" target="_blank">'.__('管理','dmeng').' &raquo;</a>' : '<a href="'.add_query_arg('action','new',dmeng_get_user_url('post')).'" target="_blank">'.__('投稿','dmeng').' &raquo;</a>') . 
			'<a href="'.wp_logout_url(dmeng_get_current_page_url()).'" title="'.esc_attr__('Log out of this account').'" data-no-instant>' .
			__('Log out &raquo;') . 
			'</a></li>';
			
		$unread_count = intval(get_dmeng_message($current_user->ID, 'count', "( msg_type='unread' OR msg_type='unrepm' )"));
		$unread_count = $unread_count ? sprintf(__('(%s)', 'dmeng'), $unread_count) : '';
		
		$profile_tabs = array(
			'post' => __('文章', 'dmeng'),
			'comment' => __('评论', 'dmeng'),
			'like' => __('赞', 'dmeng'),
			'credit' => __('积分', 'dmeng'),
			'gift' => __('礼品', 'dmeng'),
			'message' => __('消息', 'dmeng').$unread_count
		);
		
		$profile_tabs_output = '';
		foreach( $profile_tabs as $tab_key=>$tab_title ){
			$tab_attr_title = sprintf(__('查看我的%s', 'dmeng'), $tab_title);
			$profile_tabs_output .= sprintf('<a href="%1$s" title="%2$s">%3$s</a>', dmeng_get_user_url($tab_key), $tab_attr_title, $tab_title);
		}

		$profile_li .= '<li class="tabs">'.$profile_tabs_output.'</li>';
	}else{
		
		$weekname = date( 'l', current_time( 'timestamp', 0 ) );
		$weekarray = array(
			'Monday' => __('星期一', 'dmeng'),
			'Tuesday' => __('星期二', 'dmeng'),
			'Wednesday' => __('星期三', 'dmeng'),
			'Thursday' => __('星期四', 'dmeng'),
			'Friday' => __('星期五', 'dmeng'),
			'Saturday' => __('星期六', 'dmeng'),
			'Sunday' => __('星期天', 'dmeng'),
		);

		$profile_li .= '<li class="date">'.sprintf( __('今天是%1$s，%2$s', 'dmeng'), date( __(' Y 年 m 月 d 日', 'dmeng'), current_time( 'timestamp', 0 ) ), $weekarray[$weekname]).'</li>';

		$author_url = 'javascript:;';
		$avatar_url = '';
		
		$login_methods[] = array(
			'key' => 'wordpress',
			'name' => __( '本地' , 'dmeng' ),
			'url' => wp_login_url(dmeng_get_current_page_url())
		);
		
		if(dmeng_is_open_qq()){
			$login_methods[] = array(
				'key' => 'qq',
				'name' => __( 'QQ' , 'dmeng' ),
				'url' => dmeng_get_open_login_url('qq', 'login', dmeng_get_current_page_url())
			);
		}
		if(dmeng_is_open_weibo()){
			$login_methods[] = array(
				'key' => 'weibo',
				'name' => __( '微博' , 'dmeng' ),
				'url' => dmeng_get_open_login_url('weibo', 'login', dmeng_get_current_page_url())
			);
		}
		
		$login_methods_output = '';
		foreach( $login_methods as $login_method ){
			$login_methods_output .= sprintf('<a href="%1$s" class="%2$s" title="%3$s" rel="nofollow" data-no-instant></a>', $login_method['url'], $login_method['key'], sprintf( '使用%s帐号登录',$login_method['name']));
		}

		$profile_li .= '<li class="login clearfix"><span>'.__('你好，请登录！', 'dmeng').'</span>'.$login_methods_output.'</li>';

	}
	
	$email_tips = '';
	if( ! is_email($current_user->user_email) ){
		$email_tips =  'data-toggle="tooltip" title="'.__('请添加正确的邮箱以保证账户安全','dmeng').'"';
		$author_url .= '#pass';
	}
	if( $current_user->user_email != $current_user->dmeng_verify_email ){
		$email_tips =  'data-toggle="tooltip" title="'.__('请验证一个邮箱用以接收通知','dmeng').'"';
		$author_url .= '#pass';
	}
	
	$avatar_html = $avatar_url ? sprintf('<a href="%s" class="thumbnail avatar"%s>%s</a>', $author_url, $email_tips, $avatar_url) : '';

	$profile_html = '<ul class="user-profile clearfix">'.$profile_li.'</ul>';

	$header_content .= '<div class="col-lg-4 col-md-5 col-sm-6 col-xs-12"><div class="header-profile">'.$avatar_html . $profile_html.'</div></div>';

	}

	$header_content .= '</div></div>';

}


 ?>
<header id="masthead" itemscope itemtype="http://schema.org/WPHeader">
	<?php echo $header_content;?>
	<div class="navbar navbar-default navbar-static-top" role="banner">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".header-navbar-collapse"><span class="sr-only"><?php _e('切换菜单','dmeng');?></span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>
				<?php
				$brand_class[] = 'navbar-brand';
				if(!$custom_header){
					$brand_class[] = 'show';
				}
				$blogname = get_option('blogname');
				$blogname =  ( is_home() || is_front_page() ) ? '<h1>'.$blogname.'</h1>' : $blogname;
				printf('<a class="%1$s" href="%2$s" rel="home" itemprop="headline">%3$s</a>', join(' ', $brand_class), esc_url(home_url('/')), $blogname);
				?>
			</div>
			<nav id="navbar" class="collapse navbar-collapse header-navbar-collapse" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
			<?php
				// 载入菜单
				//  wp_bootstrap_navwalker 是 /inc/wp_bootstrap_navwalker.php 的类，已在functions.php载入
				
				// 主菜单
				if ( has_nav_menu( 'header_menu' ) ) {
					wp_nav_menu( array(
						'menu'              => 'header_menu',
						'theme_location'    => 'header_menu',
						'depth'             => 0,
						'container'         => '',
						'container_class'   => '',
						'menu_class'        => 'nav navbar-nav',
						'items_wrap' 		=> '<ul class="%2$s">%3$s</ul>',
						'walker'            => new Dmeng_Bootstrap_Menu()
					)	);
				}

				// 右侧菜单
				if ( has_nav_menu( 'header_right_menu' ) ) {
					wp_nav_menu( array(
						'menu'              => 'header_right_menu',
						'theme_location'    => 'header_right_menu',
						'depth'             => 0,
						'container'         => '',
						'container_class'   => '',
						'menu_class'        => 'nav navbar-nav navbar-right',
						'items_wrap' 		=> '<ul class="%2$s">%3$s</ul>',
						'walker'            => new Dmeng_Bootstrap_Menu()
					)	);
				}

				if( $general_setting['navbar_searchform'] ) {
					$searchform_float = intval($general_setting['navbar_searchform'])==2 ? 'navbar-right' : 'navbar-left';
			?>
			<form class="navbar-form <?php echo $searchform_float;?>" role="search" method="get" id="searchform" action="<?php echo home_url('/');?>">
				<div class="form-group">
					<input type="text" class="form-control" placeholder="<?php _e('搜索 &hellip;','dmeng');?>" name="s" id="s" required>
				</div>
				<button type="submit" class="btn btn-default" id="searchsubmit"><span class="glyphicon glyphicon-search"></span></button>
			</form>
			<?php } ?>
			</nav><!-- #navbar -->
		</div>
	</div>
</header><!-- #masthead -->
