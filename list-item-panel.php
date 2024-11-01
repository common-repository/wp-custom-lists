<?php
if ( !function_exists('add_action') ) {
  header('Status: 403 Forbidden');
  header('HTTP/1.1 403 Forbidden');
  exit();
}

global $wpdb;

$termLabel = '';
$labelTitle = '';
$labelBtn = '';
$action = '';
$layout = 'edit';
$readonly = '';
$showBtn = 1;
$msg = '';


if (isset($_GET['itemid'])) {
  $itemid = intval($_GET['itemid']);
} else {
  $itemid = 0;
}

if (isset($_GET['action'])) {
  if ($_GET['action'] == 'delete') {
	$layout = 'delete';  
	$readonly = ' readonly="readonly" ';
  }
}



//tester permission
if ( isset($_POST['post_title']) && isset($_POST[$this->plugin_nonce . '_nonce']) ) {

  if ( !wp_verify_nonce( $_POST[$this->plugin_nonce . '_nonce'], $this->plugin_nonce ) ) {
	 die();
  }


  $termLabel = $_POST['post_title'];
  
  if ($layout == 'delete') {

	/*
    //Delete posts
    $q = $wpdb->prepare("DELETE FROM " . $wpdb->prefix . "posts WHERE ID IN(SELECT object_id FROM " . $wpdb->prefix . "term_relationships WHERE term_taxonomy_id=%d)", $itemid);
	//echo $q;
	$wpdb->query($q);
	
	//Delete term
	$t = wp_delete_term( $itemid, $this->postType . '-category');
	*/

	$this->wp_list_delete($itemid);

	$showBtn = 0;
	
	$msg = __('List deleted', 'wp-list-cpt');

  } else {

	if ($itemid == 0) {
  
	  $resArray = wp_insert_term( $termLabel, $this->postType . '-category');	
	  
	  if (is_array($resArray)) {
		$itemid = @$resArray['term_id'];
	  } 
	  
	  $msg = __('List Added', 'wp-list-cpt');
	  
	} else {
		
	  wp_update_term($itemid, $this->postType . '-category', array('name' => $termLabel));
  
      $msg = __('List updated', 'wp-list-cpt');
  
	}
  
  }
  
}

$action = '?page=list-new&itemid=' . $itemid . '&action=' . $layout;
 

if ($itemid > 0) {

  $labelTitle = __('Delete list', 'wp-list-cpt');
  $labelBtn = __('Delete');

  $term = get_term( $itemid, $this->postType . '-category' );

  if ($term) {

    if ($layout == 'edit') {

      $labelTitle = __('Edit list', 'wp-list-cpt');
      $labelBtn = __('Update');
	
	} 

	$termLabel = $term->name;
	  
  }


} else {

  if ($layout == 'edit') {

    $termLabel = '';
    $labelTitle = __('Add list', 'wp-list-cpt');
    $labelBtn = __('Add');
    $action = '';
	
  }
	
}



?>

<div id="wpbody">
  <div id="wpbody-content" tabindex="0">
  
    <div class="wrap">
      <h2><?php echo $labelTitle; ?></h2>
     
<?php if ($msg != '') { ?>
      <div class="updated below-h2" id="message">
        <p><?php echo $msg; ?></p>
      </div>
<?php } ?>
     
      <form id="post" method="post" action="<?php echo $action; ?>" name="post">
<?php
wp_nonce_field( $this->plugin_nonce, $this->plugin_nonce . '_nonce' );
?>
        <div id="poststuff">
          <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
              <div id="titlediv">
                <div id="titlewrap">
                
                  <label id="title-prompt-text" class="screen-reader-text" for="title" ><?php echo __('Enter title here'); ?></label>
                  <input <?php echo $readonly; ?> id="title" type="text" autocomplete="off" value="<?php echo esc_attr($termLabel); ?>" size="30" name="post_title">                
                  <?php if ($showBtn) { ?><p class="submit"><input type="submit" value="<?php echo $labelBtn; ?>" class="button button-primary" id="submit" name="submit"></p><?php } ?>

                </div>
              </div>
            
            </div>
          </div>
        </div>
      </form>
  
    </div>
 
  </div>
</div>

