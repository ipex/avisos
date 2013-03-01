
/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: FLYNAX.LIB.JS
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
	* current step by switchStep method
	**/
	this.currentStep;
	
	/**
	* the reference to the self object
	**/
	var self = this;
	
	/**
	* random featured
	*
	* @param int delay - slideshow delay in seconds
	* @param array photos - photos to slide
	* @param array data - listing title and url
	**/
	this.randomFeatured = function( delay, photos, data ){
		var index = 0;
		var timer = false;
		var busy = false;
		delay *= 1000;
		
		if ( photos )
		{
			index++;
			timer = setTimeout('play()', delay);
		}
		
		play = function(){
			busy = true;
			
			var img = new Image();
			img.onload = function(){
				var title = data && data[index]['title'] ? quote(data[index]['title'], true) : '';
				$('.random_featured div.picture a img').after('<img title="'+title+'" src="'+rlConfig['files_url']+photos[index]+'" class="tmp" />');
				
				if ( data && data[index]['url'] )
				{
					$('.random_featured div.picture a').attr('href', data[index]['url']);
				}
				if ( data )
				{
					$('.random_featured div.fields').html(data[index]['fields'] != undefined ? data[index]['fields'] : '');
				}
				$('.random_featured div.picture a img:first').animate({opacity: 0});
				$('.random_featured div.picture a img:first').next().fadeIn(function(){
					$(this).removeClass('tmp');
					$('.random_featured div.picture a img:first').remove();
					busy = false;
				});
				
				$('.random_featured ul li.active').removeClass('active');
				$('.random_featured ul li:eq('+index+')').addClass('active');
				
				index++;
				if ( index == photos.length )
				{
					index = 0;
				}
				
				timer = setTimeout('play()', delay);
				
			};
			img.src = rlConfig['files_url'] + photos[index];
		};
		
		$('.random_featured ul li').click(function(){
			if ( busy )
				return;
				
			index = $('.random_featured ul li').index($(this));
			clearTimeout(timer);
			play();
		});
		
		$('.random_featured .prev').click(function(){
			if ( busy )
				return;
				
			var current = $('.random_featured ul li').index($('.random_featured ul li.active'));
			index = current == 0 ? photos.length - 1 : current - 1;
			clearTimeout(timer);
			play();
		});
		
		$('.random_featured .next').click(function(){
			if ( busy )
				return;
				
			clearTimeout(timer);
			play();
		});
	};
	
	/**
	* random list of listings
	**/
	this.randomList = function(){
		var height;
		var search_area = 'table.type_top_content>tbody>tr>td:first>div.sell_space';
		var target_area = 'td.random_featured div.random_list';
		
		/* set area sizes */
		if ( $('table.type_top_content>tbody>tr>td').length > 1 )
		{
			height = $(search_area).outerHeight();
		}
		else
		{
			height = 210;
		}
		
		$(target_area).outerHeight(height);
		$(target_area+' div.inner').height($(search_area).height());
		$(target_area).show();
		
		/* scroller handler */
		var inner_height = $(target_area).find('>div.inner').height();
		var ul_height = $(target_area).find('>div.inner>ul').height();
		var progress = false;
		
		$(target_area).parent().find('.nav').css('opacity', 0).show();
		
		if ( ul_height > inner_height )
		{
			/* navigation */
			$(target_area).find('.nav').mouseenter(function(){
				progress = true;
				$(this).stop().animate({opacity: 1}, function(){ progress = false; });
			}).mouseleave(function(e){
				progress = false;
				$(this).stop().animate({opacity: 0.6});
				
				if ( $(e.relatedTarget).hasClass('random_list') || $(e.relatedTarget).context.localName == 'li' )
				{
					$(target_area).trigger('mouseenter');
				}
			});
			
			/* run theatre */
			$(target_area).find('ul').theatre({
				effect: 'vertical'
			});
			
			/* listeners */
			$(target_area).mouseenter(function(){
				if ( !progress )
				{
					$(this).find('.nav').stop().animate({opacity: 0.6});
				}
			}).mouseleave(function(){
				if ( !progress )
				{
					$(this).find('.nav').stop().animate({opacity: 0});
				}
			});
			
			$(target_area).find('.nav').click(function(){
				$(target_area).find('ul').theatre('next')
			});
			$(target_area).find('.prev').click(function(){
				$(target_area).find('ul').theatre('prev')
			});
		}
	};
	
	/**
	* registration steps handler
	**/
	this.registration = function(no_message){
		var base = this;
		
		/* check user exit */
		$('input[name="profile[username]"]').blur(function(){
			var val = $(this).val();
			if ( val != '' )
			{
				xajax_userExist(val);
			}
		});
		
		/* check email exit */
		$('input[name="profile[mail]"]').blur(function(){
			var val = $(this).val();
			if ( val != '' )
			{
				xajax_emailExist(val);
			}
		});
		
		/* check type fields exist */
		$('select[name="profile[type]"]').change(function(){
			base.accountTypeChange(this);
		});
		
		/* check personal address */
		if ( account_types[$('select[name="profile[type]"]').val()] )
		{
			$('input[name="profile[location]"]').blur(function(){
				var val = $(this).val();
				if ( val != '' )
				{
					xajax_checkLocation(val);
				}
			});	
		}

		/* validate "on the fly" fields */
		xajax_validateProfile(
			$('input[name="profile[username]"]').val(), 
			$('input[name="profile[mail]"]').val(), 
			$('input[name="profile[location]"]').val(),
			account_types[$('select[name="profile[type]"]').val()]
		);
		
		if ( $('select[name="profile[type]"] option').length > 1 )
		{
			this.accountTypeChange($('select[name="profile[type]"]'), true);
		}
		
		var check_repeat = false;
		/* check for password */
		$('input[name="profile[password]"]').blur(function(){
			if ( $('input[name="profile[password]"]').val().length < 3 )
			{
				printMessage('error', lang['notice_reg_length'].replace('{field}', lang['password']), 'profile[password]');
			}
			else
			{
				check_repeat = true;
				
				if ( rlConfig['account_password_strength'] && $('#password_strength').val() < 3 )
				{
					printMessage('warning', lang['password_weak_warning'])
				}
				else if ( rlConfig['account_password_strength'] && $('#password_strength').val() >= 3 )
				{
					$('div.warning div.close').trigger('click');
				}
			}
		});
		
		$('input[name="profile[password_repeat]"]').blur(function(){
			if ( !$(this).val() )
				return;
				
			if ( $(this).next().hasClass('fail_field') || $(this).next().hasClass('success_field') )
			{
				$(this).next().remove();
			}
			
			var pass = $('input[name="profile[password]"]').val();
			
			if ( pass != '' && check_repeat )
			{
				if ( $(this).val() != pass )
				{
					printMessage('error', lang['notice_pass_bad'], 'profile[password_repeat]');
					$(this).after('<span class="fail_field">&nbsp;</span>')
				}
				else
				{
					$('div.error div.close').trigger('click');
					$(this).removeClass('error').after('<span class="success_field">&nbsp;</span>')
				}
			}
		});
		
		/* profile submit */
		$('form[name=account_reg_form]').submit(function(){
			if ( reg_account_submit )
			{
				return true;
			}
			else
			{
				if ( reg_account_type && reg_account_type != $('select[name="profile[type]"]').val() )
				{
					reg_account_fields = 0;
				}
				xajax_submitProfile(
					$('input[name="profile[username]"]').val(),
					$('input[name="profile[password]"]').val(),
					$('input[name="profile[mail]"]').val(),
					$('input[name="profile[display_email]"]').attr('checked'),
					$('select[name="profile[type]"]').val(),
					$('input[name="profile[location]"]').val(),
					$('input[name=security_code]').val(),
					reg_account_fields
				);
				$(this).val(lang['loading']);
			
				return false;
			}
		});
	};
	
	/**
	* account type change event hendler | Secondary method
	**/
	this.accountTypeChange = function(obj, direct){
		var val = $(obj).val();
		if ( val != '' )
		{
			xajax_checkTypeFields(val);
		}
		
		/* description replacement */
		if ( val != '' )
		{
			$('img.qtip').hide();
			$('img.desc_'+val).show();
		}
		else
		{
			$('img.qtip').hide();
		}
		
		/* personal address toggle */
		if ( val != '' && account_types[val] )
		{
			if ( direct )
			{
				$('#personal_address_field').show();
			}
			else
			{
				$('#personal_address_field').slideDown();
			}
		}
		else
		{
			$('#personal_address_field').slideUp();
		}
	};
	
	/**
	* switch steps by requested step key
	**/
	this.switchStep = function(step){
		$('table.steps td').removeClass('active');
		$('table.steps td#step_'+step).prevAll().attr('class', 'past');
		$('table.steps td#step_'+step).attr('class', 'active');
		$('div.step_area').hide();
		$('div.area_'+step).show();
		$('input[name=reg_step]').val(step);
		
		this.currentStep = step;
	};
	
	/**
	* password strength handler
	**/
	this.passwordStrength = function(){
		$('#pass_strength').html(lang['password_strength_pattern'].replace('{number}', 0).replace('{maximum}', 5));
		
		$('input[name="profile[password]"]').keyup(function(){
			doMatch(this);
		});
		
		var strengthHandler = function( val ){
			var strength = 0;
			var repeat = new RegExp('(\\w)\\1+', 'gm');
			val = val.replace(repeat, '$1');
			
			/* check for lower */
			var lower = new RegExp('[a-z]+', 'gm');
			var lower_matches = val.match(lower);
			var lower_strength = 0;
			if (lower_matches)
			{
				for(var i=0; i<lower_matches.length;i++)
				{
					lower_strength += lower_matches[i].length;
				}
			}
			if (lower_strength >= 2 && lower_strength <= 4)
			{
				strength++;
			}
			else if (lower_strength > 4)
			{
				strength += 2;
			}
			
			/* check for upper */
			var upper = new RegExp('[A-Z]+', 'gm');
			var upper_matches = val.match(upper);
			var upper_strength = 0;
			if (upper_matches)
			{
				for(var i=0; i<upper_matches.length;i++)
				{
					upper_strength += upper_matches[i].length;
				}
			}
			if (upper_strength > 0 && upper_strength < 3)
			{
				strength++;
			}
			else if (upper_strength >= 3)
			{
				strength += 2;
			}
			
			/* check for numbers */
			var number = new RegExp('[0-9]+', 'gm');
			var number_matches = val.match(number);
			var number_strength = 0;
			if (number_matches)
			{
				for(var i=0; i<number_matches.length;i++)
				{
					number_strength += number_matches[i].length;
				}
			}
			if (number_strength > 0 && number_strength < 4)
			{
				strength++;
			}
			else if (number_strength >= 4)
			{
				strength += 2;
			}
			
			/* check for system symbols */
			var symbol = new RegExp('[\!\@\#\$\%\^\&\*\(\)\-\+\|\{\}\:\?\/\,\<\>\;\\s]+', 'gm');
			var symbol_matches = val.match(symbol);
			var symbol_strength = 0;
			if (symbol_matches)
			{
				for(var i=0; i<symbol_matches.length;i++)
				{
					symbol_strength += symbol_matches[i].length;
				}
			}
			if (symbol_strength > 0 && symbol_strength < 3)
			{
				strength++;
			}
			else if (symbol_strength >= 3 && symbol_strength < 5)
			{
				strength += 2;
			}
			else if (symbol_strength >= 3 && symbol_strength >= 5)
			{
				strength += 3;
			}
			
			/* check for length */
			if ( val.length >= 8 )
			{
				strength += 0.5;
			}
			
			strength = strength > 5 ? 5 : strength;	
			return Math.floor(strength);
		};
		
		var doMatch = function(obj)
		{
			var password = $(obj).val();
			var strength = strengthHandler(password);
			var scale = new Array('', 'red', 'red', 'yellow', 'yellow', 'green');
			
			$('div.password_strength div.scale div.color').width(strength*20+'%').attr('class', '').addClass('color').addClass(scale[strength]);
			$('div.password_strength div.scale div.shine').width(strength*20+'%');
			$('#pass_strength').html(lang['password_strength_pattern'].replace('{number}', strength).replace('{maximum}', 5));
			
			$('#password_strength').val(strength);
		};
		
		doMatch($('input[name="profile[password]"]'));
	};
	
	/**
	* qtips handler
	**/
	this.qtip = function(direct){
		if ( direct )
		{
			this.qtipInit();
		}
		else
		{
			var base = this;
			$(document).ready(function(){
				base.qtipInit();
			});
		}
	}
	
	/**
	* qtips init | secondary method
	**/
	this.qtipInit = function(){
		$('.qtip').each(function(){
			$(this).qtip({
				content: $(this).attr('title') ? $(this).attr('title') : $(this).prev('div.qtip_cont').html(),
				show: 'mouseover',
				hide: 'mouseout',
				position: {
					corner: {
						target: 'topRight',
						tooltip: 'bottomLeft'
					}
				},
				style: qtip_style
			}).attr('title', '');
		});
	};
	
	/**
	* languages selector
	**/
	this.langSelector = function(){
		var lang_bar_open = false;
		$(document).ready(function(){
			$('div#user_navbar div.languages div.bg').click(function(event){
				if ( !lang_bar_open )
				{
					$(this).find('ul').show();
					$('div#user_navbar div.languages').addClass('active');
					$('#current_lang_name').show();
					lang_bar_open = true;
				}
				else
				{
					if ( event.target.localName == 'a' )
						return;
						
					$('div#user_navbar div.languages div.bg').find('ul').hide();
					$('div#user_navbar div.languages').removeClass('active');
					$('#current_lang_name').hide();
					lang_bar_open = false;
				}
			});
		});
		
		$(document).click(function(event){
			var close = true;
			
			$(event.target).parents().each(function(){
				if ( $(this).attr('class') == 'languages' )
				{
					close = false;
				}
			});
			
			if ( $(event.target).parent().attr('class') == 'bg' ||$(event.target).attr('class') == 'bg' || $(event.target).attr('class') == 'arrow' || event.target.localName == 'a' )
			{
				close = false;
			}

			if ( close )
			{
				$('div#user_navbar div.languages div.bg').find('ul').hide();
				$('div#user_navbar div.languages').removeClass('active');
				$('#current_lang_name').hide();
				lang_bar_open = false;
			}
		});
	};
	
	/**
	* payment gateways handler
	**/
	this.paymentGateway = function(){
		$('ul#payment_gateways li').click(function(){
			$('ul#payment_gateways li').removeClass('active');
			$(this).addClass('active');
			$(this).find('input').attr('checked', true);
		});
	};
	
	/**
	* upload video ui handler
	**/
	this.uploadVideoUI = function(){
		this.videoTypeHandler = function(slide){
			if ( !$('#video_type').length )
			{
				return false;
			}
			
			var id = $('#video_type').val().split('_')[0];
			if ( slide )
			{
				$('.upload').slideUp();
				$('#'+id+'_video').slideDown('slow');
			}
			else
			{
				$('.upload').hide();
				$('#'+id+'_video').show();
			}
		}
		
		var base = this;
		$('#video_type').change(function(){
			base.videoTypeHandler(true);
		});
		
		this.videoTypeHandler();
	};
	
	/**
	* categories tree level loader
	**/
	this.treeLoadLevel = function(tpl, callback){
		$('div.tree ul li>img:not(.no_child), div.tree li.locked a:not(.add)').unbind('click').click(function(){
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
				xajax_getCatLevel(id, false, tpl, callback);
				$(this).parent().find('span.tree_loader').fadeIn('fast');
				$(this).parent().find('img:first,a:first').addClass('done');
			}
		});
		
		$('div.tree span.tmp_info a').click(function(){
			$(this).parent().hide();
			$(this).parent().next().show();
		});
		$('div.tree span.tmp_input img').click(function(){
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
	* save search link handler
	**/
	this.saveSearch = function(){
		$('span#save_search').click(function(){
			$(this).flModal({
				type: 'notice',
				content: save_search_notice,
				prompt: 'xajax_saveSearch("'+$(this).attr('class')+'")',
				width: 'auto',
				height: 'auto',
				click: false
			});
		});
	};
	
	/**
	* search form tabs handler
	**/
	this.searchTabs = function(){
		$('ul.search_tabs li').click(function(){
			$('ul.search_tabs li.active').removeClass('active');
			$(this).addClass('active');
			var index = $('ul.search_tabs li').index(this);
			
			$('div.search_tab_area').hide();
			$('div.search_tab_area input.search_tab_hidden').attr('disabled', true);
			$('div.search_tab_area:eq('+index+')').show();
			$('div.search_tab_area:eq('+index+') input.search_tab_hidden').attr('disabled', false);
		});
	};
	
	/**
	* multilingual tabs handler
	**/
	this.mlTabs = function(){
		$('div.ml_tabs>ul>li').click(function(){
			/* set active tab */
			$(this).parent().find('li.active').removeClass('active');
			$(this).addClass('active');
			
			/* open related content */
			var code = $(this).attr('lang');
			$(this).parent().parent().next().find('>div:visible').hide();
			$(this).parent().parent().next().find('>div[lang='+code+']').show();
			$(this).parent().parent().next().find('>div[lang='+code+'] input, >div[lang='+code+'] textarea').focus();
		});
		
		/* process tabs scrolling */
		$('div.ml_tabs').each(function(){
			var div_width = $(this).width();
			var ul_width = $(this).find('>ul').width();
			var li_width = 0;
			$(this).find('>ul>li').each(function(){
				var side = rlLangDir == 'ltr' ? 'right' : 'left';
				var side_padding = parseInt($(this).css('padding-left'));
				var side_margin = parseInt($(this).css('margin-'+side));

				li_width += $(this).width() + (side_padding*2) + side_margin;
			});
			li_width += 2;
			
			if ( li_width > div_width )
			{
				var self = this;
				var diff = li_width - div_width;
				
				$(this).width(div_width);
				$(this).find('>ul').width(li_width);
				$(this).addClass('scrolling');
				
				$(this).mouseenter(function(){
					if ( parseInt($(self).find('>ul').css('left')) < 0 )
					{
						$(this).find('>div.left').fadeIn();
					}
					if ( Math.abs(parseInt($(self).find('>ul').css('left'))) < diff )
					{
						$(this).find('>div.right').fadeIn();
					}
				}).mouseleave(function(){
					$(this).find('>div.nav').fadeOut();
				});

				$(this).find('>div.right').mouseenter(function(){
					$(self).find('>div.left').fadeIn();
					$(self).find('>ul').animate({left: -10000}, {
						duration: 15000,
						step: function(now){
							if ( diff <= Math.abs(now) )
							{
								$(this).stop();
							}
						}
					});
				}).mouseleave(function(){
					$(self).find('>ul').stop();
				});

				$(this).find('>div.left').mouseenter(function(){
					$(self).find('>div.right').fadeIn();
					$(self).find('>ul').animate({left: 10000}, {
						duration: 15000,
						step: function(now){
							console.log(now)
							if ( 0 <= now )
							{
								$(this).stop();
							}
						}
					});
				}).mouseleave(function(){
					$(self).find('>ul').stop();
				});
			}
		});
	};
	
	/**
	* field from and to phrases handler
	**/
	this.fromTo = function(from, to){
		$('input.field_from').focus(function(){
			if ( $(this).val() == from )
			{
				$(this).val('');
			}
		}).blur(function(){
			if ( $(this).val() == '' )
			{
				$(this).val(from);
			}
		});
		$('input.field_to').focus(function(){
			if ( $(this).val() == to )
			{
				$(this).val('');
			}
		}).blur(function(){
			if ( $(this).val() == '' )
			{
				$(this).val(to);
			}
		});
	};
	
	/**
	* highlight search results in grid
	**/
	this.highlightSRGrid = function(query){
		if ( !query )
			return;
		
		query = trim(query);
		var repeat = new RegExp('(\\s)\\1+', 'gm');
		query = query.replace(repeat, ' ');
		query = query.split(' ');
		
		var pattern = '';
		for (var i=0; i<query.length; i++)
		{
			if ( query[i].length > 2 )
			{
				pattern += query[i]+'|'
			}
		}
		pattern = rtrim(pattern, '|');
		
		var pattern = new RegExp('('+pattern+')', 'gi');
		var link_pattern = new RegExp('<a([^>]*)>(.*)</a>');
		
		$('#listings div.list div.item td.fields>span,#listings table.table div.item td.fields td.value').each(function(){
			var value = trim($(this).html());
			var href = false;
			if ( $(this).find('a').length > 0 )
			{
				value = trim($(this).find('a').html());
				href = $(this).find('a').attr('href');
			}

			//value = value.replace(/(<([^>]+)>)/ig,"");
			value = value.replace(pattern, '<span class="ks_highlight">$1</span>');
			value = href ? '<a href="'+href+'">'+value+'</a>' : value;
				
			$(this).html(value);
		});
	};
	
	/**
	* fighlight search results on listing details
	**/
	this.highlightSRDetails = function(query){
		query = trim(query);
		
		if ( !query )
			return false;
		
		var repeat = new RegExp('(\\s)\\1+', 'gm');
		query = query.replace(repeat, ' ');
		query = query.split(' ');
		
		var pattern = '';
		for (var i=0; i<query.length; i++)
		{
			if ( query[i].length > 2 )
			{
				pattern += query[i]+'|'
			}
		}
		pattern = rtrim(pattern, '|');
		
		var pattern = new RegExp('('+pattern+')', 'gi');
		
		var link_pattern = new RegExp('<a([^>].*)>(.*)</a>');
		
		$('table.listing_details td.details table.table td.value').each(function(){
			var value = trim($(this).html());
			var href = false;
			if ( value.indexOf('<a') >= 0 )
			{
				var matches = value.match(link_pattern);
				if ( matches[2] )
				{
					value = trim(matches[2]);
					href = matches[1];
				}
			}
			value = value.replace(pattern, '<span class="ks_highlight">$1</span>');
			value = href ? '<a '+href+'>'+value+'</a>' : value;
			$(this).html(value);
		});
	};
	
	/**
	* plans click handler, available for any plans lists | "selected_plan_id" and "last_plan_id" public variables are required
	**/
	this.planClick = function(obj){
		if ( obj.length == 0 )
			return;

		selected_plan_id = $(obj).attr('id').split('_')[1];
		var plan = plans[selected_plan_id];
		
		/* show payment gateways */
		if ( plan['Price'] <= 0 || ((plan['Listings_remains'] || plan['Standard_listings'] == 0 || plan['Featured_listings'] == 0) && plan['Package_ID']) )
		{
			$('#fs_gateways').slideUp();
		}
		/* hide payment gateways */
		else
		{
			$('#fs_gateways').slideDown();
		}
		
		if ( last_plan_id == selected_plan_id )
		{
			return;
		}
		
		last_plan_id = selected_plan_id;
			
		$('div.featured_option').hide();
		$('div.featured_option').prev().show();
		$('div.featured_option input').attr('disabled', true);
		
		//if ( plan['Featured'] && plan['Advanced_mode'] )
		if ( plan['Package_ID'] )
		{
			$('#featured_option_'+selected_plan_id).prev().hide();
			$('#featured_option_'+selected_plan_id).show();
			$('#featured_option_'+selected_plan_id+' input').attr('disabled', false);
			$('#featured_option_'+selected_plan_id+' input.disabled').attr('disabled', true);
			$('#featured_option_'+selected_plan_id+' input:not(.disabled):first').attr('checked', true);
			$('#featured_option_'+selected_plan_id+' input.checked:not(.disabled)').attr('checked', true);
		}
	};
	
	/**
	* is payment gateway method selected checker
	**/
	this.isGatewaySelected = function(){
		if ( $('form[name=payment] input:checked').length <= 0 )
		{
			printMessage('error', lang['gateway_fail']);
			return false;
		}
		
		return true;
	};
	
	/**
	* run contact owner xajax method on form submit
	*
	* @param object obj - clicked element
	* @param int listing_id - requested listing id
	**/
	this.contactOwnerSubmit = function(obj, listing_id){
		xajax_contactOwner($('#contact_name').val(), $('#contact_email').val(), $('#contact_phone').val(), $('#contact_owner_message').val(), $('#contact_code_security_code').val(), listing_id);
		$(obj).val(lang['loading']);
	};
	
	/**
	* get page hash, # removed
	**/
	this.getHash = function(){
		var hash = window.location.hash;
		return hash.substring(1);
	};
	
	/**
	* show/hide other sub-categories
	**/
	this.moreCategories = function(){
		$('div.sub_categories span.more').click(function(){
			$('div.other_categories_tmp').remove();
			
			var pos = $(this).offset();
			var sub_cats = $(this).parent().find('div.other_categories').html();
			var tmp = '<div class="other_categories_tmp"><div></div></div>'
			$('body').append(tmp);
			$('div.other_categories_tmp div').html(sub_cats);
			$('div.other_categories_tmp div').append('<img class="close" title="'+lang['close']+'" src="'+rlConfig['tpl_base']+'img/blank.gif" />');
			
			var rest = rlLangDir == 'ltr' ? 0 : $('div.other_categories_tmp').width();
			$('div.other_categories_tmp').css({
				top: pos.top,
				left: pos.left-rest,
				display: 'block'
			});
			
			$('div.other_categories_tmp div img.close').click(function(){
				$('div.other_categories_tmp').remove();
			});
		});
		
		$(document).click(function(event){
			if ( $(event.target).parent().attr('class') != 'other_categories_tmp' 
				&& $(event.target).parent().parent().attr('class') != 'other_categories_tmp' 
				&& $(event.target).attr('class') != 'more' 
				
			)
			{
				$('div.other_categories_tmp').remove();
			}
		});
	};
	
	/**
	* content type switcher
	*
	* @param array fields - array of the form fields
	*
	**/
	this.htmlEditor = function(fields){
		if ( !fields )
			return;
	
		var configs = {
			toolbar: [
				['Source', '-', 'Bold', 'Italic', 'Underline', 'Strike'],
				['Link', 'Unlink', 'Anchor'],
				['Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat'],
				['TextColor', 'BGColor']
			],
			height: 160,
			language: rlLang
		};
		
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
	* on map fields handler
	**/
	this.onMapHandler = function(){
		$('input[name="f[account_address_on_map]"]').change(function(){
			if ( parseInt($('input[name="f[account_address_on_map]"]:checked').val()) )
			{
				$('.on_map').find('input, textarea, select').attr('disabled', true).addClass('disabled');
			}
			else
			{
				$('.on_map').find('input, textarea, select').attr('disabled', false).removeClass('disabled');
			}
		});
		
		if ( parseInt($('input[name="f[account_address_on_map]"]:checked').val()) )
		{
			$('.on_map').find('input, textarea, select').attr('disabled', true).addClass('disabled');
		}
		else
		{
			$('.on_map').find('input, textarea, select').attr('disabled', false).removeClass('disabled');
		}
	};

	this.multiCatsHandler = function(){
		$('select.multicat').change(function(){
//			var ltype = $(this).attr('id').split('Category_ID')[1].split('_')[1];
			var ltype = trim( $(this).attr('id').split('Category_ID')[1].split('level')[0], '_' );
			var level = parseInt( $(this).attr('id').split('level')[1] );
			var form_key = $(this).attr('id').split('Category_ID')[0].substring(0, $(this).attr('id').split('Category_ID')[0].length -1 );

			var next_level = level+1;
			var next_field = $(this).attr('id').replace('level'+level, 'level'+next_level);

			$('#'+next_field).val(0).attr('disabled','disabled');
			
			var post_field = $(this).attr('id').replace('level'+level, 'value');
			if( $(this).val() != '0' && $(this).val() )
			{
				$('#'+post_field).val( $(this).val() );
				if( !$(this).hasClass('last') )
				{
					xajax_multiCatNext( $(this).val(), ltype, form_key, level );
				}
			}
			else
			{
				if(level != 0)
				{
					var prev_level = level - 1;
					var prev_field = $(this).attr('id').replace('level'+level, 'level'+prev_level);
					$('#'+post_field).val( $('#'+prev_field).val() );
				}else
				{
					$('#'+post_field).val(0);
				}
			}
		});
		$('input[name="f[Category_ID]"]').each(function(){
			if( $(this).val() != '0' && $(this).val() )
			{
				xajax_multiCatBuild( $(this).val(), $(this).attr('id') );
			}
		});
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
					$(this).next('input,select').focus();
				}
				
				if ( $(this).val().length == 0 && event.keyCode == 8)
				{
					$(this).prev('input,select').focus().select();
				}
			});
		});
	}
}

var flynax = new flynaxClass();
