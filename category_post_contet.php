<?php
/*
Plugin Name: category_post_content
Plugin URI: http://cmslab.jp
Description: ウィジェットから簡単にカテゴリー単位で最近の投稿を表示することができます。
Version: 1.0
Author: Atsuyoshi Tabata
Author URI: http://cmslab.jp
*/



/* ---- shortcode ---- */
function post_content_list( $args = '' ) {
    $defaults = array(
        'cid' => '',
		'limit' => '5',
		'title' => __('New Post','twentyten'),
    );
    $r = wp_parse_args($args, $defaults);
	$cid = $r['cid'];
	$limit = $r['limit'];
	$title = $r['title'];
	if($cid) {
        $cid_array = explode(",", $cid);
        $cid_count = count($cid_array);
    } else {
        $cid_count = '';
	}
    $zen_title = '<h2 class="title">'.$title.'</h2>';
    $output = $zen_title;
    $output .= '<ul class="post_list">'."\n";
    if($cid) {
        $posts = get_posts('numberposts='.$limit.'&cat='.$cid);
    } else {
        $posts = get_posts('numberposts='.$limit);
    }
    foreach($posts as $post):
        $output .= '<li>'."\n";
        /* $output .= '<span class="data">'.date('Y/m/d', strtotime($post->post_date)).'</span>'."\n";*/
        $output .= '<span class="title"><a href="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a></span>'."\n";
		if($cid_count > 1){
			$output .= '<span class="cat">('.get_the_category_list(', ').')</span>'."\n";
		}
        $output .= '</li>'."\n";
    endforeach;
    $output .= '</ul>'."\n";
    if($cid == -1 | empty($cid)){
        $output .= '<p class="go_backnumber"><a href="'.get_option('siteurl') . recent_posts().'">'. __('一覧表示','twentyten') .'</a></p>'."\n";
    } else {
        $category_link = get_category_link($cid);
        $output .= '<p class="go_backnumber"><a href="'.$category_link.'">'. __('一覧表示','twentyten') .'</a></p>'."\n";
    }
    return $output;
}

add_shortcode('post_content_list', 'shortcode_post_content_list');
function shortcode_post_content_list($atts, $content=null){
    extract( shortcode_atts( array(
        'cid' => '',
        'limit' => '5',
        'title' => __('New Post','twentyten'),
    ), $atts ) );
    $output = post_content_list('cid='.$cid.'&limit='.$limit.'&title='.$title);
    return $output;
}













/* ---- Widget ---- */
class Widget_Post extends WP_Widget {
	function Widget_Post() {
		$widget_ops = array(
			'classname' => 'widget_post_content_list',
			'description' => __( "カテゴリー単位で最近の投稿を切り替えることができます。", 'twentyten' ),
		);
		$this->WP_Widget('widget_post_content_list', __( '最近の投稿（カテゴリ選択機能付き）', 'twentyten' ), $widget_ops);
	}

	function form( $instance ) {
		$title = strip_tags(@$instance['title'] );
		$cid = trim(strip_tags(@$instance['cid']));
		$limit = empty($instance['limit']) ? 10 : intval( $instance['limit'] );
	?>
    	<p>
        	<label for="<?php echo $this->get_field_id('title'); ?>">
            <?php _e('Title:', 'twentyten'); ?>
            <input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </label>
        </p>        
    	<p>
        	<label for="<?php echo $this->get_field_id('cid'); ?>">
            <?php _e('Category ID:', 'twentyten'); ?>
            <?php wp_dropdown_categories('show_option_none='.__("Select all","twentyten").'&hide_empty=0&selected=' . esc_attr($cid) . '&name=' . $this->get_field_name('cid') . '&hierarchical=1'); ?>
            </label>
        </p>        
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>">
            <?php _e('Number of posts to show:', 'twentyten'); ?>
                <select id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>">
                <?php
                for ( $i = 1; $i <= 30; ++$i )
                echo "<option value='$i' " . ( $limit == $i ? "selected='selected'" : '' ) . ">$i</option>";
                ?>
                </select>
            </label>
        </p>
    <?php }

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['cid'] = trim(strip_tags( $new_instance['cid'] ));
		$instance['limit'] = strip_tags( $new_instance['limit'] );
		return $instance;
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = empty($instance['title'] ) ? __('New Post List', 'twentyten') : strip_tags( $instance['title'] );
		$limit = empty($instance['limit']) ? 10 : intval( $instance['limit'] );
		echo $before_widget;
		echo post_content_list('cid='.trim(strip_tags($instance['cid'])).'&limit='.$limit.'&title='.$title);
		echo $after_widget;
	}
}
add_action('widgets_init', create_function('', 'return register_widget("Widget_Post");'));



?>