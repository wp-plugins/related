<?php

class Related_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'related_widget', 'description' => __('Related Posts Widget','related') );
		parent::__construct('related_widget', 'Related Posts Widget', $widget_ops);
		$this->alt_option_name = 'related_widget';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('related_widget', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();
		extract($args);

		$title	= apply_filters('widget_title', empty($instance['title']) ? 'Related Posts' : $instance['title'], $instance, $this->id_base);

		if ( is_singular() ) {
			global $related;
			$related_str = $related->show( get_the_ID() );

			if ( ! empty( $related_str ) ) {
				echo $before_widget;
				if ( $title ) echo $before_title . $title . $after_title;

				echo $related_str;

				echo $after_widget;
			}
		}

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('related_widget', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']	= strip_tags($new_instance['title']);
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['related_widget']) )
			delete_option('related_widget');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('related_widget', 'widget');
	}

	function form( $instance ) {
    	/*
    	 * Set Default Value for widget form
    	 */
    	$default_value	=	array( "title"=> "Related Posts" );
    	$instance		=	wp_parse_args( (array) $instance, $default_value );

		$title = isset($instance['title']) ? esc_attr($instance['title']) : ''; ?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'related'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p><?php
	}
}


function related_widget() {
	register_widget('Related_Widget');
}
add_action('widgets_init', 'related_widget' );
