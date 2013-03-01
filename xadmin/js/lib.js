
/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: LIB.JS
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
 *	Flynax Classifieds Software 2013 | All copyrights reserved.
 *	
 *	http://www.flynax.com/
 ******************************************************************************/

var flynaxClass = function(){
	
	/**
	* youTube embed code pattern
	**/
	this.youTubeFrame = '<iframe width="{width}" height="{height}" src="http://www.youtube.com/embed/{key}" frameborder="0" allowfullscreen></iframe>';
	
	/**
	* load youTube video
	*
	* @param string key - youtube video key
	* @param selctor dom - dom element to assing video
	**/
	this.loadVideo = function(key, dom){
		$(dom).append(this.youTubeFrame.replace('{key}', key).replace('{width}', '100%').replace('{height}', 400));
	};
	
	/**
	* tabs handler
	**/
	this.tabs = function(unbind){
		if ( !unbind )
		{
			$('ul.tabs li').unbind('click');
		}
		
		$('ul.tabs li').click(function(){
			var key = $(this).attr('lang');
			
			$(this).parent().find('li.active').removeClass('active');
			$(this).addClass('active');
			
			$(this).parent().parent().find('div.tab_area').hide();
			$(this).parent().parent().find('div.'+key).show();
			$(this).parent().parent().find('div.'+key+' input, div.'+key+' textarea').focus();
		});
	};
	
	/**
	* categories tree level loader
	**/
	this.treeLoadLevel = function(tpl, callback, selector){
		selector = selector ? selector+' ' : '';
		$(selector+'div.tree ul li>img:not(.no_child)').unbind('click').click(function(){
			if ( $(this).hasClass('done') )
			{
				var img = this;
				$(this).parent().find('ul:first').fadeToggle(function(event){
					if ( $(this).is(':visible') )
					{
						$(img).addClass('opened');
					}
					else
					{
						$(img).removeClass('opened');
					}
				});
			}
			else
			{
				var id = parseInt($(this).parent().attr('id').split('_')[2]);
				var postfix = $(this).parent().parent().attr('lang');
				xajax_getCatLevel(id, false, tpl, callback, postfix);
				$(this).addClass('done').parent().find('span.tree_loader').fadeIn('fast');
			}
		});
		
		$(selector+'div.tree span.tmp_info a').click(function(){
			$(this).parent().hide();
			$(this).parent().next().show();
		});
		$(selector+'div.tree span.tmp_input img').click(function(){
			$(this).parent().hide();
			$(this).parent().prev().show();
		});
	};
	
	/**
	* slide to
	**/
	this.slideTo = function(selector){
		var top_offset;
		var bottom_offset;
		
		if ( self.pageYOffset )
		{
			top_offset = self.pageYOffset;
		}
		else if ( document.documentElement && document.documentElement.scrollTop )
		{
			top_offset = document.documentElement.scrollTop;// Explorer 6 Strict
		}
		else if ( document.body )
		{
			top_offset = document.body.scrollTop;// all other Explorers
		}
	
		var pos = $(selector).offset();
	 	bottom_offset = top_offset + $(window).height();

		if ( top_offset > pos.top || pos.top > bottom_offset || (pos.top + $(selector).height()) > bottom_offset )
		{
			$('html, body').stop().animate({scrollTop:pos.top - 10}, 'slow');
		}
	};
	
	/**
	* ext modal window
	**/
	this.extModal = function(parent, key){
		if ( !list )
		{
			console.log('@list - object should be defined as list of items');
			return;
		}
		
		if ( !parent || !key )
			return;
		
		/* remove exist modal windows */
		$('div.flExtModal').remove();
		Ext.QuickTips.getQuickTip().doHide();
		
		/* build */
		var poss = $(parent).offset();
		var top = poss.top+16;
		var left = poss.left+17;
		var html = '<div class="flExtModal" style="left: '+left+'px;top: '+top+'px;"><div><ul>';
		for (var i=0; i<list.length; i++ )
		{
			html += '<li><a href="'+list[i].href.replace('{key}', key)+'">'+list[i].text+'</a></li>';
		}
		html += '</ul></div></div>';
		
		/* append */
		$('body').append(html);
		var item_width = $('div.flExtModal').width();
		var document_width = $(document).width();
		if ( left + item_width > document_width )
		{
			var diff = left - ((left + item_width) - document_width);
			$('div.flExtModal').css('left', diff);
		}
		$('div.flExtModal').show();
		
		$(document).click(function(event){
			var close = true;
			
			$(event.target).parents().each(function(){
				if ( $(this).attr('class') == 'flExtModal' )
				{
					close = false;
				}
			});
			
			if ( $(event.target).attr('class') == 'build' )
			{
				close = false;
			}
			
			if ( close )
			{
				$('div.flExtModal').remove();
			}
		});
	};
	
	/**
	* open tree levels
	**/
	this.openTree = function(selected, points){
		/* post handler */
		if ( selected.length > 0 )
		{
			if ( points.length > 0 )
			{
				for ( var i=0; i<points.length; i++ )
				{
					$('#tree_cat_'+points[0]+'>img:first').trigger('click');
				}
				points.splice(0, 1);
			}
			else
			{
				for ( var i=0; i<selected.length; i++ )
				{
					$('#tree_cat_'+selected[i]+'>label>input').attr('checked', true).trigger('click');
					$('#tree_cat_'+selected[i]+'>label>input').attr('checked', true);
				}
				selected = false;
			}
		}
	};
	
	/**
	* content type switcher
	**/
	this.switchContentType = function(selector, fields){
		if ( !selector || !fields )
			return;
		
		$(document).ready(function(){
			$(selector+'.checked').click();
		});
		
		$(selector).bind('change', function(){
			var type = $(this).val();
			switch (type){
				case 'html':
					var configs = rlConfig['fckeditor_bar'] == 'Basic' ? {
						toolbar: [
							['Source', '-', 'Bold', 'Italic', 'Underline', 'Strike'],
							['Image', 'Flash', 'Link', 'Unlink', 'Anchor'],
							['Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat'],
							['TextColor', 'BGColor']
						]
					} : false;
					configs.language = rlConfig['lang'];
					
					var nl_pattern = /[\t\n\r]/gi;
					for (var i=0;i<fields.length;i++)
					{
						var field = fields[i];

						CKEDITOR.replace(field, configs);
						var instance = CKEDITOR.instances[field];
						var code = instance.getData();
						if ( !code )
						{
							code = $('#'+field).prev().html();
						}
						code = code.replace(nl_pattern, '<br />');
						instance.setData(code);
						
						$('#'+field).parent().addClass('ckeditor');
					}
					break;
					
				case 'plain':
					var nl_pattern = new RegExp('(<br\\s\/>)', 'gi');
					for (var i=0;i<fields.length;i++)
					{
						var field = fields[i];
						
						var instance = CKEDITOR.instances[field];
						var code = instance.getData();
						code = code.replace(nl_pattern, '').replace(/<.*?>/g, '');
						instance.destroy();
						$('#'+field).val(code);
						
						$('#'+field).parent().removeClass('ckeditor');
					}
					break;
			}
		});
	};
	
	/**
	* swith textarea to html editor
	*
	* @param array fields - array of the form fields
	*
	**/
	this.htmlEditor = function(fields){
		if ( !fields )
			return;
	
		var configs = rlConfig['fckeditor_bar'] == 'Basic' ? {
			toolbar: [
				['Source', '-', 'Bold', 'Italic', 'Underline', 'Strike'],
				['Image', 'Flash', 'Link', 'Unlink', 'Anchor'],
				['Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat'],
				['TextColor', 'BGColor']
			]
		} : false;
		configs.language = rlConfig['lang'];
		configs.height = 160;
		
		var nl_pattern = /[\t\n\r]/gi;
		for (var i=0;i<fields.length;i++)
		{
			var field = fields[i];
			CKEDITOR.replace(field, configs);
			
			var code = $('#'+field).prev().html();
			//code = code.replace(nl_pattern, '<br />');
			var instance = CKEDITOR.instances[field];
			instance.setData(code);
		}
	};
	
	/**
	* qtips handler
	**/
	this.qtip = function(){
		$(document).ready(function(){
			$('.qtip').each(function(){
				$(this).qtip({
					width: 'auto',
					content: $(this).attr('title'),
					show: 'mouseover',
					hide: 'mouseout',
					position: {
						corner: {
							target: 'topRight',
							tooltip: 'bottomLeft'
						}
					},
					style: {
						width: 'auto',
						background: '#858585',
						color: 'white',
						border: {
							width: 7,
							radius: 5,
							color: '#858585'
						},
						tip: 'bottomLeft'
					}
				}).attr('title', '');
			});
		});
	}
	
	/**
	* get page hash, # removed
	**/
	this.getHash = function(){
		var hash = window.location.hash;
		return hash.substring(1);
	};
	
	/**
	* print confirm window
	**/
	this.confirm = function(message, method, params, callback){
		Ext.MessageBox.confirm(lang['confirm'], message, function(btn){
			if ( btn == 'yes' )
			{
				method(params);
			}
			else
			{
				if ( callback )
				{
					callback();
				}
			}
		});
	}
	
	/**
	* phone field control
	**/
	this.phoneFieldControls = function(){
		var base = this;
		
		this.code = function(){
			if ( $('input[name="phone[code]"]').is(':checked') )
			{
				$('.phone_code_prev').fadeIn();
			}
			else
			{
				$('.phone_code_prev').fadeOut();
			}
		};
		
		this.area = function(){
			var val = parseInt($('input[name="phone[area_length]"]').val());
			var set = val ? Array(val+1).join('x') : '';
			set = set ? '('+set+')' : '';
			$('#phone_area_preview').html(set);
			$('#phone_area_input').attr('maxlength', val).val('');
		};
		
		this.number = function(){
			var val = parseInt($('input[name="phone[phone_length]"]').val());
			if (val)
			{
				var set = '';
				for(var i=1; i <= val; i++)
				{
					set += i;
					if ( i == 3 )
					{
						set += '-';
					}
				}
			}
			else
			{
				var set = '';
			}
			$('#phone_number_preview').html(set);
			$('#phone_number_input').attr('maxlength', val).val('');
		};
		
		this.ext = function(){
			if ( $('input[name="phone[ext]"]').is(':checked') )
			{
				$('.phone_ext').fadeIn();
			}
			else
			{
				$('.phone_ext').fadeOut();
			}
		};
		
		$('input[name="phone[code]"]').click(function(){
			base.code();
		});
		this.code();
		
		$('input[name="phone[area_length]"]').keyup(function(){
			base.area();
		});
		this.area();
		
		$('input[name="phone[phone_length]"]').keyup(function(){
			base.number();
		});
		this.number();
		
		$('input[name="phone[area_length]"], input[name="phone[phone_length]"]').focus(function(){
			$(this).select();
		});
		
		$('input[name="phone[ext]"]').click(function(){
			base.ext();
		});
		this.ext();
	}
	
	/**
	* phone field manager
	**/
	this.phoneField = function(){
		var deny_codes = [9, 16];
		
		$(document).ready(function(){	
			$('span.phone-field input').keyup(function(event){
				if ( deny_codes.indexOf(event.keyCode) >= 0 )
				{
					return;
				}
					
				if ( $(this).val().length >= parseInt($(this).attr('maxlength')) )
				{
					$(this).next('input').focus();
				}
				
				if ( $(this).val().length == 0 && event.keyCode == 8)
				{
					$(this).prev('input').focus().select();
				}
			});
		});
	}
	
	/**
	* build copy phrases icon
	**/
	this.copyPhrase = function(){
		var icon = '<img alt="'+lang['ext_copy_phrase_to_lang']+'" title="'+lang['ext_copy_phrase_to_lang']+'" title="" src="'+rlConfig['tpl_base']+'img/blank.gif" alt="" class="copy-phrase" />';
		
		if ( $('table.form ul.tabs > li').length > 1 )
		{
			$('table.form ul.tabs').each(function(){
				var input = $(this).parent().find('div.tab_area:first input[type=text]:first, div.tab_area:first textarea:first');
				if ( input.length > 0 )
				{
					var width = $(input).width();
					$(input).attr('class', '').width(width);
					$(input).after(icon);
					$(input).next().click(function(){
						var val = $(this).prev().val();
						if ( val )
						{
							$(this).prev().parent().parent().find('div.tab_area:not(:first) input[type=text], div.tab_area:not(:first) textarea').val(val);
							printMessage('notice', lang['ext_copy_phrase_done']);
						}
					});
				}
			});
		}
	}
};

var flynax = new flynaxClass();

/**
*
* jQuery categoroes slider plugin by Flynax
*
**/
(function($){
	$.flPhrase = function(el, options){
		var base = this;
		
		base.block_width = 0;
		base.position = 0;
		
		// access to jQuery and DOM versions of element
		base.$el = $(el);
		base.el = el;

		// add a reverse reference to the DOM object
		base.$el.data("flPhrase", base);

		base.init = function(){
			base.options = $.extend({},$.flPhrase.defaultOptions, options);

			// initialize working object id
			if ( $(base.el).attr('id') )
			{
				base.options.id = $(base.el).attr('id');
			}
			else
			{
				$(base.el).attr('id', base.options.id);
			}

			var key = base.options.key ? base.options.key : $(base.el).attr('accesskey');
			var lang = base.options.lang ? base.options.lang : $(base.el).attr('lang');
			
			$.getJSON("controllers/jsonResponse.php?q=phrase", {key: key, lang: lang}, function(response){
				if ( response )
				{
					$(base.el).val(response);
				}
			});
		};
		
		// run initializer
		base.init();
	};

	$.flPhrase.defaultOptions = {
		key: false,
		lang: false
	};

	$.fn.flPhrase = function(options){
		return this.each(function(){
			(new $.flPhrase(this, options));
		});
	};

})(jQuery);

/* blocks collapser */
$(document).ready(function(){
	$('.block table.header td.center div.collapse').click(function(){
		var this_obj = $(this).parent().parent().parent().parent().next();
		var this_key = $(this_obj).attr('lang');
		if ( $(this_obj).is(':visible') )
		{
			$(this_obj).slideUp('normal', function(){
				/* resize home page sliders */
				slidersResize();
			});
			$(this).addClass('collapse_hover');
			createCookie('ap_blocks_'+this_key, 'hide', 30);
		}
		else
		{
			$(this_obj).slideDown('normal', function(){
				/* resize home page sliders */
				slidersResize();
			});
			$(this).removeClass('collapse_hover');
			eraseCookie('ap_blocks_'+this_key);
		}
	});
});

/* menu handler */
var menu_in_progress = false;
var slide_popup_timeout = 0;
var slide_popup_item = '';
var slide_popup_obj = new Object();

$(document).ready(function(){
	/* create cookie entry for COMMON section */
	if ( !readCookie('adMenu_1') )
	{
		createCookie('adMenu_1', 'show', 31);
	}
	
	/* slide control */
	$('#mode_switcher').click(function(){
		if ( menu_in_progress )
		{
			return false;
		}
		
		menu_in_progress = true;
		
		if ( $('.ms_container:visible').length == 0 )
		{
			menu_handler();
		}
		else
		{
			$('.ms_container:visible').addClass('tmp_visible');
			$('.ms_container:visible:not(:last)').slideUp('normal');
			$('.ms_container:visible:last').slideUp('normal', function(){
				menu_handler();
			});
		}
	});
	
	var slide_popup_source = ' \
	<div class="popup"> \
		<div class="caption">[name]</div> \
		<div class="body">[body]</div> \
		<div class="footer"></div> \
	</div>';
	
	/* slide mode control */
	$('#mmenu_slide div.scaption').mouseenter(function(){
		var item_id = $(this).attr('id');
		
		if ( slide_popup_timeout > 0 && slide_popup_item == item_id)
		{
			clearTimeout(slide_popup_timeout);
		}
		else
		{
			if ( slide_popup_obj.length > 0 && slide_popup_timeout > 0 )
			{
				clearTimeout(slide_popup_timeout);
				menu_popup_fadeout(slide_popup_obj);
			}
			
			slide_popup_item = item_id;
			slide_popup_obj = $(this);
			
			$(this).addClass('hover');

			if ( !$(this).hasClass('active') )
			{
				var pos = $(this).find('div.outer div').css('backgroundPosition').split(' ');
				var new_pos = '-18px '+ pos[1];
				$(this).find('div.outer div').css('background-position', new_pos);
			}
			
			/* popup */
			var item_key = $(this).attr('lang');
			var items_list = '';
			
			if ( apMenu[item_key] )
			{
				for (var i in apMenu[item_key])
				{
					var link = '';
					var class_name = '';
					if ( apMenu[item_key][i].Name != undefined )
					{
						link = rlUrlHome +'index.php?controller='+ apMenu[item_key][i]['Controller'];
						link += apMenu[item_key][i]['Vars'] != '' ? '&amp;'+ apMenu[item_key][i]['Vars'] : '';
						class_name = apMenu[item_key][i]['Active'] ? ' class="active_item"' : '';
						items_list += '<div'+class_name+'><a title="'+apMenu[item_key][i]['Name']+'" href="'+ link +'">'+apMenu[item_key][i]['Name']+'</a></div>';
					}
				}
			}
			
			var slide_popup_copy = slide_popup_source.replace('[name]', apMenu[item_key]['section_name']).replace('[body]', items_list);
			$(this).after(slide_popup_copy);
		}
	}).mouseleave(function(){
		var self = this;
		
		slide_popup_timeout = setTimeout(function(){
			menu_popup_fadeout(self)
		}, 10);
		
		/* popup events handler unbind */
		$(this).next().unbind();
		
		/* popup events handler bind */
		$(this).next().mouseenter(function(){
			clearTimeout(slide_popup_timeout);
		}).mouseleave(function(){
			slide_popup_timeout = setTimeout(function(){
				menu_popup_fadeout(self)
			}, 300);
		});
	});
});

var menu_popup_fadeout = function(obj){
	slide_popup_timeout = 0;
	
	$(obj).next().remove();
	$(obj).removeClass('hover');
	
	if ( !$(obj).hasClass('active') )
	{
		var pos = $(obj).find('div.outer div').css('backgroundPosition').split(' ');
		var new_pos = '0 '+ pos[1];
		$(obj).find('div.outer div').css('background-position', new_pos);
	}
}

var hideNotices = function(){
	$('div#system_message>div').fadeOut('slow');
}

var slidersResize = function(width){
	if ( width )
	{
		var diff = Math.ceil(width);
	}
	else
	{
		var diff = menu_collapsed ? 61 : 221;
	}
	var sliders_width = Math.floor($(window).width()) - diff;
	$('div#sliders_container').width(sliders_width);
	$('#header_sliders').show();
}

var menu_handler = function(){
	/* expand */
	if ( menu_collapsed )
	{
		$('#mmenu_slide').fadeOut('normal');
		$('#logo').animate({width: 123});
		$('#outer_logo').animate({paddingLeft: 45});
		
		$('.header_left, .middle_left').animate({width: 221}, {
  			step: function(now, fx) {
  				/* resize home page sliders */
    			slidersResize(now);
  			},
  			complete: function(){
  				$('#sidebar').css({width: 221});
	
				/* resize grid */
				if ( grid[0] )
				{
					for( var i = 0; i < grid.length; i++ )
					{
						grid[i].setSize($(window).width() - 60 - $('#sidebar').width(), false);
					}
				}
				
				/* resize photos area (if exist) */
				if ( typeof(setPositions) == 'function' )
				{
					setPositions();
				}
				
				/* resize home page sliders */
				slidersResize();
				
				menu_in_progress = false;
  			}
		});
		
		$('#mmenu_full').fadeIn('normal', function(){
			$('.tmp_visible').slideDown('normal').removeClass('tmp_visible');
		});
		
		menu_collapsed = false;
		createCookie('ap_menu_collapsed', false, 0);
	}
	/* collapse */
	else
	{
		$('#mmenu_full').fadeOut('normal', function(){
			$('#logo').animate({width: 38});
			$('#outer_logo').animate({paddingLeft: 12});
			
			$('.header_left, .middle_left').animate({width: 61}, {
	  			step: function(now, fx) {
	  				/* resize home page sliders */
	    			slidersResize(now);
	  			},
	  			complete: function(){
	  				/* resize grid */
					if ( grid[0] )
					{
						for( var i = 0; i < grid.length; i++ )
						{
							grid[i].setSize($(window).width() - 60 - $('#sidebar').width(), false);
						}
					}
					
					/* resize photos area (if exist) */
					if ( typeof(setPositions) == 'function' )
					{
						setPositions();
					}
					
					/* resize home page sliders */
					slidersResize();
					
					menu_in_progress = false;
	  			}
			});
			$('#sidebar').css({width: 61});
			$('#mmenu_slide').fadeIn('normal');
		});
		
		createCookie('ap_menu_collapsed', true, 30);
		menu_collapsed = true;
	}
}
/* menu handler end */


/**
*
* infinity fields handler
* 
**/
$(document).ready(function(){
	$('table.infinity span.active, table.infinity span.inactive').click(function(){
		var status = $(this).attr('class');
		
		if ( status == 'active' )
		{
			unlim_field_disable(this);
		}
		else
		{
			unlim_field_enable(this);
		}
	});
	
	$('table.infinity span.active, table.infinity span.inactive').each(function(){
		var status = $(this).attr('class');
		
		if ( status == 'inactive' )
		{
			unlim_field_disable(this);
		}
		else
		{
			unlim_field_enable(this);
		}
	});
	
});

var unlim_field_disable = function(obj){
	$(obj).attr('class', 'inactive').attr('title', phrase_set_unlimited);
	$(obj).next().val(0);
	$(obj).parent().prev().children('input').removeClass('disabled');
	var val = $(obj).parent().prev().find('input').attr('accesskey');
	$(obj).parent().prev().find('input').val(val).attr('readonly', false);
	$(obj).parent().prev().find('input').attr('accesskey', false);
}

var unlim_field_enable = function(obj){
	$(obj).attr('class', 'active').attr('title', phrase_unset_unlimited);
	$(obj).next().val(1);
	$(obj).parent().prev().children('input').addClass('disabled');
	var val = $(obj).parent().prev().find('input').val();
	$(obj).parent().prev().find('input').val('').attr('readonly', true);
	$(obj).parent().prev().find('input').attr('accesskey', val);
}

/**
*
* alert the message and focus current field
*
* @param srting field - jQuery format field 
* @param string message - alert message text
* 
**/
function fail_alert( field, message )
{
	Ext.MessageBox.alert(lang['alert'], message, function(){
		if ( field != '' )
		{
			$(field).addClass('error');
			$(field).focus();
		}
	});
}

/**
* notices/errors handler
*
* @param string type - message type: error, notice, warning
* @param string/array message - message text
*
**/
var printMessageTimeout = false;
var printMessage = function(type, message, fields, direct){
	clearTimeout(printMessageTimeout);
	
	var html = ' \
		<div class="'+type+' hide"> \
			<div class="inner"> \
				<div class="icon"></div> \
				<div class="message">'+message+'</div> \
			</div> \
		</div> \
	';
	
	$('#system_message').mouseenter(function(){
		clearTimeout(printMessageTimeout);
	}).mouseout(function(){
		printMessageTimeout = setTimeout('printMessageClose()', 30000);
	});
	
	$('#system_message').html(html);
	$('#system_message div.'+type).fadeIn();
	
	printMessageTimeout = setTimeout('printMessageClose()', 30000);
	flynax.slideTo('#system_message');
};

var printMessageClose = function(){
	$('#system_message>div').fadeOut(function(){
		$(this).html('');
	});
};

/**
* 
* is two array different
*
**/
var rlIsDiff = function(arr1, arr2)
{
	if ( arr1.rlLength() != arr2.rlLength() )
	{
		return true;
	}
	
	for ( var i in arr1 )
	{
		if ( typeof(arr1[i]) != 'function' )
		{
			if ( isArray(arr1[i]) != isArray(arr2[i]) )
			{
				return true;
			}
			else
			{
				if ( rlToStringS(arr1[i]) != rlToStringS(arr2[i]) )
				{
					return true;
				}
			}
		}
	}
	
    return false;
}

/**
* 
* get mixed array length
*
**/
Array.prototype.rlLength = function(){
	var length = 0;
	
    for( var i in this )
    {
		if ( typeof(this[i]) != 'function' )
		{
			length++;
		}
    }
    
    return length;
}

/**
* 
* convert mixed single level array to string, simple
*
**/
var rlToStringS = function(obj){
	var string = '';
	
    for( var i in obj )
    {
		if ( typeof(obj[i]) != 'function' )
		{
			string += i+':'+obj[i]+',';
		}
    }
    
    return rtrim(string, ',');
}

var isArray = function(obj)
{
	if ( obj && obj.constructor.toString().indexOf("Array") == -1 )
	{
		return false;
	}

	return true;
}

/* adaptation for IE6 */
//if(!Array.indexOf)
//{
//    Array.prototype.indexOf = function(obj){
//        for(var i=0; i<this.length; i++){
//            if(this[i]==obj){
//                return i;
//            }
//        }
//        return -1;
//    }
//}

/**
*
* hide or show the menu blocks (via jQuery effect) by ID
* 
**/
var timeout;
var allow_click = true;
$(document).ready(function(){
	
	$('#mmenu_full div.caption, #mmenu_full div.caption_active').click(function(){
		var id = $(this).attr('id').split('_')[2];
		
		timeout = setTimeout(function(){
			block_action(id)
		}, 200);
		
	}).dblclick(function(){
		allow_click = false;
		timeout = false;
		
		var id = $(this).attr('id').split('_')[2];
		
		$('#mmenu_full div.caption, #mmenu_full div.caption_active').each(function(){
			if ( $(this).next().is(':visible') )
			{
				var item_id = $(this).attr('id').split('_')[2];

				if ( item_id != id )
				{
					block_action(item_id, true);
				}
			}
		});
		
		if ( !$(this).next().is(':visible') )
		{
			block_action(id, true);
		}
		
		timeout = 500;
		setTimeout(function(){
			allow_click = true;
		}, 200);
	});
});

var block_action = function(id, allow)
{
	if ( allow_click || allow )
	{
		if ( $( '#lblock_'+id ).css('display') == 'block' )
		{
			$( '#lblock_'+id ).slideUp('normal', function(){
				/* resize home page sliders */
				slidersResize();
			});
			
			$( '#lb_status_'+id ).removeClass('lb_head_rpart_max');
			$( '#lb_status_'+id ).addClass('lb_head_rpart_min');
			
			createCookie('adMenu_'+id, 'hide', 30);
		}
		else
		{
			$( '#lblock_'+id ).slideDown('slow', function(){
				/* resize home page sliders */
				slidersResize();
			});
			
			$( '#lb_status_'+id ).removeClass('lb_head_rpart_min');
			$( '#lb_status_'+id ).addClass('lb_head_rpart_max');
			
			createCookie('adMenu_'+id, 'show', 30);
		}
	}
}

/**
*
* hide or show the fieldset blocks (via jQuery effect) by ID
*
* @param srting id - field id
* 
**/
function fieldset_action( id )
{
	if ( $( '#'+id ).css('display') == 'block' )
	{
		$( '#'+id ).slideUp('normal');
		$( '#legend_'+id ).removeClass('up');
		$( '#legend_'+id ).addClass('down');
		
		createCookie('adFieldset_'+id, 'hide', 30);
	}
	else
	{
		$( '#'+id ).slideDown('slow');
		$( '#legend_'+id ).removeClass('down');
		$( '#legend_'+id ).addClass('up');
		
		createCookie('adFieldset_'+id, 'show', 30);
	}
}

$(document).ready(function(){
	$('fieldset.light').each(function(){
		var id = $(this).children('legend').attr('id').replace('legend_', '');
		if ( readCookie('adFieldset_'+id) == 'hide' )
		{
			$(this).children('legend').next().hide();
			$(this).children('legend').removeClass('up');
			$(this).children('legend').addClass('down');
		}
	});
});

/**
*
* hide or show the object (via jQuery effect) by ID, and hide all objects by html path
*
* @param srting id - field id
* @param srting path - html path
* 
**/
function show( id, path )
{
	if (path != '')
	{
		$(path+'.hide').slideUp('normal');
	}
	
	if ( !id )
		return false;

	if ( $( '#'+id ).css('display') == 'block' )
	{
		$( '#'+id ).slideUp('slow');
	}
	else
	{
		$( '#'+id ).slideDown('slow');
	}
}

/**
*
* hide or show the object on the block page (via jQuery effect) by ID, and hide all objects by html path
*
* @param srting id - field id
* @param srting path - html path
* 
**/
function block_banner( id, path )
{
	if ( id == '')
	{
		id = 'btype_other';
	}

	if( id != 'btype_banner' )
	{
		if( id == 'btype_html' )
		{
			$( '#btype_html' ).slideDown('normal');
			$( '#btype_banner' ).slideUp('fast');
			$( '#btype_other' ).slideUp('fast');
		}
		else
		{
			$( '#btype_other' ).slideDown('normal');
			$( '#btype_banner' ).slideUp('fast');
			$( '#btype_html' ).slideUp('fast');
		}
	}
	else
	{
		$( '#btype_banner' ).slideDown('normal');
		$( '#btype_other' ).slideUp('fast');
		$( '#btype_html' ).slideUp('fast');
	}
	
}

/* jQuery block manager | auto size controller */
$(document).ready(function() {
	$('.size').click(function(){
		if ($(this).attr('id') == 'original' || $(this).attr('id') == 'block')
		{
			$('#resW').attr('readonly', true);
			$('#resH').attr('readonly', true);
			$('#resW').val('');
			$('#resH').val('');
		}	
		else if ($(this).attr('id') == 'flash')
		{
			$('#resW').attr('readonly', false);
			$('#resH').attr('readonly', false);
			$('#original').attr('disabled', true);
			$('#block').attr('disabled', true);
			$('#custom').attr('checked', true);
		}
		else if ($(this).attr('id') == 'image')
		{
			$('#original').attr('disabled', false);
			$('#block').attr('disabled', false);
		}
		else
		{
			$('#resW').attr('readonly', false);
			$('#resH').attr('readonly', false);
		}
	});
});

/**
*
* confirm alert
*
* @param string message - confirm message text
* @param srting method  - javascript method (function)
* @param Array  params  - method (function) params
* @param string load_object  - load object ID
* @param string mod  - confirm mod
* @param string callback - fail callback function
* 
**/
function rlConfirm( message, method, params, load_object, mod, callback )
{
	Ext.MessageBox.confirm(lang['confirm'], message, function(btn){
		if ( btn == 'yes' )
		{
			if ( mod == 'smarty')
			{
				var func = method+'('+params+')';
			}
			else
			{
				var func = method+'(\"'+params+'\")';
				
			}
			
			eval(func);

			if ( load_object )
			{
				$('#'+load_object).fadeIn('normal');
			}
		}
		else
		{
			if ( callback )
			{
				eval(callback+'()');
			}
		}
	});
}

/**
*
* prompt alert
*
* @param string message - prompt message text
* @param srting method - javascript method (function)
* @param Array params - method (function) params
* @param bool contact_mode - contact form mode
*
*/
function rlPrompt( message, method, params, contact_mode )
{
	var caption = contact_mode ? message : lang['ext_confirm'];
	message = contact_mode ? '' : message +'<br /><br />'+ lang['ext_explain_your_reason'];

	Ext.MessageBox.prompt(caption, message, function(btn, reason){
		if ( btn == 'ok' )
		{
			var func = method+'(params, reason)';
			eval(func);
		}
	}, null, true);
}

/**
*
* listing fields manager
*
* @param string type - field type
* 
**/
function field_types( type )
{
	$('#additional_options div.hide').slideUp('fast');
	$('.data_format').attr('disabled', true);
	$('#field_'+type).slideDown('normal');
	$('#field_'+type+' select').attr('disabled', false);
}

/**
*
* data formats manager
*
* @param string id - selector id
* 
**/
$(document).ready(function(){
	$('select.data_format').change(function(){
		var id = $(this).attr('id').replace('dd_', '');
		
		if ( $(this).is(':not(:disabled)') )
		{
			if ( $(this).val() == '0' )
			{
				$('#'+id).slideDown('normal');
			}
			else
			{
				$('#'+id).slideUp('fast');
			}
		}
	});
});

/**
*
* image resize fieds action
*
* @param string type - resize type
* 
**/
function resize_action( type )
{
	$('#resW').attr('readonly', false).removeClass('disabled');
	$('#resH').attr('readonly', false).removeClass('disabled');
	
	if (type == 'W')
	{
		$('#resH').attr('readonly', true).addClass('disabled');
	}
	else if (type == 'H')
	{
		$('#resW').attr('readonly', true).addClass('disabled');
	}
	else if (type == 'C')
	{
		$('#resW').attr('readonly', false).removeClass('disabled');
		$('#resH').attr('readonly', false).removeClass('disabled');
	}
	else
	{
		$('#resW').attr('readonly', true).addClass('disabled');
		$('#resH').attr('readonly', true).addClass('disabled');
	}
}

/**
*
* @var string (step) - item step
*
**/
var step = null;

/**
*
* listing fields builder
*
* @param string type  - field type
* @param string langs - system langs
* 
**/
function field_build( type, lg )
{
	var data = '';
	
	// set default field type
	if ( type == 'checkbox' )
	{
		input_d_type = 'checkbox';
	}
	else
	{
		input_d_type = 'radio';
	}
	
	eval("step = "+type+"_step");
	
	data += '<tr id="'+type+'_'+step+'"><td>';
	
	for (var i = 0; i <= lg.length-1; i++)
	{
		var item = lg[i].split('|');
		data += '<div><input type="text" class="margin float" name="'+type+'['+step+']['+item[0]+']" /><span class="field_description">'+lang['ext_item_value']+' <span class="green_10">(<b>'+item[1]+'</b>)</span></span></div>';
	}
	
	// default button build
	data += '</td>';
	data += '<td style="padding: 0 10px;"><input id="'+type+'_def_'+step+'" type="'+input_d_type+'" name="'+type;
	if ( type == 'checkbox' )
	{
		data += '['+step+']';
	} 
	data += '[default]" value="'+step+'" /> <label for="'+type+'_def_'+step+'">'+lang['ext_default']+'</label></td>';
	
	// remove button build
	data += '<td><a href="javascript:void(0)" onclick="$(\'#'+type+'_'+step+'\').remove();" class="delete_item">'+lang['ext_remove']+'</a></td></tr>';

	eval(type+"_step++");
	
	$("#"+type).append(data);
}

/**
*
* check form
*
* @param array data - form data
* @param srting method  - javascript method (function)
* @param Array  params  - method (function) params
* @param string load_object  - load object ID
* 
**/
function rlCheck( data, method, load_object )
{
	var len = data.length;
	var criterion = null;
	var cond = null;
	
	var params = "Array( ";
	
	for (var i=0; i<len; i++)
	{
		var object = data[i][0].indexOf('.') == 0 ? data[i][0]+':checked' : '#'+data[i][0];
		
		$(object).removeClass('error');
		
		criterion = data[i][2] == 'undefined' ? null : data[i][2];
		params += "Array('"+data[i][0]+"', '"+quote($(object).val())+"', '"+criterion+"')";

		if ( i+1 != len)
		{
			params += ', ';
		}
		
		if (data[i][0] != '')
		{
			if (!data[i][2])
			{
				if ($(object).val() == '' || typeof($(object).val()) == 'undefined')
				{
					return fail_alert( object, data[i][1] );
				}
			}
			else
			{
				cond = data[i][2].split('^');
				
				if (cond[0] == 'f')
				{
					var query = "var res = "+cond[1]+"('"+$(object).val()+"') );";
				}
				else
				{
					var query = "var res = "+$(object).val().length+cond[0]+cond[1]+" ? true : false;";
				}
				
				eval(query);
				
				if (!res)
				{
					return fail_alert( object, data[i][1] );
				}
			}
		}
		
		criterion = null;
	}
	
	params += ")";

	var func = method+'('+params+')';

	eval(func);
	
	$('#'+load_object).fadeIn('normal');
}

/**
*
* additional checking form for add phrase
*
* @param array data - form data
* 
**/
function js_addPhrase(params)
{
	var vars = "Array( ";
	
	var len_v = params.length;

	for (var i = 0; i < params.length; i++)
	{
		vars += "Array( '"+quote(params[i][0])+"', '"+quote(params[i][1])+"' )";
		
		if ( len_v != i+1)
		{
			vars += ", ";
		}
	}
	
	vars += " )";

	var len = 0;
	
	$('#lang_add_phrase textarea').each(
		function(){
			len++;
		}
	);

	var values = "Array( ";
	
	var step = 1;
	
	$('#lang_add_phrase textarea').each(
		function(){
			values += "Array( '"+quote($(this).attr('name'))+"', '"+quote($(this).val(), true)+"' )";
			if (len != step)
			{
				values += ", ";
			}
			step++;
		}
	);
	
	values += ")";
	
	var func = 'xajax_addPhrase('+vars+', '+values+')';
	
	eval(func);
}

var form_submit = true;

/**
*
* forms submit handler
*
* @param array data - form data
* 
**/
function submitHandler()
{
	if ( form_submit )
		return true;
	else
		return false;
}

/**
*
* selectOptions
*
* @package jQuery
* 
**/
(function($) {
	$.fn.selectOptions = function(option){
		if ( $(this).length <= 0 )
		{
			return;
		}
		
		if ( $(this).attr('type') == 'text' )
		{
			$(this).val(option);
		}
		else
		{
			$(this).children('option').each(function(){
				
				if ( $(this).val() == option )
				{
					$(this).attr('selected', true);
				}
			});
		}
	}
})(jQuery);

/**
*
* autocomplete plugin
*
* @package jQuery
* 
**/
(function($) {
	$.fn.rlAutoComplete = function(options){
		options = jQuery.extend({
			type: '*',
			add_id: false,
			id: false
		},options)

		var store = new Array;
		var query = '';
		var obj = this;
		var set = false;
		var set_hidden = false;
		var cur_item = 0;
		var total = 0;
		var interface = '<div id="ac_interface" class="autocomplete"></div>';
		var hidden_field = '<input type="hidden" name="" id="ac_hidden" value="'+options.id+'" />';
		var save_pos = 0;
		
		var key_enter = 13;
		var key_down = 40;
		var key_up = 38;
		var poss = 0;
		
		if ( options.id )
		{
			$(obj).after(hidden_field);
			$('#ac_hidden').attr('name', $(obj).attr('name'));
			$(obj).attr('name', $(obj).attr('name')+'_tmp');
			set_hidden = true;
		}
		
		$(obj).attr('autocomplete', 'off');
		
		$(obj).keyup(function(e){
			if ( e.keyCode == key_enter )
			{
				if (!cur_item)
					return;
				
				$(obj).val($('#ac_item_'+cur_item).html().replace(/<b>/i, '').replace(/<\/b>/i, ''));
				$('#ac_interface').hide();
				
				// clear save position
				save_pos = poss = 0;
				
				if ( options.add_id )
				{
					var index = $('#ac_item_'+cur_item).attr('id').split('_')[2];
					index--;
					$('#ac_hidden').attr('value', store[index]['ID']);
					
					/* allow forms submition */
					form_submit = true;
				}
			}
			else if ( e.keyCode == key_down )
			{
				if ( cur_item < total && $('#ac_interface').css('display') == 'block')
				{
					cur_item++;
					poss += 24;
					drow();
				}
			}
			else if ( e.keyCode == key_up && $('#ac_interface').css('display') == 'block' )
			{
				if (cur_item > 1)
				{
					cur_item--;
					poss -= 24;
					drow('up');
				}
			}

			if( (query != $(this).val() && e.keyCode != key_enter) || (e.keyCode == key_down && cur_item == 0) )
			{
				/* build interface */
				if ( !set )
				{
					$(obj).after(interface);
					var obj_margin = $(obj).css('margin-left');
					var obj_poss = $(obj).position();
					
					$('#ac_interface').css({left: obj_poss.left, top: obj_poss.top}).css('margin-left', obj_margin);
					if ( options.add_id && !set_hidden )
					{
						$(obj).after(hidden_field);
						$('#ac_hidden').attr('name', $(obj).attr('name'));
						$(obj).attr('name', $(obj).attr('name')+'_tmp');
						set_hidden = true;
					}
					set = true;
				}
				
				if ( e.keyCode != key_down )
				{
					cur_item = 0;
				}
				
				/* do complete */
				//$(obj).after('<span class="autocomplete_loader"></span>').next().show();
				$.getJSON("controllers/jsonResponse.php?q=accounts", {str: $(this).val(), add_id: options.add_id}, function(response){
					total = response.length;
					
					if( response && response.rlLength() > 0 )
					{
						store = response;
						var content = '';
						for (var i=0;i<response.length;i++)
						{
							var search = eval('/'+query+'/i');
							var out = response[i]['Username'].replace(search, '<b>'+query+'</b>' )
							var index = i+1;
							content +='<div id="ac_item_'+index+'" style="padding: 6px;color: #6B6B6B;font-size: 13px;">'+out+'</div>';
						}
						$('#ac_interface').html(content);
						$('#ac_interface').show();
						
						if ( options.add_id )
						{
							/* prevent forms submition */
							form_submit = false;
						}
			
						$('#ac_interface div').click(function(){
							$(obj).val($(this).html().replace(/<b>/i, '').replace(/<\/b>/i, ''));
							$('#ac_interface').hide();
							
							// clear save position
							save_pos = poss = 0;
							
							if ( options.add_id )
							{
								form_submit = true;
								var index = $(this).attr('id').split('_')[2];
								index--;
								$('#ac_hidden').attr('value', response[index]['ID']);
							}
						});
		
						$('#ac_interface div').mouseover(function(){
							$('#ac_interface div').css('background-color', 'white');
							$(this).css('background-color', '#efefef');
						});
		
						$('#ac_interface div').mouseout(function(){
							$(this).css('background-color', 'white');
						});
		
						$('body').click(function(){
							$('#ac_interface').hide();
							
							// clear save position
							save_pos = poss = 0;
						});
						
						$(obj).removeClass('error');
					}
					else
					{
						$('#ac_interface').hide();
						$(obj).addClass('error');
					}
					//$(obj).removeClass('autocomplete_load');
				});
				query = $(this).val();
			}
		}).blur(function(){
			if ( !$('#ac_interface').is(':visible') )
				return;
			
			$(obj).val($('#ac_item_1').html().replace(/<b>/i, '').replace(/<\/b>/i, ''));
			$('#ac_interface').hide();
			
			// clear save position
			save_pos = poss = 0;
			
			if ( options.add_id )
			{
				var index = $('#ac_item_1').attr('id').split('_')[2];
				index--;
				$('#ac_hidden').attr('value', store[index]['ID']);
				
				/* allow forms submition */
				form_submit = true;
			}
		});
		
		function drow(direction)
		{
			$('#ac_interface div').css('background-color', 'white');
			$('#ac_item_'+cur_item).css('background-color', '#efefef');
			
			$('#ac_interface').animate({scrollTop:poss-48}, 100);
		}
	}
})(jQuery);

/**
*
* trim string
*
* @param string str - string for trim
* @param string chars - chars to be trimmed
*
* @return trimmed string
* 
**/
function trim(str, chars)
{
	return ltrim(rtrim(str, chars), chars);
}

/**
*
* left trim string
*
* @param string str - string for trim
* @param string chars - chars to be trimmed
*
* @return trimmed string
* 
**/
function ltrim(str, chars)
{
	chars = chars || "\\s";
	return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}

/**
*
* right trim string
*
* @param string str - string for trim
* @param string chars - chars to be trimmed
*
* @return trimmed string
* 
**/
function rtrim(str, chars)
{
	chars = chars || "\\s";
	return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}

/**
*
* escape or replace quotes
*
* @param string str - string for replacing
* @param bool to - replace if true and escape if false
* 
**/
function quote( str, to )
{
	if ( typeof(str) == 'undefined' )
	{
		return false;
	}
	
	if (!to)
	{
		return str.replace(/'/g, "").replace(/"/g, "");
	}
	else
	{
		var to_single = '&rsquo;';
		var to_double = '&quot;';
		
		return str.replace(/'/g, to_single).replace(/"/g, to_double).replace(/\n/g, '<br />' );
	}
}