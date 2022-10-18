jQuery(document).ready( function($) {

	// Открытие всплывающего окна
	$(document).on('click', '.text-selected-city, .callback-selected-city .w-text-h i', function(){ 
		$('.cont-popup-window').show();
		$( '.popup-background').show();
	});

	// Закрытие всплывающего окна
	$(document).on('click', '.popup-background', function(){
		$('.cont-popup-window').hide();
		$( '.popup-background').hide();
	});

	// Обработка клика по нужному городу
	$(document).on('click', '.name-city', function(){
		var name = $(this).next('.valu-name-city').val();
		$('.callback-selected-city').find('.text-selected-city').html(name + '<input type="hidden" value="'+ name +'" class="selected-city-value val-city-cooki">');
		setCookie('savesel', 'yes');
		setCookie('city', name);
		$('.cont-popup-window').hide();
		$( '.popup-background').hide();
		location.reload();
	});
	// Скрытие приветственного сообщения с выбранным городом
	$(document).on('click', '.welcome-city-yes', function(){
		$('.welcome-message-selected-city').hide();
		setCookie('savesel', 'yes');
	});

	// Показ всплывающего окна выбора города, если город выбран не верно
	$(document).on('click', '.welcome-city-no', function(){
		$('.welcome-message-selected-city').hide();
		$('.cont-popup-window').show();
		$( '.popup-background').show();
	});

	// Опеделение ближайщего города из доступных
	var city_cookie = getCookie('city');

	if(city_cookie != undefined){
		set_selected_city(city_cookie);
	}
	else{
		ymaps.ready(function () {

			if($('.yandex-map').length == 0){ // Если нет яндекс карты на странице, методы извлечения геопозиции пользователя разные в зависимости от того есть карта на странице или нет
				var located_city;// Получение текущего местоположения
				var located_name_city =  ymaps.geolocation.city; // Название текущего города
		    	located_city = { // Координаты текущего местоположения
		    		latitude : ymaps.geolocation.latitude,
		    		longitude: ymaps.geolocation.longitude
		    	}
		    	var all_citys_name = new Array(); // Массив всех доступных городов
		    	$('.valu-name-city').each(function(index){
					var name_city_this = $(this).val();
					all_citys_name.push(name_city_this);
				});

				var mass_citys = new Array();
				check_located_city(located_name_city, all_citys_name, located_city); // Проверяет текущий город на вхождение в список допустимых, наличие записи к куки, и запускает соответствующие сценарии
			}
			else{// Если есть яндекс карта на странице
				var located_name_city_query =  ymaps.geolocation.get({provider: 'yandex'}); // Получение Геопозиции
				located_name_city_query.then( // Промис определения текущего местоположения
					function(result){

						var located_city;
						located_city = { // Координаты текущего местоположения
				    		latitude : result.geoObjects.position[0],
				    		longitude: result.geoObjects.position[1]
				    	}

				    	var geocode_city = ymaps.geocode([located_city.latitude, located_city.longitude]);
				    	geocode_city.then(function(result_geocode){// Промис для геокодирования полученных координат в название города
				    		var located_name_city = result_geocode.geoObjects.get(0).properties._data.metaDataProperty.GeocoderMetaData.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.LocalityName;// Название текущего города

				    		var all_citys_name = new Array(); // Массив всех доступных городов
					    	$('.valu-name-city').each(function(index){
								var name_city_this = $(this).val();
								all_citys_name.push(name_city_this);
							});

							var mass_citys = new Array();
							check_located_city(located_name_city, all_citys_name, located_city); // Проверяет текущий город на вхождение в список допустимых, наличие записи к куки, и запускает соответствующие сценарии
				    	});

					}
					);
			}

    	  	
		});
	}
	

	function check_located_city(name_city, mass_citys, located_city){
		var result_search = mass_citys.includes(name_city);
		if(!result_search){
			set_rest_city(name_city, mass_citys, located_city);
		}
		else{
			setCookie('city', name_city);
			set_selected_city(name_city);
			location.reload();
		}
		

	}

	function set_rest_city(name_city, mass_citys, located_city){
		ymaps.ready(function () {
		  var full_data_citys = new Array();

		  
		  let requests = mass_citys.map(city => ymaps.geocode(city));
		  // Очен ьважная штука которую я использовал в первый раз и полезная
		  Promise.all(requests).then(function(results){
		  	var full_data_citys = new Array();
		  	results.forEach(function(item, index, arr){
		  		var distance = calc_distance(located_city.latitude, located_city.longitude, item.geoObjects.get(0).geometry.getCoordinates()[0], item.geoObjects.get(0).geometry.getCoordinates()[1]);

		  		var push_object = {
					    name_city: mass_citys[index],
					    distance: distance
				}
				full_data_citys.push(push_object);
		  	});
		  	full_data_citys.sort(function(a, b){
		  		return a.distance - b.distance;
		  	});
		  	setCookie('city', full_data_citys[0].name_city);
		  	set_selected_city(full_data_citys[0].name_city);
		  	location.reload();
		  });

		});
		
	}


	function set_selected_city(name_city){
		$('.welcome-message-selected-city').hide();
		$('.callback-selected-city').hide();
		$('.value-selected-name-city').html(name_city);
		$('.text-selected-city').html(name_city + '<input type="hidden" value="'+ name_city +'" class="selected-city-value val-city-cooki">');
		var save_city = getCookie('savesel');
		if(save_city == undefined){
			$('.welcome-message-selected-city').show();
		}
		$('.callback-selected-city').show();
	}

});



function calc_distance(lat1, long1, lat2, long2){ // функция вычисления расстояния между двумя городами

	var rad_lat_1 = lat1 * Math.PI / 180;
	var rad_long_1 = long1 * Math.PI / 180;
	var rad_lat_2 = lat2 * Math.PI / 180;
	var rad_long_2 = long2 * Math.PI / 180;

	var cos_lat_1 = Math.cos(rad_lat_1);
	var sin_lat_1 = Math.sin(rad_lat_1);
	var cos_lat_2 = Math.cos(rad_lat_2);
	var sin_lat_2 = Math.sin(rad_lat_2);
	var delta = rad_long_1 - rad_long_2;
	var cos_delta = Math.cos(delta);
	var sin_delta = Math.sin(delta);

	var y = Math.sqrt(Math.pow(cos_lat_2 * sin_delta, 2) + Math.pow(cos_lat_1 * sin_lat_1 - sin_lat_1 * cos_lat_2 * cos_delta, 2));
	var x = sin_lat_1 * sin_lat_2 + cos_lat_1 * cos_lat_2 * cos_delta;

	var atan = Math.atan2(y, x);
	var radius_earth = 6371;
	var result = atan * radius_earth;

	return result;

}