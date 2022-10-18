<?php
// Добавление кастомного виджета выбора города


add_action('vc_before_init', 'add_widget_selected_city' );

function add_widget_selected_city(){
  vc_map( 
    array(
        "name" => __("Элемент выбора города", "widget_soc"),    
        "base" => "selected_city",      
        "description" => "Элемент выбора города",      
        "category" => __("Пользовательские элементы", "selected-city"),    
        "params" => array(        	
            array(
                "type" => "text",
                "holder" => "div",
                "heading" => esc_html__("Иконка выбора города", 'my_first_vidget'),
                "param_name" => "icon_selected_city",
                "value" => "",
                "description" => esc_html__("Введите тэг иконки с сайта FontAwesome", 'my_first_vidget')
            ),
        )
      
    )
  );
  
}

add_shortcode('selected_city', 'selected_city_content');

function selected_city_content($atts){
  
	if(isset($atts['icon_selected_city']))$icon_selected_city=esc_html(trim($atts['icon_selected_city'])); else $icon_selected_city='<i class="fas fa-map-marker-alt"></i>';
	

	//$content = wpb_js_remove_wpautop($content, true);

    if(!empty($_COOKIE['city'])){
        $city_name = $_COOKIE['city'];
    }
    else{
        $city_name = 'Уфа';
    }
    // Получение настроек дизайна
    $background_success = '';
    $font_size_success = '';
    $color_success = '';
    if(get_field('color-background-success-city', 'option')){$background_success = 'background : '.get_field('color-background-success-city', 'option').';';}
    if(get_field('font-size-success-city-text', 'option')){$font_size_success =  'font-size : '.get_field('font-size-success-city-text', 'option').'px;';}
    if(get_field('color-size-success-city-text', 'option')){$color_success = 'color:'.get_field('color-size-success-city-text', 'option').';';}


	$output = '
		<div class="w-text ush_text_1 geosite has_text_color callback-selected-city">
			<span class="w-text-h">
				'.$icon_selected_city.'
				<span class="w-text-value text-selected-city">
                   <img src="https://dots-map.com/wp-content/uploads/2022/09/loading_spinner.gif" class="load-selected-city">
                </span>
			</span>
        <div class="welcome-message-selected-city" style="'.$background_success.'">
            <p class="welcome-message-selected-city-text" style="'.$font_size_success.'">Выш город <span class="value-selected-name-city">'.$city_name.'</span>?</p>
            <div class="flex flex-welcome-selected-city">
                <div class="item-welcome-city">
                    <p class="welcome-city-variation welcome-city-yes" style="'.$font_size_success.''.$color_success.'">Да</p>
                </div>
                <div class="item-welcome-city">
                    <p class="welcome-city-variation welcome-city-no" style="'.$font_size_success.''.$color_success.'">Нет</p>
                </div>
            </div>
        </div>
		</div>
        
	';
   
    //$output .= $content;
   

    return $output;
}

// Добавляем модальное окно в подвал
add_action('wp_footer', 'popup_selected_city_html');

function popup_selected_city_html(){

    $citys = get_field('citys', 'option');

    $citys_html = '';

    // Получение настроек дизайна
    $background_popup = '';
    $background_popup_back = '';
    $color_head_popup = '';
    $font_size_head_popup = '';
    $color_text_popup = '';
    $font_size_text_popup = '';
    $padding_popup = '';

    if(get_field('color-background-popup', 'option')){$background_popup = 'background : '.get_field('color-background-popup', 'option').';';}
    if(get_field('color-background-popup-back', 'option')){$background_popup_back =  'background : '.get_field('color-background-popup-back', 'option').';';}
    if(get_field('color-head-popup', 'option')){$color_head_popup = 'color:'.get_field('color-head-popup', 'option').';';}
    if(get_field('font-size-popup-head', 'option')){$font_size_head_popup = 'font-size:'.get_field('font-size-popup-head', 'option').'px;';}
    if(get_field('color-text-popup', 'option')){$color_text_popup = 'color:'.get_field('color-text-popup', 'option').';';}
    if(get_field('font-size-popup-text', 'option')){$font_size_text_popup = 'font-size:'.get_field('font-size-popup-text', 'option').'px;';}
    if(get_field('padding-popup-window', 'option')){
        $padding_popup = get_field('padding-popup-window', 'option');
        $padding_left = $padding_popup['left'];
        $padding_top = $padding_popup['top'];
        $padding_right = $padding_popup['right'];
        $padding_bottom = $padding_popup['bottom'];
        $padding_popup = 'padding:'.$padding_left.'px '. $padding_top.'px '.$padding_right.'px '.$padding_bottom.'px;';
    }
    
    foreach($citys as $city){
        $citys_html .= 
        '<div class="item-city">
            <p class="name-city" style="'.$color_text_popup.$font_size_text_popup.'">'.$city['city-name'].'</p>
            <input type="hidden" class="valu-name-city" value="'.$city['city-name'].'">
        </div>';
    }

    echo '
            <div class="popup-background" style="'.$background_popup_back.'"></div>
            <div class="cont-popup-window" style="'.$background_popup.$padding_popup.'">
                
                <div class="popup-modal">
                    <p class="text-delect-city" style="'.$color_head_popup.$font_size_head_popup.'">Выберите город</p>
                    <div class="flex">
                        '.$citys_html.'
                    </div>
                </div>
            </div>';

}



// Исключаем записи не для выбранного города
/*
add_action( 'pre_get_posts', 'pre_get_posts_for_front' );
function pre_get_posts_for_front( $query ){

    if( !is_admin()  && get_queried_object()  ){

         echo '<pre>';
        var_dump($query);
        echo '</pre>';

        
        if(isset($_COOKIE['city'])){
            $selected_city = $_COOKIE['city'];
            $meta_query = array(
              'relation' => 'OR',
              array(
                'key'     => 'city',
                'value'   => $selected_city,
              ),
              array(
                'key'     => 'none-city',
                'value'   => 'yes',
              ),
            );
            $query->set('meta_query', $meta_query);
        }
        

        $query->set('posts_per_page', '2');
    }

}
*/

// Удаляем старые шорткоды, которые написаны другим разработчиком, но изменяем под себя
add_action( 'init', 'overide_custom_funct_nc', 9000 ); // Переопределение всего нужного кода предыдущего разработчика


function overide_custom_funct_nc(){
    // Переопределение шорткода вывода
    remove_shortcode('nc_the_default_filter_afishafilter', 'nc_the_default_filter_afishafilter');
    add_shortcode('nc_the_default_filter_afishafilter', 'nc_the_default_filter_afishafilter_custom');

    // переопределение обработчика фильтра
    remove_action( 'wp_ajax_afishamestafilter', 'true_filter_function_afishamestafilter' );
    remove_action( 'wp_ajax_nopriv_afishamestafilter', 'true_filter_function_afishamestafilter' );
    add_action( 'wp_ajax_afishamestafilter', 'true_filter_function_afishamestafilter_custom' );
    add_action( 'wp_ajax_nopriv_afishamestafilter', 'true_filter_function_afishamestafilter_custom' );

    // переопределния филтра, который я не понял где, может не используется, но корректировки внести надо
    remove_action( 'wp_ajax_prikfilter', 'prikfilter_filter_function' );
    remove_action( 'wp_ajax_nopriv_prikfilter', 'prikfilter_filter_function' );
    add_action( 'wp_ajax_prikfilter', 'prikfilter_filter_function_custom' );
    add_action( 'wp_ajax_nopriv_prikfilter', 'prikfilter_filter_function_cunstom' );

    // переопределение фильтра событий
    remove_action('wp_ajax_afishafilter', 'afishafilter_filter_function');
    remove_action('wp_ajax_nopriv_afishafilter', 'afishafilter_filter_function');
    add_action('wp_ajax_afishafilter', 'afishafilter_filter_function_custom');
    add_action('wp_ajax_nopriv_afishafilter', 'afishafilter_filter_function_custom');

    // Переопределяем шорт код вывода карты
    remove_shortcode('nc_the_map', 'nc_the_map');
    add_shortcode('nc_the_map', 'nc_the_map_custom');

    // Переопределение фильтра локаций
    //remove_action('wp_ajax_locfilter', 'true_filter_function');
    //remove_action('wp_ajax_nopriv_locfilter', 'true_filter_function');
    //add_action('wp_ajax_locfilter', 'true_filter_function_custom');
    //add_action('wp_ajax_nopriv_locfilter', 'true_filter_function_custom');

    // переопределение вывода постов в категории
    remove_shortcode('views_all_post_cat', 'views_all_post_cat');
    add_shortcode('views_all_post_cat', 'views_all_post_cat_custom');


}

// Переработанный шорткод вывода записей на странцие места/афиша


function nc_the_default_filter_afishafilter_custom(){

    if( !is_admin() ){

        $post_type_s = true;
        $post_type_l = true;

        if(!empty($_COOKIE['city'])){// Получаем из куки значение города, иначе равно УфА
            $selected_city = $_COOKIE['city'];
        }
        else{
            $selected_city = 'Уфа';
        }

        $ids_l = array();
        if ($post_type_l) {
            $args_l = array(
                'post_type' => 'lokatsii',
                'order'     => 'ASC',
                'fields'    => 'ids',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'category',
                        'field' => 'id',
                        'operator' => 'NOT IN',
                        'terms' => array(10,11),
                    )
                )
            );

            $args_l['meta_query']['query_selected_city'] = array(  // Добавляем запрос на проверку города
                'relation' => 'OR',
                array(
                    'key' => 'city',
                    'value' => $selected_city,
                ),
                    array(
                    'key' => 'none-city',
                    'value' => 'yes',
                ),
            );

            $q_l = new WP_Query( $args_l );
            $ids_l = $q_l->posts;
            wp_reset_query();
        }

        $ids_s = array();
        if ($post_type_s) {
            $args_s = array(
                'post_type' => 'sobitiya',
                'fields'    => 'ids',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'category',
                        'field' => 'id',
                        'operator' => 'NOT IN',
                        'terms' => array(10,11),
                    )
                ),
                'orderby' => 'data_filter_s',
                'order'    => 'ASC',


            );

            $args_s['meta_query']['query_selected_city'] = array(  // Добавляем запрос на проверку города
                'relation' => 'OR',
                array(
                    'key' => 'city',
                    'value' => $selected_city,
                ),
                    array(
                    'key' => 'none-city',
                    'value' => 'yes',
                ),
            );

            $q_s = new WP_Query( $args_s );
            $ids_s = $q_s->posts;
            wp_reset_query();
        }

        $ids = array_merge($ids_s, $ids_l);

        $implode = implode(",", $ids);

        echo do_shortcode('[us_grid post_type="ids" orderby="post__in" ids="' . $implode . '" items_quantity="12" no_items_message="Скоро тут появится что-то интересное!" pagination="regular" pagination_style="1" items_layout="471" columns="3" items_gap="15px"]');
    }

}

// Переопредление работы фильтра

function true_filter_function_afishamestafilter_custom(){

    if(!empty($_COOKIE['city'])){// Получаем из куки значение города, иначе равно УфА
        $selected_city = $_COOKIE['city'];
    }
    else{
        $selected_city = 'Уфа';
    }

    if( isset( $_POST[ 'stoimost' ] ) ) {
        switch ($_POST[ 'stoimost' ]) {
            case '0':
                $stoimost = array( 0, 300 );
                break;
            case '1':
                $stoimost = array( 300, 700 );
                break;
            case '2':
                $stoimost = array( 700, 1200 );
                break;
            case '3':
                $stoimost = array( 1200, 1000000 );
                break;
        }
    }

    $post_type_s = false;
    $post_type_l = false;

    if( isset( $_POST[ 'posttype' ] ) ) {
        if (in_array("sobitiya", $_POST[ 'posttype' ])){
            $post_type_s = true;
        }
        if (in_array("lokatsii", $_POST[ 'posttype' ])){
            $post_type_l = true;
        }
    } else {
        $post_type_s = true;
        $post_type_l = true;
    }

    $ids_l = array();
    if ($post_type_l) {
        $args_l = array(
            'post_type' => 'lokatsii',
            'order'     => 'ASC',
            'fields'    => 'ids',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'category',
                    'field' => 'id',
                    'operator' => 'NOT IN',
                    'terms' => array(10,11),
                )
            )
        );

        if( isset( $_POST[ 'term' ] ) ) {
            $args_l[ 'tax_query' ] = array(
                array(
                    'taxonomy' => 'category',
                    'field' => 'id',
                    'terms' => $_POST[ 'term' ]
                )
            );
        }

        if(
            isset( $_POST[ 'datafilters' ] )
            || isset( $_POST[ 'checkdots' ] )
            || isset( $_POST[ 'freerebonok' ] )
            || isset( $_POST[ 'stoimost' ] )
            || isset( $_POST[ 'vremia' ] )
            || isset( $_POST[ 'vozrast' ] )
            || isset( $_POST[ 'besplatno' ] )
            && 'on' == $_POST[ 'besplatno' ] )
        {
            $args_l[ 'meta_query' ] = array( 'relation' => 'AND' );
        }

        if( isset( $_POST[ 'besplatno' ] ) ){
            $args_l[ 'meta_query' ][] = array(
                'key' => 'besplatno_filter',
                'compare' => '==',
                'value' => '1'
            );
        }

        if( isset( $_POST[ 'vozrast' ] ) ){
            $args_l[ 'meta_query' ][] = array(
                'key' => 'vozrast_filter',
                'value' => $_POST[ 'vozrast' ],
                'compare' => 'LIKE'
            );
        }

        if( isset( $_POST[ 'vremia' ] ) ) {
            $args_l[ 'meta_query' ][] = array(
                'key' => 'vremya_filter',
                'value' => $_POST[ 'vremia' ],
                'compare' => 'LIKE'
            );
        }

        if( isset( $_POST[ 'stoimost' ] ) ) {
            $args_l[ 'meta_query' ][] = array(
                'key' => 'stoimost_filter',
                'value' => $stoimost,
                'type' => 'numeric',
                'compare' => 'BETWEEN'
            );
        }

        if( isset( $_POST[ 'freerebonok' ] ) ) {
            $args_l[ 'meta_query' ][] = array(
                'key' => 'mozhno_ostavit_rebenka_filter',
                'compare' => '==',
                'value' => '1'
            );
        }

        if( isset( $_POST[ 'checkdots' ] ) ) {
            $args_l[ 'meta_query' ][] = array(
                'key' => 'vybor_dots_filter',
                'compare' => '==',
                'value' => '1'
            );
        }

        $args_l['meta_query'][]['query_selected_city'] = array(  // Добавляем запрос на проверку города
            'relation' => 'OR',
            array(
                'key' => 'city',
                'value' => $selected_city,
            ),
                array(
                'key' => 'none-city',
                'value' => 'yes',
            ),
        );

        $q_l = new WP_Query( $args_l );
        $ids_l = $q_l->posts;
        wp_reset_query();
    }

    $ids_s = array();
    if ($post_type_s) {
        $args_s = array(
            'post_type' => 'sobitiya',
            'fields'    => 'ids',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'category',
                    'field' => 'id',
                    'operator' => 'NOT IN',
                    'terms' => array(10,11),
                )
            ),
            'orderby' => 'data_filter_s',
            'order'    => 'ASC',
        );

        if( isset( $_POST[ 'term' ] ) ) {
            $args_s[ 'tax_query' ] = array(
                array(
                    'taxonomy' => 'category',
                    'field' => 'id',
                    'terms' => $_POST[ 'term' ]
                )
            );
        }

        if(
            isset( $_POST[ 'datafilters' ] )
            || isset( $_POST[ 'checkdots' ] )
            || isset( $_POST[ 'freerebonok' ] )
            || isset( $_POST[ 'stoimost' ] )
            || isset( $_POST[ 'vremia' ] )
            || isset( $_POST[ 'vozrast' ] )
            || isset( $_POST[ 'besplatno' ] )
            && 'on' == $_POST[ 'besplatno' ] )
        {
            $args_s[ 'meta_query' ] = array( 'relation' => 'AND' );
        }

        if( !empty($_POST[ 'datafilters' ]) ) {
            $date = new DateTime($_POST[ 'datafilters' ]);
            $datafilters = $date->format('Ymd');
            $args_s[ 'meta_query' ][] = array(
                'key' => 'data_filter_s',
                'compare'   => '==',
                'value'     => $datafilters,
            );
        }

        if( isset( $_POST[ 'besplatno' ] ) ){
            $args_s[ 'meta_query' ][] = array(
                'key' => 'besplatno_filter_s',
                'compare' => '==',
                'value' => '1'
            );
        }

        if( isset( $_POST[ 'vozrast' ] ) ){
            $args_s[ 'meta_query' ][] = array(
                'key' => 'vozrast_filter_s',
                'value' => $_POST[ 'vozrast' ],
                'compare' => 'LIKE'
            );
        }

        if( isset( $_POST[ 'vremia' ] ) ) {
            $args_s[ 'meta_query' ][] = array(
                'key' => 'vremya_filter_s',
                'value' => $_POST[ 'vremia' ],
                'compare' => 'LIKE'
            );
        }

        if( isset( $_POST[ 'stoimost' ] ) ) {
            $args_s[ 'meta_query' ][] = array(
                'key' => 'stoimost_filter_s',
                'value' => $stoimost,
                'type' => 'numeric',
                'compare' => 'BETWEEN'
            );
        }

        if( isset( $_POST[ 'freerebonok' ] ) ) {
            $args_s[ 'meta_query' ][] = array(
                'key' => 'mozhno_ostavit_rebenka_filter_s',
                'compare' => '==',
                'value' => '1'
            );
        }

        if( isset( $_POST[ 'checkdots' ] ) ) {
            $args_s[ 'meta_query' ][] = array(
                'key' => 'vybor_dots_filter_s',
                'compare' => '==',
                'value' => '1'
            );
        }
        $args_s['meta_query']['query_selected_city'] = array(  // Добавляем запрос на проверку города
            'relation' => 'OR',
            array(
                'key' => 'city',
                'value' => $selected_city,
            ),
                array(
                'key' => 'none-city',
                'value' => 'yes',
            ),
        );

        $q_s = new WP_Query( $args_s );
        $ids_s = $q_s->posts;
        wp_reset_query();
    }

    $ids = array_merge($ids_s, $ids_l);

    $marks = array();
    $post_id_arr = array();
    $marks_counter = 1;

    $count_pag = count($ids);
    $stranica = ceil($count_pag / 12);

    if( isset( $_POST[ 'stranica' ] ) && $_POST[ 'stranica' ] != 0){
        $ind = $_POST[ 'stranica' ] - 1;
        $propusk = array_chunk($ids, 12);
        $output = $propusk[$ind];

        $current = $_POST[ 'stranica' ];
    } else {
        if ($stranica > 1) {
            $propusk = array_chunk($ids, 12);
            $output = $propusk[0];

            $current = 1;
        } else {
            $output = $ids;
        }
    }
    //
    foreach ($output as $post_id) {
        $post_id_arr[] = $post_id;
        if (get_post_type($post_id) == 'lokatsii') {
            $data = (array)json_decode(get_field('na_karte_filter', $post_id, false), true);
        }
        if (get_post_type($post_id) == 'sobitiya') {
            $data = (array)json_decode(get_field('na_karte_filter_s', $post_id, false), true);
        }
        if (!isset($data['marks']) || !is_array($data['marks'])) {
            continue;
        }
        foreach ((array)$data['marks'] as $mark) {
            $mark['id'] = $marks_counter;
            $mark['content'] = "<div class='mark_building'>";
            $mark['content'] .= "<div class='mark_info'>";
            $mark['content'] .= "<div class='info_title'><a href='".get_the_permalink($post_id)."'>".get_the_title($post_id)."</a></div>";
            $mark['content'] .= "</div>";
            $mark['content'] .= "</div>";

            $marks[] = $mark;
            $marks_counter++;
        }
    }
    $map_data['markss'] = $marks;

    $implode_post_id = implode(",", $post_id_arr);

    $map_data['post_id_html'] = do_shortcode('[us_grid post_type="ids" orderby="post__in" ids="' . $implode_post_id . '" items_quantity="12" no_items_message="Скоро тут появится что-то интересное!" items_layout="471" columns="3" items_gap="15px"]');

    if ($stranica > 1) {
        $map_data['pagination'] = paginationAjaxFilter($stranica, $current);
    }

    echo json_encode($map_data);
    die();
}

// Переопределение не понятного фильтра 

function prikfilter_filter_function_custom(){

    if(!empty($_COOKIE['city'])){// Получаем из куки значение города, иначе равно УфА
        $selected_city = $_COOKIE['city'];
    }
    else{
        $selected_city = 'Уфа';
    }

    $args = array(
        'post_type' => 'lokatsii',
        'order' => 'ASC',
        'fields' => 'ids',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'category',
                'field' => 'id',
                'terms' => '11'
            )
        )
    );
    if( isset( $_POST[ 'checkdots' ] ) || isset( $_POST[ 'freerebonok' ] ) || isset( $_POST[ 'stoimost' ] ) || isset( $_POST[ 'vremia' ] ) || isset( $_POST[ 'vozrast' ] ) || isset( $_POST[ 'besplatno' ] ) && 'on' == $_POST[ 'besplatno' ] ) {
        $args[ 'meta_query' ] = array( 'relation' => 'AND' );
    }
    if( isset( $_POST[ 'besplatno' ] ) ) {
        $args[ 'meta_query' ][] = array(
            'key' => 'besplatno_filter',
            'compare' => '==',
            'value' => '1'
        );
    }
    if( isset( $_POST[ 'vozrast' ] ) ) {
        $args[ 'meta_query' ][] = array(
            'key' => 'vozrast_filter',
            'value' => $_POST[ 'vozrast' ],
            'compare' => 'IN'
        );
    }
    if( isset( $_POST[ 'vremia' ] ) ) {
        $args[ 'meta_query' ][] = array(
            'key' => 'vremya_filter',
            'value' => $_POST[ 'vremia' ],
            'compare' => 'IN'
        );
    }
    if( isset( $_POST[ 'stoimost' ] ) ) {
        switch ($_POST[ 'stoimost' ]) {
            case '0':
                $stoimost = array( 0, 300 );
                break;
            case '1':
                $stoimost = array( 300, 700 );
                break;
            case '2':
                $stoimost = array( 700, 1200 );
                break;
            case '3':
                $stoimost = array( 1200, 1000000 );
                break;
        }
        $args[ 'meta_query' ][] = array(
            'key' => 'stoimost_filter',
            'value' => $stoimost,
            'type' => 'numeric',
            'compare' => 'BETWEEN'
        );
    }
    if( isset( $_POST[ 'freerebonok' ] ) ) {
        $args[ 'meta_query' ][] = array(
            'key' => 'mozhno_ostavit_rebenka_filter',
            'compare' => '==',
            'value' => '1'
        );
    }
    if( isset( $_POST[ 'checkdots' ] ) ) {
        $args[ 'meta_query' ][] = array(
            'key' => 'vybor_dots_filter',
            'compare' => '==',
            'value' => '1'
        );
    }

    $args['meta_query']['query_selected_city'] = array(  // Добавляем запрос на проверку города
        'relation' => 'OR',
        array(
            'key' => 'city',
            'value' => $selected_city,
        ),
             array(
            'key' => 'none-city',
            'value' => 'yes',
        ),
    );

    $map_data = [];

    $ids = query_posts( $args );
    $implode = implode(",", $ids);

    $map_data['post_id_html'] =  do_shortcode('[us_grid post_type="ids" ids="' . $implode . '" items_quantity="6" no_items_message="Скоро тут появится что-то интересное!" pagination="regular" items_layout="134"]');
    echo json_encode($map_data);
    die();
}

// переопределение фильтра событий

function afishafilter_filter_function_custom()
{
    if(!empty($_COOKIE['city'])){// Получаем из куки значение города, иначе равно УфА
        $selected_city = $_COOKIE['city'];
    }
    else{
        $selected_city = 'Уфа';
    }

    $args = [
        'post_type' => 'sobitiya',
        'order' => 'ASC',
        'fields' => 'ids',
        'posts_per_page' => -1,
    ];

    if (isset($_POST['term'])) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'category',
                'field' => 'id',
                'terms' => $_POST['term']
            ]
        ];
    }

    if (isset($_POST['datafilters']) || isset($_POST['checkdots']) || isset($_POST['freerebonok']) || isset($_POST['stoimost']) || isset($_POST['vremia']) || isset($_POST['vozrast']) || isset($_POST['besplatno']) && 'on' == $_POST['besplatno']) {
        $args['meta_query'] = ['relation' => 'AND'];
    }

    // if ( ! empty($_POST['datafilters'])) {
    //     $date = new DateTime($_POST['datafilters']);
    //     $datafilters = $date->format('Ymd');
    //     $args['meta_query'][] = [
    //         'key' => 'data_filter_s',
    //         'compare' => '==',
    //         'value' => $datafilters,
    //     ];
    // }
    if ( ! empty($_POST['datafilters'])) {
        $date = new DateTime($_POST['datafilters']);
        $datafilters = $date->format('Ymd');
        $args['meta_query'][] = [
            'key' => 'diapozon_dat_filter_s_$_data_ot_filter_s',
            'value' => $datafilters,
            'compare' => '<=',
            'type' => 'DATE',
        ];
        $args['meta_query'][] = [
            'key' => 'diapozon_dat_filter_s_$_data_do_filter_s',
            'value' => $datafilters,
            'compare' => '>=',
            'type' => 'DATE'
        ];
    }

    if (isset($_POST['besplatno'])) {
        $args['meta_query'][] = [
            'key' => 'besplatno_filter_s',
            'compare' => '==',
            'value' => '1'
        ];
    }

    if (isset($_POST['vozrast'])) {
        $args['meta_query'][] = [
            'key' => 'vozrast_filter_s',
            'value' => $_POST['vozrast'],
            'compare' => 'LIKE'
        ];
    }

    if (isset($_POST['vremia'])) {
        $args['meta_query'][] = [
            'key' => 'vremya_filter_s',
            'value' => $_POST['vremia'],
            'compare' => 'LIKE'
        ];
    }

    if (isset($_POST['stoimost'])) {
        switch ($_POST['stoimost']) {
            case '0':
                $stoimost = [0, 300];

                break;

            case '1':
                $stoimost = [300, 700];

                break;

            case '2':
                $stoimost = [700, 1200];

                break;

            case '3':
                $stoimost = [1200, 1000000];

                break;
        }
        $args['meta_query'][] = [
            'key' => 'stoimost_filter_s',
            'value' => $stoimost,
            'type' => 'numeric',
            'compare' => 'BETWEEN'
        ];
    }

    if (isset($_POST['freerebonok'])) {
        $args['meta_query'][] = [
            'key' => 'mozhno_ostavit_rebenka_filter_s',
            'compare' => '==',
            'value' => '1'
        ];
    }

    if (isset($_POST['checkdots'])) {
        $args['meta_query'][] = [
            'key' => 'vybor_dots_filter_s',
            'compare' => '==',
            'value' => '1'
        ];
    }

    $args['meta_query']['query_selected_city'] = array(  // Добавляем запрос на проверку города
        'relation' => 'OR',
        array(
            'key' => 'city',
            'value' => $selected_city,
        ),
            array(
            'key' => 'none-city',
            'value' => 'yes',
        ),
    );

    $map_data = [];

    $ids = query_posts($args);
    // $implode = implode(',', $ids);
    $implode = '';

    if ( ! empty($_POST['datafilters'])) {
        $date_filter = date('Ymd', strtotime($_POST['datafilters']));

        $ids_f = [];

        foreach ($ids as $id) {
            $rows = get_field('diapozon_dat_filter_s', $id);

            foreach ($rows as $item) {
                $data_ot_filter_s = date('Ymd', strtotime($item['data_ot_filter_s']));
                $data_do_filter_s = date('Ymd', strtotime($item['data_do_filter_s']));

                if (strtotime($date_filter) >= strtotime($data_ot_filter_s) && strtotime($date_filter) <= strtotime($data_do_filter_s)) {
                    if ( ! in_array($id, $ids_f)) {
                        $ids_f[] = $id;
                    }
                }
            }
        }

        $implode = implode(',', $ids_f);
    } else {
        $implode = implode(',', $ids);
    }

    // $map_data['post_id_html'] = do_shortcode('[us_grid post_type="ids" orderby="custom" orderby_custom_field="data_filter_s" order_invert="1" ids="' . $implode . '" items_quantity="12" no_items_message="Скоро тут появится что-то интересное!" pagination="regular" pagination_style="1" items_layout="185" columns="3" items_gap="15px"]');
    $map_data['post_id_html'] = do_shortcode('[us_grid post_type="ids" ids="' . $implode . '" orderby="custom" orderby_custom_field="rejting_filter_s" orderby_custom_type="1" items_quantity="12" no_items_message="Скоро тут появится что-то интересное!" pagination="regular" pagination_style="1" items_layout="185" columns="3" items_gap="15px"]');

    echo json_encode($map_data);

    die();
}

// Копия функции построяния карты, но с доп запросом

function the_multiple_yandex_map_custom($post_ids = [])
{
    $marks = [];
    $marks_counter = 1;

    foreach ($post_ids as $post_id) {
        if ('lokatsii' == get_post_type($post_id)) {
            $data = (array) json_decode(get_field('na_karte_filter', $post_id, false), true);
        }

        if ('sobitiya' == get_post_type($post_id)) {
            $data = (array) json_decode(get_field('na_karte_filter_s', $post_id, false), true);
        }

        if ( ! isset($data['marks']) || ! is_array($data['marks'])) {
            continue;
        }

        foreach ((array) $data['marks'] as $mark) {
            $mark['id'] = $marks_counter;
            $mark['content'] = "<div class='mark_building'>";
            $mark['content'] .= "<div class='mark_info'>";
            $mark['content'] .= "<div class='info_title'><a href='" . get_the_permalink($post_id) . "'>" . get_the_title($post_id) . '</a></div>';
            $mark['content'] .= '</div>';
            $mark['content'] .= '</div>';

            $marks[] = $mark;

            $marks_counter++;
        }
    }

    $map_data = (array) json_decode(get_field('karta_out', get_the_ID(), false), true);
    $map_data['marks'] = $marks;


    // Добавленный код с дополнительным запросом
    if(!empty($_COOKIE['city'])){
        $city_name = $_COOKIE['city'];
        $ch = curl_init('https://geocode-maps.yandex.ru/1.x/?apikey=e1cadf43-4de0-4d06-9ab9-6ce121a9887b&format=json&lang=ru_RU&geocode='. urlencode($city_name));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);
             
        $res = json_decode($res, true);
        if(!empty($res['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'])){
            $coordinates = $res['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
            $coordinates = explode(' ', $coordinates);
            $center_lat = $coordinates[1];
            $center_lng = $coordinates[0];
        }
        else{
            $center_lat = 54.735152;
            $center_lng = 55.958736;
        }
    }
    else{
        $center_lat = 54.735152;
        $center_lng = 55.958736;
    }

    $map_data['center_lat'] = $center_lat;
    $map_data['center_lng'] = $center_lng;
    // Конец кода с дополнительным запросом 

        
    
   

    return the_yandex_map('', false, json_encode($map_data));
}

// Переопределение шорткода вывода карты
function nc_the_map_custom()
{
    if(!empty($_COOKIE['city'])){// Получаем из куки значение города, иначе равно УфА
        $selected_city = $_COOKIE['city'];
    }
    else{
        $selected_city = 'Уфа';
    }


    $args = [
        'post_type' => ['sobitiya', 'lokatsii'],
        'order' => 'ASC',
        'fields' => 'ids',
        'posts_per_page' => -1,
        'tax_query' => [
            [
                'taxonomy' => 'category',
                'field' => 'id',
                'operator' => 'NOT IN',
                'terms' => [10, 11],
            ]
        ]
    ];

    $args['meta_query']['query_selected_city'] = array(  // Добавляем запрос на проверку города
        'relation' => 'OR',
        array(
            'key' => 'city',
            'value' => $selected_city,
        ),
            array(
            'key' => 'none-city',
            'value' => 'yes',
        ),
    );


    $q = new WP_Query($args);
    $ids = [];

    if ($q->have_posts()) {
        while ($q->have_posts()) {
            $q->the_post();
            $ids[] = get_the_ID();
        }
        wp_reset_query();
    }

    return '<div id="nc_the_map">' . the_multiple_yandex_map_custom($ids) . '</div>';
    die();
}

// Переопредление фильтра локаций

function true_filter_function_custom()
{
    if(!empty($_COOKIE['city'])){// Получаем из куки значение города, иначе равно УфА
        $selected_city = $_COOKIE['city'];
    }
    else{
        $selected_city = 'Уфа';
    }

    $args = [
        'order' => 'ASC',
        'fields' => 'ids',
        'posts_per_page' => -1,
        'tax_query' => [
            [
                'taxonomy' => 'category',
                'field' => 'id',
                'operator' => 'NOT IN',
                'terms' => [10, 11],
            ]
        ]
    ];
    $post_type_s = false;
    $post_type_l = false;

    if (isset($_POST['posttype'])) {
        $args['post_type'] = $_POST['posttype'];

        if (in_array('sobitiya', $_POST['posttype'])) {
            $post_type_s = true;
        }

        if (in_array('lokatsii', $_POST['posttype'])) {
            $post_type_l = true;
        }
    } else {
        $args['post_type'] = ['sobitiya', 'lokatsii'];
        $post_type_s = true;
        $post_type_l = true;
    }

    if (isset($_POST['term'])) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'category',
                'field' => 'id',
                'terms' => $_POST['term']
            ]
        ];
    }

    if (
        isset($_POST['datafilters'])
        || isset($_POST['checkdots'])
        || isset($_POST['freerebonok'])
        || isset($_POST['stoimost'])
        || isset($_POST['vremia'])
        || isset($_POST['vozrast'])
        || isset($_POST['besplatno'])
        && 'on' == $_POST['besplatno']) {
        $args['meta_query'] = ['relation' => 'AND'];
    }

    if ( ! empty($_POST['datafilters'])) {
        $date = new DateTime($_POST['datafilters']);
        $datafilters = $date->format('Ymd');
        $args['meta_query'][] = [
            'key' => 'data_filter_s',
            'compare' => '==',
            'value' => $datafilters,
        ];
    }

    if (isset($_POST['besplatno'])) {
        if ($post_type_s && $post_type_l) {
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => 'besplatno_filter_s',
                    'compare' => '==',
                    'value' => '1'
                ],
                [
                    'key' => 'besplatno_filter',
                    'compare' => '==',
                    'value' => '1'
                ]
            ];
        }

        if ($post_type_s && ! $post_type_l) {
            $args['meta_query'][] = [
                'key' => 'besplatno_filter_s',
                'compare' => '==',
                'value' => '1'
            ];
        }

        if ($post_type_l && ! $post_type_s) {
            $args['meta_query'][] = [
                'key' => 'besplatno_filter',
                'compare' => '==',
                'value' => '1'
            ];
        }
    }

    if (isset($_POST['vozrast'])) {
        if ($post_type_s && $post_type_l) {
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => 'vozrast_filter_s',
                    'value' => $_POST['vozrast'],
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'vozrast_filter',
                    'value' => $_POST['vozrast'],
                    'compare' => 'LIKE'
                ]
            ];
        }

        if ($post_type_s && ! $post_type_l) {
            $args['meta_query'][] = [
                'key' => 'vozrast_filter_s',
                'value' => $_POST['vozrast'],
                'compare' => 'LIKE'
            ];
        }

        if ($post_type_l && ! $post_type_s) {
            $args['meta_query'][] = [
                'key' => 'vozrast_filter',
                'value' => $_POST['vozrast'],
                'compare' => 'LIKE'
            ];
        }
    }

    if (isset($_POST['vremia'])) {
        if ($post_type_s && $post_type_l) {
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => 'vremya_filter_s',
                    'value' => $_POST['vremia'],
                    'compare' => 'LIKE'
                ],
                [
                    'key' => 'vremya_filter',
                    'value' => $_POST['vremia'],
                    'compare' => 'LIKE'
                ]
            ];
        }

        if ($post_type_s && ! $post_type_l) {
            $args['meta_query'][] = [
                'key' => 'vremya_filter_s',
                'value' => $_POST['vremia'],
                'compare' => 'LIKE'
            ];
        }

        if ($post_type_l && ! $post_type_s) {
            $args['meta_query'][] = [
                'key' => 'vremya_filter',
                'value' => $_POST['vremia'],
                'compare' => 'LIKE'
            ];
        }
    }

    if (isset($_POST['stoimost'])) {
        switch ($_POST['stoimost']) {
            case '0':
                $stoimost = [0, 300];

                break;

            case '1':
                $stoimost = [300, 700];

                break;

            case '2':
                $stoimost = [700, 1200];

                break;

            case '3':
                $stoimost = [1200, 1000000];

                break;
        }

        if ($post_type_s && $post_type_l) {
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => 'stoimost_filter_s',
                    'value' => $stoimost,
                    'type' => 'numeric',
                    'compare' => 'BETWEEN'
                ],
                [
                    'key' => 'stoimost_filter',
                    'value' => $stoimost,
                    'type' => 'numeric',
                    'compare' => 'BETWEEN'
                ]
            ];
        }

        if ($post_type_s && ! $post_type_l) {
            $args['meta_query'][] = [
                'key' => 'stoimost_filter_s',
                'value' => $stoimost,
                'type' => 'numeric',
                'compare' => 'BETWEEN'
            ];
        }

        if ($post_type_l && ! $post_type_s) {
            $args['meta_query'][] = [
                'key' => 'stoimost_filter',
                'value' => $stoimost,
                'type' => 'numeric',
                'compare' => 'BETWEEN'
            ];
        }
    }

    if (isset($_POST['freerebonok'])) {
        if ($post_type_s && $post_type_l) {
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => 'mozhno_ostavit_rebenka_filter_s',
                    'compare' => '==',
                    'value' => '1'
                ],
                [
                    'key' => 'mozhno_ostavit_rebenka_filter',
                    'compare' => '==',
                    'value' => '1'
                ]
            ];
        }

        if ($post_type_s && ! $post_type_l) {
            $args['meta_query'][] = [
                'key' => 'mozhno_ostavit_rebenka_filter_s',
                'compare' => '==',
                'value' => '1'
            ];
        }

        if ($post_type_l && ! $post_type_s) {
            $args['meta_query'][] = [
                'key' => 'mozhno_ostavit_rebenka_filter',
                'compare' => '==',
                'value' => '1'
            ];
        }
    }

    if (isset($_POST['checkdots'])) {
        if ($post_type_s && $post_type_l) {
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => 'vybor_dots_filter_s',
                    'compare' => '==',
                    'value' => '1'
                ],
                [
                    'key' => 'vybor_dots_filter',
                    'compare' => '==',
                    'value' => '1'
                ]
            ];
        }

        if ($post_type_s && ! $post_type_l) {
            $args['meta_query'][] = [
                'key' => 'vybor_dots_filter_s',
                'compare' => '==',
                'value' => '1'
            ];
        }

        if ($post_type_l && ! $post_type_s) {
            $args['meta_query'][] = [
                'key' => 'vybor_dots_filter',
                'compare' => '==',
                'value' => '1'
            ];
        }
    }

    

    $ids = query_posts($args);
    $marks = [];
    $post_id_arr = [];
    $marks_counter = 1;

    foreach ($ids as $post_id) {
        $post_id_arr[] = $post_id;

        if ('lokatsii' == get_post_type($post_id)) {
            $data = (array) json_decode(get_field('na_karte_filter', $post_id, false), true);
        }

        if ('sobitiya' == get_post_type($post_id)) {
            $data = (array) json_decode(get_field('na_karte_filter_s', $post_id, false), true);
        }

        if ( ! isset($data['marks']) || ! is_array($data['marks'])) {
            continue;
        }

        foreach ((array) $data['marks'] as $mark) {
            $mark['id'] = $marks_counter;
            $mark['content'] = "<div class='mark_building'>";
            $mark['content'] .= "<div class='mark_info'>";
            $mark['content'] .= "<div class='info_title'><a href='" . get_the_permalink($post_id) . "'>" . get_the_title($post_id) . '</a></div>';
            $mark['content'] .= '</div>';
            $mark['content'] .= '</div>';

            $marks[] = $mark;
            $marks_counter++;
        }
    }
    $map_data['markss'] = $marks;

    $implode_post_id = implode(',', $post_id_arr);

    $map_data['post_id_html'] = do_shortcode('[us_grid post_type="ids" ids="' . $implode_post_id . '" items_quantity="12" no_items_message="Скоро тут появится что-то интересное!" pagination="regular" pagination_style="1" items_layout="471" columns="3" items_gap="15px"]');

    echo json_encode($map_data);
    die();
}

// Переопределение вывода всех постов в категории

function views_all_post_cat_custom($atts)
{
    if(!empty($_COOKIE['city'])){// Получаем из куки значение города, иначе равно УфА
        $selected_city = $_COOKIE['city'];
    }
    else{
        $selected_city = 'Уфа';
    }

    $atts = shortcode_atts([
        'id_cat' => '0',
    ], $atts);

    $args = [
        'post_type' => ['sobitiya', 'lokatsii', 'post'],
        'tax_query' => [
            [
                'taxonomy' => 'category',
                'field' => 'id',
                'terms' => $atts['id_cat'],
            ]
        ],
        'order' => 'ASC',
        'fields' => 'ids',
        'posts_per_page' => -1,
    ];

    $args['meta_query']['query_selected_city'] = array(  // Добавляем запрос на проверку города
            'relation' => 'OR',
            array(
                'key' => 'city',
                'value' => $selected_city,
            ),
                array(
                'key' => 'none-city',
                'value' => 'yes',
            ),
        );

    $ids = query_posts($args);
    wp_reset_query();
    $implode = implode(',', $ids);

    return do_shortcode('[us_grid post_type="ids" ids="' . $implode . '" items_quantity="12" no_items_message="Скоро тут появится что-то интересное!" pagination="regular" pagination_style="1" items_layout="471" columns="3" items_gap="15px"]');
    die();
}





