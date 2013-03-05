
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: {version}
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: JQUERY.AUTOCOMPLETE.JS
 *
 *	The software is a commercial product delivered under single, non-exclusive, 
 *	non-transferable license for one domain or IP address. Therefore distribution, 
 *	sale or transfer of the file in whole or in part without permission of Flynax 
 *	respective owners is considered to be illegal and breach of Flynax License End 
 *	User Agreement. 
 *
 *	You are not allowed to remove this information from the file without permission
 *	of Flynax respective owners.
 *
 *	Flynax Classifieds Software 2013 |  All copyrights reserved. 
 *
 *	http://www.flynax.com/
 *
 ******************************************************************************/

/**
*
* autocomplete plugin
*
* @package jQuery
* 
**/
(function($) {
	$.fn.vsAutoComplete = function(options){
		options = jQuery.extend({
			type: '*',
			id: false,
			field: false
		},options)

		var store = new Array;
		var query = '';
		var obj = this;
		var set = false;
		var set_hidden = false;
		var cur_item = 0;
		var total = 0;
		var interface = '<div id="vs_interface" class="header_autocomplete"></div>';
		var save_pos = 0;
		
		var key_enter = 13;
		var key_down = 40;
		var key_up = 38;
		var poss = 0;

//		$('body').click(function(){
//			if ( vs_pressKey != key_enter )
//			{
//				cur_item = 0;
//			}
//		});
		
		$('#vs_interface').remove();
		
		/* form submit handler */
		$(obj).attr('autocomplete', 'off').focus(function(){
			if (cur_item != 0)
			{
				$('form').submit(function(){
					return false;
				});
			}
		}).focusout(function(){
			$('form').submit(function(){
				this.submit();
			});
		});
		
		/* key handler */
		$(obj).bind('keyup click', function(e){
			if ( e.keyCode == key_enter )
			{
				if (cur_item != 0)
				{
					location.href = $('#ac_item_'+cur_item).parent().attr('href');
				}
			}
			else if ( e.keyCode == key_down )
			{
				if ( cur_item < total && $('#vs_interface').css('display') == 'block')
				{
					cur_item++;
					poss += 24;
					drow();
				}
			}
			else if ( e.keyCode == key_up && $('#vs_interface').css('display') == 'block' )
			{
				if (cur_item > 1)
				{
					cur_item--;
					poss -= 24;
					drow('up');
				}
			}

			if( (query != $(this).val() && e.keyCode != key_enter) || (e.keyCode == key_down && cur_item == 0) || e.type == 'click' )
			{
				if ( $(this).val().length < 2 )
				{
					$('#vs_interface').hide();
					return false;
				}
				
				/* build interface */
				if ( !set )
				{
					$(obj).after(interface);
					var obj_margin = $(obj).css('margin-left');
					var obj_poss = $(obj).position();
					var obj_width = $(obj).width();
					
					width_diff = $.browser.msie ? 20 : 19;
					$('#vs_interface').css({left: obj_poss.left, top: obj_poss.top+2}).css('margin-left', obj_margin).width(obj_width+width_diff);
					set = true;
				}
				
				if ( e.keyCode != key_down )
				{
					cur_item = 0;
				}
				
				/* do complete */
				$(obj).addClass('header_autocomplete_load');
				
				$.getJSON(ac_php, {str: $(this).val(), field: options.field}, function(response){
					if( response != '' && response != null )
					{
						store = response;
						var content = '<div class="ac_header">'+pre_search+'</div><table style="table-layout:fixed;" class="sTable">';
						var index = 0;
						
						var query_arr = query.split(' ');

						for ( var i = 0; i < response.length; i++ )
						{
							var out = response[i]['listing_title'];
							
							for ( var it = 0; it < query_arr.length; it++ )
							{
								if ( query_arr[it] != '' && query_arr[it].toLowerCase() != 'b' )
								{
									if ( response[i]['listing_title'].toLowerCase().indexOf(query_arr[it].toLowerCase()) >= 0 )
									{
										var replace = '';
										
										var ix = response[i]['listing_title'].toLowerCase().indexOf(query_arr[it].toLowerCase());
		
										for ( var j = 0; j< query_arr[it].length; j++ )
										{
											if ( response[i]['listing_title'][ix] != query_arr[it][j] )
											{
												replace += query_arr[it].charAt(j).toUpperCase();
											}
											else
											{
												replace += query_arr[it].charAt(j);
											}
											
											ix++;
										}

										var search = new RegExp(query_arr[it], 'gi');
										out = out.replace(search, '<b>'+replace+'</b>' );
									}
								}
							}
							
							if ( replace != '' )
							{
								index++;
								content += '<tr><td class="main"><a style="text-decoration: none;" title="'+view_details+'" href="'+response[i]['Listing_path']+'" target="_blank"><div class="item" id="ac_item_'+index+'">'+out+'</div></a></td><td class="cat_td"><a class="cat_link" title="'+response[i]['Category_name']+'" href="'+response[i]['Cat_path']+'">'+response[i]['Category_name']+'</a></td><td style="width: 75px;"><span title="'+join_date+'" class="date">('+response[i]['Date']+')</span></td></tr>';
							}
						}
						
						content += '</table>';
						
						total = index;
						
						$('#vs_interface').html(content);
						$('#vs_interface').show();
			
						$('#vs_interface div:not(.ac_header)').click(function(){							
							// clear save position
							save_pos = poss = 0;
						});
		
						$('#vs_interface tr').mouseover(function(){
							$('#vs_interface tr').css('background-color', 'white');
							$(this).css('background-color', '#f7f8d9');
						});
		
						$('#vs_interface tr').mouseout(function(){
							$(this).css('background-color', 'white');
						});
		
						$('body').click(function(){
							$('#vs_interface').hide();
							
							// clear save position
							save_pos = poss = 0;
						});
					}
					else
					{
						$('#vs_interface').hide();
					}
					$(obj).removeClass('header_autocomplete_load');
				});
				query = $(this).val();
			}
		});
		
		function drow(direction)
		{
			$('#vs_interface tr').css('background-color', 'white');
			$('#ac_item_'+cur_item).parent().parent().parent().css('background-color', '#f7f8d9');
			
			$('#vs_interface').animate({scrollTop:poss-48}, 100);
		}
	}
})(jQuery);