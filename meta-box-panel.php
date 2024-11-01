<?php
if ( !function_exists('add_action') ) :
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
endif;


global $post;

$selectedItem = '';

parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $queries);
if (isset($queries[$this->postType . '-category'])) {

  $term = $this->wp_list_get_term($this->postType . '-category',  $queries[$this->postType . '-category']);
  if ($term) {
	$selectedItem = $term->term_id;
  }

}

	
$terms = wp_get_post_terms( $post->ID, $this->postType . '-category');
if (count($terms) > 0) {
  $selectedItem = $terms[0]->term_id;
}

$list = get_terms( $this->postType . '-category', array( 'hide_empty' => false ) );

?>


<div id="taxonomy-box">

  <div id="taxonomy-item">

    <label for="listitem"><?php echo __( 'Title' ); ?><span>&nbsp;*</span></label>
    <select name="listitem" id="listitem">
      <option value=""></option>
<?php 

		if ($list) {
		  foreach($list as $k=>$item) {
			  
			$selected = '';
			if ($item->term_id == $selectedItem) {
			  $selected = ' selected="selected" ';
			}
			  
		    echo '<option ' . $selected . ' value="' . $item->term_id . '">' . $item->name . '</option>';
		  }
		}

?>
    </select>
    
  </div>

</div>