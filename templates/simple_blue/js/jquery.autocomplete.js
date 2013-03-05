
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
			field: false
		},options)

		var query = '';
		var obj = this;
		var set = false;
		var cur_item = 0;
		var total = 0;
		var interface = '<div id="ac_interface"></div>';
		var save_pos = 0;
		var timer = 0;
		
		var key_enter = 13;
		var key_down = 40;
		var key_up = 38;
		var del_up = 46;
		var poss = 0;
		
		$('#ac_interface').remove();
		$(obj).attr('autocomplete', 'off');
		
		var request = function(e)
		{
			if( (query != $(obj).val() && e.keyCode != key_enter) || (e.keyCode == key_down && cur_item == 0) || e.type == 'click' )
			{
				if ( $(obj).val().length < 2 )
				{
					$('#ac_interface').hide();
					return false;
				}
				
				/* build interface */
				if ( !set )
				{
					$(obj).after(interface);
					set = true;
				}
				
				if ( e.keyCode != key_down )
				{
					cur_item = 0;
				}
				
				/* do complete */
				$.getJSON(ac_php, {mode: 'listing', item: $(obj).val(), field: options.field, type: options.type, lang: rlLang}, function(response){
					if( response != '' && response != null )
					{
						var content = '<ul>';
						var index = 0;
						var query_arr = query.split(' ');

						for ( var i = 0; i < response.length; i++ )
						{
							if ( response[i]['listing_title'] != 'listing' && response[i]['listing_title'] != '' )
							{
								var out = response[i]['listing_title'];
								
								for ( var it = 0; it < query_arr.length; it++ )
								{
									if ( query_arr[it] != '' && query_arr[it].toLowerCase() != 'b' && query_arr[it].length > 2 )
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
									content += '<li id="ac_item_'+index+'"><div class="ac-listing"><a title="'+view_details+'" href="'+response[i]['Listing_path']+'" target="_blank">'+out+'</a></div><div class="ac-category"><a title="'+response[i]['Category_name']+'" href="'+response[i]['Category_path']+'" target="_blank">'+response[i]['Category_name']+'</a></div><div class="clear"></div></li>';
								}
							}
						}
						
						content += '</ul>';
						
						total = index;
						
						$('#ac_interface').html(content);
						$('#ac_interface').show();
			
						$('#ac_interface div:not(.ac_header)').click(function(){							
							save_pos = poss = 0;
						});
		
						$('#ac_interface li').mouseover(function(){
							$(this).addClass('active');
						}).mouseout(function(){
							$(this).removeClass('active');
						});
					}
					else
					{
						$('#ac_interface').hide();
					}
				});
				query = $(obj).val();
			}
		}
		
		/* keydown handler */
		$(obj).bind('keyup', function(e){
			console.log(e.keyCode)
			if ( e.keyCode == key_enter )
			{
				if (cur_item != 0)
				{
					location.href = $('#ac_item_'+cur_item).find('a').attr('href');
				}
			}
			else if ( e.keyCode == key_down )
			{
				if ( cur_item < total && $('#ac_interface').is(':visible') )
				{
					cur_item++;
					poss += 31;
					drow();
				}
			}
			else if ( e.keyCode == key_up && $('#ac_interface').is(':visible') )
			{
				if (cur_item > 1)
				{
					cur_item--;
					poss -= 31;
					drow('up');
				}
			}
			else if ( e.keyCode == del_up )
			{
				$('#ac_interface').hide();
				$('#ac_interface ul').remove();
			}
			
			clearTimeout(timer);
			timer = setTimeout(function(){
				request(e);
			}, 600);
		})
		
		$(obj).click(function(){
			if ( $('#ac_interface ul').length > 0 )
			{
				$('#ac_interface').show();
			}
		});
		
		$('body').click(function(e){
			if ( $(e.target).attr('id') != 'autocomplete' && $(e.target).parent().attr('class') != 'ac-category' )
			{
				$('#ac_interface').hide();
			}
		});
		
		function drow(direction)
		{
			$('#ac_interface li.active').removeClass('active');
			$('#ac_item_'+cur_item).addClass('active');
			
			$('#ac_interface').animate({scrollTop:poss-62}, 100);
		}
	}
})(jQuery);