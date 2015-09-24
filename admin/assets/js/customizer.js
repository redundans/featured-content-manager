var $ = jQuery.noConflict(), timer;

$(function() {

	$('body').ready(function(){
	$('.sortable').sortable({
		connectWith: '.connectable',
		opacity: 0.5,
		handle: '.fcm-title',
		placeholder: 'placeholder',
		receive: function( event, ui ) {
			var area = $(ui.item).parents('ul').data('area');
			var parent = $(ui.item).parents('ul.sortable li').find('input[name^=post_id]').val();

			$(ui.item).find('input[name^=area]').first().val(area);

			if( parent === undefined ){
				$(ui.item).children('.fcm-inside').find('input[name^=child]').val('false');
			} else {
				$(ui.item).find('input[name^=child]').val( 'true' );
			}
		},
		sort: function( event, ui ) {
		},
		stop: function( event, ui ) {
			update_preview();
			changeState = true;
		}
	}).disableSelection();
	});
	function update_preview(){

		$('ul.featured-area').each( function(index) {
			var customize_control = $(this).parents('li.customize-control');
			var customize_setting = $(customize_control).children('input.customizer-setting');
			var featured_item_index = 1;

			// ReIndexing Areas and menu_order;
			$(this).children('li').each( function(i) {
				$(this).first('fieldset').find('input, textarea').each( function(x) {
					var name = $(this).attr('name'), pattern = /\[(.*?)\]/g;
					$(this).attr('name', name.replace(/\[(.+?)\]/g, '['+featured_item_index+']') );
				});
				$(this).first('fieldset').find('input[name^=menu_order]').val(i);

				featured_item_index = featured_item_index + 1;
				$(this).find('li').each( function(y) {
					$(this).first('fieldset').find('input, textarea').each( function(x) {
						var name = $(this).attr('name'), pattern = /\[(.*?)\]/g;
						$(this).attr('name', name.replace(/\[(.+?)\]/g, '['+featured_item_index+']') );
					});
					$(this).first('fieldset').find('input[name^=menu_order]').val(y);
					featured_item_index = featured_item_index + 1;
				});
			});

			console.log($(customize_control).find('ul.featured-area:first :input'));

			var serialized_form = $(customize_control).find('ul.featured-area:first :input').serialize();
			if( $(customize_setting).val() != serialized_form ){
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: ajaxurl,
					data: serialized_form,
					success: function(data){
						if(data.error === false){
							$(customize_setting).val( serialized_form );
							$(customize_setting).trigger("change");
						}
					}
				});
			}
		});
	}

	function start_update_preview_timer(){
		clearTimeout(timer);
		var ms = 500; // milliseconds
		var val = this.value;
		timer = setTimeout(function() {
			update_preview();
		}, ms);
	}

	$(document).on('click', '.sidebar-name-arrow, .fcm-title h4', function(){
		$(this).closest('li').toggleClass('closed');
	});

	$(document).on('click', '.sidebar-parent-arrow', function(){
		$(this).parents('li').toggleClass('parent');
	});

	$(document).on('click', '.remove', function(){
		$(this).closest('li').remove();
		update_preview();
	});

	$(document).on('click', '.sidebar-delete-icon', function(){
		$(this).closest('li').remove();
		update_preview();
	});

	$('body').on('click', '.edit-thumbnail', function(e) {

		var send_attachment_bkp = wp.media.editor.send.attachment, container = $(this).closest('li');

		wp.media.editor.send.attachment = function(props, attachment) {
			$(container).find('.edit-thumbnail').first().html( '<img src="' + attachment.url + '">' );
			$(container).find('input[name^=post_thumbnail]').first().val( attachment.id );
			$(container).find('input[name^=post_thumbnail]').first().trigger("change");
			$(container).find('.remove-thumbnail').show();

			wp.media.editor.send.attachment = send_attachment_bkp;
		};

		wp.media.editor.open();

		return false;
	});

	$('body').on('click', '.remove-thumbnail', function(e) {
		var container = $(this).closest('li');

		$(container).find('.edit-thumbnail').first().html( 'Ange utvald bild' );
		$(container).find('input[name^=post_thumbnail]').first().val( '' );
		$(container).find('input[name^=post_thumbnail]').first().trigger("change");
		$(container).find('.remove-thumbnail').first().hide();
	});

	$('body.wp-customizer').on('click', function(event){
		var target = $(event.target);
		if (target.closest('div#available-featured-items').length) {
			return;
		} else if ( $(target).hasClass('sidebar-delete-icon') ){
			return;
		} else if( $('body.wp-customizer').hasClass('adding-featured-items') ) {
			$('body').removeClass('adding-featured-items');
			$('.adding-featured-items-target').removeClass('adding-featured-items-target');
		} else if( $(target).hasClass('add-featured-items') ) {
			// console.log($(event.target).parent().children('ul.featured-area'));
			$(event.target).parent().parent().children('ul.featured-area').addClass('adding-featured-items-target');
			$('body').addClass('adding-featured-items');
		}
	});


	$('body').on('click', 'li.featured-area-search-result-item', function(event){
		var post_id = $(this).data('id');
		var target = $('.adding-featured-items-target');

		var data = {
			action: 'get_post',
			post_id: post_id,
			target: ''
		};

		$.post( ajaxurl, data, function(response) {
			var new_index = $('.sortable li').length+1, template;
			template = wp.template( 'featured-item' );

			response.post.ID = 'new';
			response.index = new_index;

			var output = template( response );

			$(target).append( output );
			update_preview();
		}, "JSON");
	});

	function start_search_timer(){
		clearTimeout(timer);
		var ms = 500; // milliseconds
		var val = this.value;
		timer = setTimeout(function() {
			search_feature_item();
		}, ms);
	}

	function search_feature_item(){
		var search_term = $('#featured-items-search').val(), template, output;

		if( search_term.length <= 2 )
			return;

		var data = {
			action: 'search_content',
			search_term: search_term
		};

		$.post( ajaxurl, data, function(response) {
			if( !response.error ){
				$('#featured-items-filter-result ul').html('');
				template = wp.template( 'featured-area-search-result-item' );
				$(response.result).each( function(index){
					output = template( this );
					$('#featured-items-filter-result ul').append(output);
				});
			}
		}, "JSON");
	}

	$(document).on('keyup', '#featured-items-search', start_search_timer );
	$(document).on('keyup', '.sortable li input[type=text], .sortable li textarea', start_update_preview_timer );
	$(document).on('change', '.sortable li input[type=hidden]', start_update_preview_timer );

});