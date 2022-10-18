<?php 
// Создание страницы опций через плагин ACF PRO

if( function_exists('acf_add_options_page') ) {
	acf_add_options_page(array(
		'page_title' 	=> 'Выбор городов',
		'menu_title'	=> 'Выбор городов',
		'menu_slug' 	=> 'selected-city-plugin',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
}

// Добавление полей на страницу редактирования пользователя

add_action( 'edit_user_profile', 'add_city_field_profile' );

add_action( 'show_user_profile', 'add_city_field_profile' );

function add_city_field_profile($user){
	$current_user = wp_get_current_user();
	$user_data = get_userdata( $current_user->ID );

	if(isset($user_data->roles[0])){if($user_data->roles[0] != 'administrator'){echo 123123123; return;}}


	echo '<h2>Разрешенные для редактирования города</h2>';

	$citys_edit = get_the_author_meta('citys_edit', $user->ID);
	$mass_citys_edit = explode(',',$citys_edit);
	$all_citys = get_field('citys', 'option');
	foreach($all_citys  as $city){
		echo '<p>'.$city['city-name'].'</p>';
		if(in_array($city['city-name'], $mass_citys_edit)){
			$checked = 'checked';
		}
		else{
			$checked = '';
		}
		echo '<input type="checkbox" '.$checked.' name="citys_edit[]" value="'.$city['city-name'].'">';
	}
}

add_action( 'personal_options_update', 'save_city_field_profile' );
add_action( 'edit_user_profile_update', 'save_city_field_profile' );
 
function save_city_field_profile( $user_id ) {
 	$update_string = '';
 	if(!isset($_POST['citys_edit'])){return;}
 	foreach($_POST['citys_edit'] as $city){
 		$update_string .= $city. ',';
 	}
	update_user_meta( $user_id, 'citys_edit', $update_string);
 
}

// Не будем выводить посты из городов, котоыре не разрешено редактировать авторизированному пользователю
add_action( 'pre_get_posts', 'pre_get_posts_for_admin' );
function pre_get_posts_for_admin( $query ){



	if( is_admin() && $query->is_main_query() && ($query->get('post_type') == 'post' || $query->get('post_type') == 'sobitiya' ||  $query->get('post_type') == 'lokatsii')){
		$current_user = wp_get_current_user();

		$citys_edit_user = get_the_author_meta('citys_edit', $current_user->ID);
		$mass_citys_edit = explode(',',$citys_edit_user);
		$user_data = get_userdata( $current_user->ID );

		if(isset($user_data->roles[0])){if($user_data->roles[0] == 'administrator'){return;}}
		
		$meta_query = array(
		  'relation' => 'OR',
          array(
            'key'     => 'city',
            'value'   => $mass_citys_edit,
            'compare' => 'IN'
          ),
          array(
            'key'     => 'none-city',
            'value'   => 'yes',
          ),
        );
        $query->set('meta_query', $meta_query);

	}
}















 ?>