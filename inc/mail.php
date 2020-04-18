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
 * 邮件
 * 
 */


//~ SMTP发信 @author 多梦 at 2014.06.22 
//~ 详细 https://github.com/PHPMailer/PHPMailer
function dmeng_phpmailer( $mail ) {
	$smtp = json_decode(get_option('dmeng_smtp','{"option":"0","host":"","ssl":"0","port":"25","user":"","pass":"","name":""}'));
	if(intval($smtp->option)){
		$mail->IsSMTP();
		$mail->SMTPAuth = true; 
		$mail->isHTML(true);
		//~ 发信服务器
		$mail->Host = sanitize_text_field($smtp->host);
		//~ 端口
		$mail->Port = intval($smtp->port);
		//~ 发信用户
		$mail->Username = sanitize_text_field($smtp->user);
		//~ 密码
		$mail->Password = sanitize_text_field($smtp->pass);
		//~ SSL
		if(intval($smtp->ssl)) $mail->SMTPSecure = 'ssl';
		//~ 来源（显示发信用户）
		$mail->From = sanitize_text_field($smtp->user);
		//~ 昵称
		$mail->FromName = sanitize_text_field($smtp->name);
	}
}
add_action( 'phpmailer_init', 'dmeng_phpmailer' );

//~ 邮件签名尾巴
function dmeng_email_content_signature($content){
	return '<style type="text/css">.email_wrapper{background:#fcfcfc;border:1px solid #eee;border-radius: 4px;-webkit-box-shadow: 0 1px 1px rgba(0,0,0,.05);box-shadow: 0 1px 1px rgba(0,0,0,.05);}.email_wrapper h3 {color: #2a6496;}</style><div class="email_wrapper"><div style="margin:15px">'.$content.'<br><p style="color: #777777;">'.sprintf(__('本邮件由<a href="%1$s" target="_blank">%2$s</a>发送','dmeng') , get_bloginfo('url') , get_bloginfo('name')).'</p></div></div>';
}
add_filter('dmeng_email_content', 'dmeng_email_content_signature');

//~ 发邮件
function dmeng_send_email( $email, $title, $content ){
	$content = trim(apply_filters( 'dmeng_email_content', $content ));
	wp_mail( $email, $title, $content );
	return;
}
add_action( 'dmeng_send_email_event', 'dmeng_send_email', 10, 3 );

//~ 添加一个动作到批准评论之后（用于发送邮件通知）
function dmeng_comment_unapproved_to_approved($comment){
	do_action( "dmeng_comment_unapproved_to_approved", $comment->comment_ID, $comment );
}
add_action('comment_unapproved_to_approved', 'dmeng_comment_unapproved_to_approved');

//评论通过审核回复邮件
function comment_mail_notify($comment_id) {
    $comment = get_comment($comment_id);
    $parent_id = $comment->comment_parent ? $comment->comment_parent : '';
    $spam_confirmed = $comment->comment_approved;
    if (($parent_id != '') && ($spam_confirmed != 'spam')) {
    $wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));//发件人e-mail地址，no-reply可改为可用的e-mail
    $to = trim(get_comment($parent_id)->comment_author_email);
    $subject = '您在 [' . get_option("blogname") . '] 的留言有了回应';
    $message = '<div style="border-right:#666666 1px solid;border-radius:8px;color:#111;font-size:12px;width:95%;border-bottom:#666666 1px solid;font-family:微软雅黑,arial;margin:10px auto 0px;border-top:#666666 1px solid;border-left:#666666 1px solid"><div class="adM">
    </div><div style="width:100%;background:#666666;min-height:60px;color:white;border-radius:6px 6px 0 0"><span style="line-height:60px;min-height:60px;margin-left:30px;font-size:12px">您在<a style="color:#00bbff;font-weight:600;text-decoration:none" href="' . get_option('home') . '" target="_blank">' . get_option('blogname') . '</a> 上的留言有回复啦！</span> </div>
    <div style="margin:0px auto;width:90%">
    <p><span style="font-weight:bold;">' . trim(get_comment($parent_id)->comment_author) . '</span>, 您好!</p>
    <p>您于' . trim(get_comment($parent_id)->comment_date) . ' 在文章《' . get_the_title($comment->comment_post_ID) . '》上发表评论: </p>
    <p style="border-bottom:#ddd 1px solid;border-left:#ddd 1px solid;padding-bottom:20px;background-color:#eee;margin:15px 0px;padding-left:20px;padding-right:20px;border-top:#ddd 1px solid;border-right:#ddd 1px solid;padding-top:20px">' . nl2br(get_comment($parent_id)->comment_content) . '</p>
    <p><span style="font-weight:bold;">' . trim($comment->comment_author) . '</span> 于' . trim($comment->comment_date) . ' 给您的回复如下: </p>
    <p style="border-bottom:#ddd 1px solid;border-left:#ddd 1px solid;padding-bottom:20px;background-color:#eee;margin:15px 0px;padding-left:20px;padding-right:20px;border-top:#ddd 1px solid;border-right:#ddd 1px solid;padding-top:20px">' . nl2br($comment->comment_content) . '</p>
    <p>您可以点击 <a style="color:#00bbff;text-decoration:none" href="' . htmlspecialchars(get_comment_link($parent_id)) . '" target="_blank">查看回复的完整内容</a></p>
    <p>感谢你对 <a style="color:#00bbff;text-decoration:none" href="' . get_option('home') . '" target="_blank">' . get_option('blogname') . '</a> 的关注，如您有任何疑问，欢迎在博客留言，我会一一解答</p><p style="color:#A8979A;">(此邮件由系统自动发出，请勿回复。)</p></div></div>';
    $from = "From: \"" . get_option('blogname') . "\" <$wp_email>";
    $headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
    wp_mail( $to, $subject, $message, $headers );
    //echo 'mail to ', $to, '<br/> ' , $subject, $message; // for testing
    }
}
add_action('wp_insert_comment', 'comment_mail_notify' , 99, 2 );
add_action('dmeng_comment_unapproved_to_approved', 'comment_mail_notify' , 99, 2 );

//~ 通过链接的消息状态参数改变消息状态
function dmeng_msg_auto_status(){
	if( isset($_GET['msg_action']) && isset($_GET['msg_id']) && isset($_GET['msg_nonce']) ){
		if(dmeng_email_verify_nonce( $_GET['msg_action'] . $_GET['msg_id'] )==$_GET['msg_nonce']){
			update_dmeng_message_type( $_GET['msg_id'], get_current_user_id(), htmlspecialchars($_GET['msg_action']) );
		}
	}
}
add_action('template_redirect', 'dmeng_msg_auto_status');

function dmeng_retrieve_password_message($message, $key){

	if ( strpos( $_POST['user_login'], '@' ) ) {
		$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_user_by('login', $login);
	}
	
	$user_login = $user_data->user_login;
	
	$message = '<h3>'.sprintf( __('%1$s，你好！','dmeng'), $user_data->display_name ).'</h3>';
	$message .= '<p>';
	$message .= sprintf( __('有人要求重设您在%1$s的帐号密码（用户名：%2$s）。若这不是您本人要求的，请忽略本邮件，一切如常。 要重置您的密码，请打开下面的链接：%3$s','dmeng'),
												get_bloginfo('name'),
												$user_login,
												network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login')
												);
	$message .= '</p>';

	$content = apply_filters( 'dmeng_email_content', $message );
	return $content;
}
add_filter('retrieve_password_message', 'dmeng_retrieve_password_message', 10,  2);

function dmeng_pre_email_message($message){
	return '<pre>'.apply_filters( 'dmeng_email_content', $message ).'</pre>';
}
add_filter('comment_moderation_text', 'dmeng_pre_email_message', 10, 1);

function dmeng_email_verify_nonce($ehash, $user_id=0){
	return substr( wp_hash( ($user_id ? $user_id : get_current_user_id()) . $ehash . NONCE_KEY ), -12, 10 );
}

function dmeng_email_verify_url($email){
	$ehash = wp_hash($email);
	return add_query_arg(
		array(
			'action' => 'email_verify',
			'ehash' => $ehash,
			'_wp_nonce' => dmeng_email_verify_nonce($ehash)
		),
		home_url('/')
	);
}

function dmeng_email_verify(){
	
	do_action( 'dmeng_before_ajax', false, false );
	
	if( empty($_POST['email']) ){
		die( __('邮箱地址不能为空', 'dmeng') );
	}
	
	if( is_email($_POST['email']) ){
		$exists_id = email_exists($_POST['email']);
		$user_info = wp_get_current_user();
		$user_id = $user_info->ID;

		if( $exists_id && $exists_id != $user_id ){
			die( sprintf(__('这个电子邮件地址（%s）已经被使用，请换一个。','dmeng'),  $_POST['email']) );
		}

		$verify_email = $user_info->dmeng_verify_email;
		
		if( $verify_email==$_POST['email'] ){
			die( sprintf(__('这个电子邮件地址（%s）你已经验证，无须再次验证。','dmeng'),  $_POST['email']) );
		}
		
		$transient_key = 'dmeng_email_verify_'.$user_id;
		$transient = (array)json_decode(get_transient( $transient_key ));
		if ( $transient ) {
			die( sprintf(__('每五分钟只能申请一次，你在 %1$s 申请了验证 %2$s ，请验证这个邮箱或稍后再试。','dmeng'),  $transient['time'], $transient['email'] ) );
		}
		
		$verify_url = dmeng_email_verify_url($_POST['email']);
		
		$current_time = current_time('mysql');

		dmeng_send_email(
			$_POST['email'],
			sprintf(__('%1$s用户邮箱验证', 'dmeng'), get_bloginfo('name') ),
			'<h3>'.sprintf(__('%1$s，你好！', 'dmeng'), $user_info->display_name ).'</h3>'.
			'<p>'.sprintf(__('请点击下面的链接来验证你的邮箱地址（本链接从 %1$s 开始五分钟内有效）', 'dmeng'), $current_time ).'</p>'.
			'<p><a href="'.$verify_url.'" target="_blank">'.$verify_url.'</a></p>'.
			'<p>'.__('如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中', 'dmeng').'</p>'
		);
		
		set_transient(
			$transient_key,
			json_encode(
				array(
					'time' => $current_time,
					'email' => $_POST['email']
				)
			),
			300 );
		
		die( sprintf(__('验证邮件已发送到（%s）邮箱，请尽快验证（五分钟内有效）。','dmeng'),  $_POST['email']) );
		
	}else{
		die( __('请输入一个有效的电子邮件地址！！！', 'dmeng') );
	}
	
}
add_action( 'wp_ajax_dmeng_email_verify', 'dmeng_email_verify' );

function dmeng_email_verify_template(){
	if( ( is_home() || is_front_page() ) && isset($_GET['action']) && isset($_GET['ehash']) && isset($_GET['_wp_nonce']) ){
		if( $_GET['action']=='email_verify' ){
			
			$wp_title =  __('邮箱认证', 'dmeng');
			
			$user_id = get_current_user_id();
			
			if( $user_id==0 ){
				wp_die( sprintf( __('要验证邮箱，请先<a href="%1$s">登录</a>，已在别的页面登录<a href="%2$s">点击刷新</a>即可。', 'dmeng'), wp_login_url( dmeng_get_current_page_url() ), 'javascript:location.reload()'), $wp_title , array( 'response'=>503 ));
			}
			
			if( dmeng_email_verify_nonce($_GET['ehash']) != $_GET['_wp_nonce'] ){
				wp_die( sprintf( __('咦？你肯定这是你的验证链接吗？<br><br>安全认证码无效，请到<a href="%1$s">个人资料页</a>重新获取链接。', 'dmeng'), dmeng_get_user_url('profile') ), $wp_title , array( 'response'=>503 ));
			}
			
			$transient_key = 'dmeng_email_verify_' . $user_id;
			$transient = (array)json_decode(get_transient( $transient_key ));
			
			if( $transient && isset($transient['email']) ){

				if( wp_hash($transient['email'])==$_GET['ehash'] ){
				
					$user_id = wp_update_user( array( 'ID' => $user_id, 'user_email' => $transient['email'] ) );

					if ( is_wp_error( $user_id ) ) {
						
						wp_die( sprintf(__('邮箱信息更新出错，请重试！<br><br><a href="%1$s">&laquo; 返回个人资料页</a>', 'dmeng'), dmeng_get_user_url('profile')) , $wp_title , array( 'response'=>503 ));
						
					} else {
						
						update_user_meta($user_id, 'dmeng_verify_email', $transient['email']);
						delete_transient( $transient_key );
						wp_die( sprintf(__('恭喜！邮箱地址（%1$s）验证成功<br><br><a href="%2$s">&laquo; 返回个人资料页</a>', 'dmeng') , $transient['email'], dmeng_get_user_url('profile')), $wp_title , array( 'response'=>503 ));
						
					}

				}

			}
			
			wp_die( sprintf(__('验证链接无效<br><br><a href="%1$s">&laquo; 返回个人资料页</a>', 'dmeng'), dmeng_get_user_url('profile')) , $wp_title , array( 'response'=>503 ));
		}
	}
}
add_action('template_redirect', 'dmeng_email_verify_template');

function dmeng_verify_email_methods($profile_fields) {

	$profile_fields['dmeng_verify_email'] = sprintf(__('已验证邮箱 (<a href="%s" target="_blank">?</a>)', 'dmeng'), dmeng_get_user_url('profile').'#pass');

	return $profile_fields;
}
add_filter('user_contactmethods', 'dmeng_verify_email_methods');