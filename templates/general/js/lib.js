
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: {version}
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
 *	Flynax Classifieds Software 2013 |  All copyrights reserved. 
 *
 *	http://www.flynax.com/
 *
 ******************************************************************************/

/**
*
* jQuery file uploader plugin by Flynax 
*
**/
(function($){
	$.flUpload = function(el, options){
		var base = this;
		
		base.validate = new Array();
		base.validate['image'] = ['image/jpeg', 'image/gif', 'image/png'];
		base.index = 0;
		base.files = new Array();
		
		// access to jQuery and DOM versions of element
		base.$el = $(el);
		base.el = el;

		// add a reverse reference to the DOM object
		base.$el.data("flUpload", base);

		base.init = function(){
			base.options = $.extend({},$.flUpload.defaultOptions, options);

			// initialize working object id
			if ( $(base.el).attr('id') )
			{
				base.options.id = $(base.el).attr('id');
			}
			else
			{
				$(base.el).attr('id', base.options.id);
			}
			
			base.upload();
			base.deleteObj();
		};

		// upload
		base.upload = function(){
			$(base.el).change(function(){
				base.index = 0;

				if ( $.browser.msie )
				{
					var name;
					eval("name = $(this).val().split('\\').reverse()[0];");
					base.poorMode(false, name);
					
					return;
				}
				
				var imgObj = this.files[0];
				var name = 'name' in imgObj ? imgObj.name : imgObj.fileName;
				var ext = name.split('.').reverse()[0];

				if ( base.options.validate && base.validate[base.options.validate].indexOf(imgObj.type) < 0 )
				{
					printMessage('error', lang['notice_bad_file_ext'].replace('{ext}', '<b>'+ext+'</b>'));
				}
				else
				{
					if ( base.options.sampleFrame )
					{
						var img = new Image();
						if ( 'getAsDataURL' in imgObj )
						{
							img.src = imgObj.getAsDataURL();
							img.onload = function(){
								var canvas = document.createElement('canvas');
								var width = base.options.sampleMaxWidth;
								var height = Math.floor((base.options.sampleMaxWidth * img.height) / img.width);
								
								if ( base.options.fixedSize && height < base.options.sampleMaxHeight )
								{
									width = width*base.options.sampleMaxHeight/height;
									height = base.options.sampleMaxHeight;
								}
								if (!canvas.getContext) {
									console.log('.getContext() doesn\'t work')
								}
								canvas.width = base.options.fixedSize ? base.options.sampleMaxWidth : width;
								canvas.height = base.options.fixedSize ? base.options.sampleMaxHeight : height;
								canvas.getContext('2d').drawImage(img, 0, 0, width, height);
								canvas.className = 'new';
								
								$(base.options.sampleFrame).addClass('active').find('canvas').remove();
								$(base.options.sampleFrame).prepend(canvas);
								$(base.options.sampleFrame).find('.preview').hide();
							};
						}
						else
						{
							base.poorMode(imgObj, name, ext);
						}
					}
					else
					{
						console.log('Flynax Error: No sample/preview object specified')
					}
				}
			});
		};
		
		base.poorMode = function(imgObj, name, ext){
			var html = '<div title="'+name+'" style="width: '+base.options.sampleMaxWidth+'px;height: '+base.options.sampleMaxHeight+'px;">'+name+'</div>';
			$(base.options.sampleFrame).find('img.preview,div').hide();
			$(base.options.sampleFrame).prepend(html);
			$(base.options.sampleFrame).addClass('active');
		};
		
		base.deleteObj = function(){
			$(base.options.sampleFrame).find('img.delete').unbind('click').click(function(){
				if ( $(this).hasClass('ajax') )
					return;

				$(base.options.sampleFrame).find('img.preview').show();
				$(base.options.sampleFrame).find('canvas, div').remove();
				$(base.options.sampleFrame).removeClass('active');
			});
		};
		
		// run initializer
		base.init();
	};

	$.flUpload.defaultOptions = {
		sampleFrame: false,
		sampleMaxWidth: 105,
		sampleMaxHeight: 105,
		validate: 'image',
		fixedSize: false,
		allowed: 3,
		unlimited: false
	};

	$.fn.flUpload = function(options){
		return this.each(function(){
			(new $.flUpload(this, options));
		});
	};

})(jQuery);

/**
*
* jQuery modal window plugin by Flynax 
*
**/
(function($){
	$.flModal = function(el, options){
		var base = this;
		var lock = false;
		var direct = false;
		
		// access to jQuery and DOM versions of element
		base.$el = $(el);
		base.el = el;
		
		base.objHeight = 0;
		base.objWidth = 0;
		base.sourceContent = false;

		// add a reverse reference to the DOM object
		base.$el.data("flModal", base);

		base.init = function(){
			base.options = $.extend({},$.flModal.defaultOptions, options);

			// initialize working object id
			if ( $(base.el).attr('id') )
			{
				base.options.id = $(base.el).attr('id');
			}
			else
			{
				$(base.el).attr('id', base.options.id);
			}
			
			// add mask on click
			if ( base.options.click )
			{
				base.$el.click(function(){
					base.mask();
					base.loadContent();
				});
			}
			else
			{
				base.mask();
				base.loadContent();
			}
		};

		base.mask = function(){
			var width = $(document).width();
			var height = $(document).height();
			
			var dom = '<div id="modal_mask"><div id="modal_block" class="modal_block"></div></div>';
			
			$('body').append(dom);
			$('#modal_mask').width(width);
			$('#modal_mask').height(height);
			$('#modal_block').width(base.options.width).height(base.options.height);
			
			// on resize document
			$(window).unbind('resize').resize(function(){
				base.resize();
			});
			
			if ( base.options.scroll )
			{
				$(window).unbind('scroll').scroll(function(){
					base.scroll();
				});
			}
		};
		
		base.resize = function(){
			if ( lock )
				return;

			var width = $(window).width();
			var height = $(document).height();
			$('#modal_mask').width(width);
			$('#modal_mask').height(height);
			
			var margin = ($(window).height()/2)-base.objHeight + $(window).scrollTop();
			$('#modal_block').stop().animate({marginTop: margin});
			
			var margin = base.objWidth * -1;
			$('#modal_block').stop().animate({marginLeft: margin});
		};
		
		base.scroll = function(){
			if ( lock )
				return;

			var margin = ($(window).height()/2)-base.objHeight + $(window).scrollTop();
			$('#modal_block').stop().animate({marginTop: margin});
		};
		
		base.loadContent = function(){
			/* load main block source */
			var dom = '<div class="inner"><div class="modal_content"></div><div class="close" title="'+lang['close']+'"></div></div>';
			$('div#modal_block').html(dom);
			
			var track_margin = base.options.height - 72;
			$('#modal_block div.small_track').css('margin-top', track_margin+'px');
			
			/* load content */
			var content = '';
			var caption_class = base.options.type ? ' '+base.options.type : '';
			base.options.caption = base.options.type && !base.options.caption ? lang[base.options.type] : base.options.caption;
			
			/* save source */
			if ( base.options.source )
			{
				if ( $(base.options.source + ' > div.tmp-dom').length > 0 )
				{
					base.sourceContent = $(base.options.source + ' > div.tmp-dom');
					direct = true;
				}
				else
				{
				base.sourceContent = $(base.options.source).html();
			}
			}
			
			/* build content */
			content = base.options.caption ? '<div class="caption'+caption_class+'">'+ base.options.caption + '</div>': '';
			content += base.options.content ? base.options.content : '';
			
			/* clear soruce objects to avoid id overload */
			if ( base.options.source && !direct )
			{
				$(base.options.source).html('');
				content += !base.options.content ? base.sourceContent : '';
			}
			
			$('div#modal_block div.inner div.modal_content').html(content);
			
			if ( base.options.source && direct )
			{
				$('div#modal_block div.inner div.modal_content').append(base.sourceContent);
			}
			
			if ( base.options.prompt )
			{
				var prompt = '<div class="prompt"><input name="ok" type="button" value="Ok" /><input name="close" type="button" value="'+lang['cancel']+'" /></div>';
				$('div#modal_block div.inner div.modal_content').append(prompt);
			}
			
			if ( base.options.ready )
			{
				base.options.ready();
			}
			
			$('#modal_block input[name=close]').click(function(){
				base.close();
			});
			
			if ( base.options.prompt )
			{
				$('#modal_block div.prompt input[name=close]').click(function(){
					base.close();
				});
				$('#modal_block div.prompt input[name=ok]').click(function(){
					var func = base.options.prompt;
					func += func.indexOf('(') < 0 ? '()' : '';
					eval(func);
					base.close();
				});
			}
			
			/* set initial sizes */
			base.objHeight = $('#modal_block').height()/2;
			base.objWidth = $('#modal_block').width()/2;
			
			var setTop = ($(window).height()/2) - base.objHeight + $(window).scrollTop();
			$('#modal_block').css('marginTop', setTop);
			var setLeft = base.objWidth * -1;
			$('#modal_block').css('marginLeft', setLeft);
			
			$('#modal_mask').click(function(e){
				if ( $(e.target).attr('id') == 'modal_mask' )
				{
				base.close();
				}
			});
			
			$('#modal_block div.close').click(function(){
				base.close();
			});
		};
		
		base.close = function(){
			lock = true;
			
			$('#modal_block').animate({opacity: 0});
			$('#modal_mask').animate({opacity: 0}, function(){
				$(this).remove();
				$('#modal_block').remove();
			
			if ( base.options.source )
			{
					$(base.options.source).append(base.sourceContent);
			}
				
				lock = false;
			});
		};
		
		// run initializer
		base.init();
	};

	$.flModal.defaultOptions = {
		scroll: true,
		type: false,
		width: 340,
		height: 230,
		source: false,
		content: false,
		caption: false,
		prompt: false,
		click: true,
		ready: false
	};

	$.fn.flModal = function(options){
		return this.each(function(){
			(new $.flModal(this, options));
		});
	};

})(jQuery);

/**
*
* jQuery categoroes slider plugin by Flynax
*
**/
(function($){
	$.flCatSlider = function(el, options){
		var base = this;
		
		base.block_width = 0;
		base.work_width = 0;
		base.position = 0;
		base.areaMargin = 30;
		base.pages = 1;
		
		// access to jQuery and DOM versions of element
		base.$el = $(el);
		base.el = el;

		// add a reverse reference to the DOM object
		base.$el.data("flCatSlider", base);

		base.init = function(){
			base.options = $.extend({},$.flCatSlider.defaultOptions, options);

			// initialize working object id
			if ( $(base.el).attr('id') )
			{
				base.options.id = $(base.el).attr('id');
			}
			else
			{
				$(base.el).attr('id', base.options.id);
			}

			if ( !$(base.el).attr('id') )
				return;
			
			base.setSizes();
			base.eventsHandler();
			base.scroll();
		};

		base.setSizes = function(){
			base.pages = parseInt($(base.el).attr('id').split('_')[2]) || 1;
			
			if ( base.pages < 2 )
				return;
				
			base.block_width = parseInt($(base.el).width());
			base.work_width = base.block_width - base.areaMargin
			$(base.el).width(base.block_width);
			$(base.el).find('ul').width(base.block_width * base.pages);
			$(base.el).find('ul li').width(base.block_width).show();
		};
		
		base.eventsHandler = function(){
			if ( base.pages < 0 )
				return;
				
			$(base.el).next().find('div.navigation a').click(function(){
				var point = parseInt($(this).attr('accesskey'));
				base.position = point - 1;
				base.slider();
			});
			
			$(base.el).next().find('div.next').click(function(){
				var active = base.position + 1;
				if ( active < base.pages )
				{
					base.position++;
					base.slider();
				}
			});
			$(base.el).next().find('div.prev').click(function(){
				var active = base.position + 1;
				if ( active > 1 )
				{
					base.position--;
					base.slider();
				}
			});
		};
		
		base.slider = function(){
			var pos = (base.block_width * base.position) * -1;
			var active = base.position + 1;
			if ( rlLangDir == 'ltr' )
			{
				$(base.el).find('ul').stop().animate({marginLeft: pos});
			}
			else
			{
				$(base.el).find('ul').stop().animate({marginRight: pos});
			}
			$(base.el).next().find('div.navigation a').removeClass('active');
			$(base.el).next().find('div.navigation a[accesskey='+ active +']').addClass('active');
			
			if ( active == 1 )
			{
				$(base.el).next().find('div.prev').fadeOut('normal');
			}
			else
			{
				$(base.el).next().find('div.prev').fadeIn('normal');
			}
			
			if ( active == base.pages )
			{
				$(base.el).next().find('div.next').fadeOut('normal');
			}
			else
			{
				$(base.el).next().find('div.next').fadeIn('normal');
			}
		};
		
		base.scroll = function(){
			$(base.el).parent().bind('mousewheel', function(e, data){
				if ( data < 0 )
				{
					var active = base.position + 1;
					if ( active < base.pages )
					{
						base.position++;
						base.slider();
						e.preventDefault();
					}
				}
				else
				{
					var active = base.position + 1;
					if ( active > 1 )
					{
						base.position--;
						base.slider();
						e.preventDefault();
					}
				}
			});
		};
		
		// run initializer
		base.init();
	};

	$.flCatSlider.defaultOptions = {
		scroll: 1
	};

	$.fn.flCatSlider = function(options){
		return this.each(function(){
			(new $.flCatSlider(this, options));
		});
	};

})(jQuery);

/**
*
* jQuery common slider plugin by Flynax
*
**/
(function($){
	$.flSlider = function(el, options){
		var base = this;
		
		base.items = new Array();
		base.loading = new Array();
		base.currentSlide = 0;
		base.parent = false;
		base.cont = false;
		base.slidesNumber = 0;
		base.itemsPerSlide = 0;
		base.workSide = 0;
		base.itemSide = 0;
		base.itemsCount = 0;
		
		// access to jQuery and DOM versions of element
		base.$el = $(el);
		base.el = el;

		// add a reverse reference to the DOM object
		base.$el.data("flSlider", base);

		base.init = function(){
			base.options = $.extend({},$.flSlider.defaultOptions, options);

			// initialize working object id
			if ( $(base.el).attr('id') )
			{
				base.options.id = $(base.el).attr('id');
			}
			else
			{
				$(base.el).attr('id', base.options.id);
			}

			base.preLoader();
		};

		base.preLoader = function(){
			if ( !base.options.height && base.options.vertical )
			{
				console.log('The HEIGHT parameter is required in VERTICAL slider mode');
				return;
			}
			
			$(base.el).css('opacity', 0);
			
			var index = 0;
			$(base.el).find('li').each(function(){
				eval(" \
				var obj"+index+" = this; \
				var src"+index+" = $(obj"+index+").find('img').attr('src'); \
				if ( src"+index+" ) \
				{ \
					base.loading["+index+"] = 'progress'; \
					var img"+index+" = new Image(); \
					img"+index+".onload = function(){ \
						base.loading["+index+"] = 'success'; \
						base.items["+index+"] = new Array(src"+index+", $(obj"+index+").width(), $(obj"+index+").height()); \
						if ( base.loading.indexOf('progress') < 0 ) \
						{ \
							base.setSizes(); \
						} \
					}; \
					img"+index+".src = src"+index+"; \
				} \
				");
				index++;
			});
		};
		
		base.setSizes = function(){
			$(base.el).animate({opacity: 1});
			var add_class = base.options.vertical ? ' vertical' : ' horizontal';
			base.itemsCount = $(base.el).find('li').length;
			$(base.el).before('<div class="slider '+add_class+'"><div class="prev"></div><div class="container"></div><div class="next"></div></div>');
			$(base.el).prev().children('div.container').html($(base.el));
			base.parent = $(base.el).parent().parent();
			base.cont = $(base.el).parent();
			
			if ( base.options.vertical )
			{	
				$(base.parent).height(base.options.height);
				$(base.cont).height(base.options.height-40);// -container margin (top and bottom)
				base.workSide = base.options.height-40;
				base.itemSide = $(base.el).find('li:first').height();
			}
			else
			{
				base.workSide = $(base.cont).width();
				$(base.cont).width(base.workSide);//fix the width
				
				var max_height = 0;
				var total_width = 0;
				
				for (var i=0; i<base.items.length; i++ )
				{
					max_height = base.items[i][2] > max_height ? base.items[i][2]: max_height;
					total_width += base.items[i][1];
				}
				$(base.parent).height(max_height);
				$(base.el).width(total_width);
				base.itemSide = $(base.el).find('li:first').width() + base.options.clearance;
			}
			
			if ( !base.itemSide )
			{
				console.log('Can not detect slider work side length (width/height), probably ul located in hidden element.');
				return;
			}
			
			base.itemsPerSlide = Math.floor((base.workSide+base.options.clearance)/base.itemSide);
			base.slidesNumber = Math.ceil(base.itemsCount/base.itemsPerSlide);
			
			base.setMargin();
			base.eventsHandler();
		};
		
		base.setMargin = function(){
			var work_width = (base.itemsPerSlide * base.itemSide) - base.options.clearance;
			base.options.perSlide = base.itemsPerSlide < base.options.perSlide ? base.itemsPerSlide: base.options.perSlide;
			var rest = base.workSide - work_width;
			
			var margin = Math.floor(rest / (base.itemsPerSlide -1));
			if ( margin >= 1 )
			{
				base.itemSide += margin;
				if ( base.options.vertical )
				{
					$(base.el).find('li').css('marginBottom', margin+'px');
				}
				else
				{
					$(base.el).find('li').css('marginRight', margin+'px');
				}
			}
		};
		
		base.eventsHandler = function(){
			$(base.parent).find('div.navigation a').click(function(){
				var point = parseInt($(this).attr('accesskey'));
				currentSlide = point - 1;
				base.slider();
			});
			
			$(base.parent).find('div.next').click(function(){
				if ( base.currentSlide+1 < base.slidesNumber )
				{
					base.currentSlide++;
					base.slider();
				}
			});
			$(base.parent).find('div.prev').click(function(){
				if ( base.currentSlide > 0 )
				{
					base.currentSlide--;
					base.slider();
				}
			});
		};
		
		base.slider = function(){
			var pos = (base.itemSide * base.options.perSlide * base.currentSlide) * -1;
			
			if ( base.options.vertical )
			{
				$(base.el).animate({marginTop: pos});
			}
			else
			{
				$(base.el).animate({marginLeft: pos});
			}
//			$(base.el).next().find('div.navigation a').removeClass('active');
//			$(base.el).next().find('div.navigation a[accesskey='+ active +']').addClass('active');
		};
		
		// run initializer
		base.init();
	};

	$.flSlider.defaultOptions = {
		vertical: false,
		height: false,//required in vertical mode,
		preload: true,
		perSlide: 1,
		clearance: 0
	};

	$.fn.flSlider = function(options){
		return this.each(function(){
			(new $.flSlider(this, options));
		});
	};

})(jQuery);

/**
*
* tabs click handler
*
* @param object obj - tab object referent
* 
**/
$(document).ready(function(){
	$('div.tabs li').click(function(){
		tabsSwitcher(this);
	});
});

var tabsSwitcher = function(obj){
	var key = $(obj).attr('id').split('_')[1];
	
	$('div.tab_area').hide();
	$('div.tabs li.active').removeClass('active');
	
	$(obj).addClass('active');
	$('div#area_'+key).show();
	
	$('#system_message>div').fadeOut();
};

/**
*
* content padding handler
* 
**/
$(document).ready(function(){
	if ( $('div#controller_area>*').length == 0 )
	{
		$('div#controller_area').css('padding', 0);
	}
});

/**
*
* favorites handler
* 
**/
$(document).ready(function(){
	flFavoritesHandler();
});

var flFavoritesHandler = function(){
	$('a.add_favorite').each(function(){
		var id = $(this).attr('id').split('_')[1];
		var ids = readCookie('favorites');
		
		if ( ids && ids.indexOf(id) >= 0 )
		{
			$(this).addClass('remove_favorite');
			$(this).attr('title', lang['remove_from_favorites']);
		}
	});
	
	$('a.add_favorite').unbind('click').click(function(){
		var id = $(this).attr('id').split('_')[1];
		var ids = readCookie('favorites');
		
		if ( ids )
		{
			ids = ids.split(',');
			
			if ( ids.indexOf(id) >= 0 )
			{
				ids.splice(ids.indexOf(id), 1);
				
				createCookie('favorites', ids.join(','), 93);
				
				$(this).removeClass('remove_favorite');
				$(this).attr('title', lang['add_to_favorites']);
				
				if ( rlPageInfo['key'] == 'my_favorites' )
				{
					var type = readCookie('grid_mode');
					var parent;
					
					if ( type == 'list' )
					{
						parent = $(this).parent().parent().parent().parent().parent().parent().parent().parent().parent();
					}
					else
					{
						parent = $(this).parent().parent().parent().parent().parent();
					}
					
					$(parent).fadeOut('normal', function(){
						if ( type == 'table' && $(this).parent().parent().find('td>div.item').length == 1 )
						{
							$(this).parent().parent().remove();
						}
						else
						{
							$(this).remove();
						}
						
						if ( $('#listings div.item').length < 1 )
						{
							if ( $('ul.paging').length > 0 )
							{
								var redirect = rlConfig['seo_url'];
								redirect += rlConfig['mod_rewrite'] ? rlPageInfo['path'] +'.html' : 'index.php?page='+ rlPageInfo['path'];
								location.href = redirect;
							}
							else
							{
								var div = '<div class="">'+lang['no_favorite']+'</div>';
								$('div.controller_area').append(div);
								$('table.grid_navbar').remove();
							}
						}
					});
						
					$('#notice_message').html(lang['notice_removed_from_favorites']);
					$('#notice_obj').fadeIn();
				}
				
				return;
			}
			else
			{
				ids.push(id);
			}
		}
		else
		{
			ids = new Array();
			ids.push(id);
		}
		
		createCookie('favorites', ids.join(','), 93);
		
		$(this).addClass('remove_favorite');
		$(this).attr('title', lang['remove_from_favorites']);
	});
}

/**
*
* grid modes handler
*
**/
var gridActiveHeight;
var gridInProgress = false;
var gridLength;

$(document).ready(function(){
	$('table.grid_navbar td.switcher>div').click(function(){
		var type = $(this).attr('class');
		
		if ( type == readCookie('grid_mode') || gridInProgress )
		{
			return;
		}
		
		gridInProgress = true;
		
		$('table.grid_navbar td.switcher>div>div').removeClass('active');
		$('table.grid_navbar td.switcher>div.'+type).children('div').addClass('active');
		
		createCookie('grid_mode', type, 93);
		
		if ( type == 'list' )
		{
			gridActiveHeight = $('div#listings>table.table').height();
			$('div#listings>table.table').fadeOut('normal', function(){
				$('div#listings>table.table').after('<div class="list"></div>');
				$('div#listings>div.list').height(gridActiveHeight);
				transformToList(0);
			});
		}
		else
		{
			gridActiveHeight = $('div#listings>div.list').height();
			$('div#listings').css('min-height', gridActiveHeight+'px');
			gridLength = $('div#listings>div.list>div.item').length;
			$('div#listings>div.list').fadeOut('normal', function(){
				$('div#listings>div.list').after('<table class="table"></table>');
				gridAvailableRow = false;
				transformToTable(0);
			});
		}
	});
});

var transformToList = function(index){
	var item = $('div#listings>table.table>tbody>tr>td:not(.divider):eq('+index+')');
	$(item).find('div.item').hide();
	
	/* move fields from table to td */
	var fields = new Array();
	var names = new Array();
	var ids = new Array();
	var j = 0;
	$(item).find('div.item').find('table>tbody>tr:first>td:last>div>table>tbody>tr').find('td:last').each(function(){
		if ( rlConfig['sf_display_fields'] )
		{
			names[j] = rtrim($(this).parent().find('td:first').html(), ':');
		}
		
		ids[j] = $(this).parent().attr('id');
		
			j++;
		fields.push(trim($(this).html()));
	});
	
	$(item).find('div.item').find('table>tbody>tr:first>td:last>table').remove();
		
	var new_table = '<table class="sTable"><tr><td class="fields"><div></div></td><td class="ralign"></td></tr></table>';
	$(item).find('div.item').find('table>tbody>tr:first>td:last').html(new_table);
	
	for ( var i = 0; i < fields.length; i++ )
	{
		var comma = i < fields.length-1 ? ',' : '';
		var title = rlConfig['sf_display_fields'] && names[i] ? ' title='+ names[i] : '';
		var new_value = '<span'+title+' id='+ids[i]+'>'+ fields[i] + comma +'</span> ';
		$(item).find('div.item').find('table>tbody>tr:first>td:last>table>tbody>tr>td:first>div').append(new_value);
	}
	
	/* move additional info */
	$(item).find('div.item').find('table>tbody>tr:first>td:last>table tr td:last').html($(item).find('div.item').find('table>tbody>tr:eq(1)>td').html());
	$(item).find('div.item').find('table>tbody>tr:eq(1)>td').html($(item).find('table.nav'));
	
	/* remove unnecessary class */
	$(item).find('div.item').find('table>tbody>tr:first>td:last').removeClass('fields');
	
	/* move listing item to new area */
	$('div#listings>div.list').append($(item).find('div.item'));
	$('div#listings>div.list>div.item:last').fadeIn();
	var length = $('div#listings>table.table>tbody>tr>td:not(.divider)').length;
	
	index++;
	if ( index < length )
	{
		setTimeout('transformToList('+index+')', 100);
	}
	else
	{
		gridInProgress = false;
		$('div#listings>div.list').css('height', 'auto');
		$('div#listings>table.table').remove();
	}
};

var gridAvailableRow = false;
var transformToTable = function(index){
	var item = $('div#listings>div.list>div.item:first');
	$(item).hide();
	
	/* move additional info */
	$(item).append($(item).find('table.nav'));
	$(item).find('table>tbody>tr:eq(1)>td').html($(item).find('table>tbody>tr:eq(0)>td:last>table>tbody>tr>td:last').html());
	
	/* move fields from table to td */
	var fields = new Array();
	var names = new Array();
	var ids = new Array();
	var j = 0;
	
	$(item).find('table>tbody>tr:first>td:last td.fields div>span').each(function(){
		if ( rlConfig['sf_display_fields'] )
		{
			names[j] = $(this).attr('title');
		}
		ids[j] = $(this).attr('id');
			j++;
		fields.push(trim($(this).html()));
	});
	
	$(item).find('table>tbody>tr:first>td:last').addClass('fields');
	$(item).find('table>tbody>tr:first>td:last>table').attr('class', false).html('');
		
	for ( var i = 0; i < fields.length; i++ )
	{
		var first = i == 0 ? ' first' : '';
		var name = rlConfig['sf_display_fields'] && names[i] ? '<td class="name">'+ names[i] +':</td>' : '';
		var new_row = '<tr>'+name+'<td class="value'+first+'" id="'+ids[i]+'">'+rtrim(fields[i], ',')+'</td></tr>';
		$(item).find('table>tbody>tr:first>td:last>table').append(new_row);
	}
	$(item).find('table>tbody>tr:first>td:last>table.false').wrap('<div></div>');
	/* move listing item to new area */
	if ( !gridAvailableRow )
	{
		$('div#listings>table.table').append('<tr><td></td><td class="divider"></td><td></td></tr>');
		$('div#listings>table.table>tbody>tr:last>td:first').html($(item));
		gridAvailableRow = true;
	}
	else
	{
		$('div#listings>table.table>tbody>tr:last>td:last').html($(item));
		gridAvailableRow = false;
	}
	
	$('div#listings>table.table div.item:last').fadeIn();
	
	index++;
	if ( index < gridLength )
	{
		setTimeout('transformToTable('+index+')', 100);
	}
	else
	{
		gridInProgress = false;
		$('div#listings>div.list').remove();
		$('div#listings').attr('style', false);
	}
};

/**
*
* hide and show dinamic blocks
*
* @param string id - block id
* 
**/
function action_block( id )
{
	if ( $( '#block_content_'+id ).css('display') == 'block' )
	{
		$( '#block_content_'+id ).slideUp('normal');
		$( '#block_arrow_'+id ).removeClass('arrow_block_up');
		$( '#block_arrow_'+id ).addClass('arrow_block_down');
		
		createCookie('feMenu_'+id, 'hide', 30);
	}
	else
	{
		$( '#block_content_'+id ).slideDown('slow');
		$( '#block_arrow_'+id ).removeClass('arrow_block_down');
		$( '#block_arrow_'+id ).addClass('arrow_block_up');
		
		var tab_cookie = readCookie('feMenu_'+id);
		
		if ( tab_cookie == 'hide' )
		{
			createCookie('feMenu_'+id, 'show', 1);
		}
	}
}

/**
*
* photos count effects handler
* 
**/
$(document).ready(function(){
	$('#listings fieldset div.photos_count').prev().each(function(){
		var imgObj = new Image();
		
		var parentObj = this;
		imgObj.onload = function(){
			$(parentObj).next().children('div').width($(parentObj).children('a img').width()-2);
		};
		
		imgObj.src = $(this).children('a img').attr('src');
	});
	$('#listings fieldset').mouseenter(function(){
		$(this).find('.photos_count').children('div').stop().css('opacity', 0.5).animate({opacity: 0.8});
	});
	$('#listings fieldset').mouseleave(function(){
		$(this).find('.photos_count').children('div').stop().animate({opacity: 0.5});
	});
});

/**
*
* paging transit handler
* 
**/
$(document).ready(function(){
	$('ul.paging li.transit input').bind('focus', function(){
		$(this).select();
	}).keypress(function(event){
		if( event.keyCode == 13 ) //enter key pressed
		{
			var page = parseInt($(this).val());
			var info = $('ul.paging li.transit input[name=stats]').val().split('|');
			var first = $('ul.paging li.transit input[name=first]').val();
			var pattern = $('ul.paging li.transit input[name=pattern]').val();
			
			if ( page > 0 && page != parseInt(info[0]) && page <= parseInt(info[1]) )
			{
				if ( page == 1 )
				{
					location.href = first;
				}
				else
				{
					location.href = pattern.replace('[pg]', page);
				}
			}
		}
	});
});

/**
* notices/errors handler
*
* @param string type - message type: error, notice, warning
* @param string/array message - message text
* @param string/array fields - error fields names, array or through comma
*
**/
var printMessageTimer = false;
var printMessage = function(type, message, fields, direct){
	if ( !message || !type )
		return;
	
	var types = new Array('error', 'notice', 'warning');
	var height = 0;
	
	if ( types.indexOf(type) < 0 )
		return;
		
	if ( typeof(message) == 'object' )
	{
		var tmp = '<ul>';
		for( var i=0; i<message.length; i++ )
		{
			tmp += '<li>'+message[i]+'</li>';
		}
		tmp += '</ul>';
		message = tmp;
	}
	
	$('input,select,textarea,table.error').removeClass('error');
	
	/* highlight error fields */
	if ( fields )
	{
		if ( typeof(fields) != 'object' )
		{
			fields = fields.split(',');
		}

		for ( var i = 0; i<fields.length; i++ )
		{
			if ( !fields[i] )
				continue;

			if ( trim(fields[i]) != '' )
			{
				if ( fields[i].charAt(0) == '#' )
				{
					$(fields[i]).addClass('error');
				}
				else
				{
					var selector = 'input[name^="'+fields[i]+'"]:last:not(.policy),select[name="'+fields[i]+'"],textarea[name="'+fields[i]+'"]';
					if ( $(selector).length > 0 && $(selector).attr('type') != 'radio' && $(selector).attr('type') != 'checkbox' )
					{
						$(selector).addClass('error');
					}
					else
					{
						if ( $(selector).attr('type') == 'radio' || $(selector).attr('type') == 'checkbox' )
						{
							$(selector).closest('table').addClass('error');
						}
						else
						{
							$('input[name="'+fields[i]+'[1]"],select[name="'+fields[i]+'[1]"],textarea[name="'+fields[i]+'][1]"').parent().parent().parent().parent().addClass('error');
						}
					}
				}	
			}
		}
	}
	
	/* print error in direct mode */
	if ( direct )
	{
		var html = ' \
			<div class="'+type+' hide"> \
				<div class="inner"> \
					<div class="icon"></div> \
					<div class="message">'+message+'</div> \
				</div> \
			</div> \
		';
		
		$('#system_message').html(html);
		$('#system_message div.'+type).fadeIn();
		
		$('input.error,select.error,textarea.error').focus(function(){
			$(this).removeClass('error');
		});
		$('table.error').click(function(){
			$(this).removeClass('error');
		});
		
		return;
	}
	
	/* print errors */
	if ( $('body>div.'+type).length > 0 )
	{
		$('body>div.'+type+' div.message').fadeOut(function(){
			$(this).html(message).fadeIn();
			height = $('body>div.'+type).height() * -1;
			
			clearTimeout(printMessageTimer);
			printMessageTimer = setTimeout('closeMessage()', 30000);
		});
	}
	else
	{
		$('body>div.error, body>div.notice, body>div.warning, #system_message>div.error, #system_message>div.notice, #system_message>div.warning').fadeOut('fast', function(){
			$(this).remove();
		});
		
		clearTimeout(printMessageTimer);
		
		var html = ' \
			<div class="'+type+'"> \
				<div class="inner"> \
					<div class="icon"></div> \
					<div class="message">'+message+'</div> \
					<div class="close" title="'+lang['close']+'"></div> \
				</div> \
			</div> \
		';
		
		$('body').prepend(html);
			height = $('body>div.'+type).height() * -1;
		$('body>div.'+type).css('margin-top', height).show().animate({marginTop: 0}, function(){
			printMessageTimer = setTimeout('closeMessage()', 30000);
		});
	}
	
	$('body>div.'+type).unbind('mouseenter').unbind('mouseleave').mouseenter(function(){
		clearTimeout(printMessageTimer);
	}).mouseleave(function(){
		printMessageTimer = setTimeout('closeMessage()', 30000);
	});
	
	/* close */
	$('body>div.'+type+' div.close').unbind('click').click(function(){
		closeMessage();
	});
	
	$('input.error,select.error,textarea.error').focus(function(){
		$(this).removeClass('error');
	});
	$('table.error').click(function(){
		$(this).removeClass('error');
	});
	
	this.closeMessage = function(){
		$('body>div.'+type).animate({marginTop: height}, 'fast', function(){
			$('body>div.'+type).remove();
		});
		clearTimeout(printMessageTimer);
	};
};

/**
*
* fieldset handler
* 
**/
$(document).ready(function(){
	flFieldset();
});

var flFieldset = function(){
	$('div.fieldset td.arrow').unbind('click').click(function(){
		var arrow = this;
		var parent = $(this).closest('.fieldset');
		var id = $(parent).attr('id');
		var cookies = readCookie('fieldset');
		
		if ( $(parent).find('div.body>div').is(':visible') )
		{
			if ( cookies )
			{
				cookies = cookies.split(',');
				if ( cookies.indexOf(id) < 0 )
				{
					cookies.push(id);
					createCookie('fieldset', cookies.join(','), 62);
				}
			}
			else
			{
				createCookie('fieldset', id, 62);
			}
			
			$(parent).find('div.body>div').slideUp(function(){
				$(arrow).addClass('up');
			});
		}
		else
		{
			if ( cookies )
			{
				cookies = cookies.split(',');
				cookies.splice(cookies.indexOf(id), 1);
				createCookie('fieldset', cookies.join(','), 62);
			}
			
			$(parent).find('div.body>div').slideDown(function(){
				$(arrow).removeClass('up');
			});
			//ereaseCookie();
		}
	});
	
	var cookies = readCookie('fieldset');
	cookies = cookies ? cookies.split(',') : false;
	
	$('div.fieldset').each(function(){
		if ( cookies )
		{
			var id = $(this).attr('id');
			if ( cookies.indexOf(id) >= 0 )
			{
				$(this).find('>div.body>div').hide();
				$(this).find('>table>tbody>tr>td.arrow').addClass('up');
			}
		}
	});
}

/**
*
* prompt alert
*
* @param string message - prompt message text
* @param srting method  - javascript method (function)
* @param Array  params  - method (function) params
* @param string load_object  - load object ID
* 
**/
function rlConfirm( message, method, params, load_object )
{
	if (confirm(message))
	{
		var func = method+'('+params+')';
		
		eval(func);
		
		if ( load_object != '')
		{
			$('#'+load_object).fadeIn('normal');
		}
	}
}

/**
* add more link to the main menu
**/
function flMainMenuMore()
{
	var ul_width = $('ul#main_menu').width();
	var li_sum_width = 0;
	$('ul#main_menu > li').each(function(){
		li_sum_width += $(this).outerWidth();
	});
	
	if ( li_sum_width > ul_width )
	{
		
	}
}

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
	if (path != undefined)
	{
		$(path).slideUp('fast');
	}

	if ( $( '#'+id ).css('display') == 'block' )
	{
		$( '#'+id ).slideUp('normal');
	}
	else
	{
		$( '#'+id ).slideDown('slow');
	}
}

/* adaptation for IE6 */
if(!Array.indexOf)
{
    Array.prototype.indexOf = function(obj){
        for(var i=0; i<this.length; i++){
            if(this[i]==obj){
                return i;
            }
        }
        return -1;
    }
}

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
	if ( !str )
		return;
		
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
	if ( !str )
		return;
		
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