<?php
/**
 * Child theme of Bono
 * https://wpshop.ru/themes/bono
 *
 * @package Bono
 */

/**
 * Enqueue child styles
 *
 * НЕ УДАЛЯЙТЕ ДАННЫЙ КОД
 */
add_action( 'wp_enqueue_scripts', 'enqueue_child_theme_styles', 100);
function enqueue_child_theme_styles() {
    wp_enqueue_style( 'bono-style-child', get_stylesheet_uri(), array( 'bono-style' )  );
    wp_enqueue_style( 'style-lightbox', get_stylesheet_directory_uri (). '/css/lightbox.css');
}

/**
 * НИЖЕ ВЫ МОЖЕТЕ ДОБАВИТЬ ЛЮБОЙ СВОЙ КОД
 */

// Скрываем лищние поля woocommerce
add_filter( 'woocommerce_checkout_fields', 'truemisha_del_fields', 25 );
 
function truemisha_del_fields( $fields ) {
 
	// оставляем эти поля
	// unset( $fields[ 'billing' ][ 'billing_first_name' ] ); // имя
	// unset( $fields[ 'billing' ][ 'billing_last_name' ] ); // фамилия
	// unset( $fields[ 'billing' ][ 'billing_phone' ] ); // телефон
	// unset( $fields[ 'billing' ][ 'billing_email' ] ); // емайл
    // unset( $fields[ 'billing' ][ 'billing_country' ] ); // страна
	// unset( $fields[ 'billing' ][ 'billing_address_1' ] ); // адрес 1
    // unset( $fields[ 'billing' ][ 'billing_address_2' ] ); // адрес 2
    // unset( $fields[ 'order' ][ 'order_comments' ] ); // заметки к заказу
    // unset( $fields[ 'billing' ][ 'billing_company' ] ); // компания
	    
    // удаляем все эти поля
	//unset( $fields[ 'billing' ][ 'billing_city' ] ); // город
	//$array['billing']['billing_state']['label'] = 'Область / край';
	//unset( $fields[ 'billing' ][ 'billing_postcode' ] ); // почтовый индекс
 
	return $fields;
 
}

// изменяем надписи в форме комментирования
function wpschool_change_submit_label($defaults) {
    // Текст перед формой комментирования
    // $defaults['title_reply'] = 'Текст перед формой';
    // Текст кнопки в форме комментирования
    $defaults['label_submit'] = 'Отправить';
    return $defaults;
}
add_filter( 'comment_form_defaults', 'wpschool_change_submit_label' );

add_action( 'Wpshop\TheTheme\Features\ImageManagement::init', function ( $instance ) {
    remove_action( 'customize_register', [ $instance, '_update_wc_customizer_sections' ], 20 );
    remove_filter( 'woocommerce_get_image_size_thumbnail', [ $instance, '_get_image_size_thumbnail' ] );
} );


// Подключаем свой скрипт
add_action( 'wp_enqueue_scripts', 'enabled_my_scripts' );
function enabled_my_scripts(){
    
    wp_enqueue_script( 'filterizr-my', get_stylesheet_directory_uri () . '/js/jquery.filterizr.min.js');
    wp_enqueue_script( 'pkgd-my', get_stylesheet_directory_uri () . '/js/isotope.pkgd.min.js');
    wp_enqueue_script( 'litebox', get_stylesheet_directory_uri () . '/js/lightbox.js');
    wp_enqueue_script( 'masked', get_stylesheet_directory_uri () . '/js/jquery.mask.min.js');
    wp_enqueue_script( 'my-scripts', get_stylesheet_directory_uri () . '/js/my_scripts.js');

    wp_localize_script( 'my-scripts', 'myajax',
        array(
            'url' => admin_url('admin-ajax.php')
        )
    );
}

// Пополнение баланса
add_action( 'wp_ajax_paymentbalance', 'paymentbalance' );
add_action( 'wp_ajax_nopriv_paymentbalance', 'paymentbalance' );

function paymentbalance(){

    $summ = $_POST['summ'];

    $user = wp_get_current_user();

    // Создание товара, который будет оплачиваться как баланс

    $post = array(
        'post_author' => 1,
        'post_content' => 'Баланс для юзера '.$user->user_login, //Описание товара 
        'post_status' => "publish",
        'post_title' => 'Баланс для юзера '.$user->user_login, // Название товара
        'post_type' => "product",
    );
    $post_id = wp_insert_post($post); //Создаем запись

    if(!$post_id){
        wp_send_json_success(array('val' => false));
    }

    wp_set_object_terms($post_id, 232, 'product_cat'); //Задаем категорию товара

    update_post_meta($post_id, '_sku', 123);
    update_post_meta( $post_id, '_visibility', 'visible' );
    update_post_meta( $post_id, '_downloadable', 'no');
    update_post_meta( $post_id, '_virtual', 'no');
    wp_set_object_terms($post_id, 'simple', 'product_type');
    update_post_meta( $post_id, '_regular_price', $summ);
    update_post_meta( $post_id, '_price', $summ);

    //Создание заказа, на который будет перенаправлен пользователь

    $order = wc_create_order(array('customer_id' => $user->ID));

    if(!$order){
        wp_send_json_success(array('val' => false));
    }

    $address = array(
        'first_name' => $user->user_firstname,
        'last_name'  => $user->user_lastname,
        'company'    => '',
        'email'      => $user->user_email,
        'phone'      => '',
        'address_1'  => '',
        'address_2'  => '', 
        'city'       => '',
        'state'      => '',
        'postcode'   => '',
        'country'    => ''
    );

    update_post_meta( $order->get_id(), 'type_order_for_balance', 'yes');

    $order->set_address( $address, 'billing' );
    $order->set_address( $address, 'shipping' );

    $order->add_product( get_product( $post_id ), 1 );
    $order->calculate_totals();
    // Удаление созданноо товара

    wp_delete_post($post_id);

    // Отключаем ненужные нам способы оплаты
    $order->set_payment_method(array('qiwiforbalance'));

    wp_send_json_success(array('val' => true, 'redirect' => $order->get_checkout_payment_url()));
}

// Отключение способов оплаты для определенных категорий сделано через js

// Добавление дочерней вкладки для пополнения баланса

add_action('rcl_setup_tabs','balance_add_sub_tab',10);
function balance_add_sub_tab(){
   
    $subtab = array(
        'id'=> 'paybalance',
        'name'=> 'Пополнить баланс',
        'icon' => 'fa-icon',
        'callback'=>array(
            'name'=>'balance_pay_content_function'
        )
    );
   
    rcl_add_sub_tab('wallet',$subtab);
   
}

function balance_pay_content_function($user_lk){
    $html = '';

    $html .= '<div class="cont-payment-user-balance">';
        $html .= '<p class="label-payment-custom">Введите сумму</p>';
        $html .= '<input type="number" class="summ-payment">';
        $html .= '<p class="btn-payment-cstom">Пополнить</p>';
    $html .= '</div>';

    return $html;
}

add_shortcode('example', 'example_function');

function example_function(){
    $html = '123';

    return $html;
}


// Изображения для галереии

add_action( 'init', 'init_image_for_gallery' ); // Использовать функцию только внутри хука init
 
function init_image_for_gallery() {
    $labels = array(
        'name' => 'Изображения для галереи',
        'singular_name' => 'Изображение', // админ панель Добавить->Функцию
        'add_new' => 'Добавить изображение',
        'add_new_item' => 'Добавить новое изображение', // заголовок тега <title>
        'edit_item' => 'Редактировать изображение',
        'new_item' => 'Новое изображение',
        'all_items' => 'Все изображения',
        'view_item' => 'Просмотр изображения на сайте',
        'search_items' => 'Искать изображение',
        'not_found' =>  'Изображений не найдено.',
        'not_found_in_trash' => 'В корзине нет изображений.',
        'menu_name' => 'Изображения для галереи' // ссылка в меню в админке
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true, // показывать интерфейс в админке
        'has_archive' => true, 
        'menu_icon' => get_stylesheet_directory_uri() .'/img/function_icon.png', // иконка в меню
        'menu_position' => 20, // порядок в меню
        'supports' => array( 'title', 'editor', 'comments', 'author', 'thumbnail')
    );
    register_post_type('imagesgallery', $args);
}

 
add_action( 'init', 'create_tax_cat_images', 0 );
function create_tax_cat_images () {
$args = array(
    'label' => 'Категории изображений',
    'labels' => array(
    'name' => 'Категории изображений',
    'singular_name' => 'Категории изображений',
    'menu_name' => __( 'Категории изображений' ),
    'all_items' => __( 'Все категории изображений' ),
    'edit_item' => __( 'Изменить категорию' ),
    'view_item' => __( 'Просмотреть категории' ),
    'update_item' => __( 'Обновить категорию' ),
    'add_new_item' => __( 'Добавить новую категорию' ),
    'new_item_name' => __( 'Категории изображений' ),
    'parent_item' => __( 'Родительская' ),
    'parent_item_colon' => __( 'Родительская:' ),
    'search_items' => __( 'Поиск категорий' ),
    'popular_items' => null,
    'separate_items_with_commas' => null,
    'add_or_remove_items' => null,
    'choose_from_most_used' => null,
    'not_found' => __( 'Категорий не найдено.' ),
    ),
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'show_in_nav_menus' => true,
    'show_tagcloud' => true,
    'show_in_quick_edit' => true,
    'show_in_rest' => true,
    'meta_box_cb' => null,
    'show_admin_column' => false,
    'description' => '',
    'hierarchical' => true,
    'update_count_callback' => '',
    'query_var' => true,
    'rewrite' => array(
    'slug' => 'flat',
    'with_front' => false,
    'hierarchical' => true,
    'ep_mask' => EP_NONE,
),
    'sort' => null,
    '_builtin' => false,
);
register_taxonomy( 'catimages', array('imagesgallery'), $args );
}





// Проверка параметров для оплаты eximbay

add_action( 'wp_ajax_eximbay_payment_info', 'eximbay_payment_info' );
add_action( 'wp_ajax_nopriv_eximbay_payment_info', 'eximbay_payment_info' );

function eximbay_payment_info($order_id){

    $current_user = wp_get_current_user();
    $data = array(
        'ver' => 230, // константа
        'txntype' => 'PAYMENT', // константа
        'charset' => 'UTF-8', // константа
        'statusurl' => 'https://aliveaquarium.ru/status-platezha/', // константа
        'returnurl' => 'https://aliveaquarium.ru/rezultat-platezha/', // константа
        'shipTo_country' => $_POST['country_shipping'],
        'shipTo_city' => $_POST['billing_city'],
        'shipTo_state' => $_POST['shipTo_state'],
        'shipTo_street1' => $_POST['shipTo_street1'],
        'shipTo_postalCode' => $_POST['shipTo_postalCode'],
        'shipTo_phoneNumber' => $_POST['shipTo_phoneNumber'],
        'shipTo_firstName' => $_POST['shipTo_firstName'],
        'shipTo_lastName' => $_POST['shipTo_lastName'],
        'mid' => $_POST['mid'],
        'ref' => 'demo20170418202020',
        'ostype' => 'P',
        'displaytype' => 'P',
        'cur' => 'RUB',
        'amt' => $_POST['total_amount'],
        'shop' => 'aliveaquarium',
        'buyer' => $_POST['shipTo_firstName'] . ' ' . $_POST['shipTo_lastName'],
        'email' => $_POST['email'],
        'tel' => $_POST['shipTo_phoneNumber'],
        'lang' => 'RU',
        'paymethod' => $_POST['method_pay'],
        'autoclose' => 'Y',
        'param1' => $_POST['order_id'],
        'param2' => $_POST['user_login']
    );

    $counter = 0;
    foreach($_POST['item_order'] as $item){
        $data['item_'.$counter.'_product'] = $item['name'];
        $data['item_'.$counter.'_quantity'] = $item['quality'];
        $data['item_'.$counter.'_unitPric'] = $item['price'];
        $counter++;
    }






    $secretKey = $_POST['secretkey'];
    $reqURL = "https://secureapi.eximbay.com/Gateway/BasicProcessor.krp";
    $fgkey = "";
    $sortingParams = "";

    //echo '<pre>';
        //var_dump($data);
    //echo '</pre>';

    foreach($data as $Key=>$value) {
        $hashMap[$Key]  = $value;
    }

    $size = count($hashMap);
    ksort($hashMap);

    $counter = 0;

    foreach ($hashMap as $key => $val) {
        if ($counter == $size-1){
            $sortingParams .= $key."=" .$val;
        }else{
            $sortingParams .= $key."=" .$val."&";
        }
        ++$counter;
    }

    $linkBuf = $secretKey. "?".$sortingParams;

    $fgkey = hash("sha256", $linkBuf);

    WC()->cart->empty_cart();
    wp_send_json_success(array('params' => $hashMap, 'fgkey' => $fgkey));

    
}

// Создание заказа в момент оплаты exim

add_action( 'wp_ajax_create_order_for_exim', 'create_order_for_exim' );
add_action( 'wp_ajax_nopriv_create_order_for_exim', 'create_order_for_exim' );

function create_order_for_exim(){
    $check_create_auto = false;
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address_1 = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postcode = $_POST['postcode'];
    $country = $_POST['country'];


    $address = array(
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'company'    => '',
        'email'      => $email,
        'phone'      => $phone,
        'address_1'  =>  $address_1,
        'address_2'  => '', 
        'city'       => $city,
        'state'      => $state,
        'postcode'   => $postcode,
        'country'    => $country
    );
$user_id = 0;
// Проверка авторизирован ли пользователь
    if(is_user_logged_in()){
        $user = wp_get_current_user();
        $user_login = $user->user_login;
        $user_id = $user->ID;
    }
    else{
        // Если не существует пользоватенля с указанной почтой, то создается новый
        if(get_user_by('email', $email)){

            // Авторизируем пользователя и получаем его данные
            $user = get_user_by('email', $email);
            $user_id = $user->ID;
            $user_login = $user->user_login;
            wp_send_json_success(array('message' => 'Что бы продолжить покупку авторизируйтесь ', 'error' => true));
        }
        else{
            $random_password = wp_generate_password( 12 );
            $arr = explode('@', $email);
            $user_login = $arr[0];
            $message_mail = 'Вы сделали заказ, на сайте <a href="https://aliveaquarium.ru/">aliveaquarium.ru/</a>. Мы создали для вас аккаунт автоматически.<br>Логин: '.$user_login.'<br>Пароль: '.$random_password;
            if($user_id_create = wp_create_user( $user_login, $random_password, $email )){
                update_user_meta($user_id_create, 'oferta', 1);
                $check_create_auto = true;
                $user_for_role = new WP_User( $user_id_create );
                $user_for_role->add_role( 'contributor' );
                update_user_meta($user_id_create, 'first_order', 'no');
                if(wp_mail($email, 'Регистрация', $message_mail)){
                    $mass_sign = array(
                        'user_login'    => $user_login,
                        'user_password' => $random_password,
                        'remember'      => true,
                    );
                    $user = wp_signon( $mass_sign, false );
                    $user_id = $user->ID;

                    if ( is_wp_error( $user ) ) {
                        wp_send_json_success(array('message' => 'Не получилось авторизировать созданного пользователя. Сообщение системы: ', 'error' => true));
                    }
                }
                else{
                    wp_send_json_success(array('message' => 'Пользователь создан, но не удалось отправить письмо на email с данными для авторизации.', 'error' => true));
                }
            }
            else{
                wp_send_json_success(array('message' => 'Не удалось создать пользователя', 'error' => true));
            }
        }
    }
// Конец манипуляций с пользователем
    $order = wc_create_order(array('customer_id' => $user_id));

    if ( is_wp_error( $order ) ) {
        wp_send_json_success(array('res' => 'Сообщение системы: '.$order->get_error_message(), 'error' => true));
    }

    foreach($_POST['item_order'] as $item){
        if($item['variation'] != ''){
            $order->add_product( get_product($item['variation']), $item['quality'] );
        }
        else{
            $order->add_product( get_product($item['id']), $item['quality'] );
        }
    }


    $user_for_fee = get_user_by('login', $user_login);
    if(get_user_meta($user_for_fee->ID, 'first_order', true) == 'yes'){
        $item_fee = new WC_Order_Item_Fee();

        $item_fee->set_name( 'Скидка '.get_option('size_sale_first_order').'% на первый заказ' );
        $item_fee->set_amount( -WC()->cart->subtotal * (get_option('size_sale_first_order')/100) ); // Fee amount
        $item_fee->set_tax_class( '' ); // default for ''
        $item_fee->set_tax_status( 'taxable' ); // or 'none'
        $item_fee->set_total( -WC()->cart->subtotal * (get_option('size_sale_first_order')/100) );

        $item_fee->calculate_taxes();
        $order->add_item( $item_fee );
        update_user_meta($user_for_fee->ID, 'first_order', 'no');
    }

    if($check_create_auto){
        update_user_meta($user_for_fee->ID, 'first_order', 'yes');
    }

    $order->set_address( $address, 'billing' ); //Добавляем данные о доставке
    $order->set_address( $address, 'shipping' );

    $order->calculate_totals();

    wp_send_json_success(array('res' => $order->get_id(), 'link_order' => wc_get_checkout_url($order->get_id()), 'user_login' => $user_login));

}


// Паспортные данные для доставки

add_action('init','add_tab_pasport_data');
function add_tab_pasport_data(){
    
    $tab_data = array(
        'id'=>'pasport',
        'name'=>'Паспортные данные',
        'public'=>0,//делаем вкладку приватной
        'icon'=>'fa-address-card-o',//указываем иконку
        'output'=>'menu',//указываем область вывода
        'content'=>array(
            array( //массив данных первой дочерней вкладки
                'callback' => array(
                    'name'=>'my_pasport_data_render',//функция формирующая контент
                )
            )
        )
    );

    rcl_tab($tab_data);

}

function my_pasport_data_render($user_lk){
    $html = '<p class="head-tab-custom">Паспортные данные</p>';

    $country_give = get_user_meta($user_lk, 'country-give', true);
    $html .= '<p class="label-pasport">Страна выдачи</p>';
    $mass_country = array('AZ' => 'Азербайджан', 'AM' => 'Армения', 'BY' => 'Беларусь', 'GE' => 'Грузия', 'KZ' => 'Казахстан', 'MD' => 'Молдова', 'RU' => 'Российская Федерация', 'UZ' => 'Узбекистан', 'UA' => 'Украина');
    $html_option = '';

    foreach ($mass_country as $key => $item) {
        if($country_give == $key){
            $selected = 'selected';
        }
        else{
            $selected = '';
        }
        $html_option .= '<option value="'.$key.'" '.$selected.'>'.$item.'</option>';
        
    }       
    $html .= '
              <select class="input-pasport country-give">
                '.$html_option.'
              </select>
    ';

    $number_pasport = get_user_meta($user_lk, 'number-pasport', true);
    $html .= '<p class="label-pasport">Номер паспорта</p>';
    $html .= '<input type="text" placeholder="Номер паспорта" value="' . esc_attr( $number_pasport ) . '" class="input-pasport number-pasport" />';

    $from_pasport = get_user_meta($user_lk, 'from-pasport', true);
    $html .= '<p class="label-pasport">Кем выдан</p>';
    $html .= '<input type="text" placeholder="Кем выдан" value="' . esc_attr( $from_pasport ) . '" class="input-pasport from-pasport" />';

    $date_pasport = get_user_meta($user_lk, 'date-pasport', true);
    $html .= '<p class="label-pasport">Дача выдачи</p>';
    $html .= '<input type="text" placeholder="Дача выдачи" value="' . esc_attr( $date_pasport ) . '" class="input-pasport date-pasport" />';

    $inn_pasport = get_user_meta($user_lk, 'inn-pasport', true);
    $html .= '<p class="label-pasport">ИНН</p>';
    $html .= '<input type="text" placeholder="ИНН" value="' . esc_attr( $inn_pasport ) . '" class="input-pasport inn-pasport" />';

    $html .= '<input type="hidden" class="user_id" value="'.$user_lk.'">';

    $html .= '<p class="btn-pasport-save">Сохранить</p>';

    return $html;
}

// Сохранение паспортных данных

add_action( 'wp_ajax_savepasport', 'savepasport' );
add_action( 'wp_ajax_nopriv_savepasport', 'savepasport' );

function savepasport(){

    if(isset($_POST['user_id'])){
        $user_id = $_POST['user_id'];
    }
    else{
        wp_send_json_success(array('res' => false));
    }


    if(isset($_POST['c_give'])){update_user_meta($user_id, 'country-give' ,$_POST['c_give']);}
    if(isset($_POST['number'])){update_user_meta($user_id, 'number-pasport' ,$_POST['number']);}
    if(isset($_POST['from'])){update_user_meta($user_id, 'from-pasport' ,$_POST['from']);}
    if(isset($_POST['date'])){update_user_meta($user_id, 'date-pasport' ,$_POST['date']);}
    if(isset($_POST['inn'])){update_user_meta($user_id, 'inn-pasport' ,$_POST['inn']);}
    
    wp_send_json_success(array('res' => true));

}

// Добавление полей в форму заказа

add_filter( 'woocommerce_after_checkout_billing_form' , 'label_pasport_render_checkout' );

function label_pasport_render_checkout( $fields ) {
   echo '<p class="head-custom-field-checkout">Паспортные данные <span><a href="https://boxberry.ru/about_us/news/events/1303" target="_blank">подробнее</a></span></p>';
}

// Добавление поля Страна выдачи
add_filter( 'woocommerce_after_checkout_billing_form' , 'country_give_render_checkout' );

function country_give_render_checkout( $checkout ) {
    $user = wp_get_current_user();
    woocommerce_form_field( 'country_give', array(
        'type' => 'country', 
        'label' => __('Страна выдачи', 'woocommerce'),
        'placeholder' => _x('Страна выдачи', 'placeholder', 'woocommerce'),
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true
    ), get_user_meta($user->ID, 'country-give', true));

    return $fields;
}

add_action( 'woocommerce_checkout_update_order_meta', 'country_give_save_checkout' );

function country_give_save_checkout( $order_id ) {
    if ( ! empty( $_POST['country_give'] ) ) {
        update_post_meta( $order_id, 'country_give', sanitize_text_field( $_POST['country_give'] ) );
    }
}

// Добавление поля Номер паспорта

add_filter( 'woocommerce_after_checkout_billing_form' , 'number_pasport_render_checkout' );

function number_pasport_render_checkout( $checkout ) {
    $user = wp_get_current_user();
    woocommerce_form_field( 'number_pasport', array(
        'type' => 'text', 
        'label' => __('Номер паспорта', 'woocommerce'),
        'placeholder' => _x('Номер паспорта', 'placeholder', 'woocommerce'),
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true
    ), get_user_meta($user->ID, 'number-pasport', true));

    return $fields;
}

add_action( 'woocommerce_checkout_update_order_meta', 'number_pasport_save_checkout' );

function number_pasport_save_checkout( $order_id ) {
    if ( ! empty( $_POST['number_pasport'] ) ) {
        update_post_meta( $order_id, 'number_pasport', sanitize_text_field( $_POST['number_pasport'] ) );
    }
}

// Добавление поля Кем выдан

add_filter( 'woocommerce_after_checkout_billing_form' , 'from_pasport_render_checkout' );

function from_pasport_render_checkout( $checkout ) {
    $user = wp_get_current_user();
    woocommerce_form_field( 'from_pasport', array(
        'type' => 'text', 
        'label' => __('Кем выдан', 'woocommerce'),
        'placeholder' => _x('Кем выдан', 'placeholder', 'woocommerce'),
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true
    ), get_user_meta($user->ID, 'from-pasport', true));

    return $fields;
}

add_action( 'woocommerce_checkout_update_order_meta', 'from_pasport_save_checkout' );

function from_pasport_save_checkout( $order_id ) {
    if ( ! empty( $_POST['from_pasport'] ) ) {
        update_post_meta( $order_id, 'from_pasport', sanitize_text_field( $_POST['from_pasport'] ) );
    }
}

// Добавление поля Дата выдачи

add_filter( 'woocommerce_after_checkout_billing_form' , 'date_pasport_render_checkout' );

function date_pasport_render_checkout( $checkout ) {
    $user = wp_get_current_user();
     woocommerce_form_field( 'date_pasport', array(
        'type' => 'text', 
        'label' => __('Дата выдачи', 'woocommerce'),
        'placeholder' => _x('Дата выдачи', 'placeholder', 'woocommerce'),
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true
    ), get_user_meta($user->ID, 'date-pasport', true));

    return $fields;
}

add_action( 'woocommerce_checkout_update_order_meta', 'date_pasport_save_checkout' );

function date_pasport_save_checkout( $order_id ) {
    if ( ! empty( $_POST['date_pasport'] ) ) {
        update_post_meta( $order_id, 'date_pasport', sanitize_text_field( $_POST['date_pasport'] ) );
    }
}

// Добавление поля ИНН

add_filter( 'woocommerce_after_checkout_billing_form' , 'inn_pasport_render_checkout' );

function inn_pasport_render_checkout( $checkout ) {
    $user = wp_get_current_user();
    woocommerce_form_field( 'inn_pasport', array(
        'type' => 'text', 
        'label' => __('ИНН', 'woocommerce'),
        'placeholder' => _x('ИНН', 'placeholder', 'woocommerce'),
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true
    ), get_user_meta($user->ID, 'inn-pasport', true));

    return $fields;
}

add_action( 'woocommerce_checkout_update_order_meta', 'inn_pasport_save_checkout' );

function inn_pasport_save_checkout( $order_id ) {
    if ( ! empty( $_POST['inn_pasport'] ) ) {
        update_post_meta( $order_id, 'inn_pasport', sanitize_text_field( $_POST['inn_pasport'] ) );
    }
}


// Вывод информации по кодам обработки в заказах


add_action( 'add_meta_boxes', 'order_add_responce_code' );
 
function order_add_responce_code() {
 
    add_meta_box(
        'responce_code', // ID нашего метабокса
        'Коды возврата EXIM', // заголовок
        'responce_code_metabox_callback', // функция, которая будет выводить поля в мета боксе
        'shop_order', // типы постов, для которых его подключим
        'normal', // расположение (normal, side, advanced)
        'default' // приоритет (default, low, high, core)
    );
 
}
 
function responce_code_metabox_callback( $post ) {
 
    $mass_error = get_post_meta($post->ID, 'log_pay', false);

    echo '<table>';
    foreach($mass_error as $item){
        echo '<tr><td>'.$item.'</td></tr>';
    }
    echo '</table>';
 
}

// Запрос к boxberry
add_action( 'wp_ajax_reqboxberry', 'reqboxberry' );
add_action( 'wp_ajax_nopriv_reqboxberry', 'reqboxberry' );

function reqboxberry(){


    $order_num = $_POST['order_num'];
    $country_code = $_POST['country_code'];
    $type = $_POST['type'];
    $postcode = $_POST['postcode'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $country_code_pasport = $_POST['country_code_pasport'];
    $number_pasport = $_POST['number_pasport'];
    $from_pasport = $_POST['from_pasport'];
    $date_pasport = $_POST['date_pasport'];
    $inn_pasport = $_POST['inn_pasport'];
    $cod_value = $_POST['cod_value'];
    $cod_cur = $_POST['cod_cur'];
    $id_order = $_POST['id_order'];
    $action = $_POST['action'];

    $check_field = false;

    //wp_send_json_success(array('success' => false,'res' =>  ''));
    if(!isset($country_code)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает кода страны');
        $check_field = true;   
    }
    if(!isset($postcode)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает индекса');
        $check_field = true;    
    }
    if(!isset($city)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает города');
        $check_field = true;    
    }
    if(!isset($address)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает адреса');
        $check_field = true;    
    }
    if(!isset($name)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает имени');
        $check_field = true;    
    }
    if(!isset($lastname)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает фамилиии');
        $check_field = true;    
    }
    if(!isset($email)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает email');
        $check_field = true;    
    }
    if(!isset($phone)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает телефона');
        $check_field = true;    
    }
    if(!isset($country_code_pasport)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает страны выдачи паспорта');
        $check_field = true;    
    }
    if(!isset($number_pasport)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает номера паспорта');
        $check_field = true;    
    }
    if(!isset($from_pasport)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает кем выдан паспорт');
        $check_field = true;    
    }
    if(!isset($date_pasport)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает даты выдачи паспорта');
        $check_field = true;    
    }
    if(!isset($inn_pasport)){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не хватает ИНН');
        $check_field = true;    
    }




    $mass_req = array(
        'method' => 'CreateParcel',
        'token' => '1Z2ownrQRQqSYAkpb0J3YbP6FDNWdYTk',
        'parcels' => array()
    );
    

    $order = wc_get_order($id_order);
    $order_items = $order->get_items();


    if(count($order_items) == 0){
        add_post_meta($id_order, 'responce_boxberry_field', 'Не удалось получить элементы заказа');
        $check_field = true;
    }

    foreach( $order_items as $item_id => $item ){
        $item_data = $item->get_data();




        $mass_data = array();
        $mass_data['orderNum'] = 'Order №'.$id_order.'-'.$item_data['variation_id'];
        $mass_data['countryTo'] = (int)$country_code;
        $mass_data['type'] = 2;
        $mass_data['address'] = array(
            'postcode' => $postcode,
            'city' => $city,
            'addressString' => $address 
        );
        $mass_data['pointcode'] = '';

        $mass_data['recipient'] = array(
            'phone' => $phone,
            'email' => $email,
            'fullNameString' => $name.' '.$lastname,
            'passport' => array(
                'countryCode' => $country_code_pasport,
                'passport' => $number_pasport,
                'issuedWhen' => $date_pasport,
                'issuedBy' => $from_pasport,
                'inn' => $inn_pasport
            )
        );
        $mass_data['goods_cost_currency'] = 'USD';



        //$product = $item->get_product();
        // Получаем и суммируес вес товара
        $prod_type = wc_get_product($item_id);
        $product = $item->get_product();

        
        $ves = (float)$product->get_weight() * 1000;
        // Получаем модель и бренд товара
        $mass_name_prod = explode(" ", $item_data['name']);
        $brend = $mass_name_prod[0];
        $counter = 0;
        $model_prod = '';



        foreach($mass_name_prod as $item){
            if($counter != 0){
                $model_prod .= $item.' ';
            }
            $counter++;
        }

        $mass_data['box'][] = array(
            'size' => array(
                'x' => (float)$product->get_length(),
                'y' => (float)$product->get_width(),
                'z' => (float)$product->get_height(),
            ),
            'weightBruto' => $ves,
            'items' =>  [
                [
                    'sku' => $product->get_sku(),
                    'currency' => 'RUB',
                    'name' => $model_prod,
                    'webLink' => get_permalink($item_data['product_id']),
                    'descrEn' =>  $item_data['name'],
                    'brand' => $brend,
                    'price' => $product->get_price(),
                    'quantity' => $item_data['quantity'],
                ]
            ]
        );



        $mass_req['parcels'][] = $mass_data;
       

    }


    if($check_field){
        wp_send_json_success(array('success' => false,'res' =>  ''));
        wp_mail(get_option('admin_email'), 'Сообщение Boxberry', 'Не удалось передать заказ в систему Boxberry из-за нехватки параметров. Номер заказа № '.$id_order);
    }
    //wp_send_json_success(array('res' =>  $mass_req));
    //wp_send_json_success(array('res' =>  serialize($mass_req)));
    // Формирование и отправка запроса
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://go.bxb.delivery/json.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mass_req));
    $data = json_decode(curl_exec($ch), 1);

    if (!empty($data['errors']['isError'])) {

        wp_mail(get_option('admin_email'), 'Сообщение Boxberry', 'Во время передачи заказа в систему Boxberry произошла ошибка. Номер заказа № '.$id_order);
        add_post_meta($id_order, 'responce_boxberry', $data['errors']['errorCode'].': '.$data['errors']['errorMessage']);

        wp_send_json_success(array('success' => false, 'res' =>  $data['errors']['errorCode'].': '.$data['errors']['errorMessage']));
    } else {

        add_post_meta($id_order, 'responce_boxberry', 'Заказ успешно передан.');

        wp_send_json_success(array('success' => true,'res' =>  $data));
    }



}

// Информация по передаче в Boxberry
add_action( 'add_meta_boxes', 'order_add_responce_boxberry' );
 
function order_add_responce_boxberry() {
 
    add_meta_box(
        'responce_boxverry', // ID нашего метабокса
        'Информация Boxberry', // заголовок
        'responce_boxberry_metabox_callback', // функция, которая будет выводить поля в мета боксе
        'shop_order', // типы постов, для которых его подключим
        'normal', // расположение (normal, side, advanced)
        'default' // приоритет (default, low, high, core)
    );
 
}
 
function responce_boxberry_metabox_callback( $post ) {

    echo '<h4>Справочная информация</h4>';

    $mass_error_field = get_post_meta($post->ID, 'responce_boxberry_field', false);
    if(count($mass_error_field) == 0){
        echo '<p>Все хорошо!</p>';
    }
    else{
        foreach($mass_error_field as $item){
            echo '<p>Сообщение: '.$item.'</p>';
        }
    }

    echo '<h4>Ответ запроса Boxberry</h4>';

    echo get_post_meta($post->ID, 'responce_boxberry', true);
   
 
}

// Меняем надпись поля "Регион"
add_filter( 'woocommerce_default_address_fields' , 'wpse_120741_wc_def_state_label' );
function wpse_120741_wc_def_state_label( $address_fields ) {
     $address_fields['state']['label'] = 'Область / край';
     return $address_fields;
}



// Подготовка параметров для отправки на отдельной странице оплаты заказа

add_action( 'wp_ajax_eximbay_payment_info_single', 'eximbay_payment_info_single' );
add_action( 'wp_ajax_nopriv_eximbay_payment_info_single', 'eximbay_payment_info_single' );

function eximbay_payment_info_single($order_id){

    $current_user = wp_get_current_user();

    $order_id = $_POST['order_id'];

    $order_ent = wc_get_order($order_id);
    $data_order = $order_ent->get_data();
    $data = array(
        'ver' => 230, // константа
        'txntype' => 'PAYMENT', // константа
        'charset' => 'UTF-8', // константа
        'statusurl' => 'https://aliveaquarium.ru/status-platezha/', // константа
        'returnurl' => 'https://aliveaquarium.ru/rezultat-platezha/', // константа
        'shipTo_country' => $data_order['billing']['country'],
        'shipTo_city' => $data_order['billing']['city'],
        'shipTo_state' => $data_order['billing']['state'],
        'shipTo_street1' => $data_order['billing']['address_1'],
        'shipTo_postalCode' => $data_order['billing']['postcode'],
        'shipTo_phoneNumber' => $data_order['billing']['phone'],
        'shipTo_firstName' => $data_order['billing']['first_name'],
        'shipTo_lastName' => $data_order['billing']['last_name'],
        'mid' => $_POST['mid'],
        'ref' => 'demo20170418202020',
        'ostype' => 'P',
        'displaytype' => 'P',
        'cur' => 'RUB',
        'amt' => $data_order['total'],
        'shop' => 'aliveaquarium',
        'buyer' => $data_order['billing']['first_name'] . ' ' . $data_order['billing']['last_name'],
        'email' => $data_order['billing']['email'],
        'tel' => $data_order['billing']['phone'],
        'lang' => 'RU',
        'paymethod' => $_POST['method_pay'],
        'autoclose' => 'Y',
        'param1' => $order_id,
        'param2' => $current_user->user_login
    );

    $counter = 0;
    $order_items = $order_ent->get_items();
    foreach( $order_items as $item_id => $item ){
        $data['item_'.$counter.'_product'] = $item->get_name();
        $item_data = $item->get_data();
        $data['item_'.$counter.'_quantity'] = $item_data['quantity'];
        $data['item_'.$counter.'_unitPric'] = $item_data['subtotal'];
        $counter++;
    }

    $secretKey = $_POST['secretkey'];
    $reqURL = "https://secureapi.eximbay.com/Gateway/BasicProcessor.krp";
    $fgkey = "";
    $sortingParams = "";

    //echo '<pre>';
        //var_dump($data);
    //echo '</pre>';

    foreach($data as $Key=>$value) {
        $hashMap[$Key]  = $value;
    }

    $size = count($hashMap);
    ksort($hashMap);

    $counter = 0;

    foreach ($hashMap as $key => $val) {
        if ($counter == $size-1){
            $sortingParams .= $key."=" .$val;
        }else{
            $sortingParams .= $key."=" .$val."&";
        }
        ++$counter;
    }

    $linkBuf = $secretKey. "?".$sortingParams;

    $fgkey = hash("sha256", $linkBuf);

     WC()->cart->empty_cart();
    wp_send_json_success(array('params' => $hashMap, 'fgkey' => $fgkey));

    
}


// Замута с авторизацие. Через длинную цепочку функций удаляем возможность регистрировать аккаунт и оставляем только привязку акканта.

remove_action( 'init', 'wsl_process_login' );

add_action( 'init', 'wsl_process_login_anton' );
function wsl_process_login_anton(){
    $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : null;

    if( ! in_array( $action, array( "wordpress_social_authenticate", "wordpress_social_profile_completion", "wordpress_social_account_linking", "wordpress_social_authenticated" ) ) )
    {
        return false;
    }

    require_once WORDPRESS_SOCIAL_LOGIN_ABS_PATH . 'hybridauth/library/src/autoload.php';

    // authentication mode
    $auth_mode = wsl_process_login_get_auth_mode();

    // start loggin the auth process, if debug mode is enabled
    wsl_watchdog_init();

    // halt, if mode login and user already logged in
    if( 'login' == $auth_mode && is_user_logged_in() )
    {
        $current_user = wp_get_current_user();

        return wsl_process_login_render_notice_page( sprintf( _wsl__( "You are already logged in as %s. Do you want to <a href='%s'>log out</a>?", 'wordpress-social-login' ), $current_user->display_name, wp_logout_url( home_url() ) ) );
    }

    // halt, if mode link and user not logged in
    if( 'link' == $auth_mode && ! is_user_logged_in() )
    {
        return wsl_process_login_render_notice_page( sprintf( _wsl__( "You have to be logged in to be able to link your existing account. Do you want to <a href='%s'>login</a>?", 'wordpress-social-login' ), wp_login_url( home_url() ) ) );
    }

    // halt, if mode test and not admin
    if( 'test' == $auth_mode && ! current_user_can('manage_options') )
    {
        return wsl_process_login_render_notice_page( _wsl__( 'You do not have sufficient permissions to access this page.', 'wordpress-social-login' ) );
    }

    // Bouncer :: Allow authentication?
    if( get_option( 'wsl_settings_bouncer_authentication_enabled' ) == 2 )
    {
        return wsl_process_login_render_notice_page( _wsl__( "Authentication through social networks is currently disabled.", 'wordpress-social-login' ) );
    }

    add_action( 'wsl_clear_user_php_session', 'wsl_process_login_clear_user_php_session' );

    // HOOKABLE:
    do_action( "wsl_process_login_start" );

    // if action=wordpress_social_authenticate
    // > start the first part of authentication (redirect the user to the selected provider)
    if( $action == "wordpress_social_authenticate" )
    {
        return wsl_process_login_begin();
    }

    // if action=wordpress_social_authenticated or action=wordpress_social_profile_completion
    // > finish the authentication process (create new user if doesn't exist in database, then log him in within wordpress)
    wsl_process_login_end_anton();
}


function wsl_process_login_end_anton()
{
    // HOOKABLE:
    do_action( "wsl_process_login_end_start" );

    // HOOKABLE: set a custom Redirect URL
    $redirect_to = wsl_process_login_get_redirect_to();

    // HOOKABLE: selected provider name
    $provider = wsl_process_login_get_selected_provider();

    // authentication mode
    $auth_mode = wsl_process_login_get_auth_mode();

    $is_new_user             = false; // is it a new or returning user
    $user_id                 = ''   ; // wp user id
    $adapter                 = ''   ; // hybriauth adapter for the selected provider
    $hybridauth_user_profile = ''   ; // hybriauth user profile
    $requested_user_login    = ''   ; // username typed by users in Profile Completion
    $requested_user_email    = ''   ; // email typed by users in Profile Completion

    // provider is enabled?
    if( ! get_option( 'wsl_settings_' . $provider . '_enabled' ) )
    {
        return wsl_process_login_render_notice_page( _wsl__( "Unknown or disabled provider.", 'wordpress-social-login' ) );
    }

    if( 'test' == $auth_mode )
    {
        $redirect_to = admin_url( 'options-general.php?page=wordpress-social-login&wslp=auth-paly&provider=' . $provider );

        return wp_safe_redirect( $redirect_to );
    }

    if( 'link' == $auth_mode )
    {
        // a social account cant be associated with more than one wordpress account.

        $hybridauth_user_profile = wsl_process_login_request_user_social_profile( $provider );

        $adapter = wsl_process_login_get_provider_adapter( $provider );

        $user_id = (int) wsl_get_stored_hybridauth_user_id_by_provider_and_provider_uid( $provider, $hybridauth_user_profile->identifier );

        if( $user_id && $user_id != get_current_user_id() )
        {
            return wsl_process_login_render_notice_page( sprintf( _wsl__( "Your <b>%s ID</b> is already linked to another account on this website.", 'wordpress-social-login'), $provider ) );
        }

        $user_id = get_current_user_id();

        // doesn't hurt to double check
        if( ! $user_id )
        {
            return wsl_process_login_render_notice_page( _wsl__( "Sorry, we couldn't link your account.", 'wordpress-social-login' ) );
        }
    }
    elseif( 'login' != $auth_mode )
    {
        return wsl_process_login_render_notice_page( _wsl__( 'Bouncer says no.', 'wordpress-social-login' ) );
    }

    if( 'login' == $auth_mode )
    {
        // returns user data after he authenticate via hybridauth
        list
        (
            $user_id                ,
            $adapter                ,
            $hybridauth_user_profile,
            $requested_user_login   ,
            $requested_user_email   ,
            $wordpress_user_id
        )
        = wsl_process_login_get_user_data_anton( $provider, $redirect_to );

        // if no associated user were found in wslusersprofiles, create new WordPress user
        if( ! $wordpress_user_id )
        {
            $user_id = wsl_process_login_create_wp_user( $provider, $hybridauth_user_profile, $requested_user_login, $requested_user_email );

            $is_new_user = true;
            $redirect_to = apply_filters('wsl_redirect_after_registration', $redirect_to);
        }else{
            $user_id = $wordpress_user_id;
            $is_new_user = false;
        }
    }

    // if user is found in wslusersprofiles but the associated WP user account no longer exist
    // > this should never happen! but just in case: we delete the user wslusersprofiles/wsluserscontacts entries and we reset the process
    $wp_user = get_userdata( $user_id );

    if( ! $wp_user )
    {
        wsl_delete_stored_hybridauth_user_data( $user_id );

        return wsl_process_login_render_notice_page( sprintf( _wsl__( "Sorry, we couldn't connect you. <a href=\"%s\">Please try again</a>.", 'wordpress-social-login' ), site_url( 'wp-login.php', 'login_post' ) ) );
    }

    // store user hybridauth profile (wslusersprofiles), contacts (wsluserscontacts) and buddypress mapping
    wsl_process_login_update_wsl_user_data( $is_new_user, $user_id, $provider, $adapter, $hybridauth_user_profile, $wp_user );

    // finally create a wordpress session for the user
    wsl_process_login_authenticate_wp_user( $user_id, $provider, $redirect_to, $adapter, $hybridauth_user_profile, $wp_user );
}



function wsl_process_login_get_user_data_anton( $provider, $redirect_to )
{
    // HOOKABLE:
    do_action( "wsl_process_login_get_user_data_start", $provider, $redirect_to );

    $user_id                  = null;
    $config                   = null;
    $hybridauth               = null;
    $adapter                  = null;
    $hybridauth_user_profile  = null;
    $requested_user_login     = '';
    $requested_user_email     = '';
    $wordpress_user_id        = 0;

    /* 1. Grab the user profile from social network */

    if( ! ( isset( $_SESSION['wsl::userprofile'] ) && $_SESSION['wsl::userprofile'] && $hybridauth_user_profile = json_decode( $_SESSION['wsl::userprofile'] ) ) )
    {
        $hybridauth_user_profile = wsl_process_login_request_user_social_profile( $provider );

        $_SESSION['wsl::userprofile'] = json_encode( $hybridauth_user_profile );
    }

    $adapter = wsl_process_login_get_provider_adapter( $provider );

    $hybridauth_user_email          = sanitize_email( $hybridauth_user_profile->email );
    $hybridauth_user_email_verified = sanitize_email( $hybridauth_user_profile->emailVerified );

    /* 2. Run Bouncer::Filters if enabled (domains, emails, profiles urls) */

    // Bouncer::Filters by emails domains name
    if( get_option( 'wsl_settings_bouncer_new_users_restrict_domain_enabled' ) == 1 )
    {
        if( empty( $hybridauth_user_email ) )
        {
            return wsl_process_login_render_notice_page( _wsl__( get_option( 'wsl_settings_bouncer_new_users_restrict_domain_text_bounce' ), 'wordpress-social-login') );
        }

        $list = get_option( 'wsl_settings_bouncer_new_users_restrict_domain_list' );
        $list = preg_split( '/$\R?^/m', $list );

        $current = strstr( $hybridauth_user_email, '@' );

        $shall_pass = false;

        foreach( $list as $item )
        {
            if( trim( strtolower( "@$item" ) ) == strtolower( $current ) )
            {
                $shall_pass = true;
            }
        }

        if( ! $shall_pass )
        {
            return wsl_process_login_render_notice_page( _wsl__( get_option( 'wsl_settings_bouncer_new_users_restrict_domain_text_bounce' ), 'wordpress-social-login') );
        }
    }

    // Bouncer::Filters by e-mails addresses
    if( get_option( 'wsl_settings_bouncer_new_users_restrict_email_enabled' ) == 1 )
    {
        error_log(__METHOD__ . ' start wsl_settings_bouncer_new_users_restrict_email_enabled.');
        error_log(__METHOD__ . ' hybridauth_user_email is ' . $hybridauth_user_email );
        if( empty( $hybridauth_user_email ) )
        {
            return wsl_process_login_render_notice_page( _wsl__( get_option( 'wsl_settings_bouncer_new_users_restrict_email_text_bounce' ), 'wordpress-social-login') );
        }

        $list = get_option( 'wsl_settings_bouncer_new_users_restrict_email_list' );
        $list = preg_split( '/$\R?^/m', $list );

        $shall_pass = false;

        foreach( $list as $item )
        {
            if( trim( strtolower( $item ) ) == strtolower( $hybridauth_user_email ) )
            {
                $shall_pass = true;
            }
        }

        if( ! $shall_pass )
        {
            return wsl_process_login_render_notice_page( _wsl__( get_option( 'wsl_settings_bouncer_new_users_restrict_email_text_bounce' ), 'wordpress-social-login') );
        }
    }

    // Bouncer::Filters by profile urls
    if( get_option( 'wsl_settings_bouncer_new_users_restrict_profile_enabled' ) == 1 )
    {
        error_log(__METHOD__ . ' start restrict_profile_enabled.');
        $list = get_option( 'wsl_settings_bouncer_new_users_restrict_profile_list' );
        $list = preg_split( '/$\R?^/m', $list );
        error_log(__METHOD__ . ' $list is ' . print_r($list, true));

        $shall_pass = false;

        foreach( $list as $item )
        {
            error_log(__METHOD__ . ' $item is ' . $item );
            error_log(__METHOD__ . ' $hybridauth_user_profile->profileURL is ' . $hybridauth_user_profile->profileURL);
            if( trim( strtolower( $item ) ) == strtolower( $hybridauth_user_profile->profileURL ) )
            {
                $shall_pass = true;
            }
        }

        if( ! $shall_pass )
        {
            return wsl_process_login_render_notice_page( _wsl__( get_option( 'wsl_settings_bouncer_new_users_restrict_profile_text_bounce' ), 'wordpress-social-login') );
        }
    }

    /* 3. Check if user exist in database by looking for the couple (Provider name, Provider user ID) or verified email */

    // check if user already exist in wslusersprofiles
    $user_id = (int) wsl_get_stored_hybridauth_user_id_by_provider_and_provider_uid( $provider, $hybridauth_user_profile->identifier );

    // if not found in wslusersprofiles, then check his verified email
    if( ! $user_id && ! empty( $hybridauth_user_email_verified ) )
    {
        // check if the verified email exist in wp_users
        $user_id = (int) wsl_wp_email_exists( $hybridauth_user_email_verified );

        // check if the verified email exist in wslusersprofiles
        if( ! $user_id )
        {
            $user_id = (int) wsl_get_stored_hybridauth_user_id_by_email_verified( $hybridauth_user_email_verified );
        }

        // if the user exists in Wordpress
        if( $user_id )
        {
            $wordpress_user_id = $user_id;
        }
    }

    /* 4 Deletegate detection of user id to custom filters hooks */

    // HOOKABLE:
    $user_id = apply_filters( 'wsl_hook_process_login_alter_user_id', $user_id, $provider, $hybridauth_user_profile );

    /* 5. If Bouncer::Profile Completion is enabled and user didn't exist, we require the user to complete the registration (user name & email) */
    if( ! $user_id )
    {
        // Bouncer :: Accept new registrations?
        if( get_option( 'wsl_settings_bouncer_registration_enabled' ) == 2
            && ( get_option( 'wsl_settings_bouncer_authentication_enabled' ) == 2 || get_option( 'wsl_settings_bouncer_accounts_linking_enabled' ) == 2 ) )
        {
            return wsl_process_login_render_notice_page( _wsl__( "Registration is now closed.", 'wordpress-social-login' ) );
        }

        // Bouncer::Accounts linking/mapping
        // > > not implemented yet! Planned for WSL 2.3
        if( get_option( 'wsl_settings_bouncer_accounts_linking_enabled' ) == 1 )
        {
            do
            {
                list
                (
                    $shall_pass,
                    $user_id,
                    $requested_user_login,
                    $requested_user_email
                )
                = wsl_process_login_new_users_gateway_anton( $provider, $redirect_to, $hybridauth_user_profile );
            }
            while( ! $shall_pass );
            $wordpress_user_id = $user_id;
        }

        // Bouncer::Profile Completion
        // > > in WSL 2.3 Profile Completion will be reworked and merged with Accounts linking
        elseif( ( get_option( 'wsl_settings_bouncer_profile_completion_require_email' ) == 1 && empty( $hybridauth_user_email ) )
            || get_option( 'wsl_settings_bouncer_profile_completion_change_username' ) == 1 )
        {
            do
            {
                list
                (
                    $shall_pass,
                    $user_id,
                    $requested_user_login,
                    $requested_user_email
                )
                = wsl_process_login_new_users_gateway_anton( $provider, $redirect_to, $hybridauth_user_profile );
            }
            while( ! $shall_pass );
        }

    }else{
        $wordpress_user_id = $user_id;
    }

    /* 6. returns user data */

    return array(
        $user_id,
        $adapter,
        $hybridauth_user_profile,
        $requested_user_login,
        $requested_user_email,
        $wordpress_user_id
    );
}

// Подключаем файл с самой формой
require_once( dirname(__FILE__) . '/anton-form-link.php');


// Добавление к зареистрированным пользователям роли "Участник"

add_action( 'user_register', 'add_additional_role', 10 );

function add_additional_role($user_id){
    $user_for_role = new WP_User( $user_id );
    $user_for_role->add_role( 'contributor' );
}


// Изменение виджета авторизации через соц сети
remove_filter('rcl_profile_fields', 'add_profile_fields_wsl', 10);
add_filter('rcl_profile_fields', 'add_profile_fields_wsl_anton', 10);

function add_profile_fields_wsl_anton($fields){
        $fields[] = array('type' => 'custom','slug' => 'your_social','title' => __('Связать аккаунт'),'content' => wsl_render_auth_widget_anton(array('mode' => 'link','caption' => '')),'notice' => __('Привяжите один или несколько аккаунтов из соц сетей'));
        return $fields;
    }

require_once( dirname(__FILE__) . '/function-login-widget.php');

// Удаление привязанной социальной сети

add_action( 'wp_ajax_deletelinked', 'deletelinked' );
add_action( 'wp_ajax_nopriv_deletelinked', 'deletelinked' );

function deletelinked(){
    global $wpdb;
    $user_id = $_POST['user_id'];
    $id_provider = $_POST['id_provider'];

    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wslusersprofiles WHERE user_id = {$user_lk}");

    if($wpdb->delete( $wpdb->prefix.'wslusersprofiles', ['user_id' => $user_id, 'provider' => $id_provider])){
        wp_send_json_success(array('res' => true));
    }
    else{
        wp_send_json_success(array('res' => false));
    }
}


// Отключение учета чекбокса реистрации из настроект вордпрес
function dd3_open_rcl_register(){
    $option = 1;
    return $option;
}
add_filter('rcl_users_can_register','dd3_open_rcl_register');

// Добавление настройки размера скидки для первого заказа

remove_action('admin_menu', 'add_global_referal_options');
add_action('admin_menu', 'add_global_referal_options_anton');

function add_global_referal_options_anton(){
    add_menu_page('Referal', 'Referal', 'manage_options', 'functions_referal','global_referal_options_anton');
    add_submenu_page( 'functions_referal', 'Учет рефералов', 'Учет рефералов', 'manage_options', 'stat_functions_referal', 'admin_stat_referal');
    add_submenu_page( 'functions_referal', 'Учет поощрений', 'Учет поощрений', 'manage_options', 'stat_functions_incentive', 'admin_stat_incentive');
}

function global_referal_options_anton(){

    echo '<div class="wrap">';
    echo reg_form_wpp('rfrl'); ?>
        <h2>Настройки Referal Recall</h2>
        <form method="post" action="options.php">
        <?php wp_nonce_field('update-options'); ?>
    <table width="800">
    <tr>
    <td>
            <h3>Страница со статистикой партнерской программы:</h3>
    </td>
    <td><?php $args = array(
        'depth'            => 0,
        'child_of'         => 0,
        'selected'         => get_option('refstat_page'),
        'echo'             => 1,
        'name'             => 'refstat_page',
        'show_option_none' => '',
        'exclude'          => '',
        'exclude_tree'     => ''
        );
    wp_dropdown_pages( $args ); ?>
    </td>
    </tr>
    <tr>
    <td>
            <h3>Поощрение за реферала:</h3>
    </td>
    <td>
    <select name="incentive_referal" size="1">
        <option value="">Не используется</option>
        <option value="1" <?php if(get_option('incentive_referal')==1) echo 'selected="selected"' ?>>Репутация при регистрации</option>
        <option value="2" <?php if(get_option('incentive_referal')==2) echo 'selected="selected"' ?>>Платеж на личный счет при регистрации</option>
        <option value="3" <?php if(get_option('incentive_referal')==3) echo 'selected="selected"' ?>>Процент с покупок реферала</option>
        <option value="4" <?php if(get_option('incentive_referal')==4) echo 'selected="selected"' ?>>Фикс. Платеж при заказе реферала</option>
    </select>
    </td>
    </tr>
    <tr>
    <td>
            <h3>Размер поощрения от реферала первого уровня:</h3>
    </td>
    <td>
    <input type="text" name="size_incentive_referal" value="<?php echo get_option('size_incentive_referal'); ?>">
    </td>
    </tr>
    <tr>
    <td>
            <h3>Размер поощрения от реферала второго уровня:</h3>
    </td>
    <td>
    <input type="text" name="size_incentive_secondary_referal" value="<?php echo get_option('size_incentive_secondary_referal'); ?>">
    </td>
    </tr>
    <tr>
    <td>
            <h3>Размер скидки для первого заказа:</h3>
    </td>
    <td>
    <input type="number" name="size_sale_first_order" value="<?php echo get_option('size_sale_first_order'); ?>">
    </td>
    </tr>
    </table>
    <p><input type="submit" name="Submit" value="Сохранить" /></p>
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="size_incentive_secondary_referal,refstat_page,incentive_referal,size_incentive_referal,size_sale_first_order" />
    </form>
    </div>
<?php
}


// Применяем скидку в корзине на первый заказ
function woo_discount_total(WC_Cart $cart) {
    $current_user = wp_get_current_user();
    if($current_user->exists()){
        if(get_user_meta($current_user->ID, 'first_order', true) == 'yes'){
            if(is_admin() && !defined('DOING_AJAX')) {
            
                return;
                
            }

            $discount = $cart->subtotal * get_option('size_sale_first_order')/100; // 0.05 - это 5%
            
            $cart->add_fee('Скидка '.get_option('size_sale_first_order').'% на первый заказ', -$discount);
        }
    }

}

add_action('woocommerce_cart_calculate_fees' , 'woo_discount_total');

// Добавление поля для проверки первого заказа
remove_action( 'init', 'rr_init_new_referal_incentive', 50 );
add_action( 'init', 'rr_init_new_referal_incentive_anton', 50 );
function rr_init_new_referal_incentive_anton() {

    if ( rcl_get_option( 'confirm_register_recall' ) )
        add_action( 'rcl_confirm_registration', 'save_new_ref_link_anton', 10 );
    else
        add_action( 'user_register', 'save_new_ref_link_anton', 10 );
}

function save_new_ref_link_anton( $user_id ) {
    global $wpdb;

    $author  = $_COOKIE['ref'];
    $return  = $_COOKIE['return'];

    if ( $author ) {
        update_user_meta($user_id, 'first_order', 'yes');
        $time_action = current_time( 'mysql' );

        insert_new_referall( $author, $user_id, $return );

        $size        = get_option( 'size_incentive_referal' );
        $incentive   = get_option( 'incentive_referal' );

        if ( $incentive == 2 || $incentive == 1 ) {

            if ( $incentive == 2 ) {

                update_count_partner( $author, $size );
            }

            $idrow = add_new_insentive( $author, $user_id, $size, $incentive );

            if ( $incentive == 1 && function_exists( 'rcl_update_user_rating' ) ) {
                $args = array(
                    'user_id'        => $user_id,
                    'object_id'      => $idrow,
                    'object_author'  => $author,
                    'rating_value'   => $size,
                    'rating_type'    => 'partner-system',
                    'user_overall'   => true
                );
                rcl_insert_rating( $args );
            }
            $size_incentive_secondary_referal = get_option( 'size_incentive_secondary_referal' );
            if ( $size_incentive_secondary_referal ) {
                $primary_author = get_partner_ref( $author );
                if ( $primary_author ) {
                    if ( $incentive == 2 ) {
                        update_count_partner( $primary_author, $size_incentive_secondary_referal );
                    }
                    $idrow = add_new_insentive( $primary_author, $author, $size_incentive_secondary_referal, $incentive );
                    if ( $incentive == 1 && function_exists( 'rcl_insert_rating' ) ) {
                        $args = array(
                            'user_id'        => $author,
                            'object_id'      => $idrow,
                            'object_author'  => $primary_author,
                            'rating_value'   => $size_incentive_secondary_referal,
                            'rating_type'    => 'partner-system',
                            'user_overall'   => true
                        );
                        rcl_insert_rating( $args );
                    }
                }
            }
        }
    }
}


