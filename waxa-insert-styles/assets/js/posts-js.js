jQuery(document).ready( function($) {
	var max_length_full = 3;
	var max_length_1024 = 2;
	var max_length_480 = 1;

	var max_length;

	if($(document).width() > 1024){
		max_length = max_length_full;
	}

	if($(document).width() < 1024){
		max_length = max_length_1024;
	}

	if($(document).width() < 480){
		max_length = max_length_480;
	}

	$('.grid_posts_block').hide();

	$('.grid_posts_block').each(function(index){
		var count = 0;
		$(this).find('.cont-item').each(function(index){
			count++;
			var check_count = false;
			if(count > max_length){
				$(this).hide();
				check_count = true;
			}

			if(check_count){$('.link_btn_posts_block').show();}
		});
	});

	$('.grid_posts_block').show();

	$(document).on('click', '.link_btn_posts_block', function(e){
		$(this).closest('.posts-block-align-btn').prev('.grid_posts_block').find('.cont-item').each(function(item){
			$(this).show();
		});
		$(this).hide();
	});
});