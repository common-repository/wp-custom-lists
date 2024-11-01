(function() {
	tinymce.PluginManager.add('wp_list_tc_button', function( editor, url ) {
											
		editor.addButton( 'wp_list_tc_button', {
            title: editor.getLang('wplistlang.insert_list'),
            icon: 'icon dashicons-before dashicons-list-view',
			onclick: function() {

			  editor.windowManager.open( {
			    title: editor.getLang('wplistlang.insert_list'),
				body: [{
                  type: 'listbox', 
                  name: 'wplist',
			      id: 'wplist',
                  label: editor.getLang('wplistlang.insert_list'), 
                  values: wplist_tinymce_custom_getValues()
				}],
				onsubmit: function( e ) {
				  //console.log(e);
				  var $tinyData = e.data.wplist;
				  if ($tinyData != '') {
				    editor.insertContent('[customlist list="' + e.data.wplist + '"]');
				  }
				}
			  });
	
			}
		});
	});
})();
