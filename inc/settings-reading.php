<?php

/*
 * 欢迎来到代码世界，如果你想修改多梦主题的代码，那我猜你是有更好的主意了～
 * 那么请到多梦网络（ http://www.dmeng.net/ ）说说你的想法，数以万计的童鞋们会因此受益哦～
 * 同时，你的名字将出现在多梦主题贡献者名单中，并有一定的积分奖励～
 * 注释和代码同样重要～
 * @author 多梦 @email chihyu@aliyun.com 
 */
 
/*
 * 主题设置页面 - 阅读 @author 多梦 at 2014.06.23 
 * 
 */

function dmeng_options_reading_page(){
	
	$general_default = $GLOBALS['dmeng_general_default'];
	
  if( isset($_POST['action']) && sanitize_text_field($_POST['action'])=='update' && wp_verify_nonce( trim($_POST['_wpnonce']), 'check-nonce' ) ) :

	update_option( 'dmeng_copyright_status_all', intval($_POST['copyright_status_all']) );
	update_option( 'dmeng_post_index_all', intval($_POST['post_index_all']) );

	update_option( 'dmeng_post_thumbnail', json_encode(array(
		'on' => intval($_POST['post_thumbnail_on']),
		'suffix' => $_POST['post_thumbnail_suffix']
	)) );

	update_option( 'dmeng_adsense_archive', json_encode(array(
		'top' => htmlspecialchars($_POST['adsense_archive_top']),
		'bottom' => htmlspecialchars($_POST['adsense_archive_bottom'])
	)));
	
	update_option( 'dmeng_adsense_author', json_encode(array(
		'top' => htmlspecialchars($_POST['adsense_author_top']),
		'bottom' => htmlspecialchars($_POST['adsense_author_bottom'])
	)));
	
	update_option( 'dmeng_adsense_single', json_encode(array(
		'top' => htmlspecialchars($_POST['adsense_single_top']),
		'comment' => htmlspecialchars($_POST['adsense_single_comment']),
		'bottom' => htmlspecialchars($_POST['adsense_single_bottom'])
	)));
	
	update_option( 'dmeng_google_code_prettify', intval($_POST['google_code_prettify']) );
	
	update_option( 'dmeng_general_setting', json_encode(wp_parse_args(
		wp_parse_args(
			array(
				'only_first_cat' => intval($_POST['only_first_cat'])
			),
			json_decode(get_option('dmeng_general_setting'), true)
		),
		$general_default
	)) );

    dmeng_settings_error('updated');
	  
  endif;
  
  $copyright_status = intval(get_option('dmeng_copyright_status_all',1));
  $post_index = intval(get_option('dmeng_post_index_all',1));
  $post_thumbnail = json_decode(get_option('dmeng_post_thumbnail','{"on":"1","suffix":"?imageView2/1/w/220/h/146/q/100"}'), true);
  $post_thumbnail_on = intval($post_thumbnail['on']);
  $post_thumbnail_suffix = $post_thumbnail['suffix'];
  
	$adsense_archive = json_decode(get_option('dmeng_adsense_archive','{"top":"","bottom":""}'));
	$adsense_author = json_decode(get_option('dmeng_adsense_author','{"top":"","bottom":""}'));
	$adsense_single = json_decode(get_option('dmeng_adsense_single','{"top":"","comment":"","bottom":""}'));

	$google_code_prettify = intval(get_option('dmeng_google_code_prettify',0));

	$general_setting = json_decode(get_option('dmeng_general_setting'), true);
	$general_setting = wp_parse_args( $general_setting,  $general_default);
	
	$only_first_cat = $general_setting['only_first_cat'];

	$option = new DmengOptionsOutput();

	?>
<div class="wrap">
	<h2><?php _e('多梦主题设置','dmeng');?></h2>
	<form method="post">
		<input type="hidden" name="action" value="update">
		<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
		<?php 
		
		dmeng_admin_tabs('reading');
		
		$option->table( array(
			array(
				'type' => 'select',
				'th' => __('版权声明开关','dmeng'),
				'before' => '<p>'.__('开关网站的版权声明（选择关闭将全部不显示，无论文章页怎么设置）','dmeng').'</p>',
				'key' => 'copyright_status_all',
				'value' => array(
					'default' => array($copyright_status),
					'option' => array(
						1 => __( '显示', 'dmeng' ),
						0 => __( '不显示', 'dmeng' )
					)
				)
			),
			array(
				'type' => 'select',
				'th' => __('锚点导航开关','dmeng'),
				'before' => '<p>'.__('开关文章的锚点导航（选择关闭将全部不显示，无论文章页怎么设置）','dmeng').'</p>',
				'key' => 'post_index_all',
				'value' => array(
					'default' => array($post_index),
					'option' => array(
						1 => __( '全部都显示', 'dmeng' ),
						2 => __( '只在文章页显示', 'dmeng' ),
						3 => __( '只在页面显示', 'dmeng' ),
						0 => __( '不显示', 'dmeng' )
					)
				)
			),
			array(
				'type' => 'select',
				'th' => __('文章缩略图','dmeng'),
				'before' => '<p>'.__('在列表页显示文章缩略图（推荐设置220x146特色图像）','dmeng').'</p>',
				'key' => 'post_thumbnail_on',
				'value' => array(
					'default' => array($post_thumbnail_on),
					'option' => array(
						1 => __( '只显示特色图像', 'dmeng' ),
						2 => __( '没有特色图像时显示文章的第一张图片', 'dmeng' ),
						0 => __( '不显示', 'dmeng' )
					)
				)
			),
			array(
				'type' => 'input',
				'th' => __('缩略图地址后缀','dmeng'),
				'before' => '<p>'.__('常用于缩略图处理。使用七牛云存储的童鞋直接使用默认值 ?imageView2/1/w/220/h/146/q/100 即可','dmeng').'</p>',
				'key' => 'post_thumbnail_suffix',
				'value' => $post_thumbnail_suffix
			),
			array(
				'type' => 'select',
				'th' => __('高亮代码','dmeng'),
				'before' => '<p>'.__('在内容页面载入 Google Code Prettify 高亮代码。启用后将自动高亮&lt;code&gt;标签中的内容，除此之外你也可以手动给元素添加 prettyprint CSS类实现代码高亮，支持 &lt;pre&gt; &lt;code&gt; &lt;xmp&gt; 三种标签，如&lt;pre class="prettyprint"&gt;。','dmeng').'</p>',
				'key' => 'google_code_prettify',
				'value' => array(
					'default' => array($google_code_prettify),
					'option' => array(
						1 => __( '启用', 'dmeng' ),
						0 => __( '禁用', 'dmeng' )
					)
				)
			),
			array(
				'type' => 'select',
				'th' => __('只显示一个分类','dmeng'),
				'before' => '<p>'.__('启用后归档页（分类页除外）META信息不显示分类，只在文章标题前显示第一个分类','dmeng').'</p>',
				'key' => 'only_first_cat',
				'value' => array(
					'default' => array($only_first_cat),
					'option' => array(
						1 => __( '启用', 'dmeng' ),
						0 => __( '不启用', 'dmeng' )
					)
				)
			),
		) );
		?>
		<h3 class="title"><?php _e('广告','dmeng');?></h3>
		<p><?php _e('广告条最大宽度为732px','dmeng');?></p>
		<?php
		$option->table( array(
			array(
				'type' => 'textarea',
				'th' => __('归档页','dmeng'),
				'before' => '<p>'.__('分类/标签/搜索/日期归档页顶部','dmeng').'</p>',
				'key' => 'adsense_archive_top',
				'value' => stripslashes(htmlspecialchars_decode($adsense_archive->top))
			),
			array(
				'type' => 'textarea',
				'th' => '',
				'before' => '<p>'.__('分类/标签/搜索/日期归档页底部','dmeng').'</p>',
				'key' => 'adsense_archive_bottom',
				'value' => stripslashes(htmlspecialchars_decode($adsense_archive->bottom))
			),
			array(
				'type' => 'textarea',
				'th' => __('作者页','dmeng'),
				'before' => '<p>'.__('作者页顶部','dmeng').'</p>',
				'key' => 'adsense_author_top',
				'value' => stripslashes(htmlspecialchars_decode($adsense_author->top))
			),
			array(
				'type' => 'textarea',
				'th' => '',
				'before' => '<p>'.__('作者页底部','dmeng').'</p>',
				'key' => 'adsense_author_bottom',
				'value' => stripslashes(htmlspecialchars_decode($adsense_author->bottom))
			),
			array(
				'type' => 'textarea',
				'th' => __('内容页','dmeng'),
				'before' => '<p>'.__('文章/页面/附件页顶部','dmeng').'</p>',
				'key' => 'adsense_single_top',
				'value' => stripslashes(htmlspecialchars_decode($adsense_single->top))
			),
			array(
				'type' => 'textarea',
				'th' => '',
				'before' => '<p>'.__('文章/页面/附件页评论框上方','dmeng').'</p>',
				'key' => 'adsense_single_comment',
				'value' => stripslashes(htmlspecialchars_decode($adsense_single->comment))
			),
			array(
				'type' => 'textarea',
				'th' => '',
				'before' => '<p>'.__('文章/页面/附件页底部','dmeng').'</p>',
				'key' => 'adsense_single_bottom',
				'value' => stripslashes(htmlspecialchars_decode($adsense_single->bottom))
			)
		));
		?>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( '保存更改', 'dmeng' );?>"></p>
	</form>
</div>
	<?php
}
