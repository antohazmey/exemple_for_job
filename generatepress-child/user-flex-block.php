<?php global $rcl_user,$rcl_users_set; ?>

<div class="user-single" data-user-id="<?php echo $rcl_user->ID; ?>">
	<div class="user-single-top">
		<?php rcl_user_rayting(); ?>
		<a href="<?php rcl_user_url(); ?>" class="user-avatar" rel="nofollow noindex">
			<?php rcl_user_avatar(70); ?>
		</a>
		<a href="<?php rcl_user_url(); ?>" rel="nofollow noindex" class="profile-link">
			<?php rcl_user_name();?>
		</a>
		<p>Был активен <?php echo human_time_diff( strtotime($rcl_user->time_action), current_time('timestamp') ); ?> назад</p>
	</div>
	<div class="user-single-bottom">
	<?php rcl_user_description(); ?>
	<?php 
		switch (get_user_meta($rcl_user->ID, 'status_work', 1)){
    	case 'свободен' :
    		$text_external = 'свободен';
    		break;
    	case 'частично занят' :
    		$text_external = 'частично занят';
    		break;
    	case 'занят' :
    		$text_external = 'занят';
    		break;
    	default :
    		$text_external = 'свободен';
    		break;
    }

	 ?>
	<span class="filter-data"><i class="rcli fa-clock-o"></i>Занятость: <?php echo $text_external; ?></span>
		<a href="<?php rcl_user_url(); ?>" class="user-profile-link" rel="nofollow noindex">
			Профиль
		</a>
	</div>
</div>