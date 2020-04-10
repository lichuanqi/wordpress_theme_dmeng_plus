<?php

/*
 * 欢迎来到代码世界，如果你想修改多梦主题的代码，那我猜你是有更好的主意了～
 * 那么请到多梦网络（ http://www.dmeng.net/ ）说说你的想法，数以万计的童鞋们会因此受益哦～
 * 同时，你的名字将出现在多梦主题贡献者名单中，并有一定的积分奖励～
 * 注释和代码同样重要～
 * @author 多梦 @email chihyu@aliyun.com 
 */

/*
 * 主题设置页面
 * 
 */

function dmeng_options_general_page(){
	
	$general_default = $GLOBALS['dmeng_general_default'];

  if( isset($_POST['action']) && sanitize_text_field($_POST['action'])=='update' && wp_verify_nonce( trim($_POST['_wpnonce']), 'check-nonce' ) ) :

	update_option( 'zh_cn_l10n_icp_num', sanitize_text_field($_POST['zh_cn_l10n_icp_num']) );

	update_option( 'dmeng_general_setting', json_encode(wp_parse_args(
		wp_parse_args(
			array(
				'head_code' => htmlspecialchars($_POST['head_code']),
				'head_css' => htmlspecialchars($_POST['head_css']),
				'footer_code' => htmlspecialchars($_POST['footer_code']),
				'header_profile' => intval($_POST['header_profile']),
				'navbar_searchform' => intval($_POST['navbar_searchform']),
				'float_button' => intval($_POST['float_button']),
				'qrcode' => intval($_POST['qrcode']),
				'instantclick' => ( $_POST['instantclick']=='mousedown' ? $_POST['instantclick'] : intval($_POST['instantclick']) ),
				'speedup' => ( empty($_POST['speedup']) ? array() : $_POST['speedup'] )
			),
			json_decode(get_option('dmeng_general_setting'), true)
		),
		$general_default
	)) );

	update_option( 'dmeng_black_list', sanitize_text_field($_POST['black_list']) );

	dmeng_settings_error('updated');
	  
  endif;
  
	$general_setting = json_decode(get_option('dmeng_general_setting'), true);
	$general_setting = wp_parse_args( $general_setting,  $general_default);
	
	$instantclick = $general_setting['instantclick'];
	
	$header_profile = intval($general_setting['header_profile']);
	$navbar_searchform = intval($general_setting['navbar_searchform']);
	$float_button = intval($general_setting['float_button']);
	$qrcode = intval($general_setting['qrcode']);
	$head_code = $general_setting['head_code'];
	$head_css = $general_setting['head_css'];
	$footer_code = $general_setting['footer_code'];
	
	$speedup = $general_setting['speedup'];

	$head_code = stripslashes(htmlspecialchars_decode($head_code));
	$head_css = stripslashes(htmlspecialchars_decode($head_css));
	$footer_code = stripslashes(htmlspecialchars_decode($footer_code));

	?>
<div class="wrap">
	<h2><?php _e('多梦主题设置','dmeng');?></h2>
	<form method="post">
		<input type="hidden" name="action" value="update">
		<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
		<?php 
		
		dmeng_admin_tabs();
		
		$option = new DmengOptionsOutput();
		$option->table( array(
			array(
				'type' => 'input',
				'th' => __('ICP','dmeng'),
				'key' => 'zh_cn_l10n_icp_num',
				'value' => get_option('zh_cn_l10n_icp_num')
			),
			array(
				'type' => 'textarea',
				'th' => __('头部HEAD代码','dmeng'),
				'before' => '<p>'.__('如添加meta信息验证网站所有权。','dmeng').'</p>',
				'key' => 'head_code',
				'value' => $head_code
			),
			array(
				'type' => 'textarea',
				'th' => __('脚部统计代码','dmeng'),
				'before' => '<p>'.__('放置统计代码或安全网站认证小图标等。注意：如果是JS代码请添加一个data-no-instant属性，如&lt;script type="text/javascript" data-no-instant&gt; 。','dmeng').'</p>',
				'key' => 'footer_code',
				'value' => $footer_code
			),
			array(
				'type' => 'textarea',
				'th' => __('自定义CSS','dmeng'),
				'before' => '<p>'.__('以下内容会被放置在&lt;style&gt;标签之内，无需输入&lt;style type="text/css"&gt;和&lt;/style&gt;','dmeng').'</p>',
				'key' => 'head_css',
				'value' => $head_css
			),
			array(
				'type' => 'select',
				'th' => __('顶部资料卡','dmeng'),
				'before' => '<p>'.__('是否显示右上角用户登录&个人资料展示（假如显示顶部的话）','dmeng').'</p>',
				'key' => 'header_profile',
				'value' => array(
					'default' => array($header_profile),
					'option' => array(
						1 => __( '显示', 'dmeng' ),
						0 => __( '不显示', 'dmeng' )
					)
				)
			),
			array(
				'type' => 'select',
				'th' => __('导航条搜索框','dmeng'),
				'before' => '<p>'.__('选择导航条搜索框的显示方式','dmeng').'</p>',
				'key' => 'navbar_searchform',
				'value' => array(
					'default' => array($navbar_searchform),
					'option' => array(
						1 => __( '靠左显示', 'dmeng' ),
						2 => __( '靠右显示', 'dmeng' ),
						0 => __( '不显示', 'dmeng' )
					)
				)
			),
			array(
				'type' => 'select',
				'th' => __('是否显示浮动按钮','dmeng'),
				'before' => '<p>'.__('选择是否显示到顶部、刷新、到底部等浮动按钮','dmeng').'</p>',
				'key' => 'float_button',
				'value' => array(
					'default' => array($float_button),
					'option' => array(
						1 => __( '显示', 'dmeng' ),
						0 => __( '不显示', 'dmeng' )
					)
				)
			),
			array(
				'type' => 'select',
				'th' => __('是否显示二维码','dmeng'),
				'before' => '<p>'.__('选择是否在浮动按钮中显示二维码','dmeng').'</p>',
				'key' => 'qrcode',
				'value' => array(
					'default' => array($qrcode),
					'option' => array(
						1 => __( '显示', 'dmeng' ),
						0 => __( '不显示', 'dmeng' )
					)
				)
			),
			array(
				'type' => 'input',
				'th' => __('预加载','dmeng'),
				'before' => '<p>'.__('InstantClick 功能仅供测试，没有开发经验的请保持默认值 0 ，否则会出错！！！','dmeng').'</p>',
				'key' => 'instantclick',
				'value' => $instantclick
			),
			array(
				'type' => 'checkbox',
				'th' => __('加速主题资源','dmeng'),
				'before' => '<p>'.__('使用 s.dmeng.net 的CDN服务器加速主题的静态资源，如果你不知道是什么意思，保持默认就好','dmeng').'</p>',
				'key' => 'speedup',
				'value' => array(
					'default' => $speedup,
					'option' => array(
						'css' => '主题的CSS文件', 
						'js' => '主题的JS文件', 
						'bootstrap' => 'Bootstrap的CSS和JS文件', 
						'instantclick' => 'InstantClick的JS文件', 
						'prettify' => 'Google Code Prettify的JS文件', 
						'grey_png' => 'grey.png 填充图片', 
						'look' => '表情图片', 
					)
				)
			)
		) );

		?>
		<h3><?php _e( '登录安全', 'dmeng' );?></h3>
		<?php

		$option->table( array(
			array(
				'type' => 'input',
				'th' => __('黑名单','dmeng'),
				'key' => 'black_list',
				'after' => '<p>'.__('请使用 | 分隔开，而且 | 前后都不要留空格。请慎重！黑名单里的用户名都不能用来注册，也不能登录。<span style="color:#a94442">推荐把管理员用户名添加进来，然后使用邮箱登录，此举可以大量减少暴力破解可能会浪费的服务器资源。</span>','dmeng').'</p>',
				'value' => get_option( 'dmeng_black_list')
			),
		) );
		?>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( '保存更改', 'dmeng' );?>"></p>
	</form>
</div>
	<?php
}
