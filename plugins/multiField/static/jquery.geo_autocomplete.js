
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: JQUERY.GEO_AUTOCOMPLETE.JS
 *
 *	This script is a commercial software and any kind of using it must be 
 *	coordinate with Flynax Owners Team and be agree to Flynax License Agreement
 *
 *	This block may not be removed from this file or any other files with out 
 *	permission of Flynax respective owners.
 *
 *	Copyrights Flynax Classifieds Software | 2013
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
	$.fn.vsGeoAutoComplete = function(options){
		options = jQuery.extend({
			type: '*',
			id: false,
			field: false
		},options)

		var call = false;
		var store = new Array;
		var query = '';
		var obj = this;
		var set = false;
		var set_hidden = false;
		var cur_item = 0;
		var total = 0;
		var interface = '<div id="vs_geo_interface" class="geo_autocomplete hborder"></div>';
		var save_pos = 0;
		
		var key_enter = 13;
		var key_down = 40;
		var key_up = 38;
		var poss = 0;
		var timer = false;
		$('#vs_geo_interface').remove();
		
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
		
		$(obj).unbind('keyup').bind('keyup', function(e){
			var el = $(this);
			clearTimeout(timer);
			timer = setTimeout(function(){
				request(e, el);
			}, 500);
		});
		
		/* key handler */
		$(obj).bind('keyup click', function(e){
			if ( e.keyCode == key_enter )
			{
				if (cur_item != 0)
				{
					location.href = geo_clean_url.replace('[geo_url]', $('#ac_item_'+cur_item).attr('abbr'));
				}
			}
			else if ( e.keyCode == key_down )
			{
				if ( cur_item < total && $('#vs_geo_interface').css('display') == 'block')
				{
					cur_item++;
					poss += 24;
					drow();
				}
			}
			else if ( e.keyCode == key_up && $('#vs_geo_interface').css('display') == 'block' )
			{
				if (cur_item > 1)
				{
					cur_item--;
					poss -= 24;
					drow('up');
				}
			}
		});

		var request = function(e, el){

			if( (query != el.val() && e.keyCode != key_enter) || (e.keyCode == key_down && cur_item == 0) || e.type == 'click' )
			{
				if ( el.val().length < 3 )
				{
					$('#vs_geo_interface').hide();
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
					$('#vs_geo_interface').css({left: obj_poss.left, top: obj_poss.top}).css('margin-left', obj_margin);
					set = true;
				}
				
				if ( e.keyCode != key_down )
				{
					cur_item = 0;
				}
				
				/* do complete */
				$(obj).addClass('geo_autocomplete_load');
				
				if( call !== false )
				{
					call.abort();
				}

				call = $.getJSON(ac_geo_php, {str: el.val(), lang: rlLang}, function(response){
					if( response != '' && response != null )
					{
						store = response;
						var content = '<ul>';
						var index = 0;
						
						var query_arr = query.split(' ');

						for ( var i = 0; i < response.length; i++ )
						{
							var out = response[i]['name'];
							
							for ( var it = 0; it < query_arr.length; it++ )
							{
								if ( query_arr[it] != '' && query_arr[it].toLowerCase() != 'b' )
								{
									if ( response[i]['name'].toLowerCase().indexOf(query_arr[it].toLowerCase()) >= 0 )
									{
										var replace = '';
										
										var ix = response[i]['name'].toLowerCase().indexOf(query_arr[it].toLowerCase());
		
										for ( var j = 0; j< query_arr[it].length; j++ )
										{
											if ( response[i]['name'][ix] != query_arr[it][j] )
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
								content += '<li class="item" abbr="'+response[i]['path']+'" id="ac_item_'+index+'">'+out+'</li>';
							}
						}
						
						content += '</ul>';
						
						total = index;
						
						$('#vs_geo_interface').html(content);
						$('#vs_geo_interface').show();
			
						$('#vs_geo_interface div:not(.ac_header)').click(function(){							
							// clear save position
							save_pos = poss = 0;
						});

						$('#vs_geo_interface li.item').click(function(){
							location.href = geo_clean_url.replace('[geo_url]', $(this).attr('abbr'));
						});
		
						$('#vs_geo_interface li').mouseover(function(){
							$('#vs_geo_interface li').removeClass('highlight');
							el.addClass('highlight');
						});
		
						$('#vs_geo_interface li').mouseout(function(){
							$('#vs_geo_interface li').removeClass('highlight');
						});
		
						$('body').click(function(){
							$('#vs_geo_interface').hide();
							
							// clear save position
							save_pos = poss = 0;
						});
					}
					else
					{
						$('#vs_geo_interface').hide();
					}
					$(obj).removeClass('geo_autocomplete_load');
				});
				query = el.val();
			}
		};
		
		function drow(direction)
		{
			$('#vs_geo_interface li').removeClass('highlight');
			$('#ac_item_'+cur_item).addClass('highlight');
			
			$('#vs_geo_interface').animate({scrollTop:poss-48}, 100);
		}
	}
})(jQuery);
