jQuery(document).ready(function($){


	$(document).on('input', '.search-field', function(){
		var searchTerm = $(this).val();
		// проверим, если в поле ввода более 2 символов, запускаем ajax
		if(searchTerm.length > 2){
			$.ajax({
				url : Rcl.ajaxurl,
				type: 'POST',
				data:{
					'action':'livesearch',
					'term'  :searchTerm
				},
				beforeSend: function(responce){
					$('.responce-search').html('<img src="https://freelancelingua.com/wp-content/uploads/2021/01/spinner.gif" style="width: 20px; height: 20px; text-align: center;">');
				},
				success:function(responce){
					$('.responce-search').html(responce.data.result);
					$('.responce-search').addClass('vis');
				}
			});
		}
	});


	$(document).on('focus', '.search-field', function(){
		var searchTerm = $(this).val();
		// проверим, если в поле ввода более 2 символов, запускаем ajax

		if(searchTerm == '') return;
		if(searchTerm.length > 2){
			$.ajax({
				url : Rcl.ajaxurl,
				type: 'POST',
				data:{
					'action':'livesearch',
					'term'  :searchTerm
				},
				beforeSend: function(responce){
					$('.responce-search').html('<img src="https://freelancelingua.com/wp-content/uploads/2021/01/spinner.gif" style="width: 20px; height: 20px; text-align: center;">');
				},
				success:function(responce){
					$('.responce-search').html(responce.data.result);
					$('.responce-search').addClass('vis');
				}
			});
		}
	});


	$(document).on('input', '.mark-value', function(){
		var searchTerm = $(this).val();

		var values = $(".values-mark-select").map( (i,el) => $(el).data('text_value') ).get().join(',');

		if(values == ''){
			var selected_m = 'no';
		}
		else{
			values = '123,'+values+','+'123'; // Костыль (из-за функции in_array)
			var selected_m = values;
		}
		
		// проверим, если в поле ввода более 2 символов, запускаем ajax
		if(searchTerm.length > 2){
			$('.responce-mark-task').html('<img src="https://freelancelingua.com/wp-content/uploads/2021/01/spinner.gif" style="width: 20px; height: 20px; text-align: center;">');
			$('.responce-mark-task').show();
			$.ajax({
				url : Rcl.ajaxurl,
				type: 'POST',
				data:{
					'action':'livesearchmark',
					'term'  :searchTerm,
					'values' :selected_m
				},
				success:function(responce){
					$('.responce-mark-task').html(responce.data.result);
					
				}
			});
		}
		else{
			$('.responce-mark-task').html('');
        	$('.responce-mark-task').hide();
		}
	});

	if(!$('.mark-value').is(":focus")){
	$(document).on('focus', '.mark-value', function(){
		var searchTerm = $(this).val();

		var values = $(".values-mark-select").map( (i,el) => $(el).data('text_value') ).get().join(',');
		if(values == ''){
			var selected_m = 'no';
		}
		else{
			values = '123,'+values+','+'123'; // Костыль (из-за функции in_array)
			var selected_m = values;
		}
		// проверим, если в поле ввода более 2 символов, запускаем ajax

		if(searchTerm == '') return;
		if(searchTerm.length > 2){
			$('.responce-mark-task').html('<img src="https://freelancelingua.com/wp-content/uploads/2021/01/spinner.gif" style="width: 20px; height: 20px; text-align: center;">');
			$('.responce-mark-task').show();
			$.ajax({
				url : Rcl.ajaxurl,
				type: 'POST',
				data:{
					'action':'livesearchmark',
					'term'  :searchTerm,
					'values' : selected_m
				},
				success:function(responce){
					$('.responce-mark-task').html(responce.data.result);

				}
			});
		}
		else{
			$('.responce-mark-task').html('');
        	$('.responce-mark-task').hide();
		}
	});
}

	$(document).click( function(e){
    	if ( !$(e.target).closest('.responce-mark-task').length && !$(e.target).closest('.mark-value').length ) {
    		$('.responce-mark-task').html('');
        	$('.responce-mark-task').hide();
    	}
	});

	// Живой поиск для меток услуг

	$(document).on('input', '.mark-value-service', function(){
		var searchTerm = $(this).val();

		var values = $(".values-mark-select").map( (i,el) => $(el).data('text_value') ).get().join(',');

		if(values == ''){
			var selected_m = 'no';
		}
		else{
			values = '123,'+values+','+'123'; // Костыль (из-за функции in_array)
			var selected_m = values;
		}


		
		// проверим, если в поле ввода более 2 символов, запускаем ajax
		if(searchTerm.length > 2){
			
			$('.responce-mark-service').html('<img src="https://freelancelingua.com/wp-content/uploads/2021/01/spinner.gif" style="width: 20px; height: 20px; text-align: center;">');
			$('.responce-mark-service').show();
			$.ajax({
				url : Rcl.ajaxurl,
				type: 'POST',
				data:{
					'action':'livesearchmarkservice',
					'term'  :searchTerm,
					'values' :selected_m
				},
				success:function(responce){
					$('.responce-mark-service').html(responce.data.result);
					
				}
			});
		}
		else{
			$('.responce-mark-service').html('');
        	$('.responce-mark-service').hide();
		}
	});

	if(!$('.mark-value-service').is(":focus")){
	$(document).on('focus', '.mark-value-service', function(){
		var searchTerm = $(this).val();

		var values = $(".values-mark-select").map( (i,el) => $(el).data('text_value') ).get().join(',');
		if(values == ''){
			var selected_m = 'no';
		}
		else{
			values = '123,'+values+','+'123'; // Костыль (из-за функции in_array)
			var selected_m = values;
		}
		// проверим, если в поле ввода более 2 символов, запускаем ajax

		if(searchTerm == '') return;
		if(searchTerm.length > 2){
			$('.responce-mark-service').html('<img src="https://freelancelingua.com/wp-content/uploads/2021/01/spinner.gif" style="width: 20px; height: 20px; text-align: center;">');
			$('.responce-mark-service').show();
			$.ajax({
				url : Rcl.ajaxurl,
				type: 'POST',
				data:{
					'action':'livesearchmarkservice',
					'term'  :searchTerm,
					'values' : selected_m
				},
				success:function(responce){
					$('.responce-mark-service').html(responce.data.result);

				}
			});
		}
		else{
			$('.responce-mark-service').html('');
        	$('.responce-mark-service').hide();
		}
	});
}

$(document).click( function(e){
    	if ( !$(e.target).closest('.responce-mark-service').length && !$(e.target).closest('.mark-value-service').length ) {
    		$('.responce-mark-service').html('');
        	$('.responce-mark-service').hide();
    	}
	});


	$(document).on('click', '.close-search', function(){
		
		$('.responce-search').html('');
		$('.responce-search').removeClass('vis');
		
	});

	//Показывать скрывать пароль

	$(document).on('click', '.vis-pass-cont .fa-eye', function(){
		
		$(this).closest('.vis-pass-cont').prev('.vis-pass-field').attr('type', 'text');
		$(this).closest('.vis-pass-cont').html('<i class="rcli fa-eye-slash" aria-hidden="true"></i>');
		
	});

	$(document).on('click', '.vis-pass-cont .fa-eye-slash', function(){
		
		$(this).closest('.vis-pass-cont').prev('.vis-pass-field').attr('type', 'password');
		$(this).closest('.vis-pass-cont').html('<i class="rcli fa-eye" aria-hidden="true"></i>');
		
	});

	$(document).on('input', '.vis-pass-field', function(){
		
		if($(this).val() != ''){
			$(this).next('.vis-pass-cont').addClass('grey-border');
		}
		else{
			$(this).next('.vis-pass-cont').removeClass('grey-border');
		}
		
	});

	$(document).on('focusout', '.vis-pass-field', function(){
		
		if($(this).val() != ''){
			$(this).next('.vis-pass-cont').removeClass('grey-border');
			$(this).next('.vis-pass-cont').addClass('light-grey-border');
		}
		else{
			$(this).next('.vis-pass-cont').removeClass('light-grey-border');
		}
		
	});

	$(document).on('focus', '.vis-pass-field', function(){
		
		if($(this).val() != ''){
			$(this).next('.vis-pass-cont').removeClass('light-grey-border');
			$(this).next('.vis-pass-cont').addClass('grey-border');
		}
		else{
			$(this).next('.vis-pass-cont').removeClass('grey-border');
		}
		
	});

$(document).on('click', '#mbspro_bttn_custom', function(){
		
		$('.cont-callback-bt-mobile-sidebar').toggleClass('open-mobile-sidebar');
		
});

var width_window = $('.cont-callback-bt-mobile-sidebar').width();
var width_win = $('.width-win').val();

if(width_window <= width_win){
	$('.desctop-sidebar').hide();
	$('.prime-forum-content-widget-r').hide();

	var width_bt = $('#mbspro_bttn_custom').width();
	var right = width_window - width_bt - 10;
	var height_header = $('.site-header').height();
	var top = $('#recallbar').height();

	$('#mbspro_bttn_custom').css('height', height_header+'px');

	$('.cont-callback-bt-mobile-sidebar').css('right', '-'+right+'px');
	$('.cont-callback-bt-mobile-sidebar').css('top', top+'px');
	$('.cont-callback-bt-mobile-sidebar').addClass('vis-flex');
}

$(document).on('click', '.mark-text', function(){
		
		$('.mark-field').prepend('<li class="mark-item mark-values-cont"><input type="hidden" name="values_mark_select[]" data-text_value="'+$(this).data('valuemark')+'" class="values-mark-select" value="'+$(this).data('id')+'"><p class="selected-mark-value">'+$(this).data('valuemark')+'</p><i class="rcli fa-times remove-mark" aria-hidden="true"></i></li>');
		$('.responce-mark-task').html('');
        $('.responce-mark-task').hide();
        $('.mark-value').val();
		
});

// Добавление метки услуги после поиска

$(document).on('click', '.text-service', function(){
		
		$('.mark-field').prepend('<li class="mark-item mark-values-cont"><input type="hidden" name="values_mark_select[]" data-text_value="'+$(this).data('valuemarkservice')+'" class="values-mark-select" value="'+$(this).data('id')+'"><p class="selected-mark-value">'+$(this).data('valuemarkservice')+'</p><i class="rcli fa-times remove-mark" aria-hidden="true"></i></li>');
		$('.responce-mark-service').html('');
        $('.responce-mark-service').hide();
        $('.mark-value-service').val();
		
});

$(document).on('click', '.remove-mark', function(){
		
		$(this).closest('.mark-item').remove();
		
});

// Клик на статус занятости

$(document).on('click', '.text-switch', function(){
		
		var user_lk = $(this).data('idlk');
		var user_current = $(this).data('idcurrent');

		if(user_lk != user_current) return;

		var value = $(this).data('switch');

		$.ajax({
				url : Rcl.ajaxurl,
				type: 'POST',
				data:{
					'action':'updatestatuswork',
					'value' : value,
					'user_lk': user_lk,
					'user_current': user_current
				},
				success:function(responce){
					if(responce.data.reload){
						location.reload();
					}
				}
			});

});





}); // Конец скриптов JQuery

//Функция клика на заказ услуг
function sm_add_order_anton( serviceId, masterId ) {


	if ( !parseInt( Rcl.user_ID ) ) {
		rcl_notice( 'Авторизуйтесь или зарегистрируйтесь на сайте!', 'error' );
		return false;
	}

	if ( parseInt( Rcl.user_ID ) == masterId ) {
		rcl_notice( 'Нельзя оформить заказ на свою услугу!', 'error' );
		return false;
	}

	rcl_preloader_show( jQuery( '#sm-card-' + serviceId ) );

	/*if(!serviceId){
	 location.replace('/private-service-order/?sm-master-id=' + masterId);
	 return;
	 }*/

	rcl_ajax( {
		data: {
			action: 'sm_ajax_get_new_order_form_anton',
			serviceId: serviceId,
			masterId: masterId
		}
	} );

}

function mw_load_user_transfer_form_custom(user_id){

    rcl_preloader_show(jQuery('#lk-conteyner'));

    rcl_ajax({
        data: {
            action: 'mw_load_user_transfer_form_custom',
            user_id: user_id
        }
    });

    return false;

}
