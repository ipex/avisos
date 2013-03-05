<!-- photos manager -->

<div class="dark">{$lang.max_file_size_caption} <b>{$max_file_size} MB</b></div>
				
{assign var='width' value=$config.pg_upload_thumbnail_width+4}
{assign var='height' value=$config.pg_upload_thumbnail_height-50+4}

<div id="fileupload">
	<form onsubmit="return false;" action="{$smarty.const.RL_LIBS_URL}upload/account.php" method="post" enctype="multipart/form-data">
		<span class="files canvas"></span>
		<span title="{$lang.add_photo}" class="draft fileinput-button">
			{$lang.add_photo}
			{assign var='replace' value=`$smarty.ldelim`count`$smarty.rdelim`}
			{if $allowed_photos}<span class="allowed">{$lang.allowed_count|replace:$replace:$allowed_photos}</span>{/if}
			<input type="file" name="files[]" />
		</span>

		<div><input type="button" class="start" value="{$lang.upload}" /></div>
	</form>
</div>

{literal}
<script id="template-upload" type="text/x-jquery-tmpl">
	<span class="item active template-upload">
		<span class="preview"></span><span class="start"></span>
		<img src="{/literal}{$rlTplBase}{literal}img/blank.gif" class="cancel" alt="{/literal}{$lang.delete}{literal}" title="{/literal}{$lang.delete}{literal}" />
		<span class="progress"></span>
		<div class="photo_navbar"></div>
	</span>
</script>
<script id="template-download" type="text/x-jquery-tmpl">
	<span class="item active template-download">
		<img class="thumbnail" src="${thumbnail_url}" />
		<img data-type="${delete_type}" data-url="${delete_url}" src="{/literal}{$rlTplBase}{literal}img/blank.gif" class="delete" alt="{/literal}{$lang.delete}{literal}" title="{/literal}{$lang.delete}{literal}" />
		<img src="{/literal}{$rlTplBase}{literal}img/blank.gif" alt="" class="loaded" />
		<div class="photo_navbar" id="navbar_${id}">
			<span class="primary">
				<span class="dark_12{{if !primary}} hide{{/if}}">{/literal}<b>{$lang.primary}</b>{literal}</span>
				<a class="brown_12{{if primary}} hide{{/if}}" onclick="xajax_makeMain(${listing_id}, ${id})" href="javascript:void(0)" title="{/literal}{$lang.set_primary}{literal}">{/literal}{$lang.set_primary}{literal}</a>
			</span>
			<input class="hide" type="text" name="description" value="${description}" />
			<input onclick="xajax_editDesc(${id}, $(this).prev().val())" class="accept hide" type="button" name="accept" />
			{{if is_crop}}<img id="crop_photo_${id}" dir="${original}" title="{/literal}{$lang.crop_photo}{literal}" src="{/literal}{$rlTplBase}{literal}img/blank.gif" class="crop" alt="" />{{/if}}
			<img title="{/literal}{$lang.manage_description}{literal}" src="{/literal}{$rlTplBase}{literal}img/blank.gif" class="edit" alt="" />
		</div>
	</span>
</script>
{/literal}

<style type="text/css">
div#fileupload span.hover
{literal}{{/literal}
	width: {if $config.pg_upload_thumbnail_width}{$config.pg_upload_thumbnail_width}{else}120{/if}px;
	height: {if $config.pg_upload_thumbnail_height}{$config.pg_upload_thumbnail_height}{else}90{/if}px;
{literal}}{/literal}
</style>

<script type="text/javascript">
var photo_allowed = {if $plan_info.Image_unlim}undefined{else}{if $plan_info.Image}{$plan_info.Image}{else}0{/if}{/if};
var photo_max_size = {if $max_file_size}{$max_file_size}{else}2{/if}*1024*1024;
var photo_width = {if $config.pg_upload_thumbnail_width}{$config.pg_upload_thumbnail_width}{else}120{/if};
var photo_height = {if $config.pg_upload_thumbnail_height}{$config.pg_upload_thumbnail_height}{else}90{/if};
var photo_auto_upload = {if $config.img_auto_upload}true{else}false{/if};
var photo_listing_id = {if $listing_id}{$listing_id}{else}{$smarty.session.add_listing.listing_id}{/if};
var sort_save = false;
lang['error_maxFileSize'] = "{$lang.error_maxFileSize}";
lang['error_acceptFileTypes'] = "{$lang.error_acceptFileTypes}";
lang['uploading_completed'] = "{$lang.uploading_completed}";
lang['upload'] = "{$lang.upload}";

var ph_empty_error = "{$lang.crop_empty_coords}";
var ph_too_small_error = "{$lang.crop_too_small}";

{literal}
$(document).ready(function(){
	$('#fileupload input[type=file]').attr('multiple', true);
	$('#fileupload').fileupload();
	
	if ( photo_allowed == undefined )
	{
		$('#fileupload span.draft span.allowed').hide();
	}
	
	$.getJSON($('#fileupload form').prop('action'), function (files) {
		var fu = $('#fileupload').data('fileupload');
		fu._adjustMaxNumberOfFiles(-files.length);
		fu._renderDownload(files)
			.appendTo($('#fileupload .files'))
			.fadeIn(function () {
				$(this).show();
				managePhotoDesc();
				crop_handler();
		});
    });
});

var submit_photo_step = function(){
	/* check for not uploaded photos */
	var not_saved = $('#fileupload span.template-upload').length;
	if ( not_saved > 0 )
	{
		$('#fileupload span.template-upload').addClass('suspended');
		printMessage('warning', lang['unsaved_photos_notice'].replace('{number}', not_saved));
		
		return false;
	}
	else
	{
		return true;
	}
};

var managePhotoDesc = function(){
	$('#fileupload div.photo_navbar img.edit')
		.unbind('click')
		.click(function(){
			var parent = $(this).parent();
			var id = $(parent).attr('id');
			$(parent).find('span.primary, img.edit, img.crop').hide();
			$(parent).find('input').show();
	});
	
	$("#fileupload span.files").sortable({
		items: 'span.item:not(.template-upload)',
		placeholder: 'hover',
		handle: 'img.thumbnail',
		start: function(event, obj){
			$(obj.item).find('div.photo_navbar').hide();
		},
		stop: function(event, obj){
			$(obj.item).find('div.photo_navbar').show();
			/* save sorting */
			var sort = '';
			var count = 0;
			$('#fileupload span.files span.template-download div.photo_navbar').each(function(){
				var id = $(this).attr('id').split('_')[1];
				count++;
				var pos = $('#fileupload span.files span.item').index($(this).parent())+1;
				sort += id+','+pos+';';
			});
			
			if ( sort.length > 0 && count > 1 && sort_save != sort )
			{
				sort_save = sort;
				sort = rtrim(sort, ';');
				xajax_reorderPhoto(photo_listing_id, sort);
			}
		}
	});
};
{/literal}
</script>

<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}upload/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}upload/jquery.fileupload.js"></script>
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}upload/jquery.fileupload-ui.js"></script>

<!-- photos manager end -->