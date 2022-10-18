<?php




// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:



if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

// Включим возможность добавления произвольного фона сайта
add_theme_support( 'custom-background' );

// Интеграция с Freelance Nextgen
add_action( 'fng_task_complete', 'rr_payment_performer', 10 );
function rr_payment_performer( $task_id ) {
	$task = get_post($task_id);
	$price		 = get_post_meta( $task_id, 'fng-price', 1 );
	add_referall_incentive_order( $task->post_author, $price );
}

// Интеграция с Shop Service
add_action( 'sm_order_complete', 'rr_shop_service_payment_performer', 10 );
function rr_shop_service_payment_performer( $task_id ) {
	$task = get_post($task_id);
	$price		 = get_post_meta($task_id, 'sm-order-price', 1);
	add_referall_incentive_order( $task->post_author, $price );
}


// Подключение стилей к теме
add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );

function theme_name_scripts() {
	wp_enqueue_style( 'style-theme', get_stylesheet_uri() );
	wp_enqueue_script('newscript', get_stylesheet_directory_uri() . '/js/my_scripts.js');
}

add_action( 'wp_enqueue_scripts', 'myajax_data', 99 );
function myajax_data(){

	wp_localize_script('twentyfifteen-script', 'myajax', 
		array(
			'url' => admin_url('admin-ajax.php')
		)
	);  

}

// функция живого поиска
add_action( 'wp_ajax_nopriv_livesearch', 'livesearch' );
add_action( 'wp_ajax_livesearch', 'livesearch' );

function livesearch(){

	$args = array( 
		'post_type'      => [ 'post', 'task', 'post-group', 'service' ], 
		'post_status'    => 'publish', 
		'order'          => 'DESC', 
		'orderby'        => 'date', 
		's'              => $_POST['term'], 
		'posts_per_page' => 5 
	);

	$query = new WP_Query( $args );

	$html = '';

	if($query->have_posts()){
		while ($query->have_posts()) { 
			$query->the_post();

			
			$html .= '
				<div class="search-item-container">
					<div class="top-search-item">
						<a href="'.get_the_permalink().'" class="title-search-item">'.get_the_title().'</a>
					</div>
					<div class="bottom-search-item">
						<p class="description-search-item">'.get_the_excerpt().'</p>
					</div>
				</div>
			';
			
		}
	}

	$terms = get_terms( ['taxonomy' => 'groups', 'hide_empty' => false]);

	foreach($terms as $term){
		if(strpos($term->name, $_POST['term'])){
			$html .= '
				<div class="search-item-container">
					<div class="top-search-item">
						<a href="'.get_term_link($term->term_id, 'groups').'" class="title-search-item">'.$term->name.'</a>
					</div>
					<div class="bottom-search-item">
						<p class="description-search-item">'.$term->description.'</p>
					</div>
				</div>
			';
		}
	}

	if($html == ''){
		$html .= 'Ничего не найдено, попробуйте другой запрос';
	}

	wp_send_json_success(array('result' => $html));
}

// Переопределение функции вывода поиска

add_action( 'generate_inside_navigation', 'generate_navigation_search' );

	function generate_navigation_search() {
		$generate_settings = wp_parse_args(
			get_option( 'generate_settings', array() ),
			generate_get_defaults()
		);

		if ( 'enable' !== $generate_settings['nav_search'] ) {
			return;
		}

		echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'generate_navigation_search_output',
			sprintf(
				'<form method="get" class="search-form navigation-search" action="%1$s">
					<input type="search" class="search-field" value="%2$s" name="s" title="%3$s" autocomplete="off" />
					<div class="responce-search"></div>
				</form>',
				esc_url( home_url( '/' ) ),
				esc_attr( get_search_query() ),
				esc_attr_x( 'Search', 'label', 'generatepress' )
			)
		);
	}


// Изменяем перевод пользователю


remove_action( 'init', 'rcl_add_block_wallet_button' );

add_action( 'init', 'rcl_add_block_wallet_button_custom' );

function rcl_add_block_wallet_button_custom() {
	rcl_block( 'actions', 'add_wallet_count_button_user_lk_custom', array( 'public' => -1 ) );
}

function add_wallet_count_button_user_lk_custom( $author_lk ) {
	global $user_ID, $rcl_options;
	if ( ! isset( $rcl_options['output_pay_other_user'] ) || $rcl_options['output_pay_other_user'] != 1 )
		return false;

	return rcl_get_button( [
		'label'		 => __( 'Сделать перевод', 'rcl-wallet' ),
		'onclick'	 => 'mw_load_user_transfer_form_custom(' . $author_lk . ');return false;',
		'icon'		 => 'fa-money'
	] );
}

// Навешиваем свой обработчик на вывод формы и сам перевод

rcl_ajax_action( 'mw_load_user_transfer_form_custom', false );
function mw_load_user_transfer_form_custom() {

	$form = rcl_get_form( array(
		'onclick'	 => 'rcl_send_form_data("mw_user_transfer_custom", this);return false;',
		'submit'	 => __( 'Передать' ),
		'fields'	 => array(
			array(
				'type'			 => 'number',
				'slug'			 => 'transfer_amount',
				'required'		 => 1,
				'value_min'		 => 1,
				'placeholder'	 => 0,
				'title'			 => __( 'Сумма перевода' )
			),
			array(
				'type'			 => 'password',
				'slug'			 => 'user_password',
				'required'		 => 1,
				'placeholder'	 => 'Пароль',
				'title'			 => __( 'Пароль аккаунта' ),
				'notice'	 	 => __( 'Укажите пароль от своего аккаунта' )
			),
			array(
				'type'			 => 'textarea',
				'slug'			 => 'comment_text',
				'required'		 => 0,
				'placeholder'	 => 'Комментарий',
				'title'			 => __( 'Комментарий' ),
			),
			array(
				'type'	 => 'hidden',
				'slug'	 => 'user_id',
				'value'	 => $_POST['user_id']
			)
		)
		) );

	$form = '
<form method="post" action="" target="">
   <div class="rcl-field type-number-field">
      <span class="rcl-field-title">Сумма перевода <span class="required">*</span></span>
      <div id="rcl-field-transfer_amount" class="type-number-input rcl-field-input">
         <div class="rcl-field-core"><input type="number" min="1" max="" required="required" placeholder="0" class="number-field" name="transfer_amount" id="transfer_amount" value=""></div>
      </div>
   </div>';
    if ( rcl_get_option( 'mw_need_pass', false ) ) {
   $form .= '
    <div class="rcl-field type-password-field">
      <span class="rcl-field-title">Пароль аккаунта <span class="required">*</span></span>
      <div id="rcl-field-user_pass" class="type-password-input rcl-field-input">
         <div class="rcl-field-core"><input type="password" required="required" class="password-field vis-pass-field little-form-pass-field" name="user_password" id="user_password" value=""><div class="vis-pass-cont little-form-pass-vis-cont"><i class="rcli fa-eye" aria-hidden="true"></i></div></div>
         <span class="rcl-field-notice"><i class="rcli fa-info" aria-hidden="true"></i>Укажите пароль от своего аккаунта</span>
      </div>
   </div>';
	}
   $form .= '
   <div class="rcl-field type-textarea-field">
      <span class="rcl-field-title">Комментарий</span>
      <div id="rcl-field-comment_text" class="type-textarea-input rcl-field-input">
         <div class="rcl-field-core"><textarea name="comment_text" placeholder="Комментарий" class="textarea-field" id="comment_text" rows="5" cols="50"></textarea></div>
      </div>
   </div>
   <input type="hidden" placeholder="" class="hidden-field" name="user_id" id="user_id" value="'.$_POST['user_id'].'">
   <div class="submit-box"><a href="javascript:void(0);" title="Передать" onclick="rcl_send_form_data(&quot;mw_user_transfer_custom&quot;, this);return false;" class="rcl-bttn rcl-bttn__type-primary rcl-bttn__size-standart"><i class="rcl-bttn__ico rcl-bttn__ico-left rcli fa-check-circle"></i><span class="rcl-bttn__text">Передать</span></a></div>
</form>
	';

	wp_send_json( array(
		'dialog' => array(
			'title'		 => __( 'Перевод другому пользователю' ),
			'content'	 => $form
		)
	) );
}

// Переписанная функция перевода средств
rcl_ajax_action( 'mw_user_transfer_custom', false );
function mw_user_transfer_custom() {
	global $wpdb, $user_ID, $rcl_options;

	$id_user	 = intval( $_POST['user_id'] );
	$add_count	 = rcl_commercial_round( str_replace( ',', '.', abs( $_POST['transfer_amount'] ) ) );

	if ( ! $add_count || $user_ID == $id_user )
		return false;

	$user = get_user_by( 'id', $id_user );

	if ( ! $user )
		return false;

	
	$user_data = get_userdata( $user_ID );
	$password = $_POST['user_password'];


	if ( ! wp_check_password( $password, $user_data->data->user_pass ) ) {
		wp_send_json( array(
			'error' => __( 'Неверный пароль!' )
		) );
	}
	

	$oldusercount	 = rcl_get_user_balance( $user_ID );
	$newusercount	 = $oldusercount - $add_count;
	if ( $newusercount < 0 ) {
		wp_send_json( array( 'error' => __( 'Insufficient funds on personal account!', 'rcl-wallet' ) ) );
	}

	rcl_update_user_balance( $newusercount, $user_ID, __( 'The transfer of funds to the user', 'rcl-wallet' ) . ' ' . get_the_author_meta( 'display_name', $id_user ) );

	$display_name = get_the_author_meta( 'display_name', $user_ID );

	$user_addcount = rcl_get_user_balance( $id_user );

	$new_addcount = $user_addcount + $add_count;
	rcl_update_user_balance( $new_addcount, $id_user, __( 'The funds coming from the user', 'rcl-wallet' ) . ' ' . $display_name );

	if($_POST['comment_text'] != ''){
		$html_comment = '<p>Оставленный комментарий: '.$_POST['comment_text'].'</p>';
	}

	$subject	 = __( 'Money transfer', 'rcl-wallet' );
	$email		 = get_the_author_meta( 'user_email', $id_user );
	$textmail	 = '<p>' . sprintf( __( 'Into your personal account with the website "%s" was transferred from another user', 'rcl-wallet' ), get_bloginfo( 'name' ) ) . '!</p>
            <p>' . sprintf( __( 'The user %s has transferred to your personal account %s', 'rcl-wallet' ), '<a href="' . get_author_posts_url( $user_ID ) . '">' . $display_name . '</a>', $add_count . ' ' . rcl_get_primary_currency( 1 ) ) . '</p>'.$html_comment;
	rcl_mail( $email, $subject, $textmail );

	$log['count']	 = $newusercount;
	$log['success']	 = __( 'The funds were successfully transferred to the account of that user', 'rcl-wallet' );

	wp_send_json( array(
		'success'	 => __( 'The funds were successfully transferred to the account of that user', 'rcl-wallet' ),
		'count'		 => $newusercount,
		'dialog'	 => array(
			'close' => true
		)
	) );
}

remove_action( 'rcl_add_user_balance', 'add_wallet_history_row', 10, 3 );
remove_action( 'rcl_pre_update_user_balance', 'add_wallet_history_row', 10, 3 );

add_action( 'rcl_add_user_balance', 'add_wallet_history_row_custom', 10, 3 );
add_action( 'rcl_pre_update_user_balance', 'add_wallet_history_row_custom', 10, 3 );

function add_wallet_history_row_custom( $money, $user_id, $comment, $type = false ) {
	global $wpdb;

	$time_action = current_time( 'mysql' );

	if ( doing_action( 'rcl_add_user_balance' ) ) {
		$type		 = 2;
		$newBalance	 = rcl_get_user_balance( $user_id );
		$count		 = $money;
	} else if ( doing_action( 'rcl_pre_update_user_balance' ) ) {
		$oldBalance	 = rcl_get_user_balance( $user_id );
		$type		 = ($oldBalance > $money) ? 1 : 2;
		$newBalance	 = $money;
		if ( $type == 1 ) {
			$count = $oldBalance - $money;
		} else if ( $type == 2 ) {
			$count = $money - $oldBalance;
		}
	} else {
		$newBalance	 = rcl_get_user_balance( $user_id );
		$count		 = $money;
	}

	if ( ! $count )
		return false;

	$res = $wpdb->insert(
		RCL_PREF . 'wallet_history', array(
		'user_id'		 => $user_id,
		'count_pay'		 => rcl_commercial_round( $count ),
		'user_balance'	 => $newBalance,
		'comment_pay'	 => $comment,
		'time_pay'		 => $time_action,
		'type_pay'		 => $type
		)
	);

	do_action( 'add_wallet_history_row', $wpdb->insert_id );

	return $res;
}

// Переписывание функционала заказа вывода средств


remove_action( 'init', 'add_tab_wallet', 10);

add_action( 'init', 'add_tab_wallet_custom', 10);

function add_tab_wallet_custom() {
	$args = array();
	$args = array(
		'id'		 => 'wallet-custom',
		'name'		 => __( 'Баланс', 'rcl-recall' ),
		'supports'	 => array( 'ajax' ),
		'public'	 => 0,
		'icon'		 => 'fa-money',
		'content'	 => array(
			array(
				'id'		 => 'wallet',
				'name'		 => __( 'История' ),
				'callback'	 => array(
					'name' => 'mw_wallet_history_tab_custom'
				)
			),
			array(
			'id'		 => 'wallet-request-custom',
			'name'		 => __( 'Вывод средств' ),
			'callback'	 => array(
				'name' => 'mw_wallet_requests_tab_custom'
			)
		)
		)
	);


	

	rcl_tab( $args );
}

function mw_wallet_history_tab_custom( $master_id ) {

	$content = '';

	if ( function_exists( 'rcl_get_html_usercount' ) && rcl_get_option( 'wallet_usercount', 0 ) ) {
		$content .= rcl_get_html_usercount();
		$content .= '<hr>';
	}

	$cnt = mw_count_history( array(
		'user_id' => $master_id
		) );

	if ( ! $cnt ) {
		return $content . rcl_get_notice( ['text' => __( 'You haven`t had the movements of funds on the personal account', 'rcl-wallet' ) ] );
	}

	$rclnavi = new Rcl_PageNavi( 'rcl-wallet', $cnt );

	$history = mw_get_history( array(
		'user_id'	 => $master_id,
		'number'	 => 30,
		'offset'	 => $rclnavi->offset
		) );

	$n = $cnt - $rclnavi->offset;

	$balance = rcl_get_user_balance( $master_id );

	$currency = rcl_get_primary_currency( 1 );

	$content .= '<h3>' . __( 'The history of balance changes', 'rcl-wallet' ) . '</h3>';

	$content .= $rclnavi->pagenavi();

	$Table = new Rcl_Table( array(
		'cols'	 => array(
			array(
				'title'	 => __( '№' ),
				'width'	 => 10, 'align'	 => 'center'
			),
			array(
				'title'	 => __( 'Дата', 'rcl-wallet' ),
				'width'	 => 30, 'align'	 => 'center'
			),
			array(
				'title'	 => __( 'Parish', 'rcl-wallet' ) . '/' . __( 'Consumption', 'rcl-wallet' ),
				'width'	 => 20, 'align'	 => 'center'
			),
			array(
				'title'	 => __( 'The rest', 'rcl-wallet' ),
				'width'	 => 25, 'align'	 => 'center'
			),
			array(
				'title'	 => __( 'Comment', 'rcl-wallet' ),
				'width'	 => 55
			)
		),
		'zebra'	 => 1,
		'border' => array( 'table', 'cols', 'rows' )
		) );

	foreach ( $history as $pay ) {

		$user_balance = ($pay->user_balance) ? $pay->user_balance . ' ' . $currency : '-';

		$Table->add_row( array(
			$n --,
			mysql2date( 'Y-m-d H:i', $pay->time_pay ),
			($pay->type_pay == 2 ? '+ ' . $pay->count_pay . ' ' . $currency : '- ' . $pay->count_pay . ' ' . $currency),
			$user_balance,
			$pay->comment_pay
		) );
	}

	$content .= $Table->get_table();

	$content .= $rclnavi->pagenavi();

	return $content;
}


function mw_wallet_requests_tab_custom( $master_id ) {

	$content = '';

	if ( ! mw_get_user_request( $master_id ) ) {
		$content .= mw_get_request_form_custom();
		$content .= '<hr>';
	}

	$content .= mw_requests_history_tab_custom( $master_id );

	return $content;
}


function mw_get_request_form_custom() {

	 global $userdata, $user_ID;

	$min		 = rcl_get_option( 'mw_min', 0 );
	$default	 = isset( $_COOKIE['rcl_wallet'] ) ? json_decode( wp_unslash( $_COOKIE['rcl_wallet'] ) ) : false;
	$paySystems	 = array_map( 'trim', explode( ',', rcl_get_option( 'pay_system_request' ) ) );

	$values = array();
	for ( $a = 0; $a < count( $paySystems ); $a ++ ) {
		$values[$paySystems[$a]] = $paySystems[$a];
	}

	

	

	$content = '<h3>' . __( 'Форма запроса на вывод средств' ) . '</h3>';
	/*
	$paySystems	 = array_map( 'trim', explode( ',', rcl_get_option( 'pay_system_request' ) ) );

	$values = array();
	$html_select = '';
	for ( $a = 0; $a < count( $paySystems ); $a ++ ) {
		$html_select .= '<option value="'.$paySystems[$a].'">'.$paySystems[$a].'</option>';
	}
	*/
	$html_select = '';
	$accDetailsFields = rcl_get_accDetails_fields(array('user_id' => $master_id));

	$CF = new Rcl_Custom_Fields();
	$accDetailsFields = stripslashes_deep($accDetailsFields);
	$hiddens = [];

	foreach ($accDetailsFields as $field) {
        $field = apply_filters('custom_field_profile', $field);
        $slug = isset($field['name']) ? $field['name'] : $field['slug'];
        if (!$field || !$slug) {
            continue;
        }

        if ($field['type'] == 'hidden') {
            $hiddens[] = $field;
            continue;
        }

        $value = (isset($userdata->$slug)) ? $userdata->$slug : false;

        $trAttrs = [];

        $attrs = [];

        $trAttrs['class'] = [

            'field-' . $slug,

            'form-block-rcl'

        ];

        if (isset($field['id'])) {

            $trAttrs['id'] = $field['id'];

        }

        if (isset($field['class'])) {

            $trAttrs['class'][] = $field['class'];

        }

        foreach ($trAttrs as $k => $attr) {

            if (is_array($attr)) {

                $attrs[] = $k . '="' . implode(' ', $attr) . '"';

            } else {

                $attrs[] = $k . '="' . $attr . '"';

            }

        }

        if (isset($field['attr'])) {

            $attrs[] = $field['attr'];

        }

        if ($field['slug'] != 'show_admin_bar_front' && !isset($field['value_in_key'])) {

            $field['value_in_key'] = true;

        }

        $star = (isset($field['required']) && $field['required'] == 1) ? ' <span class="required">*</span> ' : '';
        $label = sprintf('<label>%s%s</label>', $CF->get_title($field), $star);

        $html_select .= '<option value="'.$value.'">'.$label.'</option>';

    }


	$content .= '<form method="post" action="">
   <div class="rcl-field type-select-field">
      <span class="rcl-field-title">Платежная система <span class="required">*</span></span>
      <div id="rcl-field-pay_system" class="type-select-input rcl-field-input">
         <div class="rcl-field-core">
            <select required="required" name="pay_system" id="pay_system" class="select-field">
               <option value="">Выберите вариант вывода</option>
               '.$html_select.'
            </select>
         </div>
      </div>
   </div>
   <div class="rcl-field type-text-field">
      <span class="rcl-field-title">Номер кошелька/счета <span class="required">*</span></span>
      <div id="rcl-field-wallet_system" class="type-text-input rcl-field-input">
         <div class="rcl-field-core"><input type="text" required="required" class="text-field" name="wallet_system" id="wallet_system" value="123123123123"></div>
      </div>
   </div>
   <div class="rcl-field type-number-field">
      <span class="rcl-field-title">Сумма запроса <span class="required">*</span></span>
      <div id="rcl-field-output_size" class="type-number-input rcl-field-input">
         <div class="rcl-field-core"><input type="number" min="'.$min.'" required="required" class="number-field" name="output_size" id="output_size" value="0"></div>
         <span class="rcl-field-notice"><i class="rcli fa-info" aria-hidden="true"></i>Минимальная сумма запроса: '.$min.' <i class="rcli fa-rub"></i></span>
      </div>
   </div>';

   if ( rcl_get_option( 'mw_need_pass', false ) ) {
   	$content .= '
   <div class="rcl-field type-password-field">
      <span class="rcl-field-title">Пароль аккаунта <span class="required">*</span></span>
      <div id="rcl-field-user_pass" class="type-password-input rcl-field-input">
         <div class="rcl-field-core"><input type="password" required="required" class="password-field vis-pass-field" name="user_pass" id="user_pass" value=""><div class="vis-pass-cont"><i class="rcli fa-eye" aria-hidden="true"></i></div></div>
         <span class="rcl-field-notice"><i class="rcli fa-info" aria-hidden="true"></i>Укажите пароль от своего аккаунта</span>
      </div>
   </div>
   ';
	}
	$content .= '
   <div class="rcl-field type-textarea-field">
      <span class="rcl-field-title">Комментарий</span>
      <div id="rcl-field-comment_text" class="type-textarea-input rcl-field-input">
         <div class="rcl-field-core"><textarea name="comment_text" placeholder="Комментарий" class="textarea-field" id="comment_text" rows="5" cols="50"></textarea></div>
      </div>
   </div>
   <div class="submit-box"><a href="javascript:void(0);" title="Отправить" onclick="rcl_send_form_data(&quot;mw_new_output_request_custom&quot;, this);return false;" class="rcl-bttn rcl-bttn__type-primary rcl-bttn__size-standart"><i class="rcl-bttn__ico rcl-bttn__ico-left rcli fa-check-circle"></i><span class="rcl-bttn__text">Отправить</span></a></div>
</form>';

	if ( $perc = rcl_get_option( 'percent_output_request', 0 ) ) {
		$content .= '<div class="wm-percents">';
		$content .= '<div class="wm-percent">' . sprintf( __( 'Комиссия запроса на вывод: %s', 'rcl-wallet' ), $perc . ' %' ) . '</div>';
		$percScale = rcl_get_option( 'percent_scale', false );
		if ( $percScale && is_array( $percScale ) && $percScale[0] ) {
			foreach ( $percScale as $p ) {
				$p = array_map( 'trim', explode( '=', $p ) );
				$content .= '<div class="wm-percent">' . __( 'от' ) . ' ' . $p[0] . ' ' . rcl_get_primary_currency( 1 ) . ': ' . $p[1] . '%</div>';
			}
		}
		$content .= '</div>';
	}

	return $content;
}

function mw_requests_history_tab_custom( $master_id ) {

	$content = '<h3>' . __( 'История запросов на вывод средств', 'rcl-wallet' ) . '</h3>';

	$cnt = mw_count_requests( array(
		'user_rq' => $master_id
		) );

	if ( ! $cnt ) {
		return $content . rcl_get_notice( ['text' => __( 'Запросов на вывод еще не было', 'rcl-wallet' ) ] );
	}

	$rclnavi = new Rcl_PageNavi( 'rcl-wallet', $cnt );

	$history = mw_get_requests( array(
		'user_rq'	 => $master_id,
		'number'	 => 30,
		'offset'	 => $rclnavi->offset
		) );

	$n = $cnt - $rclnavi->offset;

	$percent = rcl_get_option( 'percent_output_request', 0 );

	$content .= $rclnavi->pagenavi();

	$Table = new Rcl_Table( array(
		'cols'	 => array(
			array(
				'title'	 => __( 'Дата', 'rcl-wallet' ),
				'width'	 => 30, 'align'	 => 'center'
			),
			array(
				'title'	 => __( 'Cумма запроса', 'rcl-wallet' ),
				'width'	 => 20, 'align'	 => 'center'
			),
			array(
				'title'	 => __( 'Сумма вывода', 'rcl-wallet' ),
				'width'	 => 20, 'align'	 => 'center'
			),
			array(
				'title'	 => __( 'Комментарий', 'rcl-wallet' ),
				'width'	 => 30
			),
			array(
				'title'	 => __( 'Статус', 'rcl-wallet' ),
				'width'	 => 30
			),
			array(
				'title'	 => '',
				'width'	 => 10, 'align'	 => 'center'
			)
		),
		'zebra'	 => 1,
		'border' => array( 'table', 'cols', 'rows' )
		) );

	foreach ( $history as $request ) {

		$output = '';

		if ( $request->status_rq == 1 ) {

			$output = mw_get_output_amount( $request->count_rq ) . ' ' . rcl_get_primary_currency( 1 );
		} else {
			$output = $request->output_rq . ' ' . rcl_get_primary_currency( 1 );
		}

		$status = '';

		if ( $request->status_rq == 1 ) {
			$status = '<span style="color:red;">' . __( 'Consideration', 'rcl-wallet' ) . '</span>';
		}
		if ( $request->status_rq == 2 ) {
			$status = '<span style="color:green;">' . __( 'Made', 'rcl-wallet' ) . '</span>';
		}

		$Table->add_row( array(
			mysql2date( 'Y-m-d H:i', $request->time_rq ),
			$request->count_rq . ' ' . rcl_get_primary_currency( 1 ),
			$output,
			$request->comment_rq,
			$status,
			($request->status_rq == 1 ? '<span style="color:red;" onclick="mw_cancel_request(' . $request->ID . ');return false;"><i class="rcli fa-trash" aria-hidden="true"></i></span>' : '')
		) );
	}

	$content .= $Table->get_table();

	$content .= $rclnavi->pagenavi();

	return $content;
}

//Функция создания вывода
rcl_ajax_action( 'mw_new_output_request_custom', false );
function mw_new_output_request_custom() {
	global $wpdb, $user_ID, $rcl_options;


	$count	 = rcl_commercial_round( str_replace( ',', '.', $_POST['output_size'] ) );
	$type	 = $_POST['pay_system'];
	$wallet	 = $_POST['wallet_system'];

	if ( mw_get_user_request( $user_ID ) ) {
		wp_send_json( array(
			'error' => __( 'Уже есть один действующий запрос на вывод средств!' )
		) );
	};

	if ( rcl_get_option( 'mw_need_pass', false ) ) {
		$user		 = get_userdata( $user_ID );
		$password	 = $_POST['user_pass'];
		if ( ! wp_check_password( $password, $user->data->user_pass ) ) {
			wp_send_json( array(
				'error' => __( 'Неверный пароль!' )
			) );
		}
	}

	$balance = rcl_get_user_balance( $user_ID );

	$newusercount = $balance - $count;

	if ( $newusercount < 0 ) {
		wp_send_json( array(
			'error' => __( 'Insufficient funds on personal account!', 'rcl-wallet' ) )
		);
	}

	$min = rcl_commercial_round( rcl_get_option( 'mw_min', 0 ) );

	if ( $min && $min > $count ) {
		$log['error'] = __( 'Ошибка! Минимальная сумма запроса: ' . $min . ' ' . rcl_get_primary_currency( 1 ), 'wp-recall' );
		wp_send_json( $log );
	}

	rcl_update_user_balance( $newusercount, $user_ID, __( 'Lock means on request', 'rcl-wallet' ) );

	mw_add_request( array(
		'user_rq'	 => $user_ID,
		'count_rq'	 => $count,
		'comment_rq' => $type . ' ' . str_replace( array( ' ', '-' ), '', $wallet ),
	) );

	setcookie( 'rcl_wallet', json_encode( array( 'type' => $type, 'wallet' => $wallet ) ), time() + 31104000, '/' );

	$subject	 = __( 'Request the withdrawal', 'rcl-wallet' );
	$textmail	 = '
    <h3>' . __( 'Request data', 'rcl-wallet' ) . ':</h3>
    <p>' . __( 'The amount of the request', 'rcl-wallet' ) . ': ' . $count . '</p>
    <p>' . __( 'Account number', 'rcl-wallet' ) . ': ' . $type . ' ' . $wallet . '</p>
    <p>Комментарий к выводу:</p>
    <p>'.$_POST['comment_text'].'</p>
    ';
	$admin_email = get_option( 'admin_email' );
	rcl_mail( $admin_email, $subject, $textmail );

	wp_send_json( array(
		'success'	 => __( 'Запрос успешно добавлен' ),
		'reload'	 => true
	) );
}

remove_action( 'widgets_init', 'generate_widgets_init' );

add_action( 'widgets_init', 'generate_widgets_init' );
	/**
	 * Register widgetized area and update sidebar with default widgets
	 */
	function generate_widgets_init() {
		$widgets = array(
			'sidebar-1' => __( 'Сайдбар заданий', 'generatepress' ),
			'sidebar-2' => __( 'Сайдбар фрилансеров', 'generatepress' ),
			'header' => __( 'Header', 'generatepress' ),
			'footer-1' => __( 'Сайдбар подвала', 'generatepress' ),
			'footer-bar' => __( 'Footer Bar', 'generatepress' ),
			'home-sidebar' => 'Сайдбар для главной',
			'blog-sidebar' => 'Сайдбар для блога',
			'customer-sidebar' => 'Сайдбар для заказчиков',
			'service-sidebar' => 'Сайдбар услуг',
			'group-sidebar' => 'Сайдбар групп'
		);

		foreach ( $widgets as $id => $name ) {
			register_sidebar(
				array(
					'name'          => $name,
					'id'            => $id,
					'before_widget' => '<aside id="%1$s" class="widget inner-padding %2$s">',
					'after_widget'  => '</aside>',
					'before_title'  => apply_filters( 'generate_start_widget_title', '<h2 class="widget-title">' ),
					'after_title'   => apply_filters( 'generate_end_widget_title', '</h2>' ),
				)
			);
		}
	}



add_action( 'wp_loaded', function(){
	remove_action( 'register_form', 'rcl_filters_regform', 1 );
} );

remove_action('widgets_init', 'pfm_easy_widgets_init_block_l');

add_action( 'register_form', 'rcl_filters_regform_custom', 1 );

function rcl_filters_regform_custom() {

	$regfields = '';

	echo apply_filters( 'regform_fields_rcl_custom', $regfields );

}

add_filter( 'regform_fields_rcl_custom', 'rcl_password_regform_custom', 5 );

function rcl_password_regform_custom( $content ) {



	$difficulty = rcl_get_option( 'difficulty_parole' );



	$content .= '<div class="form-block-rcl default-field">';



	$content .= '

<div class="rcl-field type-password-field">
      <span class="rcl-field-title">Пароль<span class="required">*</span></span>
      <div id="rcl-field-user_pass" class="type-password-input rcl-field-input">
         <div class="rcl-field-core"><input type="password" required class="password-field vis-pass-field main-form-field-pass" name="user_pass" id="primary-pass-user" ' . ($difficulty == 1 ? 'onkeyup="passwordStrength(this.value)"' : '') . ' value=""><div class="vis-pass-cont custom-form-main-icon"><i class="rcli fa-eye" aria-hidden="true"></i></div></div>
      </div>
   </div>
	';



	$content .= '';

	$content .= '';

	$content .= '</div>';



	if ( $difficulty == 1 ) {

		$content .= '<div class="form-block-rcl">

                <label>' . __( 'Password strength indicator', 'wp-recall' ) . ':</label>

                <div id="passwordStrength" class="strength0">

                    <div id="passwordDescription">' . __( 'A password has not been entered', 'wp-recall' ) . '</div>

                </div>

            </div>';

	}



	return $content;

}

add_filter( 'regform_fields_rcl_custom', 'rcl_secondary_password_custom', 10 );

function rcl_secondary_password_custom( $fields ) {



	if ( ! rcl_get_option( 'repeat_pass' ) )

		return $fields;



	$fields .= '<div class="form-block-rcl default-field">

                   

                   <div class="rcl-field type-password-field">
      <span class="rcl-field-title">Повтор пароля <span class="required">*</span></span>
      <div id="rcl-field-user_pass" class="type-password-input rcl-field-input">
         <div class="rcl-field-core"><input type="password" required class="password-field vis-pass-field main-form-field-pass" name="user_secondary_pass" id="secondary-pass-user" value=""><div class="vis-pass-cont custom-form-main-icon"><i class="rcli fa-eye" aria-hidden="true"></i></div></div>
      </div>
   </div>

                <div id="notice-chek-password"></div>

            </div>

            <script>jQuery(function(){

            jQuery("#registerform,.form-tab-rcl").on("keyup","#secondary-pass-user",function(){

                var pr = jQuery("#primary-pass-user").val();

                var sc = jQuery(this).val();

                var notice;

                if(pr!=sc) notice = "<span class=login-error>' . __( 'The passwords do not match!', 'wp-recall' ) . '</span>";

                else notice = "<span class=login-message>' . __( 'The passwords match', 'wp-recall' ) . '</span>";

                jQuery("#notice-chek-password").html(notice);

            });});

        </script>';



	return $fields;

}

add_filter( 'regform_fields_rcl_custom', 'rcl_custom_fields_regform_custom', 20 );

function rcl_custom_fields_regform_custom( $content ) {



	$regFields = array();



	$fields = rcl_get_profile_fields();



	if ( $fields ) {



		foreach ( $fields as $field ) {



			if ( ! isset( $field['register'] ) || $field['register'] != 1 )

				continue;



			if ( ! isset( $field['value_in_key'] ) )

				$field['value_in_key'] = true;



			$regFields[] = $field;

		}

	}



	$regFields = apply_filters( 'rcl_register_form_fields', stripslashes_deep( $regFields ) );



	if ( ! $regFields )

		return $content;



	$hiddens = array();

	foreach ( $regFields as $field ) {



		$field = apply_filters( 'custom_field_regform', $field );



		if ( $field['type'] == 'hidden' ) {

			$hiddens[] = $field;

			continue;

		}



		$class			 = (isset( $field['class'] )) ? $field['class'] : '';

		$id				 = (isset( $field['id'] )) ? 'id=' . $field['id'] : '';

		$attr			 = (isset( $field['attr'] )) ? '' . $field['attr'] : '';

		$field['value']	 = isset( $_POST[$field['slug']] ) ? $_POST[$field['slug']] : false;



		unset( $field['class'] );

		unset( $field['attr'] );

		unset( $field['id'] );



		$fieldObject = Rcl_Field::setup( $field );



		$content .= '<div class="form-block-rcl ' . $class . '" ' . $id . ' ' . $attr . '>';



		if ( $fieldObject->title ) {

			$content .= '<label>' . $fieldObject->get_title();

			if ( $field['type'] )

				$content .= '<span class="colon">:</span>';

			$content .= '</label>';

		}



		$content .= $fieldObject->get_field_input();



		$content .= '</div>';

	}



	foreach ( $hiddens as $field ) {

		$field['value']	 = isset( $_POST[$field['slug']] ) ? $_POST[$field['slug']] : false;

		$fieldObject	 = Rcl_Field::setup( $field );

		$content .= $fieldObject->get_field_input();

	}



	return $content;

}


// Свои сайт бары

add_action('add_meta_boxes', 'custom_sidebar_select_function', 1);

function custom_sidebar_select_function() {
	add_meta_box( 'select_sidebar', 'Выбор сайдбара', 'extra_fields_box_func', 'page', 'side', 'high'  );
}

function extra_fields_box_func( $post ){
	?>

	<p>Выберите сайдбар, который будет отображаться на странице</p>
	<select name="sidebar-custom">
		<?php $sel_v = get_post_meta($post->ID, 'custom-select-sidebar', 1); ?>
		<option value="no-sidebar">Без сайдбара</option>
		<option value="sidebar-1" <?php selected( $sel_v, 'sidebar-1' )?> >Сайдбар заданий</option>
		<option value="sidebar-2" <?php selected( $sel_v, 'sidebar-2' )?> >Сайдбар фрилансеров</option>
		<option value="home-sidebar" <?php selected( $sel_v, 'home-sidebar' )?> >Сайдбар для главной</option>
		<option value="customer-sidebar" <?php selected( $sel_v, 'customer-sidebar' )?> >Сайдбар для заказчиков</option>
		<option value="service-sidebar" <?php selected( $sel_v, 'service-sidebar' )?> >Сайдбар услуг</option>
		<option value="group-sidebar" <?php selected( $sel_v, 'group-sidebar' )?> >Сайдбар групп</option>
	</select>

	<input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />
	<?php
}

add_action( 'save_post', 'my_extra_fields_update', 0 );

## Сохраняем данные, при сохранении поста
function my_extra_fields_update( $post_id ){
	// базовая проверка
	if (
		   empty( $_POST['sidebar-custom'] )
		|| ! wp_verify_nonce( $_POST['extra_fields_nonce'], __FILE__ )
		|| wp_is_post_autosave( $post_id )
		|| wp_is_post_revision( $post_id )
	)
		return false;

	update_post_meta( $post_id, 'custom-select-sidebar', $_POST['sidebar-custom'] );

	return $post_id;
}

// Убираем сайдбар 
remove_action( 'widgets_init', 'uit_bottom_sidebar' );

// Удаляем отображение меню из дополнения
remove_action( 'wp_footer', 'mbspro_add_menu_bttn', 500 );


// Удаляем стандартный вывод кнопки вызова мобильно сайдбара
remove_action( 'init', 'mbspro_place_menu_button', 10 );
// Добавляем кнопку вызова мобильного сайдбара


add_action( 'rcl_bar__icons_custom', 'mbspro_add_recallbar_bttn_custom', 10 );

function mbspro_add_recallbar_bttn_custom() {

	if ( is_admin() )

        return;

    $icon = rcl_get_option( 'mbsp_ico', 'fa-chevron-down' );

    $text = rcl_get_option( 'mbsp_text', 'Меню' );



    $out = '<div id="mbspro_bttn_custom">';

    $out .= '<i class="rcli ' . $icon . '" aria-hidden="true"></i>';


    $out .= '</div>';



    echo $out;

}

// Переписываем шорт код вывода пользователей

remove_shortcode( 'userlist');

add_shortcode( 'userlist', 'rcl_get_userlist_1' );

function rcl_get_userlist_1( $atts ) {

	global $rcl_user, $rcl_users_set, $user_ID;



	require_once RCL_PATH . 'classes/class-rcl-users-list.php';

	// Что бы  не потерять параметр
	if(isset( $_GET['page_id'])){
		//$param_page = $_GET['page_id'];
		//unset($_GET['page_id']);
	}

	//if(isset( $_GET['users-filter'])){
		//$param_orderby = $_GET['users-filter'];
		//unset($_GET['users-filter']);
	//}
	

	if(isset( $_GET['usf'] )){
		if($_GET['usf'] == '1'){

			$is_usf = true;
			unset($_GET['usf']);

			if(isset($_GET['users-filter'])){
				$param_filter = $_GET['users-filter'];
				unset($_GET['users-filter']);
				$is_filter = true;
			}

			$new_atts = $atts;
			foreach($_GET as $param_get => $value){
				$string_usergroup .= $param_get.':'.$value;
				if(count($_GET) > 1){
					$string_usergroup .= '|';
				}
			}

			if($is_filter){
				$_GET['users-filter'] = $param_filter;
			}

			if(count($_GET) > 1){
				$string_usergroup = substr($string_usergroup,0,-1);
			}

			$_GET['usf'] = 1;
			

			$new_atts['usergroup'] = $string_usergroup;

			//if(isset( $_GET['users-filter'])){
				//unset($new_atts['orderby']);
				//$new_atts['orderby'] = $param_orderby;
			//}
			//$userlist .= $new_atts['orderby'];


			$users = new Rcl_Users_List($new_atts);
			//$userlist .= var_export($users);

		}
	}
	else{
		$users = new Rcl_Users_List( $atts );
		//$userlist .= var_export($users);
	}

	




	$count_users = false;



	if ( ! isset( $atts['number'] ) ) {



		$count_users = $users->count();



		$id_pager = ($users->id) ? 'rcl-users-' . $users->id : 'rcl-users';



		$pagenavi = new Rcl_PageNavi( $id_pager, $count_users, array( 'in_page' => $users->query['number'] ) );



		$users->query['offset'] = $pagenavi->offset;

	}



	$timecache = ($user_ID && $users->query['number'] == 'time_action') ? rcl_get_option( 'timeout', 600 ) : 0;



	$rcl_cache = new Rcl_Cache( $timecache );



	if ( $rcl_cache->is_cache ) {

		if ( isset( $users->id ) && $users->id == 'rcl-online-users' )

			$string	 = json_encode( $users );

		else

			$string	 = json_encode( $users->query );



		$file = $rcl_cache->get_file( $string );



		if ( ! $file->need_update ) {



			$users->remove_filters();



			return $rcl_cache->get_cache();

		}

	}



	$usersdata = $users->get_users();


	// Вывод количества пользователей и сортировки
	//$userlist .= $users->get_filters( $count_users );
	// Выводим свои кнопки сортировки

	
	// Формируем строку с параметрами поиска при сортровке
	if($is_usf){

		$userlist .='<h3>' . __( 'Total number of users', 'wp-recall' ) . ': ' . $count_users . '</h3>';
		$userlist .= '<div class="rcl-data-filters">' . __( 'Filter by', 'wp-recall' ) . ': ';

		if(isset($_GET['users-filter'])){
			$param_filter = $_GET['users-filter'];
			unset($_GET['users-filter']);
			$is_filter = true;
		}

		$string_get = '&';
		foreach($_GET as $param_get => $value){
			$string_get .= $param_get.'='.$value;
			if(count($_GET) > 1){
				$string_get .= '&';
			}
		}

		if(count($_GET) > 1){
			$string_get = substr($string_get ,0,-1);
		}
		if($is_filter){
			$_GET['users-filter'] = $param_filter;
		}
		if($is_filter){
			if($_GET['users-filter'] == 'time_action'){
				$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=time_action'.$string_get.'" title="Активности" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart rcl-bttn__disabled"><span class="rcl-bttn__text">Активности</span></a>';
			}
			else{
				$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=time_action'.$string_get.'" title="Активности" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart"><span class="rcl-bttn__text">Активности</span></a>';
			}

			if($_GET['users-filter'] == 'posts_count'){
				$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=posts_count'.$string_get.'" title="Публикациям" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart rcl-bttn__disabled"><span class="rcl-bttn__text">Публикациям</span></a>';
			}
			else{
				$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=posts_count'.$string_get.'" title="Публикациям" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart"><span class="rcl-bttn__text">Публикациям</span></a>';
			}

			if($_GET['users-filter'] == 'comments_count'){
				$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=comments_count'.$string_get.'" title="Комментарии" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart rcl-bttn__disabled"><span class="rcl-bttn__text">Комментарии</span></a>';
			}
			else{
				$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=comments_count'.$string_get.'" title="Комментарии" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart"><span class="rcl-bttn__text">Комментарии</span></a>';
			}

			if($_GET['users-filter'] == 'user_registered'){
				$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=user_registered'.$string_get.'" title="Регистрация" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart rcl-bttn__disabled"><span class="rcl-bttn__text">Регистрация</span></a>';
			}
			else{
				$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=user_registered'.$string_get.'" title="Регистрация" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart"><span class="rcl-bttn__text">Регистрация</span></a>';
			}

			if($_GET['users-filter'] == 'rating_total'){
				$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=rating_total'.$string_get.'" title="Рейтингу" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart rcl-bttn__disabled"><span class="rcl-bttn__text">Рейтингу</span></a></div>';
			}
			else{
				$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=rating_total'.$string_get.'" title="Рейтингу" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart"><span class="rcl-bttn__text">Рейтингу</span></a></div>';
			}
		}
		else{
			$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=time_action'.$string_get.'" title="Активности" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart rcl-bttn__disabled"><span class="rcl-bttn__text">Активности</span></a>';

			$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=posts_count'.$string_get.'" title="Публикациям" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart"><span class="rcl-bttn__text">Публикациям</span></a>';

			$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=comments_count'.$string_get.'" title="Комментарии" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart"><span class="rcl-bttn__text">Комментарии</span></a>';

			$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=user_registered'.$string_get.'" title="Регистрация" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart"><span class="rcl-bttn__text">Регистрация</span></a>';

			$userlist .= '<a href="'.get_the_permalink(get_the_ID()).'?users-filter=rating_total'.$string_get.'" title="Рейтингу" class="rcl-bttn data-filter rcl-bttn__type-primary rcl-bttn__size-standart"><span class="rcl-bttn__text">Рейтингу</span></a></div>';
		}


	}
	else{
		$userlist .= $users->get_filters( $count_users );
	}



	$userlist .= '<div class="rcl-userlist">';



	if ( ! $usersdata ) {

		$userlist .= rcl_get_notice( ['text' => __( 'Users not found', 'wp-recall' ) ] );

	} else {



		if ( ! isset( $atts['number'] ) && $pagenavi->in_page ) {

			$userlist .= $pagenavi->pagenavi();

		}



		$userlist .= '<div class="userlist ' . $users->template . '-list">';



		$rcl_users_set = $users;


		foreach ( $usersdata as $rcl_user ) {

			

				$users->setup_userdata( $rcl_user );

				$userlist .= rcl_get_include_template( 'user-' . $users->template . '.php' );
			


		}



		$userlist .= '</div>';



		if ( ! isset( $atts['number'] ) && $pagenavi->in_page ) {

			$userlist .= $pagenavi->pagenavi();

		}

	}



	$userlist .= '</div>';



	$users->remove_filters();



	if ( $rcl_cache->is_cache ) {

		$rcl_cache->update_cache( $userlist );

	}



	return $userlist;
}
//-------------------------------------------------------
//             Дополняем фильтр пользователей           |
//-------------------------------------------------------




class Usf_Manager_1 extends Rcl_Custom_Fields_Manager {

	public $default_fields = array();

	function __construct( $typeFields, $args = false ) {

		parent::__construct( $typeFields, $args );

		$profileFields = new Rcl_Profile_Fields_Manager();

		if ( $fields = get_site_option( 'rcl_profile_fields' ) ) {

			$types = array(
				'text',
				'textarea',
				'select',
				'multiselect',
				'checkbox',
				'radio',
				'number',
				'date',
				'dynamic',
				'runner',
				//'range'
			);

			foreach ( $fields as $field ) {
				if ( ! in_array( $field['type'], $types ) )
					continue;
				$this->default_fields[] = $field;
			}
		}

		add_filter( 'rcl_default_custom_fields_1', array( $this, 'add_default_filter_fields_1' ) );
		add_filter( 'rcl_inactive_custom_fields_1', array( $this, 'activate_type_editor_1' ) );
		add_filter( 'rcl_active_custom_fields_1', array( $this, 'activate_type_editor_1' ) );
		add_filter( 'rcl_custom_field_types_1', array( $this, 'edit_field_types_1' ), 10, 2 );
		add_filter( 'rcl_custom_fields_form_1', array( $this, 'add_users_page_option_1' ) );
	}

	function activate_type_editor_1( $fields ) {
		foreach ( $fields as $k => $field ) {
			$fields[$k]['type-edit'] = true;
		}
		return $fields;
	}

	function add_default_filter_fields_1( $fields ) {

		if ( $this->default_fields )
			$fields = array_merge( $fields, $this->default_fields );

		return $fields;
	}

	function active_fields_box_1() {

		$content = $this->manager_form(
			array(
				array(
					'type'	 => 'textarea',
					'slug'	 => 'notice',
					'title'	 => __( 'field description', 'wp-recall' )
				)
			)
		);

		return $content;
	}

	function edit_field_types_1( $types, $field ) {

		$profileTypeField = isset( $field['profile-type-field'] ) ? $field['profile-type-field'] : $field['type'];

		if ( in_array( $profileTypeField, array(
				'text', 'textarea'
			) ) ) {
			$types = array(
				'text'		 => $types['text'],
				'textarea'	 => $types['textarea']
			);
		} else if ( in_array( $profileTypeField, array(
				'checkbox', 'multiselect'
			) ) ) {
			$types = array(
				'select'		 => $types['select'],
				'radio'			 => $types['radio'],
				'checkbox'		 => $types['checkbox'],
				'multiselect'	 => $types['multiselect']
			);
		} else if ( in_array( $profileTypeField, array(
				'number', 'runner', 'range'
			) ) ) {
			$types = array(
				'number' => $types['number'],
				'runner' => $types['runner'],
				'range'	 => $types['range']
			);
		} else if ( in_array( $profileTypeField, array(
				'select', 'radio'
			) ) ) {
			$types = array(
				'select' => $types['select'],
				'radio'	 => $types['radio']
			);
		} else {
			$types = array(
				$field['type'] => $types[$field['type']]
			);
		}

		return $types;
	}

	function add_users_page_option_1( $content ) {

		$content .= '<h4>' . __( 'Users page', 'wp-recall' ) . '</h4>'
			. '<style>#users_page_rcl{max-width:100%;}</style>'
			. wp_dropdown_pages( array(
				'selected'			 => rcl_get_option( 'users_page_rcl' ),
				'name'				 => 'users_page_rcl',
				'show_option_none'	 => __( 'Not selected', 'wp-recall' ),
				'echo'				 => 0 )
			)
			. '<p>' . __( 'Укажите страницу, на которой производится вывод пользователей через шорткод [userlist]' ) . '</p>';

		$content .= '<h4>' . __( 'Правило подбора по параметрам' ) . '</h4>'
			. '<p><select name="usf-relation-1">
                    <option ' . selected( rcl_get_option( 'usf-relation_1', 'AND' ), 'AND', false ) . ' value="AND">И</option>
                    <option ' . selected( rcl_get_option( 'usf-relation_1' ), 'OR', false ) . ' value="OR">ИЛИ</option>
                </select></p>'
			. '<p>' . __( 'Правило "ИЛИ" увеличивает нагрузку на базу данных' ) . '</p>';

		return $content;
	}

}


remove_action( 'admin_menu', 'usf_init_manager', 30 );

add_action( 'admin_menu', 'usf_init_manager_1', 30 );

function usf_init_manager_1() {
	add_submenu_page( 'manage-wprecall', __( 'Фильтр пользователей' ), __( 'Фильтр пользователей' ), 'manage_options', 'usf-manager', 'usf_manager_1' );
}

function usf_manager_1() {

	rcl_sortable_scripts();

	$manager_freelancer = new Usf_Manager( 'users_filter', array(
		'create-field' => false
	) );

	$content = '<h2>' . __( 'Менеджер фильтра пользователей (Исполнители)' ) . '</h2>';
	$content .= $manager_freelancer->active_fields_box();
	$content .= $manager_freelancer->inactive_fields_box();

	$manager_customer = new Usf_Manager_1( 'users_filter_customer', array(
		'create-field' => false
	) );

	$content_1 = '<h2>' . __( 'Менеджер фильтра пользователей (Заказчики)' ) . '</h2>';
	$content_1 .= $manager_customer->active_fields_box_1();
	$content_1 .= $manager_customer->inactive_fields_box();



	echo $content;
	echo $content_1;
}

function usf_get_form_fields_1() {

	return apply_filters( 'usf_form_fields', get_site_option( 'rcl_fields_users_filter_customer' ) );

}

// Пробуем создать шорт код на основе существующего для заказчиков

add_shortcode( 'rcl-users-filter-customer', 'usf_get_form_customer' );

function usf_get_form_customer( $atts = false ) {
	global $wpdb;

	extract( shortcode_atts( array(
		'column' => 1
			), $atts ) );

	$fields = usf_get_form_fields_1();

	if ( ! $fields )
		return '<p>' . __( 'Поисковая форма не была сформирована или поля формы не были найдены.' ) . '</p>';

	foreach ( $fields as $k => $field ) {

		$fields[$k]['value_in_key'] = true;

		if ( isset( $_GET[$field['slug']] ) ) {
			$fields[$k]['default'] = $_GET[$field['slug']];
		}
	}


	if ( isset( $_GET['page_id'] ) ) {
		$fields[] = array(
			'type'	 => 'hidden',
			'slug'	 => 'page_id',
			'value'	 => $_GET['page_id']
		);
	}

	$fields[] = array(
		'type'	 => 'hidden',
		'slug'	 => 'sm-account',
		'value'	 => 'Заказчик'
	);

	$fields[] = array(
		'type'	 => 'hidden',
		'slug'	 => 'usf',
		'value'	 => '1'
	);

	$content = '<div class="usf-form column-' . $column . '">';

	$content .= rcl_get_form( array(
		'fields' => $fields,
		'method' => 'get',
		'action' => get_the_permalink(get_the_ID()),
		'submit' => __( 'Искать' )
		) );

	$content .= '</div>';

	return $content;
}

// Удаляем старый шорткод и создаем на его основе свой для исполнителей

remove_shortcode( 'rcl-users-filter');

add_shortcode( 'rcl-users-filter-freelancer', 'usf_get_form_freelancer' );
function usf_get_form_freelancer( $atts = false ) {
	global $wpdb;

	extract( shortcode_atts( array(
		'column' => 1
			), $atts ) );

	$fields = usf_get_form_fields();

	if ( ! $fields )
		return '<p>' . __( 'Поисковая форма не была сформирована или поля формы не были найдены.' ) . '</p>';

	foreach ( $fields as $k => $field ) {

		$fields[$k]['value_in_key'] = true;

		if ( isset( $_GET[$field['slug']] ) ) {
			$fields[$k]['default'] = $_GET[$field['slug']];
		}
	}

	if ( isset( $_GET['page_id'] ) ) {
		$fields[] = array(
			'type'	 => 'hidden',
			'slug'	 => 'page_id',
			'value'	 => $_GET['page_id']
		);
	}

	$fields[] = array(
		'type'	 => 'hidden',
		'slug'	 => 'sm-account',
		'value'	 => 'Исполнитель'
	);

	$fields[] = array(
		'type'	 => 'hidden',
		'slug'	 => 'usf',
		'value'	 => '1'
	);

	$content = '<div class="usf-form column-' . $column . '">';

	$content .= rcl_get_form( array(
		'fields' => $fields,
		'method' => 'get',
		'action' => get_the_permalink(get_the_ID()),
		'submit' => __( 'Искать' )
		) );

	$content .= '</div>';

	return $content;
}

remove_filter( 'rcl_users_query', 'usf_edit_users_query' );

// При успешной оплате заказа проверки

add_action('rcl_success_pay','send_mail_new_donate',10);
function send_mail_new_donate($payData){
    
    //проверяем тип платежа, нам нужен 'donate'
    if($payData->pay_type != 'application') return false;
	
	//указываем админский емейл сайта
	$email = get_option('admin_email');
	
	$user_id = $payData->user_id;

	update_user_meta($user_id,'bool_application', '1');
	update_user_meta($user_id,'date_application', time());

	$email = get_option('admin_email');
	$subject = 'Заявка на проверку профиля';
	// готовим текст письма
	$textMail = '<p>Пользователь: '.get_the_author_meta('display_name',$payData->user_id).'</p>';
	$textMail .= '<p>Сумма платежа: '.$payData->pay_summ.'</p>';
	$textMail .= '<p>Система оплаты: '.$payData->current_connect.'</p>';

	rcl_mail($email,$subject,$textMail);
    
}

// Меняем варианты выбора рейтинга

function psr_add_data_1() {

    return [ 'Не указано', 'Рекомендуем', 'Перспективный', 'Один из лучших', 'Профессор' ];

}

remove_filter( 'rcl_default_profile_fields', 'psr_rating_profile', 10, 2 );

add_filter( 'rcl_default_profile_fields', 'psr_rating_profile_1', 10, 2 );
function psr_rating_profile_1( $fields ) {



    $fields[] = array(

        'type'   => 'select',

        'slug'   => 'psr_rating',

        'values' => psr_add_data_1(),

        'title'  => 'Рейтинг от администрации'

    );



    return $fields;

}

remove_filter( 'rcl_profile_fields', 'psr_add_form', 10, 2 );

add_filter( 'rcl_profile_fields', 'psr_add_form_1', 10, 2 );

function psr_add_form_1( $fields, $args ) {

    $rating = psr_add_data_1();

    $type   = 'select';



    // чтоб сам юзер не менял себе значение

    if ( ! current_user_can( 'manage_options' ) ) {

        $val_rating = get_user_meta( $args['user_id'], 'psr_rating', true );



        $rating = ( $val_rating ) ? $val_rating : '';



        $type = 'hidden';

    }



    foreach ( $fields as $field ) {

        if ( $field['slug'] === 'psr_rating' ) {

            $field['type'] = $type;



            if ( $type == 'select' ) {

                $field['values'] = $rating;

            } else if ( $type == 'hidden' ) {

                $field['value'] = $rating;

            }

        }



        $opt[] = $field;

    }



    return $opt;

}


// Создаем крон задачу для проверки срока
 
if( !wp_next_scheduled('check_time_application') )
	wp_schedule_event( time(), 'hourly', 'check_time_application');
 
add_action( 'check_time_application', 'check_time_application_function', 10, 3 );
 
function check_time_application_function() {
	// Получаем текущее время
	$today = time();
	// Получаем всех пользователей
	$users = get_users();
	// Проверяем насколько долго действует проверка (33 дня и обнуление)
	foreach($users as $user){

		$bool_user_application = get_user_meta($user->ID,'bool_application', true);

		if($bool_user_application == '1'){
			$time_user_application = get_user_meta($user->ID,'date_application', true);
			$result_time = (int)$today - (int)$time_user_application;
			if($result_time > 2851200){
				update_user_meta($user->ID,'bool_application','0');
				// Тут будет обновление мнения администрации
				update_user_meta( $user_LK, 'psr_rating', 'Не указано' );
				// А сейчас для проверки шлем письма на почту разработчику
			}
		}
		else{
			update_user_meta($user->ID,'bool_application','0');
		}
	}
}

// Делаем верификацию

add_action('init','register_tab_verification');
function register_tab_verification(){

    $tab_data =	array(
        'id'=>'verification',
        'name'=>'Верификация',
        'icon'=>'fa-check-circle-o',
        'content'=>array(
            array(
                'callback' => array(
                    'name'=>'my_custom_function'
                )
            )
        )
    );

    rcl_tab($tab_data);
}

function my_custom_function(){
    global $user_ID;

	if(vrfd_is_verified($user_ID)){
		$content .= '<p>Ваш аккаунт уже верифицирован</p>';
	}
	else{
		if(get_user_meta($user_ID,'verificate_begin', true) == '1'){
			$content .= '<p>Ваш аккаунт уже проверяется</p>';
		}
		else{

		global $procent_freelancers, $procent_customers; // будет использоваться для проверки заполненности профиля

		$fields = array(
			array(
        		'type' => 'file',
        		'slug' => 'doc-lich',
        		'title' => __('Документ удостоверяющий личность'),
        		'placeholder' => __('Документ удостоверяющий личность'),
        		'required' => 1,
        		'notice' => __('Фотография с паспортом')
    		),
    		array(
        		'type' => 'file',
        		'slug' => 'doc-sertificate',
        		'title' => __('Документ подтверждающий профессиональную компетентность'),
        		'placeholder' => __('Документ подтверждающий профессиональную компетентность'),
        		'required' => 1,
        		'notice' => __('Фотография или скан документа')
    		),
    		array(
        		'type' => 'hidden',
        		'slug' => 'verification_form',
        		'value' => 1
    		)
		);

		$form = rcl_get_form(array(
    		'onclick' => 'rcl_send_form_data("verification_function",this);return false;',
    		'submit' => __('Запросить верификацию'),
    		'fields' => $fields
		));

		$content .= $form;
		}
	}
		

	return $content;
	
}

// Обработчик верификации

rcl_ajax_action('verification_function');
function verification_function(){
    global $user_ID;
    rcl_verify_ajax_nonce();

    $id_lich = $_POST['doc-lich'];
    $id_sertificate = $_POST['doc-sertificate'];

    $userdata = get_userdata($user_ID);

    $content_mail .= '
    	<p>Пользователь <a href="'.get_author_posts_url($user_ID).'">'. $userdata->user_firstname.' '.$userdata->user_lastname.'</a> загрузил документы для верификации</p>
    ';

    $url_lich = wp_get_attachment_image_url($id_lich, 'full');
    $url_lsertificate = wp_get_attachment_image_url($id_sertificate, 'full');

    $content_mail .= '<p>Адрес изображения документа удостоверяющего личность <a href="'.$url_lich.'">тут</a></p>';
    $content_mail .= '<p>Адрес изображения документа подтверждающего профессиональную компетентность <a href="'.$url_lsertificate.'">тут</a></p>';
    $email = get_option('admin_email');
   if(wp_mail($email, 'Верификация пользователей', $content_mail)){

   		update_user_meta($user_ID, 'verificate_begin', '1');

   		 wp_send_json(array(
        	'success' => 'Ваш аккаунт проверят как можно быстрее.', //уведомление об успехе
        	'reload' => true
    	));
   }
   else{
   		 wp_send_json(array(
        	'error' => 'Что-то пошло не так, попробуйте повторить позже.', //уведомление об успехе
        	'reload' => true
    	));
   }
    
}

// Меняем срок исполнения на актуальность задания

function fng_get_task_default_fields_1() {



	$fields = array(

		array(

			'slug'		 => 'fng-price',

			'type'		 => 'number',

			'title'		 => __( 'Стоимость задания (' . rcl_get_primary_currency( 1 ) . ')', 'fng' ),

			'notice'	 => __( 'укажите стоимость задания целым числом, может быть изменено при утверждении исполнителя', 'fng' ),

			'required'	 => 1,

			'value_min'	 => 0

		)

	);


/* Убираем срок выполнения задания
	if ( ! rcl_get_option( 'fng-days-cancel', 0 ) ) {

		$fields[] = array(

			'slug'		 => 'fng-days',

			'type'		 => 'number',

			'title'		 => __( 'Срок выполнения (в днях)', 'fng' ),

			'notice'	 => __( 'укажите срок выполнения задания после которого поднимается вопрос о смене исполнителя или продлении срока выполнения', 'fng' ),

			'required'	 => 1,

			'default'	 => 1,

			'value_min'	 => 1

		);

	}
	*/
// Добавляем актуальность, календарь будет реализован через jquery плагин

$fields[] = array(

			'slug'		 => 'fng-act-date',

			'type'		 => 'date',

			'title'		 => __( 'Актуально до', 'fng' ),

			'notice'	 => __( 'Укажите дату, до которой актуально задание', 'fng' ),

			'required'	 => 1

		);





	return $fields;

}

remove_filter( 'rcl_public_form_fields', 'fng_add_task_default_fields', 10, 2 );

add_filter( 'rcl_public_form_fields', 'fng_add_task_default_fields_1', 10, 2 );

function fng_add_task_default_fields_1( $fields, $form ) {



	if ( ! in_array( $form->post_type, array( 'task' ) ) )

		return $fields;



	if ( $fields ) {

		$fields = array_merge( $fields, fng_get_task_default_fields_1() );

	} else {

		$fields = fng_get_task_default_fields_1();

	}



	return $fields;

}

// Редактирование задания 

remove_action( 'update_post_rcl', 'fng_update_task_meta', 10, 3 );

add_action( 'update_post_rcl', 'fng_update_task_meta_1', 10, 3 );

function fng_update_task_meta_1( $post_id, $postdata, $update ) {



	if ( $postdata['post_type'] != 'task' )

		return false;



	update_post_meta( $post_id, 'fng-price', $_POST['fng-price'] );



	//if ( ! rcl_get_option( 'fng-days-cancel', 0 ) )

		//update_post_meta( $post_id, 'fng-days', $_POST['fng-days'] );

	update_post_meta( $post_id, 'fng-act-date', $_POST['fng-act-date'] );

	if ( ! $update )

		update_post_meta( $post_id, 'fng-status', 1 );

	
	


}

// Сохранение задания 

remove_action( 'save_post', 'fng_update_custom_fields_with_save_task', 0 );

add_action( 'save_post', 'fng_update_custom_fields_with_save_task_1', 0 );

function fng_update_custom_fields_with_save_task_1( $post_id ) {



	if ( ! isset( $_POST['custom_fields_nonce_rcl'] ) || $_POST['post_type'] != 'task' )

		return false;


	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )

		return false;

	if ( ! current_user_can( 'edit_post', $post_id ) )

		return false;



	if ( isset( $_POST['fng-status'] ) )

	update_post_meta( $post_id, 'fng-status', $_POST['fng-status'] );



	update_post_meta( $post_id, 'fng-price', $_POST['fng-price'] );



	//if ( ! rcl_get_option( 'fng-days-cancel', 0 ) )

		//update_post_meta( $post_id, 'fng-days', $_POST['fng-days'] );

	update_post_meta( $post_id, 'fng-act-date', $_POST['fng-act-date'] );

	


}

// Изменение функции вывода контента задания 

function fng_get_task_meta_box_1( $post_id ) {

	rcl_datepicker_scripts();

	$content = '<div class="fng-task-metas">

                <div class="task-meta task-price">' . get_post_meta( $post_id, 'fng-price', 1 ) . ' ' . rcl_get_primary_currency( 1 ) . '</div>

                ' . fng_get_post_terms( $post_id )

		.'<div class="task-meta">

                    <i class="rcli fa-clock-o far fa-clock rcl-icon"></i>' . __( 'Актуально до', 'fng' ) . ': ' . get_post_meta( $post_id, 'fng-act-date', 1 ) . '

                </div>'

		. '<div class="task-meta task-status">

                    <i class="rcli fa-refresh fa-sync rcl-icon"></i>' . __( 'Статус задания', 'fng' ) . ': <span class="task-status-' . get_post_meta( $post_id, 'fng-status', 1 ) . '">' . fng_get_status_name( get_post_meta( $post_id, 'fng-status', 1 ) ) . '</span>

                </div>'

		. (rcl_get_option( 'fng-view-performer', 0 ) && get_post_meta( $post_id, 'fng-performer', 1 ) ? '<div class="task-meta task-performer">

                    <i class="rcli fa-user rcl-icon"></i>' . __( 'Исполнитель задания', 'fng' ) . ': <a href="' . get_author_posts_url( get_post_meta( $post_id, 'fng-performer', 1 ) ) . '" target="_blank">' . get_the_author_meta( 'display_name', get_post_meta( $post_id, 'fng-performer', 1 ) ) . '</a>

                </div>' : '')

		. '</div>';



	return $content;

}

// изменение функции вывода контента задания

remove_filter( 'the_content', 'fng_add_task_meta', 50 );

add_filter( 'the_content', 'fng_add_task_meta_1', 50 );

function fng_add_task_meta_1( $content = '' ) {

	global $post;



	if ( $post->post_type != 'task' || doing_filter( 'get_the_excerpt' ) )

		return $content;



	$taskMeta = fng_get_task_meta_box_1( $post->ID );



	$taskMeta .= fng_get_task_gallery( $post->ID );



	return $taskMeta . $content;

}

remove_filter( 'the_excerpt', 'fng_add_task_excerpt', 50 );

add_filter( 'the_excerpt', 'fng_add_task_excerpt_1', 50 );

function fng_add_task_excerpt_1( $content ) {

	global $post;



	if ( $post->post_type != 'task' )

		return $content;



	return fng_get_task_meta_box_1( $post->ID ) . $content;

}

// Техническая составляющая замены срока выполнения на актуальность задания

// Удаляем старую крон задачу и создаем свою

remove_action( 'rcl_cron_hourly', 'fng_hourly_heartbeat' );

add_action( 'rcl_cron_hourly', 'fng_hourly_heartbeat_1' );

function fng_hourly_heartbeat_1() {

	global $wpdb;



	if ( rcl_get_option( 'fng-days-cancel', 0 ) )

		return false;



	$tasks = $wpdb->get_results( "SELECT "

		. "posts.*, "

		. "meta.meta_value AS task_status "

		. "FROM $wpdb->posts AS posts "

		. "INNER JOIN $wpdb->postmeta AS meta ON posts.ID = meta.post_id "

		. "WHERE posts.post_type = 'task' "

		. "AND posts.post_status = 'publish' "

		. "AND meta.meta_key = 'fng-status' "

		. "AND meta.meta_value IN (2)" );



	if ( ! $tasks )

		return false;



	foreach ( $tasks as $task ) {


		$workDays = fng_diff_days_1( get_post_meta($task->ID, 'fng-act-date', 1) );



		switch ( $task->task_status ) {



			case 2: //в работе, время работы + 24 часа, заказ просрочен



				if ( $workDays >= 1 ) {



					fng_task_expired_1( $task->ID );

				}



				break;

		}

	}

}

// Функция подсчета количества дней

function fng_diff_days_1( $dateTime ) {

	$dateTime = strtotime( $dateTime );

	$dateNow = strtotime( current_time( 'mysql' ) );

	$diffDays = ($dateNow - $dateTime) / ( 3600 * 24 );

	return $diffDays;
}

function fng_task_expired_1( $post_id ) {



	update_post_meta( $post_id, 'fng-last-update', current_time( 'mysql' ) );

	update_post_meta( $post_id, 'fng-status', -4 );



	fng_add_service_message( $post_id, __( 'Задание больше не актуально '

			. 'Автор задания может продлить время выполнения или отказаться от исполнителя.', 'fng' ) );



	do_action( 'fng_task_expired', $post_id );

}

// Добавлние кнопки запроса на продление

function fng_get_task_manager_1( $task_id ) {

	global $user_ID, $post;



	if ( ! is_object( $post ) ) {

		$post = get_post( $task_id );

	}



	$status_id = get_post_meta( $task_id, 'fng-status', 1 );



	$performer = get_post_meta( $task_id, 'fng-performer', 1 );



	$items = array();



	if ( $post->post_author == $user_ID ) {



		if ( in_array( $status_id, array( -4 ) ) ) {



			$items[] = array(

				'id'		 => 'fng-performer-fired',

				'label'		 => __( 'Отказаться от исполнителя', 'fng' ),

				'icon'		 => 'fa-frown-o',

				'onclick'	 => 'fng_ajax(' . json_encode( array(

					'action'	 => 'fng_ajax_performer_fired',

					'task_id'	 => $task_id,

					'confirm'	 => __( 'Вы уверены?', 'fng' )

				) ) . ',this);return false;'

			);



			$items[] = array(

				'id'		 => 'fng-add-time-custom',

				'label'		 => __( 'Продлить время', 'fng' ),

				'icon'		 => 'fa-clock-o fa-clock',

				'onclick'	 => 'fng_ajax(' . json_encode( array(

					'action'	 => 'fng_ajax_add_time_form_custom',

					'task_id'	 => $task_id,

					//'confirm' => __('Вы уверены?')

				) ) . ',this);return false;'

			);

		}



		if ( ! in_array( $status_id, array( 1, 5, -3 ) ) ) {



			$items[] = array(

				'id'		 => 'fng-task-claim',

				'label'		 => __( 'Арбитраж', 'fng' ),

				'icon'		 => 'fa-gavel',

				'onclick'	 => 'fng_ajax(' . json_encode( array(

					'action'	 => 'fng_ajax_get_claim_form',

					'task_id'	 => $task_id,

					'confirm'	 => __( 'Вы уверены?', 'fng' )

				) ) . ',this);return false;'

			);



			$items[] = array(

				'id'		 => 'fng-task-success',

				'label'		 => __( 'Подтвердить выполнение', 'fng' ),

				'icon'		 => 'fa-check-square-o',

				'onclick'	 => 'fng_ajax(' . json_encode( array(

					'action'	 => 'fng_ajax_task_complete',

					'task_id'	 => $task_id,

					'confirm'	 => __( 'Вы уверены?', 'fng' )

				) ) . ',this);return false;'

			);

		}

	}



	if ( $performer == $user_ID ) {



		if ( in_array( $status_id, array( 2, -4 ) ) ) {



			$items[] = array(

				'id'		 => 'fng-task-claim',

				'label'		 => __( 'Арбитраж', 'fng' ),

				'icon'		 => 'fa-gavel',

				'onclick'	 => 'fng_ajax(' . json_encode( array(

					'action'	 => 'fng_ajax_get_claim_form',

					'task_id'	 => $task_id,

					'confirm'	 => __( 'Вы уверены?', 'fng' )

				) ) . ',this);return false;'

			);



			$items[] = array(

				'id'		 => 'fng-performer-fail',

				'label'		 => __( 'Отказаться от выполнения', 'fng' ),

				'icon'		 => 'fa-remove',

				'onclick'	 => 'fng_ajax(' . json_encode( array(

					'action'	 => 'fng_ajax_performer_fail',

					'task_id'	 => $task_id,

					'confirm'	 => __( 'Вы уверены?', 'fng' )

				) ) . ',this);return false;'

			);

			// Не забыть написать время при котором появляется эта кнопка
			if(get_post_meta($task_id, 'fng-status', 1) == -4){
				$items[] = array(

					'id'		 => 'fng-act-add',

					'label'		 => __( 'Запросить продление', 'fng' ),

					'icon'		 => 'fa-clock-o ',

					'onclick'	 => 'fng_ajax(' . json_encode( array(

						'action'	 => 'fng_ajax_add_act_date',

						'task_id'	 => $task_id,

					) ) . ',this);return false;'

				);
			}
			

		}

	}



	$items = apply_filters( 'fng_manager_task_items', $items, $post );



	return fng_get_manager( $items );

}

// Функция вывода кнопок
remove_filter( 'the_content', 'fng_add_task_box', 100 );

add_filter( 'the_content', 'fng_add_task_box_1', 100 );

function fng_add_task_box_1( $content ) {

	global $post, $user_ID;



	if ( $post->post_type != 'task' || doing_filter( 'get_the_excerpt' ) )

		return $content;



	$status_id = get_post_meta( $post->ID, 'fng-status', 1 );



	$content .= fng_get_task_manager_1( $post->ID );



	if ( $status_id == 1 )

		$content .= fng_get_request_box( $post->ID );



	if ( in_array( $status_id, array( 2, 3, 4, 5, -3, -4 ) ) ) {



		$performer = get_post_meta( $post->ID, 'fng-performer', 1 );



		if ( in_array( $user_ID, array( $post->post_author, $performer ) ) || rcl_is_user_role( $user_ID, array( 'administrator', 'editor' ) ) ) {



			$chatArgs = array(

				'userslist'		 => 1,

				'chat_room'		 => 'fng-task:' . $post->ID,

				'file_upload'	 => 1

			);



			if ( $status_id == 5 ) {

				$chatArgs['userslist']	 = false;

				$chatArgs['beat']		 = false;

				$chatArgs['form']		 = false;

			}



			$content .= '<h3>' . __( 'Рабочая область', 'fng' ) . '</h3>';



			$content .= rcl_chat_shortcode( $chatArgs );

		}

	}



	return $content;

}

rcl_ajax_action( 'fng_ajax_add_act_date' );
function fng_ajax_add_act_date(){

	$form = rcl_get_form( array(
		'onclick'	 => 'rcl_send_form_data("function_add_time", this);return false;',
		'submit'	 => __( 'Запросить' ),
		'fields'	 => array(
			array(
				'slug'		 => 'fng-act-date',
				'type'		 => 'date',
				'title'		 => __( 'Запросить продление до', 'fng' ),
				'notice'	 => __( 'Укажите дату, до которой хотите запросить продление задания', 'fng' ),
				'required'	 => 1,
				'default'    => get_post_meta($_POST['task_id'], 'fng-act-date', 1)
				),
			array(
				'type'	 => 'hidden',
				'slug'	 => 'task_id',
				'value'	 => $_POST['task_id']
				)
			)
		)
	);

	wp_send_json( array(
		'dialog' => array(
			'title'		 => __( 'Выбор даты продления' ),
			'content'	 => $form
		)
	) );
}

// Регистрация пользовательского типа постов (Запросы на продление от исполнителей)

function create_req_act_posttype() {
    $labels = array(
        'name' => _x( 'Запросы на продление', 'Запросы на продление', 'root' ),
        'singular_name' => _x( 'Запрос на продление', 'Запрос на продление', 'root' ),
        'menu_name' => __( 'Запросы на продление', 'root' ),
        'all_items' => __( 'Все запросы на продление', 'root' ),
        'view_item' => __( 'Смотреть запрос на продлени', 'root' ),
        'add_new_item' => __( 'Добавить запрос на продлени', 'root' ),
        'add_new' => __( 'Добавить новый', 'root' ),
        'edit_item' => __( 'Редактировать запрос на продлени', 'root' ),
        'update_item' => __( 'Обновить запрос на продлени', 'root' ),
        'search_items' => __( 'Искать запрос на продлени', 'root' ),
        'not_found' => __( 'Не найдено', 'root' ),
        'not_found_in_trash' => __( 'Не найдено в корзине', 'root' ),
    );

    $args = array(
        'label' => __( 'reqact', 'root' ),
        'description' => __( 'Запросы на продление', 'root' ),
        'labels' => $labels,
        'supports' => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        'taxonomies' => array( 'genres' ),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 5,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'page',
    );

    register_post_type( 'reqact', $args );

}
add_action( 'init', 'create_req_act_posttype', 0 );

// Метаполя для заявок исполнителей на продление

add_action('add_meta_boxes', 'my_extra_fields_act', 1);

function my_extra_fields_act() {
	add_meta_box( 'reqact_fields', 'Дополнительная информация', 'extra_fields_box_act_func', 'reqact', 'side', 'high'  );
}

function extra_fields_box_act_func( $post ){
	echo '
		<p><label>Действующая дата : '.get_post_meta($post->ID,'old_date', 1).'</p>
		<p><label>Запрашиваемая дата дата : '.get_post_meta($post->ID,'new_date', 1).'</label></p>
		<p><label>Автор задания : '.get_post_meta($post->ID,'name_author_task', 1).'</label></p>
		<p><label>Название задания : '.get_post_meta($post->ID,'name_task', 1).'</label></p>
		<p><label>Статус : '.get_post_meta($post->ID,'status_act', 1).'</label></p>
		<input type="hidden" name="reqact_nonce" value="'.wp_create_nonce(__FILE__).'">
	';

}

// Добавляем теги в чат

function edit_chat_tags( $args ) {
    $args = array(
			'a'			 	=> array(
				'href'	 => false,
				'title'	 => true,
				'target' => true,
				'onclick' => true,
				'class' => true,
			),
			'img'			 => array(
				'src'	 => true,
				'alt'	 => true,
				'class'	 => true,
			),
			'p'			 	=> array(
				'class' => true
			),
			'blockquote' 	=> array(),
			'del'		 	=> array(),
			'em'		 	=> array(),
			'strong'	 	=> array(),
			'details'	 	=> array(),
			'summary'	 	=> array(),
			'div'	 		=> array(
				'class' => true,
			),
			'i'          	=> array(
				'class' => true
			),
			'span'		 	=> array(
				'class'	 => true,
				'style'	 => true
			),
			'input'		 	=> array(
				'class'	 => true,
				'value'	 => true,
				'type'   => true
			)
			);
    return  $args;
}
 
add_filter( 'rcl_chat_message_allowed_tags', 'edit_chat_tags' );




// Обработчик запроса на продление
rcl_ajax_action( 'function_add_time' );

function function_add_time(){

	global $user_ID;

	$args = array(
		'post_type' => 'reqact',
		'meta_query' => [ 
			'relation' => 'AND',
				[
					'key' => 'id_task',
					'value' => $_POST['task_id'],
				],
				[
					'key' => 'status_act',
					'value' => 'Ожидает решения',
				]	
		]
	);

	$query = new WP_Query( $args );
	if(count($query->posts) >= 1){

		wp_reset_postdata();

		wp_send_json(array(
        	'error' => 'Вы уже отправили запрос на продление срока задания. Ожидайте решения заказчика', //уведомление об успехе
        	'reload' => false
    	));

	}

	wp_reset_postdata();
	

	$post_data = array(
		'post_title'    => 'Заявка на продления срока актуальности услуги "'.get_the_title($_POST['task_id']).'"',
		'post_content'  => '',
		'post_status'   => 'publish',
		'post_author'   => $user_ID,
		'post_type'     => 'reqact'
	);

	$task_post = get_post($_POST['task_id']);

	if($post_id = wp_insert_post( $post_data )){
		update_post_meta($post_id, 'old_date', get_post_meta($_POST['task_id'], 'fng-act-date', 1));
		update_post_meta($post_id, 'new_date', $_POST['fng-act-date']);
		update_post_meta($post_id, 'name_author_task', get_the_author_meta( 'display_name', $task_post->post_author));
		update_post_meta($post_id, 'name_task', get_the_title($_POST['task_id']));
		update_post_meta($post_id, 'id_task', $_POST['task_id']);
		update_post_meta($post_id, 'id_author_task', $task_post->post_author);
		update_post_meta($post_id, 'status_act', 'Ожидает решения');

		$message_mail = '
		<p>Исполнитель вашего задания <a href="'.get_author_posts_url($user_ID).'">'.get_the_author_meta( 'user_firstname', $user_ID).' '.get_the_author_meta( 'user_lastname', $user_ID).' ('.get_the_author_meta( 'user_nicename', $user_ID).')</a> запрашивает продление срока актуальности задания до: '.$_POST['fng-act-date'].'. Для подтверждения/отклонения продления перейдите на страницу вашего задания <a href="'.get_permalink($_POST['task_id']).'">'.get_the_title($_POST['task_id']).'</a></p>
		';

		if(rcl_mail(get_the_author_meta( 'user_email', $task_post->post_author), 'Запрос на продление актуальности задания', $message_mail)){


			// Создание кнопок принятия/отказа запроса на продление срока задания 
			$messages = fng_get_message( $_POST['task_id']);
    		$message_id =  $messages[0]['message_id'];

    		$post_task = get_post($_POST['task_id']);
			
			$button_comlete = '<input type="hidden" class="id_author" value="'.$post_task->post_author.'"><div id="fng_ajax_complete_act" class="manager-item "><a href="javascript:void(0);" onclick="fng_ajax({&quot;action&quot;:&quot;fng_ajax_complete_act&quot;,&quot;task_id&quot;:&quot;'.$_POST['task_id'].'&quot;, &quot;new_date&quot;:&quot;'.$_POST['fng-act-date'].'&quot;, &quot;message_id&quot;:&quot;'.$message_id.'&quot;,&quot;id_zay&quot; : &quot;'.$post_id.'&quot;},this);return false;" class="rcl-bttn rcl-bttn__type-primary rcl-bttn__size-standart"><i class="rcl-bttn__ico rcl-bttn__ico-left rcli fa-check "></i><span class="rcl-bttn__text">Принять</span></a></div>';

			$button_cancel = '<div id="fng_ajax_cancel_act" class="manager-item "><a href="javascript:void(0);" onclick="fng_ajax({&quot;action&quot;:&quot;fng_ajax_cancel_act&quot;,&quot;task_id&quot;:&quot;'.$_POST['task_id'].'&quot;, &quot;new_date&quot;:&quot;'.$_POST['fng-act-date'].'&quot;, &quot;message_id&quot; : &quot;'.$message_id.'&quot;,&quot;id_zay&quot; : &quot;'.$post_id.'&quot;},this);return false;" class="rcl-bttn rcl-bttn__type-primary rcl-bttn__size-standart"><i class="rcl-bttn__ico rcl-bttn__ico-left rcli fa-times "></i><span class="rcl-bttn__text">Отклонить</span></a></div>';
		   
			// Добавление сообщения в рабочую область
			fng_add_service_message( $_POST['task_id'], __( 'Исполнитель запросил продление актуальности задания до '. $_POST['fng-act-date'] .'<div class="fng-manager preloader-box vis-cont-button">' . $button_comlete . ' ' . $button_cancel.'</div>' , 'fng' ) );



			wp_send_json(array(
        		'success' => 'Ваш запрос успешно создан и отправлен, ожидайте решения заказчика.', //уведомление об успехе
        		'reload' => false
    		));
		}

	}
	else{
		wp_send_json(array(
        	'error' => 'Что-то пошло не так, попробуйте позже.', //уведомление об успехе
        	'reload' => false
    	));
	}

}

// Замена штатного функционала продления заказа от заказчика

rcl_ajax_action( 'fng_ajax_add_time_form_custom' );

function fng_ajax_add_time_form_custom(){

	$form = rcl_get_form( array(
		'onclick'	 => 'rcl_send_form_data("function_add_time_customer", this);return false;',
		'submit'	 => __( 'Продлить' ),
		'fields'	 => array(
			array(
				'slug'		 => 'fng-act-date',
				'type'		 => 'date',
				'title'		 => __( 'Продлить до', 'fng' ),
				'notice'	 => __( 'Укажите дату, до которой хотите запросить продление задания', 'fng' ),
				'required'	 => 1,
				'default'    => get_post_meta($_POST['task_id'], 'fng-act-date', 1)
				),
			array(
				'type'	 => 'hidden',
				'slug'	 => 'task_id',
				'value'	 => $_POST['task_id']
				)
			)
		)
	);

	wp_send_json( array(
		'dialog' => array(
			'title'		 => __( 'Выбор даты продления' ),
			'content'	 => $form
		)
	) );
}

rcl_ajax_action( 'function_add_time_customer' );

function function_add_time_customer(){


	if(update_post_meta($_POST['task_id'], 'fng-act-date' , $_POST['fng-act-date']) && update_post_meta( $_POST['task_id'], 'fng-status', 2 )){

		fng_add_service_message( $_POST['task_id'], __( 'Заказчик продлил актуальность задания до '. $_POST['fng-act-date'], 'fng' ) );

		wp_send_json(array(
        		'success' => 'Вы успешно продлили дату актуальности задания.', //уведомление об успехе
        		'reload' => false
    	));
	}
	else{

		wp_send_json(array(
        		'error' => 'Что-то пошло не так, попробуйте позже.',
        		'reload' => false
    	));

	}

}

// Обработка принятия продления

rcl_ajax_action('fng_ajax_complete_act');

function fng_ajax_complete_act(){

	if(get_post_meta($_POST['id_zay'], 'status_act', 1) != 'Ожидает решения'){
		wp_send_json(array(
        		'error' => 'Вы уже приняли решение косательно этой заявки!',
        		'reload' => false
    	));
	}

	if(get_post_meta( $_POST['task_id'], 'fng-status', 1) == 2){
		wp_send_json(array(
        		'error' => 'Задание еще не просрочено.',
        		'reload' => false
    	));
	}

	if(get_post_meta($_POST['task_id'], 'fng-act-date' , 1) == $_POST['new_date']){
		wp_send_json(array(
        		'error' => 'Нельзя запрашивать действующую дату актуальности.',
        		'reload' => false
    	));
	}


	if(update_post_meta( $_POST['task_id'], 'fng-status', 2) && update_post_meta($_POST['task_id'], 'fng-act-date' , $_POST['new_date'])){


    	rcl_chat_delete_message( $_POST['message_id'] );

    	update_post_meta($_POST['id_zay'], 'old_date', $_POST['new_date']);

    	update_post_meta($_POST['id_zay'], 'status_act', 'Принята');

		fng_add_service_message( $_POST['task_id'], __( 'Заказчик принял заявку на продление до '. $_POST['new_date'], 'fng' ) );

		wp_send_json(array(
        		'success' => 'Вы успешно продлили дату актуальности задания.', //уведомление об успехе
        		'reload' => false
    	));
	}
	else{

		wp_send_json(array(
        		'error' => 'Что-то пошло не так, попробуйте позже.',
        		'reload' => false
    	));

	}




}

rcl_ajax_action('fng_ajax_cancel_act');

function fng_ajax_cancel_act(){

	if(get_post_meta($_POST['id_zay'], 'status_act', 1) != 'Ожидает решения'){
		wp_send_json(array(
        		'error' => 'Вы уже приняли решение косательно этой заявки!',
        		'reload' => false
    	));
	}

	
		update_post_meta($_POST['id_zay'], 'status_act', 'Отклонена');

		rcl_chat_delete_message( $_POST['message_id'] );


		fng_add_service_message( $_POST['task_id'], __( 'Заказчик отклонил заявку на продление до '. $_POST['new_date'], 'fng' ) );

		wp_send_json(array(
        		'success' => 'Вы отменили продление даты актуальности задания.', //уведомление об успехе
        		'reload' => false
    	));
	


}


function fng_get_message( $task_id) {

	$addon = rcl_get_addon( 'rcl-chat' );

	if ( ! $addon || ! rcl_get_option( 'fng-bot' ) )
		return false;

	require_once $addon['path'] . '/class-rcl-chat.php';

	$chat = new Rcl_Chat( array(
		'chat_room'	 => 'fng-task:' . $task_id,
		'user_id'	 => rcl_get_option( 'fng-bot' )
		) );

	 return $chat->get_messages();
}
// Удаление сообщения из чата

// Создание меток для заданий

add_action( 'init', 'create_marks_task' );
function create_marks_task(){

	// список параметров: wp-kama.ru/function/get_taxonomy_labels
	register_taxonomy( 'marktask', [ 'task' ], [
		'label'                 => '', // определяется параметром $labels->name
		'labels'                => [
			'name'              => 'Метки заданий',
			'singular_name'     => 'Метка задания',
			'search_items'      => 'Искать метку заданий',
			'all_items'         => 'Все метки даний',
			'view_item '        => 'Смотреть метку заданий',
			'parent_item'       => 'Родительская метка',
			'parent_item_colon' => 'Родительская метка:',
			'edit_item'         => 'Редактировать метку',
			'update_item'       => 'Обновить',
			'add_new_item'      => 'Добавить новую метку',
			'new_item_name'     => 'Новая метка',
			'menu_name'         => 'Метки задания',
		],
		'description'           => '', // описание таксономии
		'public'                => true,
		// 'publicly_queryable'    => null, // равен аргументу public
		// 'show_in_nav_menus'     => true, // равен аргументу public
		// 'show_ui'               => true, // равен аргументу public
		// 'show_in_menu'          => true, // равен аргументу show_ui
		// 'show_tagcloud'         => true, // равен аргументу show_ui
		// 'show_in_quick_edit'    => null, // равен аргументу show_ui
		'hierarchical'          => false,

		'rewrite'               => true,
		//'query_var'             => $taxonomy, // название параметра запроса
		'capabilities'          => array(),
		'meta_box_cb'           => null, // html метабокса. callback: `post_categories_meta_box` или `post_tags_meta_box`. false — метабокс отключен.
		'show_admin_column'     => false, // авто-создание колонки таксы в таблице ассоциированного типа записи. (с версии 3.5)
		'show_in_rest'          => null, // добавить в REST API
		'rest_base'             => null, // $taxonomy
		// '_builtin'              => false,
		//'update_count_callback' => '_update_post_term_count',
	] );
}

// Создание меток для услуг

add_action( 'init', 'create_marks_service' );
function create_marks_service(){

	// список параметров: wp-kama.ru/function/get_taxonomy_labels
	register_taxonomy( 'markservice', [ 'service' ], [
		'label'                 => '', // определяется параметром $labels->name
		'labels'                => [
			'name'              => 'Метки услуг',
			'singular_name'     => 'Метка услуг',
			'search_items'      => 'Искать метку услуг',
			'all_items'         => 'Все метки услуг',
			'view_item '        => 'Смотреть метку услуг',
			'parent_item'       => 'Родительская метка',
			'parent_item_colon' => 'Родительская метка:',
			'edit_item'         => 'Редактировать метку',
			'update_item'       => 'Обновить',
			'add_new_item'      => 'Добавить новую метку',
			'new_item_name'     => 'Новая метка',
			'menu_name'         => 'Метки услуг',
		],
		'description'           => '', // описание таксономии
		'public'                => true,
		// 'publicly_queryable'    => null, // равен аргументу public
		// 'show_in_nav_menus'     => true, // равен аргументу public
		// 'show_ui'               => true, // равен аргументу public
		// 'show_in_menu'          => true, // равен аргументу show_ui
		// 'show_tagcloud'         => true, // равен аргументу show_ui
		// 'show_in_quick_edit'    => null, // равен аргументу show_ui
		'hierarchical'          => false,

		'rewrite'               => true,
		//'query_var'             => $taxonomy, // название параметра запроса
		'capabilities'          => array(),
		'meta_box_cb'           => null, // html метабокса. callback: `post_categories_meta_box` или `post_tags_meta_box`. false — метабокс отключен.
		'show_admin_column'     => false, // авто-создание колонки таксы в таблице ассоциированного типа записи. (с версии 3.5)
		'show_in_rest'          => null, // добавить в REST API
		'rest_base'             => null, // $taxonomy
		// '_builtin'              => false,
		//'update_count_callback' => '_update_post_term_count',
	] );
}

// Обработчик поиска метки задания

add_action( 'wp_ajax_nopriv_livesearchmark', 'livesearchmark' );
add_action( 'wp_ajax_livesearchmark', 'livesearchmark' );

function livesearchmark(){

	$term_search = $_POST['term'];

	$values = $_POST['values'];

	//wp_send_json_success(array('result' => $values));

	$args = array('search'   => $term_search, 'hide_empty' => 0 );
	$terms = get_terms( 'marktask', $args );
	$bool_has = false;

	if($values != 'no'){

		$mass_val = explode(',', $values);

		for($i = 1; $i < count($mass_val); $i++){
			$mass_val[$i] = mb_strtolower($mass_val[$i]);
		}

		if(count($terms) != 0){
			foreach($terms as $term){
				//wp_send_json_success(array('result' => $term->name.'/'.$mass_val[0]));
				if(!in_array(mb_strtolower($term->name), $mass_val)){
					$bool_has = true;
					$html .= '<div class="cont-search-item-mark"><p class="mark-text" data-id="'.$term->term_id.'" data-valuemark="'.$term->name.'">'.$term->name.'</p></div>';

				}
			}
			if(!$bool_has){
				if(!in_array(mb_strtolower($term_search), $mass_val)){
					$html .= '<p class="no-search-mark">Ничего не найдено</p>';
				}
				else{
					$html .= '<p class="no-search-mark">Ничего не найдено</p>';
				}
			}
			wp_send_json_success(array('result' => $html));
		}
		else{
				if(!in_array(mb_strtolower($term_search), $mass_val)){
					$html .= '<p class="no-search-mark">Ничего не найдено</p>';
				}
				else{
					$html .= '<p class="no-search-mark">Ничего не найдено</p>';
				}
		}
	}
	else{
	
		if(count($terms) != 0){
			foreach($terms as $term){
				$html .= '<div class="cont-search-item-mark"><p class="mark-text" data-id="'.$term->term_id.'" data-valuemark="'.$term->name.'">'.$term->name.'</p></div>';
			}
			wp_send_json_success(array('result' => $html));
		}
		else{
				if(!in_array(mb_strtolower($term_search), $mass_val)){
					$html .= '<p class="no-search-mark">Ничего не найдено</p>';
				}
				else{
					$html .= '<p class="no-search-mark">Ничего не найдено</p>';
				}
		}
	}

	
}

// Добавление термина к таксономии метки заданий
/*add_action( 'wp_ajax_nopriv_addcustomterm', 'addcustomterm' );
add_action( 'wp_ajax_addcustomterm', 'addcustomterm' );

function addcustomterm(){

	$value = $_POST['value'];

	if(wp_insert_term($value, 'marktask')){
		wp_send_json_success();
	}
}*/

// Добавление к фильтру заданий поля с метками

remove_shortcode( 'fng-search-form', 'fng_get_search_form' );

add_shortcode( 'fng-search-form-custom', 'fng_get_search_form_custom' );

function fng_get_search_form_custom( $atts = false ) {
	global $wpdb;

	extract( shortcode_atts( array(
		'type' => 'vertical'
			), $atts ) );

	$maxPrice = $wpdb->get_var( "SELECT MAX(cast(postmeta.meta_value as unsigned)) FROM $wpdb->postmeta AS postmeta "
		. "INNER JOIN $wpdb->posts AS posts ON postmeta.post_id=posts.ID "
		. "WHERE posts.post_type = 'task' "
		. "AND posts.post_status='publish' "
		. "AND postmeta.meta_key='fng-price'" );

	$terms = get_terms( array(
		'taxonomy'	 => 'task-subject',
		'hide_empty' => false,
		'parent'	 => 0
	) );

	$category = array( '' => __( 'Все категории', 'fng' ) );
	foreach ( $terms as $term ) {
		$category[$term->term_id] = $term->name;
	}


	$fields = array(
		array(
			'type'			 => 'text',
			'slug'			 => 'fs',
			'default'		 => isset( $_GET['fs'] ) ? $_GET['fs'] : '',
			'title'			 => __( 'Поиск по слову', 'fng' ),
			'placeholder'	 => __( 'Поиск...', 'fng' )
		),
		array(
			'type'		 => 'select',
			'slug'		 => 'fstatus',
			'title'		 => __( 'Статус', 'fng' ),
			'values'	 => array(
				0	 => __( 'Все задания', 'fng' ),
				1	 => __( 'Подбор исполнителя', 'fng' ),
				2	 => __( 'В работе', 'fng' ),
				5	 => __( 'Завершено', 'fng' )
			),
			'default'	 => isset( $_GET['fstatus'] ) ? $_GET['fstatus'] : 0,
		),
		array(
			'type'		 => 'select',
			'slug'		 => 'fsubject',
			'title'		 => __( 'Категория', 'fng' ),
			'default'	 => isset( $_GET['fsubject'] ) ? $_GET['fsubject'] : '',
			'values'	 => $category
		),
		array(

			'slug'		 => 'mark-task',
			'type'		 => 'custom',
			'title'		 => __( 'Метки задания', 'fng' ),
			'notice'	 => __( 'укажите метки задания', 'fng' ),
			'required'	 => 1,
			'content'    => '<ul class="mark-field"><li class="mark-item mark-input-cont"><input type="text" class="text-field mark-value" value="" placeholder="Метка"><div class="responce-mark responce-mark-task"></div></li></ul>'

		),
		array(
			'type'		 => 'range',
			'slug'		 => 'fprice',
			'title'		 => __( 'Стоимость', 'fng' ),
			'value_max'	 => ($maxPrice && $maxPrice > 5000) ? $maxPrice : 5000,
			'value_step' => 100,
			'default'	 => isset( $_GET['fprice'] ) ? $_GET['fprice'] : '',
		),
		array(
			'type'		 => 'radio',
			'slug'		 => 'forderby',
			'title'		 => __( 'Сортировка по', 'fng' ),
			'values'	 => array(
				'date'	 => __( 'дате', 'fng' ),
				'price'	 => __( 'стоимости', 'fng' )
			),
			'default'	 => isset( $_GET['forderby'] ) ? $_GET['forderby'] : 'date',
		),
		array(
			'type'		 => 'radio',
			'slug'		 => 'forder',
			'title'		 => __( 'Вывод по', 'fng' ),
			'values'	 => array(
				'DESC'	 => __( 'убыванию', 'fng' ),
				'ASC'	 => __( 'возрастанию', 'fng' )
			),
			'default'	 => isset( $_GET['forder'] ) ? $_GET['forder'] : 'DESC',
		),
		array(
			'type'	 => 'hidden',
			'slug'	 => 'post_type',
			'value'	 => 'task'
		)
	);

	$fields = apply_filters( 'fng_search_fields', $fields );

	$content = '<div class="fng-search-form type-' . $type . '">';

	$content .= rcl_get_form( array(
		'fields' => $fields,
		'method' => 'get',
		'action' => get_post_type_archive_link( 'task' ),
		'submit' => __( 'Найти задание', 'fng' )
	) );

	$content .= '</div>';

	return $content;
}

remove_action( 'pre_get_posts', 'fng_task_query_filter', 10 );

add_action( 'pre_get_posts', 'fng_task_query_filter_1', 10 );

function fng_task_query_filter_1( $query ) {

	if ( ! is_admin() && $query->is_main_query() ) {



		if ( ! $query->is_post_type_archive( 'task' ) )

			return;



		$meta_query = array();



		if ( isset( $_GET['fs'] ) ) {



			$query->set( 's', $_GET['fs'] );

		}



		if ( $_GET['fsubject'] ) {



			$query->set( 'tax_query', array(

				array(

					'taxonomy'	 => 'task-subject',

					'field'		 => 'id',

					'terms'		 => $_GET['fsubject']

				)

			) );

		}

		

		if ( $_GET['values_mark_select'] ) {

			$mass_ids = array();

			foreach($_GET['values_mark_select'] as $mark){
				$mass_ids[] = $mark;
			}

			$query->set( 'tax_query', array(

				array(

					'taxonomy'	 => 'marktask',

					'field'		 => 'id',

					'terms'		 => $mass_ids

				)

			) );
			

		}



		if ( isset( $_GET['fstatus'] ) && $_GET['fstatus'] ) {



			$meta_query[] = array(

				array(

					'key'	 => 'fng-status',

					'value'	 => $_GET['fstatus']

				)

			);

		} else if ( rcl_get_option( 'fng-close-noview' ) ) {



			$meta_query[] = array(

				array(

					'key'		 => 'fng-status',

					'value'		 => 5,

					'compare'	 => '!='

				)

			);

		}



		if ( isset( $_GET['fprice'] ) ) {



			$meta_query[] = array(

				array(

					'key'		 => 'fng-price',

					'value'		 => $_GET['fprice'],

					'type'		 => 'numeric',

					'compare'	 => 'BETWEEN'

				)

			);

		}



		if ( isset( $_GET['forderby'] ) ) {



			$sort = $_GET['forderby'];



			if ( $sort == 'date' ) {

				$query->set( 'orderby', $_GET['forderby'] );

			}



			if ( $sort == 'price' ) {

				$query->set( 'orderby', 'meta_value_num' );

				$query->set( 'meta_key', 'fng-price' );

			}

		}



		if ( isset( $_GET['forder'] ) ) {



			$query->set( 'order', $_GET['forder'] );

		}



		if ( $meta_query )

			$query->set( 'meta_query', $meta_query );

			



		do_action( 'fng_pre_filter_tasks', $query );

	}

}

// Создание фильтра для услуг

add_shortcode( 'fng-search-form-service-custom', 'fng_get_search_form_service_custom' );

function fng_get_search_form_service_custom( $atts = false ) {
	global $wpdb;

	extract( shortcode_atts( array(
		'type' => 'vertical'
			), $atts ) );

	$maxPrice = $wpdb->get_var( "SELECT MAX(cast(postmeta.meta_value as unsigned)) FROM $wpdb->postmeta AS postmeta "
		. "INNER JOIN $wpdb->posts AS posts ON postmeta.post_id=posts.ID "
		. "WHERE posts.post_type = 'service' "
		. "AND posts.post_status='publish' "
		. "AND postmeta.meta_key='sm-price'" );

	$terms = get_terms( array(
		'taxonomy'	 => 'service-category',
		'hide_empty' => false,
		'parent'	 => 0
	) );

	$category = array( '' => __( 'Все категории', 'fng' ) );
	foreach ( $terms as $term ) {
		$category[$term->term_id] = $term->name;
	}

	$fields = array(
		array(
			'type'			 => 'text',
			'slug'			 => 'fs_service',
			'default'		 => isset( $_GET['fs'] ) ? $_GET['fs_service'] : '',
			'title'			 => __( 'Поиск по слову', 'fng' ),
			'placeholder'	 => __( 'Поиск...', 'fng' )
		),
		array(
			'type'		 => 'select',
			'slug'		 => 'fcategory_service',
			'title'		 => __( 'Категория', 'fng' ),
			'default'	 => isset( $_GET['fcategory_service'] ) ? $_GET['fcategory_service'] : '',
			'values'	 => $category
		),
		array(

			'slug'		 => 'mark-service',
			'type'		 => 'custom',
			'title'		 => __( 'Метки услуг', 'fng' ),
			'notice'	 => __( 'укажите метки услуги', 'fng' ),
			'required'	 => 1,
			'content'    => '<ul class="mark-field"><li class="mark-item mark-input-cont"><input type="text" class="text-field mark-value-service" value="" placeholder="Метка"><div class="responce-mark responce-mark-service"></div></li></ul>'

		),
		array(
			'type'		 => 'range',
			'slug'		 => 'fprice_service',
			'title'		 => __( 'Стоимость', 'fng' ),
			'value_max'	 => ($maxPrice && $maxPrice > 5000) ? $maxPrice : 5000,
			'value_step' => 100,
			'default'	 => isset( $_GET['fprice_service'] ) ? $_GET['fprice_service'] : '',
		),
		array(
			'type'		 => 'radio',
			'slug'		 => 'forderby_service',
			'title'		 => __( 'Сортировка по', 'fng' ),
			'values'	 => array(
				'date'	 => __( 'дате', 'fng' ),
				'price'	 => __( 'стоимости', 'fng' )
			),
			'default'	 => isset( $_GET['forderby_service'] ) ? $_GET['forderby_service'] : 'date',
		),
		array(
			'type'		 => 'radio',
			'slug'		 => 'forder_service',
			'title'		 => __( 'Вывод по', 'fng' ),
			'values'	 => array(
				'DESC'	 => __( 'убыванию', 'fng' ),
				'ASC'	 => __( 'возрастанию', 'fng' )
			),
			'default'	 => isset( $_GET['forder_service'] ) ? $_GET['forder_service'] : 'DESC',
		),
		array(
			'type'	 => 'hidden',
			'slug'	 => 'post_type',
			'value'	 => 'service'
		)
	);

	$fields = apply_filters( 'fng_search_fields', $fields );

	$content = '<div class="fng-search-form type-' . $type . '">';

	$content .= rcl_get_form( array(
		'fields' => $fields,
		'method' => 'get',
		'action' => get_post_type_archive_link( 'service' ),
		'submit' => __( 'Найти услуги', 'fng' )
	) );

	$content .= '</div>';

	return $content;
}

// Обработка живого поиска для меток услуг

add_action( 'wp_ajax_nopriv_livesearchmarkservice', 'livesearchmarkservice' );
add_action( 'wp_ajax_livesearchmarkservice', 'livesearchmarkservice' );

function livesearchmarkservice(){

	$term_search = $_POST['term'];

	$values = $_POST['values'];

	//wp_send_json_success(array('result' => $values));

	$args = array('search'   => $term_search, 'hide_empty' => 0 );
	$terms = get_terms( 'markservice', $args );
	$bool_has = false;

	if($values != 'no'){

		$mass_val = explode(',', $values);

		for($i = 1; $i < count($mass_val); $i++){
			$mass_val[$i] = mb_strtolower($mass_val[$i]);
		}

		if(count($terms) != 0){
			foreach($terms as $term){
				//wp_send_json_success(array('result' => $term->name.'/'.$mass_val[0]));
				if(!in_array(mb_strtolower($term->name), $mass_val)){
					$bool_has = true;
					$html .= '<div class="cont-search-item-mark"><p class="text-service" data-id="'.$term->term_id.'" data-valuemarkservice="'.$term->name.'">'.$term->name.'</p></div>';

				}
			}
			if(!$bool_has){
				if(!in_array(mb_strtolower($term_search), $mass_val)){
					$html .= '<p class="no-search-mark">Ничего не найдено</p>';
				}
				else{
					$html .= '<p class="no-search-mark">Ничего не найдено</p>';
				}
			}
			wp_send_json_success(array('result' => $html));
		}
		else{
				if(!in_array(mb_strtolower($term_search), $mass_val)){
					$html .= '<p class="no-search-mark">Ничего не найдено</p>';
				}
				else{
					$html .= '<p class="no-search-mark">Ничего не найдено</p>';
				}
		}
	}
	else{
	
		if(count($terms) != 0){
			foreach($terms as $term){
				$html .= '<div class="cont-search-item-mark"><p class="text-service" data-id="'.$term->term_id.'" data-valuemarkservice="'.$term->name.'">'.$term->name.'</p></div>';
			}
			wp_send_json_success(array('result' => $html));
		}
		else{
				if(!in_array(mb_strtolower($term_search), $mass_val)){
					$html .= '<p class="no-search-mark">Ничего не найдено</p>';
				}
				else{
					$html .= '<p class="no-search-mark">Ничего не найдено</p>';
				}
		}
	}

	
}

// Предзапрос перед загрузкой страницы услуг

add_action( 'pre_get_posts', 'fng_task_query_filter_service', 10 );

function fng_task_query_filter_service( $query ) {

	if ( ! is_admin() && $query->is_main_query() ) {



		if ( ! $query->is_post_type_archive( 'service' ) )

			return;



		$meta_query = array();



		if ( isset( $_GET['fs_service'] ) ) {



			$query->set( 's', $_GET['fs_service'] );

		}



		if ( $_GET['fcategory_service'] ) {



			$query->set( 'tax_query', array(

				array(

					'taxonomy'	 => 'service-category',

					'field'		 => 'id',

					'terms'		 => $_GET['fcategory_service']

				)

			) );

		}

		

		if ( $_GET['values_mark_select'] ) {

			$mass_ids = array();

			foreach($_GET['values_mark_select'] as $mark){
				$mass_ids[] = $mark;
			}

			$query->set( 'tax_query', array(

				array(

					'taxonomy'	 => 'markservice',

					'field'		 => 'id',

					'terms'		 => $mass_ids

				)

			) );
			

		}



		if ( isset( $_GET['fprice_service'] ) ) {



			$meta_query[] = array(

				array(

					'key'		 => 'sm-price',

					'value'		 => $_GET['fprice_service'],

					'type'		 => 'numeric',

					'compare'	 => 'BETWEEN'

				)

			);

		}



		if ( isset( $_GET['forderby_service'] ) ) {



			$sort = $_GET['forderby_service'];



			if ( $sort == 'date' ) {

				$query->set( 'orderby', $_GET['forderby_service'] );

			}



			if ( $sort == 'price' ) {

				$query->set( 'orderby', 'meta_value_num' );

				$query->set( 'meta_key', 'sm-price' );

			}

		}



		if ( isset( $_GET['forder_service'] ) ) {



			$query->set( 'order', $_GET['forder_service'] );

		}



		if ( $meta_query )

			$query->set( 'meta_query', $meta_query );

			



		do_action( 'fng_pre_filter_service', $query );

	}

}

// Добавление кнопки индивидуального заказа в видимую область


add_action( 'init', 'anton_add_dutton_person_service' );

function anton_add_dutton_person_service() {
	rcl_block( 'actions', 'anton_add_content_dutton_person_service', array('public' => 1) );
	
}

function anton_add_content_dutton_person_service( $author_lk ) {
	global $user_ID, $rcl_office;

	
	$typeAccount = get_user_meta( $rcl_office, 'sm-account', 1 );

	if ($typeAccount != 'Исполнитель')
		return false;



	return rcl_get_button( [
		'label'		 => __( 'Индивидуальный заказ', 'rcl-wallet' ),
		'onclick'	 => 'sm_add_order(0, '. $rcl_office .' );return false;',
		'href'    => '#',
		'icon'    => 'fa-list-alt'
	] );


}

/*
add_action( 'rcl_area_actions', 'anton_get_actions_cabinet', 52 );

function anton_get_actions_cabinet() {

    if ( ! is_user_logged_in() )

        return;



    global $user_ID, $user_LK;



    anton_manager_personal( $user_ID, $user_LK );

}

function anton_manager_personal( $user_id, $to_user ) {


    // запрос дружбы

    

        echo anton_offer_personal_button( $user_id, $to_user );

       


}

function anton_offer_personal_button( $user_id, $to_user ) {

    $data = rcl_encode_post( [

        'user_id' => $user_id,

        'to_user' => $to_user

        ] );



    $args = [

        'label'   => 'Индивидуальный заказ',

        'icon'    => 'fa-user-plus',

        'href'    => '#',

        'onclick' => 'sm_add_order(0, '. $to_user .' );return false;',

    ];



    return rcl_get_button( $args );

}
*/

// Добавление селектора срока выполнения услуги

function sm_get_service_default_fields_anton() {


	$fields = apply_filters( 'sm_service_default_fields', array(

		array(

			'slug'		 => 'sm-shipping-and-payment',
			'type'		 => 'textarea',
			'title'		 => __( 'Порядок выполнения и оплата' ),
			'required'	 => 1,

		),

		array(

			'slug'		 => 'sm-price',
			'type'		 => 'number',
			'title'		 => __( 'Стоимость услуги (' . rcl_get_primary_currency( 1 ) . ')' ),
			'required'	 => 1

		),

		array(

			'slug'		 => 'sm-service-days',
			'type'		 => 'number',
			'title'		 => __( 'Срок выполнения' ),
			'required'	 => 1

		),
		array(

			'slug'		 => 'sm-service-type-srok',
			'type'		 => 'radio',
			'title'		 => __( 'Срок исполнения в' ),
			'required'	 => 1,
			'values' 	 => array(
				__('Минуты'),
				__('Часы'),
				__('Дни'),
				__('Месяцы')
			),		
		),

	) );



	return $fields;

}


remove_filter( 'rcl_public_form_fields', 'sm_edit_service_category_field', 10, 2 );
add_filter( 'rcl_public_form_fields', 'sm_edit_service_category_field_anton', 10, 2 );

function sm_edit_service_category_field_anton( $fields, $form ) {

	global $user_ID;



	if ( ! in_array( $form->post_type, array( 'service' ) ) )

		return $fields;



	/* $category = get_user_meta($user_ID,'sm-service-category',1);



	  foreach($fields as $k => $field){

	  if($field['slug'] != 'taxonomy-service-category') continue;

	  $fields[$k]['values'] = is_array($category)? $category: array($category);

	  } */



	if ( $fields ) {

		$fields = array_merge( $fields, sm_get_service_default_fields_anton() );

	} else {

		$fields = sm_get_service_default_fields_anton();

	}



	return $fields;

}

remove_filter( 'rcl_custom_fields_post', 'sm_add_service_default_fields', 10, 3 );
add_filter( 'rcl_custom_fields_post', 'sm_add_service_default_fields_anton', 10, 3 );

function sm_add_service_default_fields_anton( $fields, $post_id, $post_type ) {


	if ( $post_type != 'service' )

		return $fields;



	if ( $fields ) {

		$fields = array_merge( $fields, sm_get_service_default_fields_anton() );

	} else {

		$fields = sm_get_service_default_fields_anton();

	}



	return $fields;

}

remove_action( 'update_post_rcl', 'sm_update_service_meta', 10, 3 );
add_action( 'update_post_rcl', 'sm_update_service_meta_anton', 10, 3 );

function sm_update_service_meta_anton( $post_id, $postdata, $update ) {



	if ( $postdata['post_type'] != 'service' )

		return false;



	update_post_meta( $post_id, 'sm-price', $_POST['sm-price'] );

	update_post_meta( $post_id, 'sm-view', 1 );

	update_post_meta( $post_id, 'sm-shipping-and-payment', $_POST['sm-shipping-and-payment'] );

	update_post_meta( $post_id, 'sm-service-days', $_POST['sm-service-days'] );

	update_post_meta( $post_id, 'sm-type-srok', $_POST['sm-service-type-srok'] );

}

remove_action( 'save_post', 'sm_update_custom_fields_with_save_service', 0 );
add_action( 'save_post', 'sm_update_custom_fields_with_save_service_anton', 0 );

function sm_update_custom_fields_with_save_service_anton( $post_id ) {



	if ( ! isset( $_POST['custom_fields_nonce_rcl'] ) )

		return false;

	//print_r($_POST);exit;

	//if ( !wp_verify_nonce($_POST['custom_fields_nonce_rcl'], __FILE__) ) return false;



	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )

		return false;

	if ( ! current_user_can( 'edit_post', $post_id ) )

		return false;



	update_post_meta( $post_id, 'sm-price', $_POST['sm-price'] );

	//update_post_meta($post_id, 'sm-view', 1);

	update_post_meta( $post_id, 'sm-shipping-and-payment', $_POST['sm-shipping-and-payment'] );

	update_post_meta( $post_id, 'sm-service-days', $_POST['sm-service-days'] );

	update_post_meta( $post_id, 'sm-type-srok', $_POST['sm-service-type-srok'] );



	return $post_id;

}

// Редактирование контента услуг

remove_filter('the_excerpt', 'sm_add_service_excerpt', 50);
add_filter('the_excerpt', 'sm_add_service_excerpt_anton', 50);

function sm_add_service_excerpt_anton($content){

    global $post;

    if($post->post_type != 'service') return $content;

    $content .= sm_add_service_meta_anton();

    $content .= sm_add_service_order_button();

    return $content;

}


remove_filter('the_content', 'sm_add_service_meta', 50);
add_filter('the_content', 'sm_add_service_meta_anton', 50);

function sm_add_service_meta_anton($content = ''){

    global $post;



    if($post->post_type != 'service') return $content;



    $meta = sm_get_service_terms($post->ID);

    $text_srok = '';

    switch ((string)get_post_meta($post->ID, 'sm-type-srok', true)){
    	case 'Минуты' :
    		$text_srok = 'мин';
    		break;
    	case 'Часы' :
    		$text_srok = 'ч';
    		break;
    	case 'Дни' :
    		$text_srok = 'дн';
    		break;
    	case 'Месяцы' :
    		$text_srok = 'мес';
    		break;
    	default :
    		$text_srok = 'не определено (старные услуги, с старым сроком)';
    		break;
    }

    $meta .= '<div class="service-metas">

                    <div class="service-meta">

                        <strong>'.__('Стоимость услуги').'</strong>: '.get_post_meta($post->ID,'sm-price',1).' '. rcl_get_primary_currency(1).'

                    </div>

                    <div class="service-meta">

                        <strong>'.__('Срок выполнения').'</strong>: '.get_post_meta($post->ID,'sm-service-days',1).' '.$text_srok.'.

                    </div>

                    <div class="service-meta">

                        <strong>'.__('Порядок выполнения и оплата').'</strong>: '.get_post_meta($post->ID,'sm-shipping-and-payment',1).'

                    </div>

                </div>';



    return $meta . $content;

}


// Переписываем крон задачу для подсчета минут, часов, дней месяцев.

remove_action('rcl_cron_hourly', 'sm_hourly_heartbeat');
add_action('rcl_cron_hourly', 'sm_hourly_heartbeat_anton');

function sm_hourly_heartbeat_anton(){

    global $wpdb;

    

    $orders = $wpdb->get_results("SELECT "

            . "posts.*, "

            . "meta.meta_value AS order_status "

            . "FROM $wpdb->posts AS posts "

            . "INNER JOIN $wpdb->postmeta AS meta ON posts.ID = meta.post_id "

            . "WHERE posts.post_type = 'service-order' "

            . "AND posts.post_status = 'publish' "

            . "AND meta.meta_key = 'sm-order-status' "

            . "AND meta.meta_value IN (3,4,5,6,7)");

    

    if(!$orders) return false;



    foreach($orders as $order){



        $min = sm_diff_min_anton(get_post_meta($order->ID, 'sm-last-update', 1));

        

        switch($order->order_status){

            case 3: //оплачен, сутки до подтверждения, заказ отменяется

                

                if($min >= 1440){

                    

                    sm_order_reject($order->ID);

                    

                }

                

                break;

            case 4: //в работе, время работы + 24 часа, заказ отменяется

            	$srok_vp = 0;

            	switch ((string)get_post_meta($order->ID, 'sm-type-srok', true)){
    				case 'Минуты' :
    					$srok_vp = get_post_meta($order->ID, 'sm-order-days', 1) + 60; // Обращение внимания что поле в бд "sm-order-days" хранит минуты, что бы не искать везде и не переименовывать
    					wp_mail('antonwerstal@yandex.ru','Проверка крон задачи', 'Времяни прошло: '.$min.'. Время когда сработает: '.$srok_vp.'. Срок в минутах');
    					break;
    				case 'Часы' :
    					$srok_vp = (get_post_meta($order->ID, 'sm-order-days', 1) * 60) + 120; // Добавляем два часа до отмены заказа
    					wp_mail('antonwerstal@yandex.ru','Проверка крон задачи', 'Времяни прошло: '.$min.'. Время когда сработает: '.$srok_vp.'. Срок в часах');
    					break;
    				case 'Дни' :
    					$srok_vp = (get_post_meta($order->ID, 'sm-order-days', 1) * 60 * 24) + 1440; // Добавляем один день до отмены заказа
    					wp_mail('antonwerstal@yandex.ru','Проверка крон задачи', 'Времяни прошло: '.$min.'. Время когда сработает: '.$srok_vp.'. Срок в днях');
    					break;
    				case 'Месяцы' :
    					$srok_vp = (get_post_meta($order->ID, 'sm-order-days', 1) * 60 * 24 * 30) + 2880; // Добавляем два день до отмены заказа
    					wp_mail('antonwerstal@yandex.ru','Проверка крон задачи', 'Времяни прошло: '.$min.'. Время когда сработает: '.$srok_vp.'. Срок в месяцах');
    					break;
    				default :
    					$srok_vp = get_post_meta($order->ID, 'sm-order-days', 1) + 60; // Как в минутах
    					break;
    			}

                if($min >= $srok_vp){

                    

                    sm_order_worktime_end($order->ID);

                    

                }

                

                break;

            case 5: //выполнен, 7 суток на рассмотрение, заказ подтверждается

                

                if($min >= 1440 * 7){

                    

                    sm_order_work_end($order->ID);

                    

                }

                

                break;

            case 6: //ожидает вручения, 10 суток, заказ завершается

                

                if($min >= 10 * 1440){

                    

                    sm_order_complete($order->ID);

                    

                }

                

                break;

            case 7: //ожидается отправка, 3 дня до подтверждения, появляется возможность открыть спор

                

                if($min >= 3 * 1440){

                    

                    update_post_meta($order->ID, 'sm-must-judge', 1);

                    

                }

                

                break;

        }

        

    }

    

}

// Своя функция для подсчета минут (время для услуг будем считать в минутах, так как эта меньшая единица измерения срока выполнения)

function sm_diff_min_anton( $dateTime ) {

	$dateTime = strtotime( $dateTime );

	$dateNow = strtotime( current_time( 'mysql' ) );

	$diffMin = ($dateNow - $dateTime) / ( 60 );

	return $diffMin;
}

// Меняем контент заказа услуги

remove_filter( 'the_content', 'sm_add_service_order_content', 5 );
add_filter( 'the_content', 'sm_add_service_order_content_anton', 5 );

function sm_add_service_order_content_anton( $content ) {

	global $post, $user_ID;



	if ( $post->post_type != 'service-order' )

		return $content;



	$service = sm_get_service( $post->ID );



	$users = array( $post->post_author, $service->post_author );



	$orderStatus = get_post_meta( $post->ID, 'sm-order-status', 1 );

	 $text_srok = '';

    switch ((string)get_post_meta($post->ID, 'sm-type-srok', true)){
    	case 'Минуты' :
    		$text_srok = 'мин';
    		break;
    	case 'Часы' :
    		$text_srok = 'ч';
    		break;
    	case 'Дни' :
    		$text_srok = 'дн';
    		break;
    	case 'Месяцы' :
    		$text_srok = 'мес';
    		break;
    	default :
    		$text_srok = 'не определено (старные услуги, с старым сроком)';
    		break;
    }



	$meta = '<div class="service-metas">';



	$meta .= '<div class="service-meta">

            <strong>' . __( 'Статус заказа' ) . '</strong>: ' . sm_get_order_status_name( get_post_meta( $post->ID, 'sm-order-status', 1 ) ) . '

        </div>';



	$meta .= '<div class="service-meta">

            <strong>' . __( 'Стоимость заказа' ) . '</strong>: ' . get_post_meta( $post->ID, 'sm-order-price', 1 ) . ' ' . rcl_get_primary_currency( 1 ) . '

        </div>';



	$meta .= '<div class="service-meta">

            <strong>' . __( 'Срок выполнения' ) . '</strong>: ' . round(get_post_meta( $post->ID, 'sm-order-days', 1 )) . ' '.$text_srok.'.

        </div>';



	if ( $comment = get_post_meta( $post->ID, 'sm-order-comment', 1 ) ) {

		$meta .= '<div class="service-meta">'

			. '<strong>Комментарий к заказу:</strong> ' . get_post_meta( $post->ID, 'sm-order-comment', 1 )

			. '</div>';

	}



	if ( in_array( $orderStatus, array( 9, 10 ) ) ) { //выполнение подтверждено

		if ( $judgeNotice = get_post_meta( $post->ID, 'sm-judge-notice', 1 ) ) {

			$meta .= '<div class="service-meta">'

				. '<strong>Решение администрации:</strong> ' . $judgeNotice

				. '</div>';

		}

	}



	$meta .= '</div>';



	return $meta . $content;

}

// Форма оформления заказа на услугу

rcl_ajax_action( 'sm_ajax_get_new_order_form_anton' );
function sm_ajax_get_new_order_form_anton() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	$serviceId	 = intval( $_POST['serviceId'] );
	$masterId	 = intval( $_POST['masterId'] );

	if ( get_user_meta( $user_ID, 'sm-account', 1 ) == 'Исполнитель' ) {
		wp_send_json( array(
			'error' => __( 'Исполнитель не может оформлять заказы на услуги' )
		) );
	}

	if ( ! $serviceId ) {
		wp_send_json( array(
			'redirect' => rcl_format_url( get_permalink( rcl_get_option( 'sm-private-public' ) ) ) . 'sm-master-id=' . $masterId
		) );
	}

	$result = array(
		'dialog' => array(
			'content'	 => sm_form( array(
				'fields' => array(
					array(
						'slug'		 => 'sm-address',
						'type'		 => 'textarea',
						'title'		 => __( 'Вы можете оставить дополнительные контактные данные' ),
						'default'	 => get_user_meta( $user_ID, 'sm-address', 1 ),
						//'required'	 => 1
					),
					array(
						'slug'		 => 'sm-order-comment',
						'type'		 => 'textarea',
						'title'		 => __( 'Хотите оставить комментарий к заказу?' ),
						//'required'	 => 1
					),
					array(
						'slug'	 => 'sm-service-id',
						'type'	 => 'hidden',
						'value'	 => $serviceId
					),
					array(
						'slug'	 => 'sm-master-id',
						'type'	 => 'hidden',
						'value'	 => $masterId
					)
				),
				'action' => 'create-order-anton',
				'submit' => __( 'Оформить заказ на услугу' )
			) ),
			'title'		 => __( 'Оформление заказа на услугу' ),
			'size'		 => 'auto',
			'class'		 => 'sm-dialog-anton'
		)
	);

	wp_send_json( $result );
}

// Очень большая функция для обработки действий с заказом услуг


remove_action( 'wp', 'sm_setup_actions' );
add_action( 'wp', 'sm_setup_actions_anton' );

function sm_setup_actions_anton() {



	if ( ! isset( $_REQUEST['sm-action'] ) || ! $_REQUEST['sm-action'] )

		return;



	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'sm-nonce' ) )

		return;



	global $user_ID;



	$data = $_REQUEST;



	switch ( $data['sm-action'] ) {

		case 'cancel-order-anton': // Мой action для отмены заказа  в зависимости от причины


			$orderId = intval( $data['sm-order-id'] );



			if ( ! $orderId ) {

				wp_die( __( 'Не удалось отредактировать заказ!' ) );

			}

			$typeCancel = $_POST['reason-cancel'];

			if($typeCancel == 0){
				// Создание спора, так как исполнитель не качественно исполняет свои услуги

				$order = get_post( $orderId );

				update_post_meta( $orderId, 'sm-last-status', get_post_meta( $orderId, 'sm-order-status', 1 ) );
				sm_update_order_status( $orderId, -3 );
				update_post_meta( $orderId, 'sm-judge-comment', $data['sm-judge-comment'] );
				do_action( 'sm_order_judge', $orderId );
				wp_redirect( get_permalink( $orderId ) );


			}
			if($typeCancel == 1){
				// Отмена услуги с расчетам по правилам

				$order = get_post( $orderId );

				// Вычисляем проценты выполненной работы

				$startWork = strtotime(get_post_meta($orderId, 'sm-work-date', 1));

				$order_srok = get_post_meta( $orderId, 'sm-order-days', 1 );

				$type_srok = get_post_meta($orderId, 'sm-type-srok', 1);

				if($type_srok == 'Минуты' || $type_srok == 0){

					$secOrder = $order_srok * 60;

				}

				if($type_srok == 'Часы' || $type_srok == 1){

					$secOrder = $order_srok * 60 * 60;

				}

				if($type_srok == 'Дни' || $type_srok == 2){

					$secOrder = $order_srok * 60 * 60 * 24;

				}

				if($type_srok == 'Месяцы' || $type_srok == 3){
			
					$secOrder = $order_srok * 60 * 60 * 24 * 30;

				}

				$fullWork =  $secOrder + $startWork;

				$deltaWork = $fullWork - strtotime( current_time( 'mysql' ) ); // Времени осталось

				$razWork = $secOrder - $deltaWork; // Времени прошло

				$prov = $razWork + $deltaWork;
				
				$percentWork = ($razWork / $secOrder) * 100; // Процент выполненной работы

				$priceFull = get_post_meta( $orderId, 'sm-order-price', 1 );

				$returnPrice = ($priceFull / 100) * $percentWork; // Цена для возврата в зависимости от прошедшего времени

				$deltaBall = round(1 * $percentWork);

				


				sm_update_order_status( $orderId, -1 );
				update_post_meta( $orderId, 'sm-work-end', -1 );

				// Изменение баланса

				$balance = rcl_get_user_balance( $order->post_author );
				$balance += round($returnPrice);
				rcl_update_user_balance( $balance, $order->post_author, __( 'Воврат средств, в связи с отменой заказа' ) );

				$balance_master = rcl_get_user_balance( get_post_meta($orderId, 'sm-master-id', 1) );
				$balance_master += round($priceFull - $returnPrice);
				rcl_update_user_balance( $balance_master, get_post_meta($orderId, 'sm-master-id', 1), __( 'Начисление средств, в связи с добровольной отменой со стороны заказчика' ) );
				// Изменение рейтинга

				$rating = -1 * $deltaBall;
				

				$args = array(

					'user_id'		 => 14,

					'object_id'		 => $order->post_author,
			
					'object_author'	 => $order->post_author,

					'rating_value'	 => $rating,

					'rating_type'	 => 'edit-admin'

				);



				rcl_insert_rating( $args );
				

			}



			break;

		case 'create-order-anton':



			$serviceId = intval( $data['sm-service-id'] );



			$service = get_post( $serviceId );



			$orderId = wp_insert_post( array(

				'post_title'	 => 'ID:' . $serviceId . ' (' . $service->post_title . ')',

				'post_content'	 => $service->post_content,

				'post_author'	 => $user_ID,

				'post_status'	 => 'publish',

				'post_type'		 => 'service-order'

				) );



			if ( ! $orderId ) {

				wp_die( __( 'Не удалось создать заказ!' ) );

			}



			update_post_meta( $orderId, 'sm-service-id', $serviceId );

			update_post_meta( $orderId, 'sm-master-id', $service->post_author );

			update_post_meta( $orderId, 'sm-order-price', get_post_meta( $serviceId, 'sm-price', 1 ) );

			update_post_meta( $orderId, 'sm-order-days', get_post_meta( $serviceId, 'sm-service-days', 1 ) );

			update_post_meta( $orderId, 'sm-type-srok', get_post_meta( $serviceId, 'sm-type-srok', 1 ) );



			sm_update_order_count( $serviceId );



			sm_update_order_status( $orderId, 1 );



			if ( $data['sm-order-comment'] )

				update_post_meta( $orderId, 'sm-order-comment', $data['sm-order-comment'] );



			update_user_meta( $user_ID, 'sm-address', $data['sm-address'] );



			do_action( 'sm_order_create', $orderId );



			wp_redirect( get_the_permalink( $orderId ) );

			exit;



			break;

		case 'edit-order':



			$orderId = intval( $data['sm-order-id'] );



			if ( ! $orderId ) {

				wp_die( __( 'Не удалось отредактировать заказ!' ) );

			}



			update_post_meta( $orderId, 'sm-order-days', $data['sm-order-days'] );



			if ( $data['sm-order-comment'] )

				update_post_meta( $orderId, 'sm-order-comment', $data['sm-order-comment'] );



			update_user_meta( $user_ID, 'sm-address', $data['sm-address'] );



			wp_redirect( get_the_permalink( $orderId ) );

			exit;



			break;

		case 'reject-order':



			$orderId = intval( $data['sm-order-id'] );



			if ( ! $orderId ) {

				wp_die( __( 'Не удалось отказаться от заказа!' ) );

			}



			sm_order_reject( $orderId );



			wp_redirect( get_permalink( $orderId ) );

			exit;



			break;

		case 'add-time-order-anton':



			$orderId = intval( $data['sm-order-id'] );



			if ( ! $orderId ) {

				wp_die( __( 'Не удалось добавить время!' ) );

			}


			$srok_old = get_post_meta( $orderId, 'sm-order-days', 1 );

			$type_old =  get_post_meta( $orderId, 'sm-type-srok', 1 );

			$convert_srok_old = get_time_convert($srok_old, $type_old, $data['sm-type-srok-custom']);

			$srok_new = $convert_srok_old + (int)$data['sm-days-order'];

			
			update_post_meta( $orderId, 'sm-order-days', $srok_new );

			if($data['sm-type-srok-custom'] == 0){
				$srok_text = 'Минуты';
			}
			if($data['sm-type-srok-custom'] == 1){
				$srok_text = 'Часы';
			}
			if($data['sm-type-srok-custom'] == 2){
				$srok_text = 'Дни';
			}
			if($data['sm-type-srok-custom'] == 3){
				$srok_text = 'Месяцы';
			}

			update_post_meta( $orderId, 'sm-type-srok', $srok_text );



			do_action( 'sm_order_add_days', $orderId );



			wp_redirect( get_permalink( $orderId ) );

			exit;



			break;

		case 'order-rework-anton':



			$orderId = intval( $data['sm-order-id'] );



			if ( ! $orderId ) {

				wp_die( __( 'Не удалось отправить на доработку!' ) );

			}

			update_post_meta( $orderId, 'sm-type-srok', 'Дни');

			$srok_old = get_post_meta( $orderId, 'sm-order-days', 1 );

			$type_old =  get_post_meta( $orderId, 'sm-type-srok', 1 );

			$convert_srok_old = get_time_convert($srok_old, $type_old, $data['sm-type-srok-custom']);

			$srok_new = $convert_srok_old + (int)$data['sm-days-order'];

			
			update_post_meta( $orderId, 'sm-order-days', $srok_new );

			if($data['sm-type-srok-custom'] == 0){
				$srok_text = 'Минуты';
			}
			if($data['sm-type-srok-custom'] == 1){
				$srok_text = 'Часы';
			}
			if($data['sm-type-srok-custom'] == 2){
				$srok_text = 'Дни';
			}
			if($data['sm-type-srok-custom'] == 3){
				$srok_text = 'Месяцы';
			}

			update_post_meta( $orderId, 'sm-type-srok', $srok_text );



			sm_update_order_status( $orderId, 4 );



			do_action( 'sm_order_rework', $orderId );



			wp_redirect( get_permalink( $orderId ) );

			exit;



			break;

		case 'order-sent':



			$orderId = intval( $data['sm-order-id'] );



			if ( ! $orderId ) {

				wp_die( __( 'Не удалось подтвердить отправку заказа!' ) );

			}



			update_post_meta( $orderId, 'sm-sent-comment', $data['sm-sent-comment'] );

			update_post_meta( $orderId, 'sm-sent-date', current_time( 'mysql' ) );



			sm_update_order_status( $orderId, 8 );



			do_action( 'sm_order_sent', $orderId );



			wp_redirect( get_permalink( $orderId ) );

			exit;



			break;

		case 'review-service':



			$orderId = intval( $data['sm-order-id'] );



			if ( ! $orderId ) {

				wp_die( __( 'Не удалось оставить отзыв!' ) );

			}



			$ratingValue = 0;



			if ( $data['sm-rating-data'] ) {



				$args = rcl_decode_data_rating( $data['sm-rating-data'] );



				$ratingValue = $args['rating_value'];



				$rating_id = rcl_insert_rating( $args );

			}



			$service = sm_get_service( $orderId );



			$order = get_post( $orderId );



			$review_id = sm_add_review( array(

				'order_id'		 => $orderId,

				'object_id'		 => $service->ID,

				'object_type'	 => 'service',

				'author_id'		 => $user_ID,

				'user_id'		 => $user_ID == $order->post_author ? $service->post_author : $order->post_author,

				'review_content' => $data['sm-review'],

				'rating_value'	 => $ratingValue

				) );



			if ( ! $review_id ) {

				wp_die( __( 'Не удалось оставить отзыв!' ) );

			}



			do_action( 'sm_new_order_review', $review_id );



			wp_redirect( get_permalink( $orderId ) );

			exit;



			break;

		case 'review-edit':



			$review_id = intval( $data['sm-review-id'] );



			if ( ! $review_id ) {

				wp_die( __( 'Не удалось изменить отзыв!' ) );

			}



			$review = sm_get_review( $review_id );



			$ratingValue = 0;



			if ( $data['sm-rating-data'] ) {



				$args = rcl_decode_data_rating( $data['sm-rating-data'] );



				rcl_delete_rating( array(

					'user_id'		 => $review->author_id,

					'object_author'	 => $review->user_id,

					'object_id'		 => $review->object_id,

					'rating_type'	 => $review->object_type,

					'rating_value'	 => $review->rating_value,

				) );



				$ratingValue = $args['rating_value'];



				$rating_id = rcl_insert_rating( array(

					'user_id'		 => $review->author_id,

					'object_author'	 => $review->user_id,

					'object_id'		 => $review->object_id,

					'rating_type'	 => $review->object_type,

					'rating_value'	 => $ratingValue

					) );

			}



			$service = sm_get_service( $orderId );



			$order = get_post( $orderId );



			$update = array(

				'review_content' => $data['sm-review']

			);



			if ( $ratingValue ) {

				$update['rating_value'] = $ratingValue;

			}



			$result = sm_update_review( $update, array(

				'review_id' => $review_id

				) );



			do_action( 'sm_edit_order_review', $review_id );



			wp_redirect( rcl_get_tab_permalink( $review->user_id, 'master-reviews' ) );

			exit;



			break;

		case 'fail-order':



			$orderId = intval( $data['sm-order-id'] );



			if ( ! $orderId ) {

				wp_die( __( 'Не удалось открыть спор!' ) );

			}



			$order = get_post( $orderId );



			update_post_meta( $orderId, 'sm-last-status', get_post_meta( $orderId, 'sm-order-status', 1 ) );



			sm_update_order_status( $orderId, -3 );



			update_post_meta( $orderId, 'sm-fail-reason', $data['sm-fail-reason'] );



			do_action( 'sm_order_fail', $orderId );



			wp_redirect( get_permalink( $orderId ) );

			exit;



			break;



		case 'judge-order':



			$orderId = intval( $data['sm-order-id'] );



			if ( ! $orderId ) {

				wp_die( __( 'Не удалось открыть спор!' ) );

			}



			$order = get_post( $orderId );



			update_post_meta( $orderId, 'sm-last-status', get_post_meta( $orderId, 'sm-order-status', 1 ) );



			sm_update_order_status( $orderId, -3 );



			update_post_meta( $orderId, 'sm-judge-comment', $data['sm-judge-comment'] );



			do_action( 'sm_order_judge', $orderId );



			wp_redirect( get_permalink( $orderId ) );

			exit;



			break;

	}

}

// Функция подсчета дней до конца
function sm_is_order_time_end_anton( $orderId ) {


	if ( get_post_meta( $orderId, 'sm-order-status', 1 ) != 4 )
		return false;

	$startWork = strtotime( get_post_meta( $orderId, 'sm-work-date', 1 ) );

	$order_srok = get_post_meta( $orderId, 'sm-order-days', 1 );

	$type_srok = get_post_meta($orderId, 'sm-type-srok', 1);

	if($type_srok == 'Минуты' || $type_srok == 0){

		$secOrder = $order_srok * 60;

	}

	if($type_srok == 'Часы' || $type_srok == 1){

		$secOrder = $order_srok * 60 * 60;

	}

	if($type_srok == 'Дни' || $type_srok == 2){

		$secOrder = $order_srok * 60 * 60 * 24;

	}

	if($type_srok == 'Месяцы' || $type_srok == 3){

		$secOrder = $order_srok * 60 * 60 * 24 * 30;

	}

	$timeEnd = $startWork + $secOrder;


	return (($timeEnd - strtotime( current_time( 'mysql' ) )) > 0) ? false : true;
}


// Отмена заказа по своему

rcl_ajax_action( 'sm_ajax_timeend_cancel_order_anton' );
function sm_ajax_timeend_cancel_order_anton() {

	global $user_ID;

	rcl_verify_ajax_nonce();

	$orderId = intval( $_POST['orderId'] );

	$orderStatus = get_post_meta( $orderId, 'sm-order-status', 1 );

	if ( $orderStatus != 4 ) {
		wp_send_json( array( 'error' => __( 'Не удалось отменить заказ!' ) ) );
	}

	$order = get_post( $orderId );

	//sm_update_order_status( $orderId, -1 );

	//update_post_meta( $orderId, 'sm-work-end', -1 );

	//$balance = rcl_get_user_balance( $order->post_author );
	//$balance += get_post_meta( $orderId, 'sm-order-price', 1 );
	//rcl_update_user_balance( $balance, $order->post_author, __( 'Возврат средств после отказа от просроченного заказа' ) );

	//do_action( 'sm_order_cancel', $orderId );

	$result = array(
		'dialog' => array(
			'title'		 => __( 'Отмена заказа' ),
			'size'		 => 'auto',
			'class'		 => 'sm-dialog',
			'content'	 => '<h3>Укажите причину отмены заказа</h3>'
			. sm_form( array(
				'fields' => array(
					array(
						'slug'		 => 'reason-cancel',
						'type'		 => 'radio',
						'title'		 => __( 'Причина отмены заказа' ),
						'required'	 => 1,
						'values' => array(
							'Исполнитель не качественно исполняет услугу',
							'Услуга больше не актуальна'
						)
					),
					array(
						'slug'	 => 'sm-order-id',
						'type'	 => 'hidden',
						'value'	 => $orderId
					)
				),
				'action' => 'cancel-order-anton',
				'submit' => __( 'Подтвердить отправку заказа' )
			) ),
		)
	);

	wp_send_json( $result );


}

// Кнопки в заказе услуг

function sm_get_service_order_manager_items_anton( $order_id ) {

	global $user_ID;



	$order = get_post( $order_id );



	$orderStatus = get_post_meta( $order->ID, 'sm-order-status', 1 );

	$masterId	 = get_post_meta( $order->ID, 'sm-master-id', 1 );

	$serviceId	 = get_post_meta( $order->ID, 'sm-service-id', 1 );



	if ( ! in_array( $user_ID, array( $masterId, $order->post_author ) ) )

		return false;



	$items = array();



	if ( $orderStatus == 1 ) {



		if ( $user_ID == $masterId ) {



			$items[] = array(

				'id'		 => 'sm-update-status',

				'label'		 => __( 'Принять заказ' ),

				'icon'		 => 'fa-exclamation-circle',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_confirm_order',

					'orderId'	 => $order->ID,

					'confirm'	 => __( 'Вы уверены?' )

				) ) . ',this);return false;'

			);



			$items[] = array(

				'id'		 => 'sm-order-reject',

				'label'		 => __( 'Отклонить заказ' ),

				'icon'		 => 'fa-times',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_get_reject_order_form',

					'orderId'	 => $order->ID,

					'confirm'	 => __( 'Вы уверены?' )

				) ) . ',this);return false;'

			);



			$items[] = array(

				'id'	 => 'sm-order-edit',

				'href'	 => rcl_format_url( get_permalink( rcl_get_option( 'public_form_page_rcl' ) ) ) . 'rcl-post-edit=' . $order->ID,

				'icon'	 => 'fa-pencil-square-o',

				'label'	 => __( 'Изменить условия' )

			);

		}

	} else if ( $orderStatus == 2 ) {



		if ( $user_ID == $order->post_author ) {



			$items[] = array(

				'id'		 => 'sm-order-cancel',

				'label'		 => __( 'Отказаться от заказа' ),

				'icon'		 => 'fa-times',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_cancel_order',

					'orderId'	 => $order->ID,

					'confirm'	 => __( 'Вы уверены?' )

				) ) . ',this);return false;'

			);



			$items[] = array(

				'id'		 => 'sm-order-payment',

				'label'		 => __( 'Оплатить заказ' ),

				'icon'		 => 'fa-rouble',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_get_order_payment_form',

					'orderId'	 => $order->ID

				) ) . ',this);return false;'

			);

		}

	} else if ( $orderStatus == 3 ) {



		if ( $user_ID == $masterId ) {



			$items[] = array(

				'id'		 => 'sm-order-work',

				'label'		 => __( 'Взять в работу' ),

				'icon'		 => 'fa-cogs',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_work_order',

					'orderId'	 => $order->ID

				) ) . ',this);return false;'

			);



			$items[] = array(

				'id'		 => 'sm-order-work',

				'label'		 => __( 'Отказаться' ),

				'icon'		 => 'fa-times',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_get_reject_order_form',

					'orderId'	 => $order->ID,

					'confirm'	 => __( 'Вы уверены?' ),

				) ) . ',this);return false;'

			);

		}

	} else if ( $orderStatus == 4 ) {



		rcl_dialog_scripts();



		if ( $user_ID == $masterId ) {



			$items[] = array(

				'id'		 => 'sm-order-made',

				'label'		 => __( 'Заказ выполнен' ),

				'icon'		 => 'fa-flag-checkered',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_made_order',

					'orderId'	 => $order->ID,

				) ) . ',this);return false;'

			);

		} else if ( $user_ID == $order->post_author ) {



			rcl_slider_scripts();

			if ( sm_is_order_time_end_anton( $order->ID ) ) {



				$items[] = array(

					'id'		 => 'sm-order-cancel',

					'label'		 => __( 'Отменить просроченный заказ' ),

					'icon'		 => 'fa-times',

					'onclick'	 => 'sm_ajax(' . json_encode( array(

						'action'	 => 'sm_ajax_timeend_cancel_order',

						'orderId'	 => $order->ID,

						'confirm'	 => __( 'Вы уверены?' ),

					) ) . ',this);return false;'

				);

			}



			if ( !sm_is_order_time_end_anton( $order->ID ) ) {



				$items[] = array(

					'id'		 => 'sm-order-cancel',

					'label'		 => __( 'Отменить заказ' ),

					'icon'		 => 'fa-times',

					'onclick'	 => 'sm_ajax(' . json_encode( array(

						'action'	 => 'sm_ajax_timeend_cancel_order_anton',

						'orderId'	 => $order->ID,

						'confirm'	 => __( 'Вы уверены?' ),

					) ) . ',this);return false;'

				);

			}



			$items[] = array( // замена на свою обработку продления, так как есть выбор в чем продлевать

				'id'		 => 'sm-order-add-time',

				'label'		 => __( 'Продлить время выполнения' ),

				'icon'		 => 'fa-hourglass-start',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_get_add_time_order_form_anton',

					'orderId'	 => $order->ID,

					'confirm'	 => __( 'Вы уверены?' ),

				) ) . ',this);return false;'

			);

		}

	} else if ( $orderStatus == 5 ) {



		rcl_dialog_scripts();



		if ( $user_ID == $order->post_author ) {



			rcl_slider_scripts();



			$items[] = array(

				'id'		 => 'sm-order-complete',

				'label'		 => __( 'Подтвердить выполнение' ),

				'icon'		 => 'fa-flag-checkered',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_complete_order',

					'orderId'	 => $order->ID

				) ) . ',this);return false;'

			);



			$items[] = array(

				'id'		 => 'sm-order-rework',

				'label'		 => __( 'На доработку' ),

				'icon'		 => 'fa-refresh',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_get_form_rework_order_anton',

					'orderId'	 => $order->ID,

					'confirm'	 => __( 'Вы уверены?' ),

				) ) . ',this);return false;'

			);



			$items[] = array(

				'id'		 => 'sm-order-failure',

				'label'		 => __( 'Открыть спор' ),

				'icon'		 => 'fa-frown-o',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_get_order_judge_form',

					'orderId'	 => $order->ID,

					'confirm'	 => __( 'Вы уверены?' ),

				) ) . ',this);return false;'

			);

		}

	} else if ( $orderStatus == 6 ) { //ожидается получение лично

		if ( $user_ID == $order->post_author ) {



			$items[] = array(

				'id'		 => 'sm-order-complete',

				'label'		 => __( 'Подтвердить получение' ),

				'icon'		 => 'fa-shopping-bag',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_complete_order',

					'orderId'	 => $order->ID

				) ) . ',this);return false;'

			);

		}

	} else if ( $orderStatus == 7 ) { //ожидается отправка

		if ( $user_ID == $order->post_author ) {



			if ( get_post_meta( $order->ID, 'sm-must-judge', 1 ) ) {

				$items[] = array(

					'id'		 => 'sm-order-judge',

					'label'		 => __( 'Открыть спор' ),

					'icon'		 => 'fa-gavel',

					'onclick'	 => 'sm_ajax(' . json_encode( array(

						'action'	 => 'sm_ajax_get_order_judge_form',

						'orderId'	 => $order->ID,

						'confirm'	 => __( 'Вы уверены?' ),

					) ) . ',this);return false;'

				);

			}

		}



		if ( $user_ID == $masterId ) {



			$items[] = array(

				'id'		 => 'sm-order-sent',

				'label'		 => __( 'Заказ отправлен' ),

				'icon'		 => 'fa-truck',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_get_sent_order_form',

					'orderId'	 => $order->ID,

				) ) . ',this);return false;'

			);

		}

	} else if ( $orderStatus == 8 ) { //доставляется

		if ( $user_ID == $order->post_author ) {



			$items[] = array(

				'id'		 => 'sm-order-judge',

				'label'		 => __( 'Открыть спор' ),

				'icon'		 => 'fa-gavel',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_get_order_judge_form',

					'orderId'	 => $order->ID,

					'confirm'	 => __( 'Вы уверены?' ),

				) ) . ',this);return false;'

			);



			$items[] = array(

				'id'		 => 'sm-order-complete',

				'label'		 => __( 'Подтвердить получение' ),

				'icon'		 => 'fa-shopping-bag',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_complete_order',

					'orderId'	 => $order->ID

				) ) . ',this);return false;'

			);

		}

	} else if ( $orderStatus == 9 && rcl_exist_addon( 'rating-system' ) ) { //завершен

		$service = sm_get_service( $order->ID );



		$judge = get_post_meta( $order->ID, 'sm-judge', 1 );



		$review = sm_get_reviews( array(

			'object_id'		 => $service->ID,

			'object_type'	 => 'service',

			'author_id'		 => $user_ID,

			'order_id'		 => $order->ID

			) );



		if ( ! $review ) {



			if ( ! $judge || $judge == 'master' || ($user_ID == $order->post_author && $judge == 'client') ) {



				$items[] = array(

					'id'		 => 'sm-order-review',

					'label'		 => __( 'Оставить отзыв' ),

					'icon'		 => 'fa-gavel',

					'onclick'	 => 'sm_ajax(' . json_encode( array(

						'action'	 => 'sm_ajax_get_order_review_form',

						'orderId'	 => $order->ID

					) ) . ',this);return false;'

				);

			}

		}

	} else if ( $orderStatus == 10 ) { //выполнение заказа подтверждено

		if ( $user_ID == $order->post_author ) {



			$items[] = array(

				'id'		 => 'sm-order-take',

				'label'		 => __( 'Забрать лично' ),

				'icon'		 => 'fa-user',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_take_order',

					'orderId'	 => $order->ID

				) ) . ',this);return false;'

			);



			$items[] = array(

				'id'		 => 'sm-order-shipping',

				'label'		 => __( 'Доставить' ),

				'icon'		 => 'fa-truck',

				'onclick'	 => 'sm_ajax(' . json_encode( array(

					'action'	 => 'sm_ajax_shiping_order',

					'orderId'	 => $order->ID

				) ) . ',this);return false;'

			);

		}

	}



	return $items;

}

// Редактируем контент заказа услуг

function sm_get_service_order_manager_anton( $order_id ) {



	$items = sm_get_service_order_manager_items_anton( $order_id );



	if ( ! $items )

		return false;



	return sm_get_manager( $items );

}

remove_filter( 'the_content', 'sm_add_service_order_manager_filter', 20 );
add_filter( 'the_content', 'sm_add_service_order_manager_filter_anton', 20 );

function sm_add_service_order_manager_filter_anton( $content ) {

	global $post, $user_ID;



	if ( $post->post_type != 'service-order' )

		return $content;



	$access = array(

		get_post_meta( $post->ID, 'sm-master-id', 1 ),

		$post->post_author

	);



	if ( ! in_array( $user_ID, $access ) )

		return $content;



	return sm_get_service_order_manager_anton( $post->ID ) . $content;

}

// Меняем функцию обработки продления заказа

rcl_ajax_action( 'sm_ajax_get_add_time_order_form_anton' );
function sm_ajax_get_add_time_order_form_anton() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	$orderId = intval( $_POST['orderId'] );

	$srok = get_post_meta( $orderId, 'sm-order-days', 1 );
	$type_srok = get_post_meta($orderId, 'sm-type-srok', 1);

	$result = array(
		'dialog' => array(
			'content'	 => sm_form( array(
				'fields' => array(
					array(
						'slug'		 => 'sm-days-order',
						'type'		 => 'number',
						'title'		 => __( 'Количество времени на доработку' ),
						'required'	 => 1
					),
					array(
						'slug'		 => 'sm-type-srok-custom',
						'type'		 => 'radio',
						'title'		 => __( 'Срок в' ),
						'required'	 => 1,
						'values'     => array(
							__('Минуты'),
							__('Часы'),
							__('Дни'),
							__('Месяцы')
						)
					),
					array(
						'slug'	 => 'sm-order-id',
						'type'	 => 'hidden',
						'value'	 => $orderId
					)
				),
				'action' => 'add-time-order-anton',
				'submit' => __( 'Добавить время на выполнение' )
			) ),
			'title'		 => __( 'Увеличение времени выполнения заказа' ),
			'size'		 => 'auto',
			'class'		 => 'sm-dialog'
		)
	);

	wp_send_json( $result );
}

// Кнопка отправки на доработку

rcl_ajax_action( 'sm_ajax_get_form_rework_order_anton' );
function sm_ajax_get_form_rework_order_anton() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	$orderId = intval( $_POST['orderId'] );

	$days = get_post_meta( $orderId, 'sm-order-days', 1 );

	$result = array(
		'dialog' => array(
			'content'	 => sm_form( array(
				'fields' => array(
					array(
						'slug'		 => 'sm-days-order',
						'type'		 => 'number',
						'title'		 => __( 'Количество времени на доработку' ),
						'required'	 => 1
					),
					array(
						'slug'		 => 'sm-type-srok-custom',
						'type'		 => 'radio',
						'title'		 => __( 'Срок в' ),
						'required'	 => 1,
						'values'     => array(
							__('Минуты'),
							__('Часы'),
							__('Дни'),
							__('Месяцы')
						)
					),
					array(
						'slug'	 => 'sm-order-id',
						'type'	 => 'hidden',
						'value'	 => $orderId
					)
				),
				'action' => 'order-rework-anton',
				'submit' => __( 'Отправить на доработку' )
			) ),
			'title'		 => __( 'Отправка заказа на доработку' ),
			'size'		 => 'auto',
			'class'		 => 'sm-dialog'
		)
	);

	wp_send_json( $result );
}
// Функция конвертации времени из одного типа в любой другой

function get_time_convert($time, $type_old, $type_new){

	switch ($type_new) {

		case 0 :
			switch ($type_old) {
				case 'Минуты' :
					return $time;
					break;
				case 'Часы' :
					return $time * 60;
					break;
				case 'Дни' :
					return $time * 60 * 24 ;
					break;
				case 'Месяцы' :
					return $time * 60 * 24 * 30;
					break;
			}
			break;
		case 1 :
			switch ($type_old) {
				case 'Минуты' :
					return $time / 60;
					break;
				case 'Часы' :
					return $time;
					break;
				case 'Дни' :
					return $time * 24;
					break;
				case 'Месяцы' :
					return $time * 24 * 30;
					break;
			}
			break;
		case 2:
			switch ($type_old) {
				case 'Минуты' :
					return $time / (60 * 24);
					break;
				case 'Часы' :
					return $time / 24;
					break;
				case 'Дни' :
					return $time;
					break;
				case 'Месяцы' :
					return $time * 30;
					break;
			}
			break;
		case 3 :
			switch ($type_old) {
				case 'Минуты' :
					return $time / (60 * 24 * 30);
					break;
				case 'Часы' :
					return $time / (24 * 30);
					break;
				case 'Дни' :
					return $time / 30;
					break;
				case 'Месяцы' :
					return $time;
					break;
			}
			break;

	}

}


// Добавление своего блока в область с именем в личном кабинете (Выбор статуса заняточти)

function psr_get_box_select_stat_work() {

    global $user_LK;

    $user_act = wp_get_current_user();

    switch (get_user_meta($user_LK, 'status_work', 1)){
    	case 'свободен' :
    		$activate_free = 'activate';
    		$text_external = 'свободен';
    		break;
    	case 'частично занят' :
    		$activate_pol_work = 'activate';
    		$text_external = 'частично занят';
    		break;
    	case 'занят' :
    		$activate_work = 'activate';
    		$text_external = 'занят';
    		break;
    	default :
    		$activate_free = 'activate';
    		$text_external = 'свободен';
    		break;
    }

    // Особенность вывода для jquery скрипте
    if($user_LK == $user_act->ID){
    $out = '<div class="container-switch-work"><div class="switch-cont-free switch-main-cont '.$activate_free.'"><p class="text-switch" data-switch="свободен" data-idlk="'.$user_LK.'" data-idcurrent="'.$user_act->ID.'">свободен</p></div><div class="switch-cont-pol-work switch-main-cont '.$activate_pol_work.'"><p class="text-switch" data-switch="частично занят" data-idlk="'.$user_LK.'" data-idcurrent="'.$user_act->ID.'">частично занят</p></div><div class="switch-cont-work switch-main-cont '.$activate_work.'"><p class="text-switch" data-switch="занят" data-idlk="'.$user_LK.'" data-idcurrent="'.$user_act->ID.'">занят</p></div></div>';
	}
	else{
		 $out = '<p class="status-work-text-external">Статус занятости : '.$text_external.'</p>';
	}




    return $out;

}

add_action( 'wp_footer', 'psr_before_counters_status_work', 6 );

function psr_before_counters_status_work() {

    if ( ! rcl_is_office() )

        return false;


    /* Выбор места размещения в необычных областях личного кабинета
    // Справа от имени

    $place = rcl_get_option( 'psr_place', '1' );

    $div   = '.tcl_user,.office-title > h2,.cab_ln_title > h2,.office-content-top > h2,.ao_name_author_lk > h2,.cab_lt_title > h2, .cab_title > h2';



    // Справа от кнопок actions

    if ( $place == 2 )

        $div = '.tcl_bttn_act,.ln_bttn_act,.office-actions,.cab_bttn,.ao_content_lk_top,.aop_content_lk_top';

 */

    $blk = psr_get_box_select_stat_work();

  	$div   = '.tcl_user,.office-title > h2,.cab_ln_title > h2,.office-content-top > h2,.ao_name_author_lk > h2,.cab_lt_title > h2, .cab_title > h2';


// Поместим блок перед выбранным местом

    $out = "<script>

jQuery(document).ready(function(){

jQuery('$div').append('$blk');

});

</script>";

    echo $out;

}

// Обработка изменения статуса занятости

add_action( 'wp_ajax_nopriv_updatestatuswork', 'updatestatuswork' );
add_action( 'wp_ajax_updatestatuswork', 'updatestatuswork' );

function updatestatuswork(){

	$value = $_POST['value'];
	$user_lk = $_POST['user_lk'];
	$user_current = $_POST['user_current'];

	update_user_meta($user_lk, 'status_work', $value);

	wp_send_json_success(array(
        'success' => __('Статус успешно изменен!'), //уведомление об успехе
        'reload' => true,
    ));

	

}












