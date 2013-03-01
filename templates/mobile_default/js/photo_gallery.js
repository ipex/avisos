
/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
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
 *	Flynax Classifieds Software 2013 | All copyrights reserved.
 *	
 *	http://www.flynax.com/
 ******************************************************************************/

var item_float = rlLangDir == 'rtl' ? 'right' : 'left';
var item_float_rev = rlLangDir == 'rtl' ? 'left' : 'right';
var screenWidth;
var thumbnailSize = 'height: 55px;';
var thumbnailSizePx = '55';

$(document).ready(function(){
	/* build preview */
	buildGallary();
	
	/* build main photo */
	var obj_main_photo = '<img style="width: '+screenWidth+'px" src="'+files_url+photos[0][1]+'" title="'+photos[0][2]+'" alt="'+photos[0][2]+'" />';
	$('#peview_photo').html(obj_main_photo);
	$('#peview_photo').attr({accessKey: files_url+photos[0][1], title: photos[0][2]});
	
	/* build photo list */
	var obj_photos = '';
	for( var pgi = 0; pgi < photos.length; pgi++ )
	{
		obj_photos += '<div class="item"><img id="photo_'+pgi+'" style="'+thumbnailSize+'" src="'+files_url+photos[pgi][0]+'" alt="" title="'+photos[pgi][2]+'" /></div>';
	}
	$('#scroll div.inner').html(obj_photos);
	$('#photo_0').parent().addClass('active');
	
	scrollClick();
	
	/* thumbnail click handler */
	$('div#scroll div.item').click(function(){
		var id = $(this).find('img').attr('id').split('_')[1];
		
		var src = files_url + photos[id][1];
		$('#peview_photo img').attr('src', src);
		
		var img = new Image();
		var self = this;
		img.onload = function(){
			$(self).find('div.mask').remove();
		}
		img.src = src;
		
		$('div#scroll div.item').removeClass('active');
		$(this).addClass('active');
		
		/* append mask */
		$(this).append('<div class="mask"></div>');
		$(this).find('div.mask').width($(this).width()).height($(this).height()).css('opacity', 0.5);
	});
});

var buildGallary = function(){
	screenWidth = parseInt($('#width_tracker').width());
	
	screenWidth = screenWidth > 0 ? screenWidth: 300;
	screenWidth -= 20;
	
	$('div#preview').width(screenWidth);
	$('div#preview img').width(screenWidth);
	$('div#thumbnails div#scroll').width(screenWidth-50);
}

var scrollClick = function(){
	var obj = 'div#scroll';
	var count = $(obj).find('div.inner div.item').length;
	var itemWidth = Math.floor((thumbnail_width * thumbnailSizePx) / thumbnail_height) + 2;
	var visibleWidth = $(obj).width();
	var margin = 3;
	var poss = 0;
	var activeItem = 0;
	var visible = Math.floor(visibleWidth/itemWidth);
	var perSlide = visible;
	var diff = Math.ceil(visibleWidth - (visible*itemWidth));
	
	var newMargin = Math.floor(diff / (visible > 1 ? visible - 1 : visible));
	if ( newMargin > margin )
	{
		$(obj).find('div.item:not(:last)').css('margin-'+item_float_rev, newMargin+'px');
		margin = newMargin;	
	}
	
	/* back to 0 */
	if ( rlLangDir == 'rtl' )
	{
		$(obj).find('div.inner').css({
			marginRight: 0
		});
	}
	else
	{
		$(obj).find('div.inner').css({
			marginLeft: 0
		});
	}
	
	$('.next').unbind('click').click(function(){
		if ( (activeItem + visible) >= count )
		{
			return;
		}
		
		poss -= (itemWidth + margin) * perSlide;
		activeItem += perSlide;
		
		if ( rlLangDir == 'rtl' )
		{
			$(obj).find('div.inner').css({
				marginRight: poss
			});
		}
		else
		{
			$(obj).find('div.inner').css({
				marginLeft: poss
			});
		}
	});
	$('.prev').unbind('click').click(function(){
		if ( activeItem <= 0 )
		{
			return;
		}
		
		poss += (itemWidth + margin) * perSlide;
		activeItem -= perSlide;
		
		if ( rlLangDir == 'rtl' )
		{
			$(obj).find('div.inner').css({
				marginRight: poss
			});
		}
		else
		{
			$(obj).find('div.inner').css({
				marginLeft: poss
			});
		}
	});
};

window.onorientationchange = function()
{
	buildGallary();
	scrollClick();
}