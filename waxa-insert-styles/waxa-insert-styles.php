<?php

/**
 * Plugin Name: Waxa insert styles
 * Author: Антон
 * Version: 1.0.0
 * Description : Реализация тестового задания
 */

function ant_register_blocks() {
 
    // Проверяем, что функция доступна.
    if( function_exists( 'acf_register_block_type' ) ) {
 
        // Блок категории
        acf_register_block_type(array(
            'name'              => 'category',
            'title'             => __('Категория'),
            'description'       => __('Категория'),
            'render_callback'   => 'category_render',
            'category'          => 'formatting',
        ));
        // Блок страницы
        acf_register_block_type(array(
            'name'              => 'posts',
            'title'             => __('Записи'),
            'description'       => __('Записи'),
            'render_callback'   => 'posts_render',
            'category'          => 'formatting',
            'align' => 'wide',
            'enqueue_style' => plugin_dir_url(__FILE__)  . 'assets/css/posts-css.css',
            'enqueue_script' => plugin_dir_url(__FILE__) . 'assets/js/posts-js.js',
        ));
        // Товары
        acf_register_block_type(array(
            'name'              => 'products',
            'title'             => __('Товары'),
            'description'       => __('Товары'),
            'render_callback'   => 'products_render',
            'category'          => 'formatting',
        ));

        // Добавление Цитаты

        // Товары
        acf_register_block_type(array(
            'name'              => 'quote',
            'title'             => __('Цитата'),
            'description'       => __('Цитата'),
            'render_callback'   => 'quote_render',
            'category'          => 'formatting',
            'enqueue_style' => plugin_dir_url(__FILE__) . 'assets/css/quote-css.css',
        ));


    }

    // Создание группы полей для созданного блока
    if( function_exists('acf_add_local_field_group') ){
    acf_add_local_field_group(array ( // Группа полей для категории
		'key' => 'group_for_category_block',
		'title' => 'Поля для блока категория',
		'fields' => array (
			array (
				'key' => 'category_name_category_block',
				'label' => 'Категория товаров',
				'name' => 'id_cat',
				'type' => 'taxonomy',
				'taxonomy' => 'product_cat',
				'field_type' => 'select',
				'add_terms' => 0,
				'return_format' => 'id',	
				'prefix' => '',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'image_category_block',
				'label' => 'Изображение',
				'name' => 'image',
				'type' => 'image',
				'return_format' => 'url',

			),
		),
		'location' => array (
			array (
				array (
					'param' => 'block',
					'operator' => '==',
					'value' => 'acf/category',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		));


    // Группа полей для блока "Страницы"

     acf_add_local_field_group(array (
		'key' => 'group_for_pages_block',
		'title' => 'Поля для блок Страницы',
		'fields' => array (
			array (
				'key' => 'page_name_post_block',
				'label' => 'Страница',
				'name' => 'objects_page',
				'type' => 'relationship',
				'post_type' => ['post'],
				'filters' => ['search', 'post_type', 'taxonomy'],
				'return_format' => 'id',	
			),
			array (
				'key' => 'title_post_block',
				'label' => 'Заголовок',
				'name' => 'title',
				'type' => 'text',
			)
		),
		'location' => array (
			array (
				array (
					'param' => 'block',
					'operator' => '==',
					'value' => 'acf/posts',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		));

     // Группа полей для товаров

     acf_add_local_field_group(array (
		'key' => 'group_for_products_block',
		'title' => 'Поля для блок Товары',
		'fields' => array (
			array (
				'key' => 'product_name_product_block',
				'label' => 'Товар',
				'name' => 'id_product',
				'type' => 'post_object',
				'post_type' => ['product'],
				'multiple' => 1,
				'return_format' => 'id',	
			),
			array (
				'key' => 'title_product_block',
				'label' => 'Заголовок',
				'name' => 'title',
				'type' => 'text',
			)
		),
		'location' => array (
			array (
				array (
					'param' => 'block',
					'operator' => '==',
					'value' => 'acf/products',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		));

     // Группа полей для Цитаты

     acf_add_local_field_group(array (
		'key' => 'group_for_quote_block',
		'title' => 'Поля для блок Цитата',
		'fields' => array (
			array (
				'key' => 'quote_head',
				'label' => 'Заголовок цитаты',
				'name' => 'quote_title',
				'type' => 'text',	
			),
			array (
				'key' => 'quote_content',
				'label' => 'Цитата',
				'name' => 'quote_content',
				'type' => 'textarea',	
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'block',
					'operator' => '==',
					'value' => 'acf/quote',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		));


    }
}
add_action( 'acf/init', 'ant_register_blocks' );


// Отрисовка блока "Категория"
function category_render($block, $content = '', $is_preview = true, $post_id = 0){
	// Добавление id
	$id = 'category-' . $block['id'];
	if( !empty($block['anchor']) ) {
	    $id = $block['anchor'];
	}


	// Добавление класса и выравнивания
	$className = 'category';
	if( !empty($block['className']) ) {
	    $className .= ' ' . $block['className'];
	}
	if( !empty($block['align']) ) {
	    $className .= ' align' . $block['align'];
	}

	$id_cat = get_field('id_cat') ?: $id_cat = 0;
	$image = get_field('image') ?: $image = '/wp-content/uploads/woocommerce-placeholder-300x300.png';

	if(empty($id_cat)){
		echo 'Необходимо выбрать категорию товаров';
		return;
	}

	echo do_shortcode('[waxa-ipcp image="'.$image.'" id="'.$id_cat.'"]');
}



// Отрисовка блока "Записи"

function posts_render($block, $content = '', $is_preview = false, $post_id = 0){

	// Добавление id
	$id = 'category-' . $block['id'];
	if( !empty($block['anchor']) ) {
	    $id = $block['anchor'];
	}

	// Добавление класса и выравнивания
	$className = 'category';
	if( !empty($block['className']) ) {
	    $className .= ' ' . $block['className'];
	}
	if( !empty($block['align']) ) {
	    $className .= ' align' . $block['align'];
	}



	$posts_id = get_field('objects_page');
	$title = get_field('title');

	$str_ids = '';

	foreach($posts_id as $post_id){
		$str_ids .= $post_id . ',';
	}

	$str_ids = mb_substr($str_ids, 0, -1);

	echo '[waxa-ippp ids="'.$str_ids.'" title="'.$title.'"]';

}


// Отрисовка блока Товары

function products_render($block, $content = '', $is_preview = true, $post_id = 0){
	// Добавление id
	$id = 'category-' . $block['id'];
	if( !empty($block['anchor']) ) {
	    $id = $block['anchor'];
	}


	// Добавление класса и выравнивания
	$className = 'category';
	if( !empty($block['className']) ) {
	    $className .= ' ' . $block['className'];
	}
	if( !empty($block['align']) ) {
	    $className .= ' align' . $block['align'];
	}

	$str_arg_shortcode = '';
	foreach(get_field('id_product') as $page){
		$str_arg_shortcode .= $page.',';
	}
	$str_arg_shortcode = mb_substr($str_arg_shortcode, 0, -1);

	$title = get_field('title') ?: $title = '';

	echo do_shortcode('[waxa-ipip ids="'.$str_arg_shortcode.'" title="'.$title.'"]');
}

// Отрисовка блока "Цитата"

function quote_render($block, $content = '', $is_preview = true, $post_id = 0){
	$id = 'category-' . $block['id'];
	if( !empty($block['anchor']) ) {
	    $id = $block['anchor'];
	}


	// Добавление класса и выравнивания
	$className = 'category';
	if( !empty($block['className']) ) {
	    $className .= ' ' . $block['className'];
	}
	if( !empty($block['align']) ) {
	    $className .= ' align' . $block['align'];
	}

	$title = get_field('quote_title') ?: $title = 'Комментарий Waxa Shop';
	$content = get_field('quote_content') ?: $content = 'Текст комментария Waxa Shop';

	echo '<div class="comment"><span class="comment-title">'.$title.'</span></div>';
	echo '<p>'.$content.'</p>';
	echo '<hr>';
}





// Реализация добавления кнопок для редактора текста

add_action('enqueue_block_editor_assets', 'waxa_load_mark_btn');
function waxa_load_mark_btn(){
    wp_enqueue_script(
        'waxa_mark',
        plugins_url( 'assets/js/mark-btn.js', __FILE__ ),
        array( 'wp-element', 'wp-rich-text', 'wp-editor' )
    );
}


// Добавляем в старый редактор кнопку маркера


function themename_change_mce_buttons( $init ) {


	$formats[] = [
        'title'  => __( 'Маркер', 'some-textdomain-here' ),
        'inline' => 'mark',
        'styles' => [
            'font-family' => 'Arial',
            'line-height'
        ]
    ];

	$init['style_formats'] = wp_json_encode( $formats);

	return $init;
}
add_filter( 'tiny_mce_before_init', 'themename_change_mce_buttons' );

add_filter( 'mce_buttons_2', function ( array $buttons = [] ) {

    return array_unique( array_merge( [ 'styleselect' ],  $buttons ) );
}, 20 );




// Переопределение шорткода вывода посто в теле записи

add_action( 'init', 'waxa_overide_shortcode', 9000 );
function waxa_overide_shortcode(){
	remove_shortcode('waxa-ippp', 'waxa_ippp_btn_func' );
    add_shortcode('waxa-ippp', 'waxa_ippp_btn_func_custom');
}

function waxa_ippp_btn_func_custom($atts){

	if( ! is_page() && ! is_single() ) return '';

    $a = shortcode_atts( array(
        'ids' => '',
        'title' => 'Материалы по теме',
    ), $atts );

    if( $a['ids'] == '' ) return '';


    $posts_id = explode( ",", $a['ids'] );
    $content = '';
    
	$args = array(
		'post_type' => 'post',
		'post__in' => $posts_id
	);
	$html_post = '';

	$query = new WP_Query($args);
	if ($query->have_posts()) {
		   while ($query->have_posts()) {
		       $query->the_post();

		       $image = get_the_post_thumbnail_url(get_the_ID(),
                           'woocommerce_single') ?: '/wp-content/uploads/woocommerce-placeholder-600x600.png';
		       $html_post .= '
				<div class="cont-item">
					<a href="'.get_the_permalink().'" class="link-item">
						<img src="'.$image.'" alt="'.esc_attr(get_the_title()).'" class="bg-item" loading="lazy">
						<div class="data-item">
							<div class="title-item">'.get_the_title().'</div>
							<div class="arrow_cirle">
								<i class="fas fa-arrow-right"></i>
							</div>
						</div>
					</a>
				</div>
			';
		   }
	}
	wp_reset_postdata();


	$content .= '
		<section class="articles-posts-block">
			<div class="cont-posts-block">
				<div class="box">
					<h2 class="main_title_posts_block">'.$a['title'].'</h2>
					<div class="grid_posts_block flex_posts_block show_line">
						'.$html_post.'
					</div>
					<div class="posts-block-align-btn"><div class="link_btn_posts_block">Все записи</div></div>
				</div>
			</div>
		</section>
	';

	return $content;
	


}

// Добавление стилей для переопределенного шорткода

add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );

function theme_name_scripts() {
	wp_enqueue_style( 'post-in-post-style', plugin_dir_url(__FILE__)  . 'assets/css/posts-css.css' );
	wp_enqueue_script( 'post-in-post-script',plugin_dir_url(__FILE__) . 'assets/js/posts-js.js');
}







