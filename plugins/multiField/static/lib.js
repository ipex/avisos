
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: LIB.JS
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

var importExportClass = function(){
	var self = this;
	var item_width = width = percent = percent_value = sub_width = sub_item_width = sub_percent = sub_percent_value = sub_percent_to_show = percent_to_show = 0;
	var window = false;
	var request;
	
	this.phrases = new Array();
	this.config = new Array();
		
	this.import = function(index){
		/* show window */
		if ( index == 0 )
		{
			if ( !window )
			{
				window = new Ext.Window({
					applyTo: 'statistic',
					layout: 'fit',
					width: 447,
					height: 160,
					closeAction: 'hide',
					plain: true
			    });
			    
			    window.addListener('hide', function(){
	            	self.stop();
	            });
			}
		    
			window.show();
		}
		
	    /* import request */
	    request = $.getJSON("../plugins/multiField/admin/import.php", {index: index}, function(response){
			if( response['finish'] )
			{
				itemsGrid.reload();
				printMessage('notice', self.phrases['completed']);
				setTimeout(function(){
					window.hide();
				}, 2000);				
			}
			else
			{
				if ( response['current'] == 1 )
				{
					$('#total').html(response['count']);
					
					var runs = response['count'];
					item_width = 362/runs;

					percent_value = 100/runs;
					$('#loading_percent').show();
				}

				if( index == 0 )
				{
					$('#current_text').html( response['current_text'] );
					width += item_width;
					percent = response['current'] >= response['count'] ? 100 : percent + percent_value;
					percent_to_show = Math.ceil(percent);
	
					sub_width = sub_percent = sub_item_width = 0;

					var sub_runs = Math.ceil( response['sub_count']/response['limit'] );
	
					sub_item_width = 362/sub_runs;
					sub_percent_value = 100/sub_runs;

					$('#processing').css('width', width+'px');

					$('#loading_percent').html(percent_to_show+'%');

					$('#current_text').html( response['current_text'] );
					$('#current').html( response['current'] );

					$('#sub_loading_percent').html('0%');
					$('#sub_loading_percent').hide();
				}else
				{
					sub_percent +=sub_percent_value;
					sub_width += sub_item_width;
					$('#sub_loading_percent').show();
				}

				sub_percent_to_show = Math.ceil(sub_percent);

				$('#sub_processing').css('width', sub_width+'px');
				$('#sub_loading_percent').html(sub_percent_to_show+'%');

/*				$('#current_text').html( response['current_text'] );
				$('#current').html( response['current'] );*/

				index = response['index'];

				self.import(index);
			}
		});
	}
	
	this.stop = function(){
		request.abort();
	}
	
	this.start = function(){
		self.import(0);
	}
}

var importExport = new importExportClass();
