
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: {version}
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: PHOTO_GALLERY.JS
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

$(document).ready(function(){
	/* enable fancybox */
	$('div.photos div.preview a, div#imgSource a').fancybox({
		padding: 10,
		removeFirst: true,
		customIndex: true,
//		prevEffect: 'fade',
//		nextEffect: 'fade',
		//autoPlay: true,
		mouseWheel: true,
		closeBtn: fb_slideshow_close,
		playSpeed: fb_slideshow_delay,
		helpers: {
    		title: {
    			type: 'over'
    		},
    		overlay: {
				opacity: 0.5
			},
			buttons: fb_slideshow
    	}
	});
	
	if ( $('div.slider').length <= 0 )
		return;
	
	var marginCss = rlLangDir == 'ltr' ? 'marginRight' : 'marginLeft';
		
	/* set sizes */
	var slider_width = $('div.photos div.slider').width();
	var item_width = $('div.photos div.slider ul li:first').width();
	var items = $('div.photos div.slider ul img').length;
	var pages = $('div.photos div.slider ul li').length;
	var items_per_slide = $('div.photos div.slider ul li:first img').length;
	var moving_width = item_width * items;
	var slider_position = 0;
	
	$('div.photos div.slider').width(slider_width);
	$('div.photos div.slider ul').width(moving_width).fadeIn();
	
	for ( var i=0; i<pages; i++ )
	{
		var attr_class = i == 0 ? ' class="active"' : '';
		$('div.photos div.navigation').append('<a'+attr_class+' id="imgnav_'+i+'" href="javascript:void(0)"><span></span></a>');
	}
	
	var currentImage = 0;
	/* thumbnail click handler */
	$('div.photos div.slider ul img').click(function(){
		loadImage(this);
	});
	
	var loadImage = function(obj){
		var index = $(obj).index('div.photos div.slider ul img');
		if ( currentImage == index )
		{
			return;
		}
		currentImage = index;
		
		$(obj).after('<div class="img_loading"></div>');
		var width = $(obj).width();
		var height = $(obj).height();
		var border = $(obj)[0].clientLeft;
		
		$(obj).next().width(width).height(height).css({opacity: 0.5});
		var photo_src = rlConfig['files_url'] + $(obj).attr('class');
		
		$.fancybox.setIndex(currentImage);
		
		var img = new Image();
		img.onload = function(){
			$('div.photos div.preview img').attr('src', photo_src);
			
			$(obj).next().fadeOut('normal', function(){
				$(this).remove();
			});
		}
		img.src = photo_src;
	}
	
	/* navigation click handler */
	$('div.photos div.navigation a').click(function(){
		var point = parseInt($(this).attr('id').split('_')[1]);
		
		if ( slider_position == point )
		{
			return;
		}
		
		slider_position = point;
		
		moveSlider();
	});
	$('div.photos div.next').click(function(){
		if ( slider_position + 1 < pages)
		{
			slider_position++;
			moveSlider();
		}
	});
	$('div.photos div.prev').click(function(){
		if ( slider_position )
		{
			slider_position--;
			moveSlider();
		}
	});
	
	var moveSlider = function(){
		var new_pos = (slider_position * item_width) * -1;
		
		if ( rlLangDir == 'ltr' )
		{
			$('div.photos div.slider ul').animate({marginLeft: new_pos});
		}
		else
		{
			$('div.photos div.slider ul').animate({marginRight: new_pos});
		}
		$('div.photos div.navigation a').removeClass('active');
		$('div.photos div.navigation a:eq('+slider_position+')').addClass('active');
		
		var index = slider_position * items_per_slide;
		loadImage($('div.photos div.slider ul img:eq('+index+')'));
		
		if ( !slider_position )
		{
			$('div.photos div.prev').fadeOut();
		}
		else
		{
			$('div.photos div.prev').fadeIn();
		}
		
		if ( slider_position + 1 == Math.ceil(pages) )
		{
			$('div.photos div.next').fadeOut();
		}
		else
		{
			$('div.photos div.next').fadeIn();
		}
	};
});