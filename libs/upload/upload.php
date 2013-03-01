<?php
/*
 * jQuery File Upload Plugin PHP Example 5.2.6
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://creativecommons.org/licenses/MIT/
 */

$reefless -> loadClass('Actions');
$reefless -> loadClass('Resize');
$reefless -> loadClass('Crop');
$reefless -> loadClass('Listings');

class UploadHandler extends reefless
{
    var $options;
    
    function UploadHandler($options=null) {
        $this->options = array(
            'script_url' => $_SERVER['PHP_SELF'],
            'upload_dir' => RL_FILES,
            'upload_url' => RL_FILES_URL,
            'dir_name' => null,// personal listing directory
            'param_name' => 'files',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            'accept_file_types' => '/^image\/(gif|jpeg|png)$/i',
            'accept_file_types_ie' => '/\.(gif|jpeg|png|jpg|jpe)$/i',
            'max_number_of_files' => null,
            'discard_aborted_uploads' => true,
            'image_versions' => array(
                'large' => array(
                    'prefix' => 'large_',
                    'max_width' => $GLOBALS['config']['pg_upload_large_width'] ? $GLOBALS['config']['pg_upload_large_width'] : 640,
                    'max_height' => $GLOBALS['config']['pg_upload_large_height'] ? $GLOBALS['config']['pg_upload_large_height'] : 480,
					'watermark' => true
                ),
                'thumbnail' => array(
                    'prefix' => 'thumb_',
                    'max_width' => $GLOBALS['config']['pg_upload_thumbnail_width'] ? $GLOBALS['config']['pg_upload_thumbnail_width'] : 120,
                    'max_height' => $GLOBALS['config']['pg_upload_thumbnail_height'] ? $GLOBALS['config']['pg_upload_thumbnail_height'] : 90,
					'watermark' => false
                )
            )
        );
        if ($options) {
            $this->options = array_replace_recursive($this->options, $options);
        }
    }
    
    function get_file_object($file_name) {
        $file_path = $this->options['upload_dir'].$file_name;
        if (is_file($file_path) && $file_name[0] !== '.') {
            $file = new stdClass();
            $file -> name = $file_name;
            $file -> primary = 0;
            $file -> description = '';
            $file -> size = filesize($file_path);
            $file -> url = $this->options['upload_url'].rawurlencode($file->name);
            foreach( $this -> options['image_versions'] as $version => $options)
            {
                if (is_file($options['upload_dir'].$file_name))
                {
                    $file -> {$version.'_url'} = $options['upload_url'].rawurlencode($file->name);
                }
            }
            $file -> delete_url = $this->options['script_url'] . '?file='.rawurlencode($file->name);
            $file -> delete_type = 'DELETE';
            return $file;
        }
        return null;
    }
    
    function get_file_objects() {
    	global $listing_id, $account_info, $config;
    	
    	$photos = $this -> fetch( '*', array( 'Listing_ID' => $listing_id ), "ORDER BY `Position`", null, 'listing_photos' );
    	if ( !$photos )
    		return false;
    	
    	$controller = defined('REALM') && REALM == 'admin' ? 'admin' : 'account';
    		
    	foreach ($photos as $photo)
    	{
    		$info = getimagesize(RL_FILES . $photo['Photo']);
    		$file = new stdClass();
    		$file -> id = $photo['ID'];
    		$file -> listing_id = $photo['Listing_ID'];
    		$file -> description = $photo['Description'];
    		$file -> is_crop = $photo['Original'] && $config['img_crop_interface'] ? 1 : 0;
    		$file -> name = $photo['Photo'];
    		$file -> primary = $photo['Type'] == 'main' ? 1 : 0;
    		$file -> size = filesize(RL_FILES . $photo['Photo']);
    		$file -> type = $info['mime'];
    		$file -> url = RL_FILES_URL . $photo['Photo'];
    		$file -> thumbnail_url = RL_FILES_URL . $photo['Thumbnail'];
    		$file -> original = RL_FILES_URL . $photo['Original'];
    		$file -> delete_url = RL_LIBS_URL .'upload/'. $controller .'.php?file='. $photo['Thumbnail'] .'&id='. $listing_id;
    		$file -> delete_type = 'DELETE';
    		
    		$files[] = $file;
    	}
		
		return $files;
    }

	function create_scaled_image($file_name, $new_file_name, $options, $version) {
    	global $rlResize, $rlCrop, $config, $rlHook;
    	
    	$file_path = $this -> options['upload_dir'] . $file_name;
    	$new_file_path = $this -> options['upload_dir'] . $new_file_name;
    	
    	$rlHook -> load('phpUploadScaledImage');

    	if ( $config['img_crop_module'] || $version == 'thumbnail' )
    	{
	    	$rlCrop -> loadImage($file_path);
			$rlCrop -> cropBySize($options['max_width'], $options['max_height'], ccCENTER);
			$rlCrop -> saveImage($new_file_path, $config['img_quality']);
			$rlCrop -> flushImages();
    	}
		
		$rlResize -> resize( $config['img_crop_module'] || $version == 'thumbnail' ? $new_file_path : $file_path, $new_file_path, 'C', array($options['max_width'], $options['max_height']), $version == 'thumbnail' ? true : false, $options['watermark'] );
		
		return true;
    }
    
    function has_error($uploaded_file, $file, $error) {
    	global $rlHook;
    	
    	$rlHook -> load('phpUploadHasError');
    	
        if ($error) {
            return $error;
        }
        if (!preg_match($this->options['accept_file_types'], $file->type))
        {
        	if (!preg_match($this->options['accept_file_types_ie'], $file->name))
        	{
            	return 'acceptFileTypes';
        	}
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = filesize($uploaded_file);
        } else {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }
        if ( $this -> options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            return 'maxFileSize';
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            return 'minFileSize';
        }
        if (is_int($this->options['max_number_of_files']) && (
                count($this->get_file_objects()) >= $this->options['max_number_of_files'])
            ) {
            return 'maxNumberOfFiles';
        }
        return $error;
    }
    
    function handle_file_upload($uploaded_file, $name, $size, $type, $error) {
    	global $listing_id, $dir, $config, $rlHook;
    	
    	$rlHook -> load('phpUploadFileUpload');
    	
    	$ext = array_reverse(explode('.', $name));
    	$ext = $ext[0];
    	
        $file = new stdClass();
        $file -> name = 'orig_'. time() . mt_rand(). '.' .$ext;
        $file -> size = intval($size);
        $file -> type = $type;
        $error = $this -> has_error($uploaded_file, $file, $error);
        $controller = defined('REALM') && REALM == 'admin' ? 'admin' : 'account';
        
        if ( !$error && $file -> name )
        {
            $file_path = $this -> options['upload_dir'] . $file -> name;
            $append_file = is_file($file_path) && $file -> size > filesize($file_path);
            clearstatcache();
            if ( $uploaded_file && is_uploaded_file($uploaded_file) )
            {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file)
                {
                    file_put_contents($file_path, fopen($uploaded_file, 'r'), FILE_APPEND);
                }
                else
                {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            }
            else
            {
                // Non-multipart uploads (PUT method support)
                file_put_contents($file_path, fopen('php://input', 'r'), $append_file ? FILE_APPEND : 0);
            }
            
            $file_size = filesize($file_path);
            
            if ( $file_size === $file -> size )
            {
                $file -> url = $this -> options['upload_url'] . rawurlencode($file->name);
                
                foreach($this->options['image_versions'] as $version => $options)
                {
                	$new = $options['prefix'] . time() . mt_rand(). '.' .$ext;
                	if ( $version == 'thumbnail' )
                	{
                		$delete_filename = $new;
                	}
                    if ( $this -> create_scaled_image( $file -> name, $new, $options, $version ) )
                    {
                        $file -> {$version} = $new;
                    }
                }
                
                /* remove original image if crop user interface disabled */
                if ( !$config['img_crop_interface'] )
                {
                	unlink($file_path);
                }
            }
            else if ( $this -> options['discard_aborted_uploads'] )
            {
                unlink($file_path);
                $file -> error = 'abort';
            }
            $file -> size = $file_size;
            $file -> delete_url = RL_LIBS_URL .'upload/'. $controller .'.php?file='. $this -> options['dir_name'] . rawurlencode($delete_filename).'&id='.$listing_id;
            $file -> delete_type = 'DELETE';
        } else {
            $file->error = $error;
        }
        return $file;
    }
    
    function get() {
    	global $rlJson, $rlHook;
    	
    	$rlHook -> load('phpUploadGet');
    	
        $file_name = isset($_REQUEST['file']) ?
            basename(stripslashes($_REQUEST['file'])) : null; 
        if ($file_name) {
            $info = $this->get_file_object($file_name);
        } else {
            $info = $this->get_file_objects();
        }
        header('Content-type: application/json');
        echo $rlJson -> encode($info);
    }
    
    function post() {
    	global $listing_id, $rlActions, $rlJson, $config, $rlHook;
    	
    	/* file directories handler */
    	$cur_photo = $this -> getOne('Photo', "`Listing_ID` = '{$listing_id}'", 'listing_photos');
    	if ( $cur_photo )
    	{
    		$exp_dir = explode('/', $cur_photo);
    		if ( count($exp_dir) > 1 )
    		{
    			array_pop($exp_dir);
    			$dir = RL_FILES . implode(RL_DS, $exp_dir) . RL_DS;
    			$dir_name = implode('/', $exp_dir) .'/';
    		}
    	}
    	
    	if ( !$dir )
    	{
	    	$dir = RL_FILES . date('m-Y') . RL_DS .'ad'. $listing_id . RL_DS;
	    	$dir_name = date('m-Y') .'/ad'. $listing_id .'/';
    	}
    	
    	$url = RL_FILES_URL . $dir_name;
		$this -> rlMkdir($dir);
    	
		$rlHook -> load('phpUploadPost');
		
		$this -> options['upload_dir'] = $dir;
		$this -> options['upload_url'] = $url;
		$this -> options['dir_name'] = $dir_name;
		
        $upload = isset($_FILES[$this->options['param_name']]) ?
            $_FILES[$this->options['param_name']] : array(
                'tmp_name' => null,
                'name' => null,
                'size' => null,
                'type' => null,
                'error' => null
            );
        $info = array();
        if (is_array($upload['tmp_name'])) {
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    isset($_SERVER['HTTP_X_FILE_NAME']) ?
                        $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
                    isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                        $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
                    isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                        $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
                    $upload['error'][$index]
                );
            }
        } else {
            $info[] = $this->handle_file_upload(
                $upload['tmp_name'],
                isset($_SERVER['HTTP_X_FILE_NAME']) ?
                    $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'],
                isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                    $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'],
                isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                    $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'],
                $upload['error']
            );
        }
        
        $max_pos = $this -> getRow("SELECT MAX(`Position`) AS `Max` FROM `". RL_DBPREFIX ."listing_photos` WHERE `Listing_ID` = {$listing_id}");
        $max_pos = $max_pos['Max'];
        
        /* add pictures to db */
        foreach ( $info as $index => $photo )
        {
        	$max_pos++;
        	$insert = array(
        		'Listing_ID' => $listing_id,
        		'Position' => $max_pos,
        		'Photo' => $dir_name . $photo -> large,
        		'Thumbnail' => $dir_name . $photo -> thumbnail,
        		'Original' => $config['img_crop_interface'] ? $dir_name . $photo -> name : '',
        		'Description' => '',
        		'Type' => 'photo',
        		'Status' => 'active'
        	);
        	
        	/* insert new photo */
        	$rlActions -> insertOne($insert, 'listing_photos');

            /* send data */
        	$info[$index] -> id = mysql_insert_id();
        	$info[$index] -> listing_id = $listing_id;
        	$info[$index] -> primary = 0;
        	$info[$index] -> is_crop = $config['img_crop_interface'] ? 1 : 0;
        	$info[$index] -> description = '';
        	$info[$index] -> original = RL_FILES_URL . $dir_name . $photo -> name;
        	$info[$index] -> thumbnail_url = $this -> options['upload_url'] . $photo -> thumbnail;
        	
        	/* update photos data */
            $GLOBALS['rlListings'] -> updatePhotoData($listing_id);
        }
        
        header('Vary: Accept');
        if (isset($_SERVER['HTTP_ACCEPT']) &&
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        echo $rlJson -> encode($info);
    }
    
    function delete() {
    	global $listing_id, $rlJson, $rlHook;
    	
    	$id = (int)$_REQUEST['id'];
        $file_name = isset($_REQUEST['file']) ? stripslashes($_REQUEST['file']) : null;
        $file_path = $this -> options['upload_dir'] . $file_name;
        
        $rlHook -> load('phpUploadDelete');
        
		$success = $id == $listing_id && is_file($file_path) && unlink($file_path);
        $info = $this -> fetch(array('Thumbnail', 'Photo', 'Original'), array('Listing_ID' => $listing_id, 'Thumbnail' => $file_name), null, 1, 'listing_photos', 'row');
        
        if ( $id == $listing_id && $info )
        {
			$original_dir = $this -> options['upload_dir'] . $info['Original'];
			unlink($original_dir);
			
			$photo_dir = $this -> options['upload_dir'] . $info['Photo'];
			unlink($photo_dir);
			
            /* remove from DB */
            $this -> query("DELETE FROM `". RL_DBPREFIX ."listing_photos` WHERE `Listing_ID` = '{$listing_id}' AND `Thumbnail` = '{$file_name}' LIMIT 1");
            
            /* update photos data */
            $GLOBALS['rlListings'] -> updatePhotoData($listing_id);
            
            /* remove related directory if it is empty */
            $del_dir = explode('/', $photo_dir);
            array_pop($del_dir);
            $this -> deleteDirectory(implode(RL_DS, $del_dir) . RL_DS, true);
        }
        header('Content-type: application/json');
        echo $rlJson -> encode($success);
    }
}

$upload_handler = new UploadHandler();

header('Pragma: no-cache');
header('Cache-Control: private, no-cache');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'HEAD':
    case 'GET':
        $upload_handler->get();
        break;
    case 'POST':
        $upload_handler->post();
        break;
    case 'DELETE':
        $upload_handler->delete();
        break;
    case 'OPTIONS':
        break;
    default:
        header('HTTP/1.0 405 Method Not Allowed');
}
