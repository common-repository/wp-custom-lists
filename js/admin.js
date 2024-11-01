jQuery(function($){
   
  if ($('.wp-list-table').length || $('#listitem').length) {
    $('body.post-type-list #toplevel_page_custom-list').addClass('current').addClass('wp-menu-open').addClass('wp-has-current-submenu');
    $('body.post-type-list #toplevel_page_custom-list li.wp-first-item a.wp-first-item, body.post-type-list #toplevel_page_custom-list li.wp-first-item').addClass('current').addClass('wp-menu-open');	
  }

  
  if ($('#listitem').length) {

    var $xplRefresh;

    $('#post').on('submit', function(e){
	  return actionSubmit();
    });

    //A améliorer !!!!
    $('#delete-action a').on('click', function(e){
	  return actionSubmit();
    });

  }

  //from wp-admin/js/post.php
  function wptitlehintCustom(id) {
	  id = id || 'title';

	  var title = $('#' + id), titleprompt = $('#' + id + '-prompt-text');

	  if (title.length && titleprompt.length) {

		if ( '' === title.val() )
			titleprompt.removeClass('screen-reader-text');
  
		titleprompt.click(function(){
			$(this).addClass('screen-reader-text');
			title.focus();
		});
  
		title.blur(function(){
			if ( '' === this.value )
				titleprompt.removeClass('screen-reader-text');
		}).focus(function(){
			titleprompt.addClass('screen-reader-text');
		}).keydown(function(e){
			titleprompt.addClass('screen-reader-text');
			$(this).unbind(e);
		});
	  
	  }
	  
  };

  wptitlehintCustom();


  function actionSubmit() {

	if ($('#listitem').val() == '') {
	   
	   $('#taxonomy-box').parent().parent().removeClass('taxonomy-required');
	   
	   if ($('#message').length) {
	     $('#message').remove();
	   }
	   
	   $('<div class="error" id="message"><p>' + $list_error_msg + '</p></div>').insertAfter('h2');
	   $('#taxonomy-box').parent().parent().addClass('taxonomy-required');
	   
	   //$('.spinner').hide();
	   //$('#publish').removeClass('button-primary-disabled');
			   
	   return false; 
	}
  
  }

});
