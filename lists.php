<?php
/*
Plugin Name: WP Custom Lists
Plugin URI: 
Description: Create lists
Version: 1.0
Author: Sevy29
Author URI: 
*/


if ( !function_exists('add_action') ) :
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
endif;

class wp_list_cpt {

  var $dir;
  var $path;
  var $plugin_nonce;
  var $optionlabel;
  var $postType;

  function __construct() {
  
    $this->dir = dirname( plugin_basename(__FILE__) );
	$this->path = plugins_url();
	$this->plugin_nonce = 'wplist';
	$this->optionlabel = 'wplist';
	$this->postType = 'list';
	   
	load_plugin_textdomain( 'wp-list-cpt', false, $this->dir . '/languages/' );

    add_action( 'init', array($this, 'wp_list_create_post_type') );

    add_action( 'admin_menu', array($this, 'wp_list_admin_nav') );

    add_action( 'admin_init', array($this, 'admin_init') );

    add_action( 'save_post', array($this, 'wp_custom_data') );

    //tinymce custom button
    add_action('admin_head', array($this, 'wp_list_add_my_tc_button') );

	//tinymce translations
    add_filter( 'mce_external_languages', array($this, 'wp_list_lang'));

		
	//load css
	add_action( 'admin_head', array($this, 'wp_list_admin_custom_css') );

	//load js
	add_action( 'admin_enqueue_scripts', array($this, 'wp_list_admin_custom_js') );
	
	//manage table column
	add_filter('manage_' . $this->postType . '_posts_columns', array($this, 'set_table_columns') ); 
	
	//short code 
	add_shortcode( 'customlist', array( $this, 'wp_list_shortcode') );

  }



  function wp_list_lang($locales) {
    //DO NOT USE http!!!!!!!
    $locales['wp_list_tc_button'] = plugin_dir_path ( __FILE__ ) . 'list-translate.php';
    return $locales;
  }


  function wp_list_shortcode($atts = array('list' => 0)) {

   $data = '';

	$args = array(
	  'post_type' => $this->postType,
	  'order' => 'ASC',
	  'orderby' => 'menu_order',
	  'tax_query' => array(
		array(
		  'taxonomy' => $this->postType . '-category',
		  'field' => 'term_id',
		  'terms' => $atts['list']
		)
	  )
	);
	
    $items = new WP_Query($args);
	
	if ($items->have_posts()) {
	
	  if (has_action('wp_list_header')) {
	    $data .= apply_filters('wp_list_header', $atts['list']);
	  }
	  
	  while($items->have_posts()) { 

		$items->the_post();
		
		if (has_action('wp_list_overwrite')) {
		  $data .= apply_filters('wp_list_overwrite', get_the_ID());
		} else {
		  $data .= '
			<div class="list-block">
			  <a class="list-title" href="#">' . get_the_title() .'</a>
			  <div class="list-content">' . apply_filters('the_content', get_the_content()) . '
				<div class="clearfix"></div>
			  </div>
			</div>
				';
	    }

	  }
	
	  if (has_action('wp_list_footer')) {
	    $data .= apply_filters('wp_list_footer', $atts['list']);
	  }
	
	}

	return $data;
	
  }


  function wp_list_add_my_tc_button() {
	global $typenow;
	
	// check if WYSIWYG is enabled
	if ( get_user_option('rich_editing') == 'true') {
	  add_filter('mce_external_plugins', array($this, 'wp_list_add_tinymce_plugin') );
	  add_filter('mce_buttons', array($this, 'wp_list_register_my_tc_button') );

      $list = get_terms('list-category', array( 'hide_empty' => false ) );

      $arrayList = array();
	  if ($list) {
	    foreach($list as $k=>$item) {
		  $arrayList[] = array('text' => $item->name, 'value' => $item->term_id);
	    }
	  }

	  echo '<script type="text/javascript">
  function wplist_tinymce_custom_getValues() {
    return ' . json_encode($arrayList) . ';
  }
</script>';
	  
	}

  }
 
 
  function wp_list_add_tinymce_plugin($plugin_array) {
	$plugin_array['wp_list_tc_button'] = $this->path . '/' . $this->dir . '/js/wp_list.js';
	return $plugin_array;
  }


  function wp_list_register_my_tc_button($buttons) {
   array_push($buttons, "wp_list_tc_button");
   return $buttons;
  }



  function wp_list_admin_custom_js() {
    
	wp_register_script('wp_list_custom-admin', $this->path . '/' . $this->dir . '/js/admin.js', array(), '1.0');
	wp_enqueue_script('wp_list_custom-admin'); 

  }


  function wp_list_admin_custom_css() {
	echo '<link rel="stylesheet" type="text/css" media="all" href="' . $this->path . '/' . $this->dir .'/css/admin.css" />' . "\n";
	echo '<script type="text/javascript">var $list_error_msg = "' . __('Validation failed. List title is required.', 'wp-list-cpt') . '";</script>' . "\n";
  }


  function wp_custom_data($post_id) {
			  
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	  return;

	if ( !wp_verify_nonce( $_POST[$this->plugin_nonce . '_nonce'], $this->plugin_nonce ) )
	  return;
  
	wp_set_post_terms( $post_id, array( (int) $_POST['listitem']), $this->postType . '-category', false);

  }


  function wp_list_admin_nav() {  
	$slug = 'custom-list';
	add_menu_page( __( 'Lists', 'wp-list-cpt' ), __( 'Lists', 'wp-list-cpt' ), 'edit_pages', $slug, array( $this, 'wp_list_panel'), 'dashicons-list-view', 30);
	add_submenu_page( $slug, __( 'List item', 'wp-list-cpt' ), __( 'New list', 'wp-list-cpt' ), 'edit_pages', 'list-new', array($this, 'wp_list_item_panel') );
  }


  function admin_init() {
	add_meta_box( 'listbox', __( 'List', 'wp-list-cpt' ), array(&$this, 'metabox'), $this->postType, 'side' );
  }


  public function metabox( $post ) {
    wp_nonce_field( $this->plugin_nonce, $this->plugin_nonce . '_nonce' );
	include_once( 'meta-box-panel.php' );
  }


  function wp_list_panel() {
	include_once( 'list-panel.php' );
  }


  function wp_list_item_panel() {
	include_once( 'list-item-panel.php' );  
  }

  function wp_list_delete($itemid) {
	  
	global $wpdb;
	  
    //Delete posts
    //$q = $wpdb->prepare("DELETE FROM " . $wpdb->prefix . "posts WHERE ID IN(SELECT object_id FROM " . $wpdb->prefix . "term_relationships WHERE term_taxonomy_id=%d)", $itemid);

    //Trash posts
	$q = $wpdb->prepare("UPDATE " . $wpdb->prefix . "posts SET post_status = 'trash' WHERE ID IN(SELECT object_id FROM " . $wpdb->prefix . "term_relationships WHERE term_taxonomy_id=%d)", $itemid);
	
	
	//echo $q;
	$wpdb->query($q);
	
	//Delete term
	$t = wp_delete_term( $itemid, $this->postType . '-category');


  }


  function set_table_columns($columns) {
  
	if (isset($columns['date'])) {
	  unset($columns['date']);	
	}
	
	if (isset($columns['comments'])) {
	  unset($columns['comments']);	
	}

	if (isset($columns['author'])) {
	  unset($columns['author']);	
	}

	return $columns;

  }


  function wp_list_get_term($ptype, $pslug) {  
    global $wpdb;	 
	return $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND slug = %s LIMIT 1", $ptype, $pslug) );
  }


  function wp_list_create_post_type() {
  
    global $wpdb;
  
    $taxname = '';
	$taxid = '';
    if (isset($_GET[$this->postType . '-category'])) {

	  $term = $this->wp_list_get_term($this->postType . '-category',  $_GET[$this->postType . '-category']);
	  if ($term) {
		$taxname = $term->name;
		//$taxid = $term->term_id;
		//print_r($term);  
	  }
	  
	}
  
	$labels = array(
	  'name'               => __( 'List:', 'wp-list-cpt' ) . ' ' . $taxname,
	  'singular_name'      => __( 'List', 'wp-list-cpt' ),
	  'add_new'            => __( 'Add new list item', 'wp-list-cpt' ),
	  'add_new_item'       => __( 'Add new list item', 'wp-list-cpt' ),
	  'edit_item'          => __( 'Edit list', 'wp-list-cpt' ),
	  'new_item'           => __( 'New list', 'wp-list-cpt' ),
	  'all_items'          => __( 'All list items', 'wp-list-cpt' ),
	  'view_item'          => __( 'View list', 'wp-list-cpt' ), 
	  'search_items'       => __( 'Search list items','wp-list-cpt' ),
	  'not_found'          => __( 'No list items found', 'wp-list-cpt' ),
	  'not_found_in_trash' => __( 'No list items found in Trash', 'wp-list-cpt' ),
	  'parent_item_colon'  => '',
	  'menu_name'          => __( 'List items', 'wp-list-cpt' )
	);
  
	$args = array(
	  'labels'             => $labels,
	  'public'             => true,
	  'publicly_queryable' => true,
	  'show_ui'            => true,
	  'show_in_menu'       => false,
	  'query_var'          => true,
	  'rewrite'            => array( 'slug' => 'list' ),
	  'capability_type'    => 'page',
	  'has_archive'        => true,
	  'hierarchical'       => false,
	  'menu_position'      => null,
	  'supports'           => array( 'title', 'editor')
	);
  
	register_post_type( $this->postType, $args );



	$labels = array(
	  'name'                       => _x( 'Lists', 'wp-list-cpt' ),
	  'singular_name'              => _x( 'list', 'wp-list-cpt' ),
	  'search_items'               => __( 'Search lists', 'wp-list-cpt' ),
	  'popular_items'              => __( 'Popular lists', 'wp-list-cpt' ),
	  'all_items'                  => __( 'All lists', 'wp-list-cpt' ),
	  'parent_item'                => null,
	  'parent_item_colon'          => null,
	  'edit_item'                  => __( 'Edit list', 'wp-list-cpt' ),
	  'update_item'                => __( 'Update list', 'wp-list-cpt' ),
	  'add_new_item'               => __( 'Add new list', 'wp-list-cpt' ),
	  'new_item_name'              => __( 'New list', 'wp-list-cpt' ),
	  'separate_items_with_commas' => __( 'Separate lists with commas', 'wp-list-cpt' ),
	  'add_or_remove_items'        => __( 'Add or remove lists', 'wp-list-cpt' ),
	  'choose_from_most_used'      => __( 'Choose from the most used lists', 'wp-list-cpt' ),
	  'not_found'                  => __( 'No lists found.', 'wp-list-cpt' ),
	  'menu_name'                  => __( 'Lists', 'wp-list-cpt' )
	);
  
	$args = array(
	  'hierarchical'          => false,
	  'labels'                => $labels,
	  'show_ui'               => false,
	  'show_admin_column'     => true,
	  'update_count_callback' => '_update_post_term_count',
	  'query_var'             => true,
	  'rewrite'               => false
	);
  
	register_taxonomy( $this->postType . '-category', $this->postType, $args );


  }
	
}

$wp_list_cpt = new wp_list_cpt();


/*
//functions.php

add_filter( 'wp_list_header', 'sbh');
add_filter( 'wp_list_footer', 'sbb');

add_filter( 'wp_list_overwrite', 'sbo');



function sbh($term_id) {
  return '<div>header...' . $term_id . '</div>';	
}

function sbb($term_id) {
  return '<div>footer...' . $term_id . '</div>';	
}


function sbo($post_id) {
  return '<div>' . get_the_title() . ' - ' . $post_id . '</div>';
}

*/


?>