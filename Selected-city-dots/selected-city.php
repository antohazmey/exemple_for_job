<?php
/**
 * Plugin Name: Выбор городов
 * Description: Плагин для реализация выбора городов, ограничения редактирования для редакторов по городам, отображения необходимого контента по выбранному городу
 * Author URI:  https://vk.com/anton19999
 * Author:      Антон
 * Version:     1.0
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// код плагина

define( 'SC_PATH', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, 'selected_city_activate' );

register_activation_hook(__FILE__, 'selected_city_activate'); // Активация плагина
register_deactivation_hook(__FILE__, 'selected_city_deactivate'); // Деактивация плагина

// Подключение всех файлов плагина
include( dirname( __FILE__ ) . '/include/enqueue.php' ); // Подключение стилей
include( dirname( __FILE__ ) . '/include/add-admin-menu.php' ); // Файл создания страницы настроек в админке
include( dirname( __FILE__ ) . '/include/front.php' ); // Файл создания страницы настроек в админке

function selected_city_activate(){}
function selected_city_deactivate(){}





 



















 ?>
