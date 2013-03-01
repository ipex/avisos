
/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: JQUERY.FLMAP.JS
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

/**
*
* jQuery Google Map plugin by Flynax 
*
**/
(function($){
	var base;
	
	$.flMap = function(el, options){
		base = this;
		
		// custom variable/object
		base.points = new Array();
		geocoder = new google.maps.Geocoder();
		base.map;
		base.infoWindow;
		base.bounds = new Array();
		base.markers = new Array();
		base.infoWindows = new Array();
		base.localSearch;
		base.currentResults = new Array();
		base.selectedResults = new Array();
		base.currentIcon;
		base.areaOpened = true;
		base.checkedIndex = 0;
		base.checkedStatus = false;
		base.selectedSMarker = false;
		base.letters = [['A'],['B'],['C'],['D'],['E'],['F'],['G'],['H'],['I'],['J'],['K'],['L'],['M'],['N'],['O'],['P'],['Q'],['R'],['S'],['T'],['U'],['V'],['W'],['X'],['Y'],['Z']];
		
		//base.pointKey = ['Xb', 'bc', 'ac', '$b', 'rd'];
		base.getKeyPattern = new RegExp('q=([^&][\\w-]*)');

		// icons		
		base.icons = [
			[rlConfig['libs_url']+'jquery/markers/orange-20.png'],
			[rlConfig['libs_url']+'jquery/markers/yellow-20.png'],
			[rlConfig['libs_url']+'jquery/markers/green-20.png'],
			[rlConfig['libs_url']+'jquery/markers/blue-20.png'],
			[rlConfig['libs_url']+'jquery/markers/gray-20.png'],
			[rlConfig['libs_url']+'jquery/markers/white-20.png'],
			[rlConfig['libs_url']+'jquery/markers/dark-20.png'],
			[rlConfig['libs_url']+'jquery/markers/brown-20.png']
			
		];
		
		base.activeMarkers = new Array();
		
		base.redIcon = new google.maps.MarkerImage(
			rlConfig['libs_url']+'jquery/markers/red-20.png',
			new google.maps.Size(12, 20),
			new google.maps.Point(0, 0),
			new google.maps.Point(6, 20)
		);
		
		base.smallShadow = new google.maps.MarkerImage(
			rlConfig['libs_url']+'jquery/markers/shadow-20.png',
			new google.maps.Size(22, 20),
			new google.maps.Point(0, 0),
			new google.maps.Point(6, 20)
		);
		
		// access to jQuery and DOM versions of element
		base.$el = $(el);
		base.el = el;

		// add a reverse reference to the DOM object
		base.$el.data("flMap", base);

		base.init = function(){
			base.options = $.extend({},$.flMap.defaultOptions, options);

			// initialize working object id
			if ( $(base.el).attr('id') )
			{
				base.options.id = $(base.el).attr('id');
			}
			else
			{
				$(base.el).attr('id', base.options.id);
			}
			
			// get points
			base.getPoints();
		};

		// get points by address
		base.getPoints = function()
		{
			var progress = new Array();
			var geocoderCount = 0;
			
			if ( base.options.addresses )
			{	
				for ( var i = 0; i < base.options.addresses.length; i++ )
				{
					// geocoder, collect points
					if ( base.options.addresses[i][2] == 'geocoder' )
					{
						geocoderCount++;
						
						if ( base.options.addresses[i][0] )
						{
							progress[i] = 'processing';
							
							eval("geocoder.geocode( {'address': base.options.addresses["+i+"][0]}, function(results, status) { \
								if ( status == google.maps.GeocoderStatus.OK ) \
								{ \
									base.points["+i+"] = (results[0].geometry.location); \
									progress["+i+"] = 'success'; \
								} \
								else \
								{ \
									progress["+i+"] = 'fail'; \
								} \
								\
								if ( progress.indexOf('processing') < 0 ) \
								{ \
									base.createMap(); \
								} \
							})");
						}
						else
						{
							base.createMap();
						}
					}
					else
					{
						var dPoint = base.options.addresses[i][0].split(',');
						base.points[i] = new google.maps.LatLng(dPoint[0], dPoint[1]);
					}
				}
				
				if ( geocoderCount == 0 )
				{
					base.createMap();
				}
			}
			else
			{
				if ( base.options.emptyMap )
				{
					base.createMap();
				}
			}
		};
		
		// create map
		base.createMap = function(){
			if ( base.points.length > 0 || base.options.emptyMap )
			{
				var center = base.options.emptyMap ? null : new google.maps.LatLng(base.points[0].lat(), base.points[0].lng());
				var options = {
					zoom: base.options.zoom,
					center: center,
					mapTypeId: google.maps.MapTypeId.ROADMAP,
					scrollwheel: base.options.scrollWheelZoom
				}
				base.map = new google.maps.Map(document.getElementById(base.options.id), options);
				
				base.setMarkers();
			}
			else
			{
				printMessage('error', base.options.phrases.notFound.replace('{location}', base.options.addresses[0][0]));
				return false;
			}
		};
		
		// set markers
		base.setMarkers = function(){
			base.bounds = new google.maps.LatLngBounds();
			
			if ( base.points.length > 0 )
			{
				for ( var i = 0; i < base.points.length; i++ )
				{
					var myLatLng = new google.maps.LatLng(base.points[i].lat(), base.points[i].lng());
					var icon = base.options.alphabetMarkers ? 'http://www.google.com/mapfiles/marker'+base.letters[i]+'.png' : null;
					var shadow = base.options.alphabetMarkers ? 
						new google.maps.MarkerImage('http://www.google.com/mapfiles/shadow50.png', new google.maps.Size(37, 37), new google.maps.Point(0, 0), new google.maps.Point(10, 34)) :
						null;
					
					base.markers[i] = new google.maps.Marker({
						position: myLatLng,
						map: base.map,
						icon: icon,
						shadow: shadow
					});
					
					base.attachInfo(base.markers[i], i);
					base.bounds.extend(myLatLng);
				}
				
				if ( base.points.length > 1 )
				{
					base.map.fitBounds(base.bounds);
				}
			}
			
			base.options.ready(base);
			
			// local search handler
			if ( base.options.localSearch && base.options.localSearch.services)
			{
				// create one InfoWindow to open when a marker is clicked.
				base.infoWindow = new google.maps.InfoWindow;
				google.maps.event.addListener(base.infoWindow, 'closeclick', function() {
					if ( base.currentIcon )
					{
						base.currentIcon.unselect();
					}
				});
				
				google.maps.event.addListener(base.map, 'zoom_changed', function() {
					base.infoWindow.close();
					if ( base.currentIcon )
					{
						base.currentIcon.unselect();
					}
				});
				
				// initialize the local searcher
				base.localSearch = new GlocalSearch();
				base.localSearch.setSearchCompleteCallback(null, base.onLocalSearch);
				
				// build services area
				base.buildArea();
			}
		};
		
		/* attache info to the marker */
		base.attachInfo = function(marker, i){
			base.infoWindows[i] = new google.maps.InfoWindow({
				content: base.options.addresses[i][1],
				size: new google.maps.Size(50,10)
			});
			
			google.maps.event.addListener(marker, 'click', function(){
				base.infoWindows[i].open(base.map, marker);
			});
		}
		
		// build services area
		base.buildArea = function(){
			var services = '';
			
			for ( var i = 0; i < base.options.localSearch.services.length; i++ )
			{
				if ( i < 8 )
				{
					services += '<li id="lsmi_'+i+'" style="background: url('+base.icons[i]+') 18px 3px no-repeat;" class="flgService"><label><input id="flg_'+base.options.localSearch.services[i][0]+'" type="checkbox" />'+base.options.localSearch.services[i][1]+'</label></li>';
				}
			}
			
			// add serices area
			var html = '<div class="flgServicesArea"><div class="caption">'+base.options.localSearch.caption+' <span class="fkgSlide">('+base.options.phrases.hide+')</span></div><div class="flgBody"><ul class="body">'+services+'</ul></div></div>';
			$('#'+base.options.id).append(html);
			
			$('.fkgSlide').click(function(){
				if ( base.areaOpened )
				{
					base.areaOpened = false;
					$(this).html('('+base.options.phrases.show+')');
					$('.flgServicesArea div.flgBody').slideUp();
				}
				else
				{
					base.areaOpened = true;
					$(this).html('('+base.options.phrases.hide+')');
					$('.flgServicesArea div.flgBody').slideDown();
				}
			});
			
			// set services listener
			$('div.flgServicesArea ul li input').click(function(){
				var key = $(this).attr('id').split('_')[1];
				var index = $(this).parent().parent().attr('id').split('_')[1];

				if ( $(this).is(':checked') )
				{
					base.localSearch.setCenterPoint(base.map.getCenter());
					base.localSearch.execute(key);
					base.activeMarkers[key] = index;
				}
				else
				{
					base.removeMarkers(key);
				}
			});
			
			// run default services
			base.defaultCheck();
		};
		
		// default services check
		base.defaultCheck = function(){
			if ( base.options.localSearch.services.length <= base.checkedIndex )
			{
				base.checkedStatus = false;
				return;
			}
			
			if ( base.options.localSearch.services[base.checkedIndex][2] == 'checked' )
			{
				base.checkedStatus = 'progress';
				
				base.localSearch.setCenterPoint(base.map.getCenter());
				base.localSearch.execute(base.options.localSearch.services[base.checkedIndex][0]);
				base.activeMarkers[base.options.localSearch.services[base.checkedIndex][0]] = base.checkedIndex;
				
				$('#flg_'+base.options.localSearch.services[base.checkedIndex][0]).prop('checked', true);
				base.checkedIndex++;
			}
			else
			{
				base.checkedIndex++;
				base.defaultCheck();
			}
		};
		
		// on local search
		base.onLocalSearch = function( recucive ){
			if ( !base.localSearch.results )
			{
				return;
			}
			
			// close the infowindow
			base.infoWindow.close();
			
			for ( var i = 0; i < base.localSearch.results.length; i++ )
			{
				base.currentResults.push(new base.localResult(base.localSearch.results[i]));
			}
			
			if ( base.checkedStatus == 'progress' )
			{
				base.defaultCheck();
			}
		};
		
		base.removeMarkers = function(key){
			for (var i = 0; i < base.currentResults.length; i++)
			{
				if ( base.currentResults[i].flKey == key )
				{
					base.currentResults[i].marker().setMap(null);
				}
			}
		};
		
		// a class representing a single Local Search result returned by the Google AJAX Search API.
		base.localResult = function(result)
		{
			var me = this;
			me.result_ = result;
			me.resultNode_ = me.node();
			me.marker_ = me.marker();
			
			google.maps.event.addDomListener(me.resultNode_, 'click', function() {
				me.select();
			});
		}
		
		base.localResult.prototype.node = function()
		{
			if (this.resultNode_) return this.resultNode_;
			return this.html();
		};
		
		// returns the map marker for this result, creating it with the given icon if it has not already been created.
		base.localResult.prototype.marker = function()
		{
			var me = this;
			var key = base.localSearch.gwsUrl.match(base.getKeyPattern)[1];
			
			if ( !key )
			{
				console.log("Key doesn't found, please contact Flynax Support.")
				return false;
			}
			
//			for ( var i=0; i < base.pointKey.length; i++ )
//			{
//				eval('var match = base.localSearch.'+base.pointKey[i]);
//				if ( typeof(match) != 'undefined' )
//				{
//					key = match;
//				}
//			}
			
			me.flKey = key;
			
			var icon = new google.maps.MarkerImage(
				base.icons[base.activeMarkers[key]],
				new google.maps.Size(12, 20),
				new google.maps.Point(0, 0),
				new google.maps.Point(6, 20)
			);
			
			if (me.marker_) return me.marker_;
			var marker = me.marker_ = new google.maps.Marker({
				position: new google.maps.LatLng(parseFloat(me.result_.lat),
				parseFloat(me.result_.lng)),
				icon: icon, 
				shadow: base.smallShadow, 
				map: base.map
			});
			marker.flKey = key;
			google.maps.event.addListener(marker, "click", function() {
				me.select();
			});
			
			return marker;
		};
			
		// unselect any selected markers and then highlight this result and display the info window on it.
		base.localResult.prototype.select = function()
		{
			if ( base.selectedSMarker )
			{
				base.selectedSMarker.highlight(false);
			}
			
			this.selected_ = true;
			base.selectedSMarker = this;
			this.highlight(true);
			base.infoWindow.setContent(this.html(true));
			base.infoWindow.open(base.map, this.marker());
			base.currentIcon = this;
		};
		
		base.localResult.prototype.isSelected = function()
		{
			return this.selected_;
		};
		
		// remove any highlighting on this result.
		base.localResult.prototype.unselect = function()
		{
			this.selected_ = false;
			this.highlight(false);
		};
		
		// returns the HTML we display for a result before it has been "saved"
		base.localResult.prototype.html = function()
		{
			var me = this;
			var container = document.createElement("div");
			container.className = "unselected";
			container.appendChild(me.result_.html.cloneNode(true));
			return container;
		}
		
		base.localResult.prototype.highlight = function(highlight)
		{
			var key = this.marker().flKey;
			var icon = new google.maps.MarkerImage(
				base.icons[base.activeMarkers[key]],
				new google.maps.Size(12, 20),
				new google.maps.Point(0, 0),
				new google.maps.Point(6, 20)
			);
			this.marker().setIcon(highlight ? base.redIcon : icon);
		}
		
		// run initializer
		base.init();
	};

	$.flMap.defaultOptions = {
		id: 'map',
		zoom: 8,
		scrollWheelZoom: true,
		localSearch: false,
		emptyMap: false,
		alphabetMarkers: false,
		phrases: {
			hide: 'Hide',
			show: 'Show',
			notFound: 'The <b>{location}</b> location not found'
		},
		ready: function(){}
	};

	$.flMap.get = function(){
		return base;
	};
	
	$.fn.flMap = function(options){
		return this.each(function(){
			(new $.flMap(this, options));
		});
	};

})(jQuery);