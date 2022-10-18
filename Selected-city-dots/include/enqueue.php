<?php 

add_action( 'wp_enqueue_scripts', 'add_scripts_styles', 4000 );
function add_scripts_styles() {
	wp_enqueue_style( 'selected-city-style', plugins_url( 'assets/selected_city_style.css' , __FILE__ ));
	wp_enqueue_script( 'ymapselectcity', 'https://api-maps.yandex.ru/2.0-stable/?apikey=e1cadf43-4de0-4d06-9ab9-6ce121a9887b&load=package.standard&lang=ru-RU');
	wp_enqueue_script( 'cookie_script_selected_sity', plugins_url( 'assets/cookie.js' , __FILE__ ), array('jquery'), null, true);
	wp_enqueue_script( 'selected-city-script', plugins_url( 'assets/selected_city_script.js' , __FILE__ ), array('jquery'), null, true);
	wp_enqueue_script( 'FontAwesome5', 'https://use.fontawesome.com/releases/v5.7.1/css/all.css');
}
 ?>