<?php

/*
 * 欢迎来到代码世界，如果你想修改多梦主题的代码，那我猜你是有更好的主意了～
 * 那么请到多梦网络（ http://www.dmeng.net/ ）说说你的想法，数以万计的童鞋们会因此受益哦～
 * 同时，你的名字将出现在多梦主题贡献者名单中，并有一定的积分奖励～
 * 注释和代码同样重要～
 * @author 多梦 @email chihyu@aliyun.com 
 */
 
/*
 * 主题设置页面 - SMTP @author 多梦 at 2014.06.23 
 * 
 */

function dmeng_options_smtp_page(){
	
  if( isset($_POST['action']) && sanitize_text_field($_POST['action'])=='update' && wp_verify_nonce( trim($_POST['_wpnonce']), 'check-nonce' ) ) :

	update_option('dmeng_smtp',json_encode(array(
		'option' => $_POST['dmeng_smtp_option'],
		'host' => $_POST['dmeng_smtp_host'],
		'ssl' => $_POST['dmeng_smtp_ssl'],
		'port' => $_POST['dmeng_smtp_port'],
		'user' => $_POST['dmeng_smtp_user'],
		'pass' => $_POST['dmeng_smtp_pass'],
		'name' => $_POST['dmeng_smtp_name'],
	)));

	dmeng_settings_error('updated');
	  
  endif;

	$smtp = json_decode(get_option('dmeng_smtp','{"option":"0","host":"","ssl":"0","port":"25","user":"","pass":"","name":""}'));
	$open = intval($smtp->option);
	$host = sanitize_text_field($smtp->host);
	$ssl = intval($smtp->ssl);
	$port = intval($smtp->port);
	$user = sanitize_text_field($smtp->user);
	$pass = sanitize_text_field($smtp->pass);
	$name = empty($smtp->name) ? get_bloginfo('name') : sanitize_text_field($smtp->name);


	if( isset($_POST['action']) && sanitize_text_field($_POST['action'])=='test' && wp_verify_nonce( trim($_POST['_wpnonce']), 'check-nonce' ) ) {
	
		if($open && $host && $user && $pass){
			
			if(is_email($_POST['dmeng_test_email'])){
				
				$result = wp_mail( $_POST['dmeng_test_email'],  __('多梦主题SMTP测试邮件','dmeng'),  __('恭喜，SMTP发信成功！','dmeng') );

				if ($result) {
					
					dmeng_settings_error('updated', __('发送成功，请查收！','dmeng'));
					
				}else{
					
					global $phpmailer;

					dmeng_settings_error('error', __('发送失败','dmeng').$phpmailer->ErrorInfo );

				}

			}else{
				
				dmeng_settings_error('error', __('没有发送邮件，测试收信邮件地址不正确！','dmeng'));
				
			}
			
		}else{
			
			dmeng_settings_error('error', __('嗯，这个只是测试多梦主题SMTP发信的，但是你没有开启或者说没有配置好，所以没有发送邮件，请先检查你的SMTP设置！','dmeng'));
			
		}
		
	}

	dmeng_admin_tabs('smtp');
		
	$option = new DmengOptionsOutput();

	?>
<div class="wrap">
	<h2><?php _e('多梦主题设置','dmeng');?></h2>
	<?php if(function_exists('stream_socket_client')==false) { ?>
		<p style="color:#a94442">
			<?php echo __('WordPress是使用 stream_socket_client 函数实现SMTP发信的，但是看样子你的主机并不支持，建议你打开这个函数。','dmeng') . ( function_exists('fsockopen') ? __('如果不能打开，你的主机支持 fsockopen 函数，你也可以考虑使用 fsockopen 函数代替，不过这会有点麻烦，因为WordPress并没有内置接口可以修改，只能直接修改源代码（这样每次升级WordPress之后都需要重新修改一次），修改方法可以<a href="http://www.dmeng.net/tougao-20141208.html" target="_blank">参考这里</a>。 ','dmeng') : '');?>
		</p>
	<?php } ?>
	<form method="post">
		<input type="hidden" name="action" value="update">
		<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
		<?php 

		$option->table( array(
			array(
				'type' => 'select',
				'th' => __('启用SMTP','dmeng'),
				'key' => 'dmeng_smtp_option',
				'value' => array(
					'default' => array(intval($open)),
					'option' => array(
						1 => __( '启用', 'dmeng' ),
						0 => __( '禁用', 'dmeng' )
					)
				)
			),
			array(
				'type' => 'input',
				'th' => __('发信服务器','dmeng'),
				'key' => 'dmeng_smtp_host',
				'value' => $host
			),
			array(
				'type' => 'select',
				'th' => __('启用SSL','dmeng'),
				'key' => 'dmeng_smtp_ssl',
				'value' => array(
					'default' => array($ssl),
					'option' => array(
						1 => __( '启用', 'dmeng' ),
						0 => __( '禁用', 'dmeng' )
					)
				)
			),
			array(
				'type' => 'input',
				'th' => __('端口号','dmeng'),
				'key' => 'dmeng_smtp_port',
				'value' => $port
			),
			array(
				'type' => 'input',
				'th' => __('发信账号','dmeng'),
				'key' => 'dmeng_smtp_user',
				'value' => $user
			),
			array(
				'type' => 'input-password',
				'th' => __('账号密码','dmeng'),
				'key' => 'dmeng_smtp_pass',
				'value' => $pass
			),
			array(
				'type' => 'input',
				'th' => __('显示昵称','dmeng'),
				'key' => 'dmeng_smtp_name',
				'value' => $name
			)
		) );
		?>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( '保存更改', 'dmeng' );?>"></p>
	</form>
	<h3><?php _e( '测试SMTP发信功能', 'dmeng' );?></h3>
	<form method="post">
		<input type="hidden" name="action" value="test">
		<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
		<?php 

		$option->table( array(
			array(
				'type' => 'input',
				'th' => __('收信邮箱','dmeng'),
				'key' => 'dmeng_test_email',
				'value' => get_option('admin_email')
			)
		) );
		?>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button" value="<?php _e( '发送测试邮件', 'dmeng' );?>"></p>
	</form>
</div>
	<?php
}
