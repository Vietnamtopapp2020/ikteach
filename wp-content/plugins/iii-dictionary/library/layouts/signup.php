<?php
	$is_math_panel = is_math_panel();
	$_page_title = __('Sign-up', 'iii-dictionary');
	
	//Create account studient
	if(isset($_POST['wp-submit']))
	{
		$form_valid = true;

		if(is_email($_POST['user_login'])) {
			if(email_exists($_POST['user_login'])) {
				ik_enqueue_messages(__('This email address is already registered. Please choose another one.', 'iii-dictionary'), 'error');
				$form_valid = false;
			}

			$user_email = $_POST['user_login'];
		}
		else {
			// we don't accept normal string as username anymore
			ik_enqueue_messages(__('This email address is invalid. Please choose another one.', 'iii-dictionary'), 'error');
			$form_valid = false;
			/* if(username_exists($_POST['user_login'])) {
				ik_enqueue_messages(__('This username is already registered. Please choose another one', 'iii-dictionary'), 'error');
				$form_valid = false;
			} */
		}

		if(trim($_POST['password']) == '') {
			ik_enqueue_messages(__('Passwords must not be empty', 'iii-dictionary'), 'error');
			$form_valid = false;
		}

		if($_POST['password'] !== $_POST['confirm_password'])
		{
			ik_enqueue_messages(__('Passwords must match', 'iii-dictionary'), 'error');
			$form_valid = false;
		}

		if(strlen($_POST['password']) < 6) 
		{
			ik_enqueue_messages(__('Passwords must be at least six characters long', 'iii-dictionary'), 'error');
			$form_valid = false;
		}

		if($_POST['birth-m'] != '00' || $_POST['birth-d'] != '00' || $_POST['birth-y'] != '')
		{
			if(checkdate($_POST['birth-m'], $_POST['birth-d'], $_POST['birth-y'])) {
				$_POST['date_of_birth'] = $_POST['birth-m'] . '/' . $_POST['birth-d'] . '/' . $_POST['birth-y'];
			}
			else {
				ik_enqueue_messages(__('Invalid date of birth', 'iii-dictionary'), 'error');
				$form_valid = false;
			}
		}
		else
		{
			$_POST['date_of_birth'] = '';
		}

		// form valid, create new user
		if($form_valid)
		{
			if(isset($user_email)) {
				$user_id = wp_create_user($_POST['user_login'], $_POST['password'], $user_email);
			}
			else {
				$user_id = wp_create_user($_POST['user_login'], $_POST['password']);
			}

			$userdata['ID'] = $user_id;

			if(isset($_POST['first_name']) && trim($_POST['first_name']) != '')
			{
				$userdata['first_name'] = $_POST['first_name'];
			}

			if(isset($_POST['last_name']) && trim( $_POST['last_name']) != '')
			{
				$userdata['last_name'] = $_POST['last_name'];
			}

			if(isset($userdata['first_name']) && isset($userdata['last_name']))
			{
				$userdata['display_name'] = $userdata['first_name'] . ' ' . $userdata['last_name'];
			}

			$new_user_id = wp_update_user( $userdata );

			update_user_meta( $user_id, 'date_of_birth', $_POST['date_of_birth'] );
			update_user_meta( $user_id, 'language_type', $_POST['language_type'] );

			// auto login the user
			$creds['user_login'] = $_POST['user_login'];
			$creds['user_password'] = $_POST['password'];
			$user = wp_signon( $creds, false );

			// send confirmation email
			if(is_email($user_email))
			{
				$title = __('Congratulations! You have successfully signed up for iklearn.com', 'iii-dictionary');
				$message =  __('You have successfully signed up for iklearn.com.', 'iii-dictionary') . "\r\n\r\n" .
							__('If you have questions or need support, please contact us at support@iklearn.com.', 'iii-dictionary') . "\r\n\r\n" .
							__('If you forgot your password, please click on the "forgot password" button after entering your username (email address).', 'iii-dictionary') . "\r\n\r\n" .
							__('Please enjoy the Merriam Webster dictionary and English learning tools.', 'iii-dictionary') . "\r\n\r\n" .
							__('For teachers - You may assign homework practice sheets available on this site. You can also create your own homework sheets. Please click here for details. The homework that is turned in by students is automatically graded and saved in your teacher\'s box. Currently, the available homework worksheets are (1) spelling practice and (2) vocabulary and grammar.', 'iii-dictionary') . "\r\n\r\n\r\n" .
							__('Happy learning!', 'iii-dictionary') . "\r\n\r\n\r\n" .
							__('Support', 'iii-dictionary');

				if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {			
				}
			}

			$_SESSION['newuser'] = 1;
			$_SESSION['mw_referer'] = locale_home_url();
			wp_redirect($_SESSION['mw_referer']);
			exit;
		}
	}
	//Create account teacher
	if(isset($_POST['i-agree'])){
		$form_valid = true;

		if(is_email($_POST['user_login'])) {
			if(email_exists($_POST['user_login'])) {
				ik_enqueue_messages(__('This email address is already registered. Please choose another one.', 'iii-dictionary'), 'error');
				$form_valid = false;
			}
			$user_email = $_POST['user_login'];
		}else {
			// we don't accept normal string as username anymore
			ik_enqueue_messages(__('This email address is invalid. Please choose another one.', 'iii-dictionary'), 'error');
			$form_valid = false;
		}

		if(trim($_POST['password']) == '') {
			ik_enqueue_messages(__('Passwords must not be empty', 'iii-dictionary'), 'error');
			$form_valid = false;
		}

		if($_POST['password'] !== $_POST['confirm_password'])
		{
			ik_enqueue_messages(__('Passwords must match', 'iii-dictionary'), 'error');
			$form_valid = false;
		}

		if(strlen($_POST['password']) < 6) 
		{
			ik_enqueue_messages(__('Passwords must be at least six characters long', 'iii-dictionary'), 'error');
			$form_valid = false;
		}

		if($_POST['birth-m'] != '00' || $_POST['birth-d'] != '00' || $_POST['birth-y'] != '')
		{
			if(checkdate($_POST['birth-m'], $_POST['birth-d'], $_POST['birth-y'])) {
				$user_metadata['date_of_birth'] = $_POST['birth-m'] . '/' . $_POST['birth-d'] . '/' . $_POST['birth-y'];
			}else {
				ik_enqueue_messages(__('Invalid date of birth', 'iii-dictionary'), 'error');
				$form_valid = false;
			}
		}else{
			$user_metadata['date_of_birth'] = '';
		}

		if(isset($_POST['language_teach'])){
			$language_teach = $_REAL_POST['language_teach'];
			$str_lang = '';
			foreach ($language_teach as $lang) {
				$str_lang .= $lang.',';
			}
			$str_lang = substr($str_lang,0 ,-1);
			$user_metadata['language_teach'] = $str_lang;
		}else{
			ik_enqueue_messages(__('You must choose language to teach', 'iii-dictionary'), 'error');
			$form_valid = false;
		}

		if($_POST['mobile-number'] == ''){
			ik_enqueue_messages(__('Mobile phone number must not be empty', 'iii-dictionary'), 'error');
			$form_valid = false;
		}else{
			$user_metadata['mobile_number'] = $_POST['mobile-number'];
		}

		if($_POST['email-paypal'] == ''){
			ik_enqueue_messages(__('Email for Paypal must not be empty', 'iii-dictionary'), 'error');
			$form_valid = false;
		}else{
			$user_metadata['email_paypal'] = $_POST['email-paypal'];
		}

		if($_POST['last-school'] == ''){
			ik_enqueue_messages(__('Last school number must not be empty', 'iii-dictionary'), 'error');
			$form_valid = false;
		}else{
			$user_metadata['last_school'] = $_POST['last-school'];
		}

		if($_POST['previous-school'] == ''){
			ik_enqueue_messages(__('Latest school you tought must not be empty', 'iii-dictionary'), 'error');
			$form_valid = false;
		}else{
			$user_metadata['previous_school'] = $_POST['previous-school'];
		}

		if(!isset($_POST['agree-english-teacher']) && !isset($_POST['agree-math-teacher'])){
			ik_enqueue_messages(__('You must agree "Teacher\'s Registration Agreement"', 'iii-dictionary'), 'error');
			$form_valid = false;
		}

		$image = $_FILES['input-image'];

		// form valid, create new user
		if($form_valid)
		{

			if(isset($_POST['first_name']) && trim($_POST['first_name']) != '')
			{
				$userdata['first_name'] = $_POST['first_name'];
			}

			if(isset($_POST['last_name']) && trim( $_POST['last_name']) != '')
			{
				$userdata['last_name'] = $_POST['last_name'];
			}

			if(isset($userdata['first_name']) && isset($userdata['last_name']))
			{
				$userdata['display_name'] = $userdata['first_name'] . ' ' . $userdata['last_name'];
			}
                        $role_register=null;
                        
                        if(isset($_POST['agree-math-teacher'])&&$_POST['agree-math-teacher']==2){
                            $role_register='mw_registered_math_teacher';
                        }
                        if(isset($_POST['agree-english-teacher'])&&$_POST['agree-english-teacher']==1){
                            $role_register='mw_registered_teacher';
                        }
                        
			$user_id = wp_insert_user(array(
						'user_login'		=> $user_email,
						'user_nicename'	=> $user_email,
						'user_pass'	 		=> $_POST['password'],
						'user_email'		=> $user_email,
						'display_name'		=> $userdata['display_name'],
						'user_registered'	=> date('Y-m-d H:i:s'),
						'role'				=> $role_register,
						'nickname' => $user_email,
						'first_name' => $userdata['first_name'],
						'last_name' => $userdata['last_name'],
						'user_status' => 0
					)
				);
                         if(isset($_POST['agree-math-teacher'])&&$_POST['agree-math-teacher']==2 && isset($_POST['agree-english-teacher'])&&$_POST['agree-english-teacher']==1){
                             get_userdata($user_id)->add_role('mw_registered_math_teacher');
                         }
			foreach ($user_metadata as $meta_key => $meta_value) {
				update_user_meta( $user_id, $meta_key, $meta_value );
			}

			$agreement_update_date = mw_get_option('agreement-update-date');
			if(isset($_POST['agree-math-teacher'])){
				
				update_user_meta($user_id, 'math_teacher_agreement_ver', $agreement_update_date);
			}
			if(isset($_POST['agree-english-teacher'])){
				update_user_meta($user_id, 'teacher_agreement_ver', $agreement_update_date);
			}
			ik_set_user_avatar($user_id, $image);
			// auto login the user
			$creds['user_login'] = $_POST['user_login'];
			$creds['user_password'] = $_POST['password'];
			$user = wp_signon( $creds, false );

			// send confirmation email
			if(is_email($user_email))
			{
				$title = __('Congratulations! You have successfully signed up for iklearn.com', 'iii-dictionary');
				$message =  __('You have successfully signed up for iklearn.com.', 'iii-dictionary') . "\r\n\r\n" .
							__('If you have questions or need support, please contact us at support@iklearn.com.', 'iii-dictionary') . "\r\n\r\n" .
							__('If you forgot your password, please click on the "forgot password" button after entering your username (email address).', 'iii-dictionary') . "\r\n\r\n" .
							__('Please enjoy the Merriam Webster dictionary and English learning tools.', 'iii-dictionary') . "\r\n\r\n" .
							__('For teachers - You may assign homework practice sheets available on this site. You can also create your own homework sheets. Please click here for details. The homework that is turned in by students is automatically graded and saved in your teacher\'s box. Currently, the available homework worksheets are (1) spelling practice and (2) vocabulary and grammar.', 'iii-dictionary') . "\r\n\r\n\r\n" .
							__('Happy learning!', 'iii-dictionary') . "\r\n\r\n\r\n" .
							__('Support', 'iii-dictionary');

				if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {			
				}
			}

			$_SESSION['newuser'] = 1;
			$_SESSION['mw_referer'] = locale_home_url();
			wp_redirect($_SESSION['mw_referer']);
			exit;
		}
	}
?>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_header($_page_title) ?>
<?php else : ?>
	<?php get_math_header($_page_title, 'red-brown') ?>
<?php endif ?>
<?php get_dict_page_title($_page_title) ?>

	<form method="post" action="<?php echo locale_home_url() ?>/?r=signup" name="registerform" enctype="multipart/form-data">
		<div class="row">
			<div class="col-sm-9">
				<div class="form-group">
					<label for="user_login"><?php _e('Username (e-mail address)', 'iii-dictionary') ?></label>
					<input id="user_login" class="form-control" name="user_login" type="text" value="" required>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="form-group">
					<label>&nbsp;</label>															
					<a href="#" id="check-availability" class="check-availability"><?php _e('Find out availability', 'iii-dictionary') ?>
						<span class="icon-loading"></span>
						<span class="icon-availability" data-toggle="popover" data-placement="bottom" data-container="body" data-trigger="hover" data-html="true" data-max-width="420px" data-content="If username availability is “not available”, someone has already signed up with the email address you entered.<br>If username is “available”, please continue on with the sign up page."></span>
					</a>
				</div>
			</div>

			<div class="col-sm-6">
				<div class="form-group">
					<label for="password"><?php _e('Create Password', 'iii-dictionary') ?></label>
					<input id="password" class="form-control" name="password" type="password" value="" required>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<label for="confirmpassword"><?php _e('Confirm Password', 'iii-dictionary') ?></label>
					<input id="confirmpassword" class="form-control" name="confirm_password" type="password" value="" required>
				</div>
			</div>

			<div class="col-sm-6 col-md-6">
				<div class="form-group">
					<label for="lastname"><?php _e('Last Name', 'iii-dictionary') ?></label>
					<input id="lastname" class="form-control" name="last_name" type="text" value="" required>
				</div>
			</div>
			<div class="col-sm-6 col-md-6">
				<div class="form-group">
					<label for="firstname"><?php _e('First Name', 'iii-dictionary') ?></label>
					<input id="firstname" class="form-control" name="first_name" type="text" value="" required>
				</div>
			</div>
			<div class="col-sm-6 col-md-6">
				<div class="form-group">
					<label><?php _e('Date of Birth', 'iii-dictionary') ?> <small>(mm/dd/yyyy)</small></label>
					<div class="row tiny-gutter">
						<div class="col-xs-4">
							<select class="select-box-it form-control" name="birth-m">
								<option value="00">mm</option>
								<?php for($i = 1; $i <= 12; $i++) : ?>
									<?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
									<option value="<?php echo $pad_str ?>"><?php echo $pad_str ?></option>
								<?php endfor ?>
							</select>
						</div>
						<div class="col-xs-4">
							<select class="select-box-it form-control" name="birth-d">
								<option value="00">dd</option>
								<?php for($i = 1; $i <= 31; $i++) : ?>
									<?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
									<option value="<?php echo $pad_str ?>"><?php echo $pad_str ?></option>
								<?php endfor ?>
							</select>
						</div>
						<div class="col-xs-4">
							<input class="form-control" name="birth-y" type="text" value="" placeholder="yyyy" required>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6 col-md-6">
				<div class="form-group">
					<label><?php _e('Language', 'iii-dictionary') ?></label>
					<?php MWHtml::language_type('en') ?>
				</div>
			</div>
			<div class="col-sm-7">
				<div class="form-group">
					<label>&nbsp;</label>
					<button class="btn btn-default btn-block orange account" type="submit" name="wp-submit"><span class="icon-plus"></span><?php _e('Create Account', 'iii-dictionary') ?></button>
				</div>
			</div>
		</div>
        </form>
            <form method="post" action="<?php echo locale_home_url() ?>/?r=signup" name="registerform" enctype="multipart/form-data">
		<input type="hidden" name="redirect_to" value="<?php echo locale_home_url() ?>/?r=login"/>
		<input type="hidden" name="self-reg" value="1"/>
		<h3><?php _e('Teacher\'s Registration Agreement','iii-dictionary'); ?></h3>
		<div class="row red-brown">
			<div class="col-sm-12" id="page-tabs-container" style="background:transparent">
				<ul id="page-tabs">
					<li class="page-tab active" data-tab="english_class"><a href="javascript:void(0);" ><?php _e('English Classes','iii-dictionary'); ?></a></li>
					<li class="page-tab" data-tab="math_class"><a href="javascript:void(0);" ><?php _e('Math Classes','iii-dictionary'); ?></a></li>
				</ul>
			</div>
			<div class="col-sm-12">
				<div class="form-group box_english">
					<div class="box box-red" >
						<div class="scroll-list" style="max-height: 200px; color: #fff">
							<?php echo mw_get_option('registration-agreement') ?>
						</div>
					</div>
					<div class="col-sm-6 col-xs-2"></div>
					<div class="col-sm-1 col-xs-2">
						<label>&nbsp;</label>
						<div class="radio radio-style1" id="r_agree_english">
							<input id="rdo-agreed" type="radio" name="agree-english-teacher" value="1" >
							<label for="rdo-agreed"> </label>
						</div>
					</div>
					<div class="col-xs-8 col-sm-5">
						<div class="form-group">
							<label>&nbsp;</label>
							<button type="button" class="btn btn-default btn-block orange form-control r_agree_english" name="ie-agree" id="i-agree"><span class="icon-check"></span> <?php _e('I AGREE', 'iii-dictionary') ?></button>
						</div>
					</div>
				</div>
				<div class="form-group box_math" style="display:none">
					<div class="box box-red" >
						<div class="scroll-list" style="max-height: 200px; color: #fff">
							<?php echo mw_get_option('math-registration-agreement') ?>
						</div>
					</div>
					<div class="col-sm-6 col-xs-2"></div>
					<div class="col-xs-2 col-sm-1">
						<div class="form-group">
							<label>&nbsp;</label>
							<div class="radio radio-style1" id="r_agree_math">
								<input id="rdo-agreed-math" <?php if($is_teaching_agreement_uptodate_math) echo 'checked'; ?> type="radio" name="agree-math-teacher" value="2">
								<label for="rdo-agreed-math"></label>
							</div>
						</div>
					</div>
					<div class="col-xs-8 col-sm-5">
						<div class="form-group">
							<label>&nbsp;</label>
							<button type="button" class="btn btn-default btn-block orange form-control r_agree_math" name="im-agree" id="i-agree"><span class="icon-check"></span> <?php _e('I AGREE', 'iii-dictionary') ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-4">
				<div class="form-group">
					<table class="table profile-picture-form">
						<tr>
							<td class="profile-picture">
								<div id="profile-picture">
									<?php 
										$user_avatar = ik_get_user_avatar($current_user->ID);
										  if(!empty($user_avatar)) : ?>
											<img src="<?php echo $user_avatar ?>" width="100" height="100" alt="<?php echo $current_user->display_name ?>">
									<?php else :
											echo get_avatar($current_user->ID, 120);
										  endif ?>
								</div>
							</td>
							<td class="upload-block">
								<label><?php _e('Profile Picture', 'iii-dictionary') ?></label><br>
								<span class="btn btn-default grey btn-file">
									<span class="icon-browse"></span><?php _e('Browse', 'iii-dictionary') ?> <input name="input-image" id="input-image" type="file">
								</span>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="col-md-8">
				<div class="form-group">
					<label><?php _e('Which language you use to teach', 'iii-dictionary') ?></label>
					<div class="row">
						<?php 
						$language_teach = get_user_meta($current_user->ID, 'language_teach', true); 
						$langs = explode(',', $language_teach);
						?>
						<div class="col-md-6">
							<label class="check_lb <?php if(in_array('en', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if(in_array('en', $langs)) echo 'checked'; ?> name="language_teach[]" value="en"> <?php _e('English','iii-dictionary'); ?></label><br>
							<label class="check_lb <?php if(in_array('ja', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if(in_array('ja', $langs)) echo 'checked'; ?> name="language_teach[]" value="ja"> <?php _e('Japanese','iii-dictionary'); ?></label><br>
							<label class="check_lb <?php if(in_array('ko', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if(in_array('ko', $langs)) echo 'checked'; ?> name="language_teach[]" value="ko"> <?php _e('Korean','iii-dictionary'); ?></label><br>
						</div>
						<div class="col-md-6">
							<label class="check_lb <?php if(in_array('zh', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if(in_array('zh', $langs)) echo 'checked'; ?> name="language_teach[]" value="zh"> <?php _e('Chinese','iii-dictionary'); ?></label><br>
							<label class="check_lb <?php if(in_array('zh-tw', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if(in_array('zh-tw', $langs)) echo 'checked'; ?> name="language_teach[]" value="zh-tw"> <?php _e('Traditional Chinese','iii-dictionary'); ?></label><br>
							<label class="check_lb <?php if(in_array('vi', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if(in_array('vi', $langs)) echo 'checked'; ?> name="language_teach[]" value="vi"> <?php _e('Vietnamese','iii-dictionary'); ?></label><br>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label><?php _e('Mobile phone number', 'iii-dictionary') ?></label>
				<input type="text" class="form-control" name="mobile-number" value="" required>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label><?php _e('Email for Paypal', 'iii-dictionary') ?></label>
				<input type="text" class="form-control" name="email-paypal" value="" required>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label><?php _e('Last school  you attended', 'iii-dictionary') ?></label>
				<input type="text" class="form-control" name="last-school" value="" required>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label><?php _e('Latest school you tought if any', 'iii-dictionary') ?></label>
				<input type="text" class="form-control" name="previous-school" value="" required>
			</div>
		</div>
		<div class="col-xs-10 col-sm-7">
			<div class="form-group">
				<label>&nbsp;</label>
				<button type="submit" class="btn btn-default btn-block orange form-control" name="i-agree" id="i-agree"><span class="icon-plus"></span> <?php _e('Create Account Teacher', 'iii-dictionary') ?></button>
			</div>
		</div>
	</form>

<script>
	(function($){
		$(function(){
			var availability_checking = false;
			$("#check-availability").click(function(e){
				e.preventDefault();
				if(availability_checking){return;}
				var tthis = $(this);
				var user_login = $("#user_login").val().trim();
				if(user_login != "") {
					tthis.popover("destroy");
					availability_checking = true;
					tthis.find(".icon-loading").fadeIn();
					$.getJSON(home_url + "/?r=ajax/availability/user", {user_login: user_login}, function(data){
						if(data[0] == 0) {
							var p_c = '<span class="popover-alert"><?php _e('Not available', 'iii-dictionary') ?></span>';
						}else{
							var p_c = '<span class="popover-alert"><?php _e('Available', 'iii-dictionary') ?></span>';
						}
						tthis.find(".icon-loading").fadeOut();
						tthis.popover({
							placement: "bottom",
							content: p_c,
							trigger: "click hover",
							html: true
						}).popover("show");
						setTimeout(function(){tthis.popover("hide")}, 1500);
						availability_checking = false;
					});
				}
			});
			$('.r_agree_english').click(function(e){
				$('#r_agree_english').find('input').attr('checked',true);
				$('#r_agree_english label').addClass('checked_lb');
			});
			$('.r_agree_math').click(function(e){
				$('#r_agree_math').find('input').attr('checked',true);
				$('#r_agree_math label').addClass('checked_lb');
			});
			$(".page-tab").click(function(e){
				var tab = $(this).attr('data-tab');
				$(".page-tab").removeClass('active');
				$(this).addClass('active');
				if(tab == 'english_class'){
					$('.box_english').show();
					$('.box_math').hide();
				}else{
					$('.box_english').hide();
					$('.box_math').show();
				}
			});
			$('.check_lb').click(function(e){
				var checked = $(this).find('input').attr('checked');
				if(checked == 'checked'){
					$(this).addClass('checked_lb');
				}
				else{
					$(this).removeClass('checked_lb');
				}
			});
		});
	})(jQuery);
</script>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>