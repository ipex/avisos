<?php
/* Copyright */

/* ext js action */
if ( $_GET['q'] == 'ext' )
{
	// system config
	require_once( '../../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL .'ext_header.inc.php' );
	require_once( RL_LIBS .'system.lib.php' );
	
	/* date update */
	if ($_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );
		
		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br($_GET['value']) );
		$id = $rlValid -> xSql( $_GET['id'] );
		$key = $rlValid -> xSql( $_GET['key'] );

		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);

		$rlActions -> updateOne( $updateData, 'bwt_payment_details');
		exit;
	}

	/* data read */
	$limit = $rlValid -> xSql( $_GET['limit'] );
	$start = $rlValid -> xSql( $_GET['start'] );

	$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.* ";
	$sql .= "FROM `" . RL_DBPREFIX . "bwt_payment_details` AS `T1` ";
	$sql .= "ORDER BY `T1`.`Position` DESC LIMIT {$start}, {$limit}";

	$data = $rlDb -> getAll( $sql );                                                                                                      
	$count = $rlDb -> getRow( "SELECT FOUND_ROWS() AS `count`" );

	foreach($data as $key => $val)
	{
		$data[$key]['name'] = $lang['payment_details+name+'.$val['Key']];
		$data[$key]['description'] = $lang['payment_details+des+'.$val['Key']];
	}
	$output['total'] = $count['count'];
	$output['data'] = $data;

	$reefless -> loadClass( 'Json' );
	echo $rlJson -> encode( $output );
	exit();
}

if ( isset( $_GET['action'] ) )
{
	// additional bread crumb step
	$bcAStep[0] = array('name' => $lang['bwt_payment_wire_transfer'], 'Controller' => 'bank_wire_transfer', 'Vars' => 'module=payment_details');
	$bcAStep[1] = array('name' => $_GET['action'] == 'add' ? $lang['bwt_add_item'] : $lang['bwt_edit_item']);

	if ( $_GET['action'] == 'add' || $_GET['action'] == 'edit' )
	{
		// get account types
		$reefless -> loadClass('Account');

		// get current plan info
		if ( isset( $_GET['item'] ) )
		{
			$id = (int)$_GET['item'];

			$payment_detail = $rlDb -> fetch('*', array('ID' => $id), null, null, 'bwt_payment_details', 'row');
			$rlSmarty -> assign_by_ref('payment_detail', $payment_detail);
		}

		if ( $_GET['action'] == 'edit' && !$_POST['fromPost'] )
		{
			$_POST['key'] = $payment_detail['Key'];

			// get names
			$names = $rlDb -> fetch( array('Code', 'Value'), array('Key' => 'payment_details+name+'. $payment_detail['Key']), "AND `Status` <> 'trash'", null, 'lang_keys' );
			foreach ($names as $pKey => $pVal)
			{
				$_POST['name'][$names[$pKey]['Code']] = $names[$pKey]['Value'];
			}
                  
			// get description
			$descriptions = $rlDb -> fetch( array('Code', 'Value'), array('Key' => 'payment_details+des+'. $payment_detail['Key']), "AND `Status` <> 'trash'", null, 'lang_keys' );
			foreach ($descriptions as $pKey => $pVal)
			{
				$_POST['description'][$descriptions[$pKey]['Code']] = $descriptions[$pKey]['Value'];
			}
			unset( $names, $descriptions );
		}

		if ( isset( $_POST['submit'] ) )
		{
			$reefless -> loadClass('Actions');

			$errors = $error_fields = array();

			// check name
			$f_name = $_POST['name'];
			$f_description = $_POST['description'];

			foreach( $allLangs as $lkey => $lval )
			{
				if ( empty( $f_name[$allLangs[$lkey]['Code']] ) )
				{
					array_push( $errors, str_replace('{field}', "<b>{$lang['name']}({$allLangs[$lkey]['name']})</b>", $lang['notice_field_empty']) );
					array_push( $error_fields, "name[{$lval['Code']}]" );
				}
				$f_names[$allLangs[$lkey]['Code']] = $f_name[$allLangs[$lkey]['Code']];
			}

			if ( !empty( $errors ) )
			{
				$rlSmarty -> assign_by_ref( 'errors', $errors );
			}
			else 
			{
				if ( $_GET['action'] == 'add' )
				{                                                                          
					$f_key = $rlValid->xSql($_POST['key']);
					$f_key = $rlValid->str2key($f_key);

					// get max position
					$position = $rlDb -> getRow( "SELECT MAX(`Position`) AS `max` FROM `" . RL_DBPREFIX . "bwt_payment_details`" );

					// write main plan information
					$data = array(
						'Key' => $f_key,
						'Position' => $position['max'] + 1
					);

					if ( $action = $rlActions -> insertOne($data, 'bwt_payment_details') )
					{
						// write name's phrases
						foreach ($allLangs as $key => $value)
						{
							$lang_keys[] = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Status' => 'active',
								'Key' => 'payment_details+name+' . $f_key,
								'Value' => $f_name[$allLangs[$key]['Code']],
								'Plugin' => 'bankWireTransfer'
							);

							if ( !empty($f_description[$allLangs[$key]['Code']]) )
							{
								$lang_keys[] = array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Status' => 'active',
									'Key' => 'payment_details+des+' . $f_key,
									'Value' => $f_description[$allLangs[$key]['Code']],
									'Plugin' => 'bankWireTransfer'
								);
							}
						}
						$rlActions -> insert($lang_keys, 'lang_keys');

						$message = $lang['bwt_item_added'];
						$aUrl = array( "controller" => $controller, 'module' => 'payment_details' );
					}
					else 
					{
						trigger_error( "Can't add new banner plan (MYSQL problems)", E_WARNING );
						$rlDebug -> logger("Can't add new banner plan (MYSQL problems)");
					}
				}
				elseif ( $_GET['action'] == 'edit' )
				{
					$f_key = $payment_detail['Key'];
                                  
					// update the lang_keys
					foreach( $allLangs as $key => $value )
					{
						if ( $rlDb -> getOne('ID', "`Key` = 'payment_details+name+{$f_key}' AND `Code` = '{$allLangs[$key]['Code']}'", 'lang_keys') )
						{
							// edit names
							$update_phrases = array(
								'fields' => array(
									'Value' => $f_name[$allLangs[$key]['Code']]
								),
								'where' => array(
									'Code' => $allLangs[$key]['Code'],
									'Key' => 'payment_details+name+' . $f_key
								)
							);
							$rlActions -> updateOne($update_phrases, 'lang_keys');
						}
						else
						{
							// insert names
							$insert_phrases = array(
								'Code' => $allLangs[$key]['Code'],
								'Module' => 'common',
								'Key' => 'payment_details+name+' . $f_key,
								'Value' => $f_name[$allLangs[$key]['Code']],
								'Plugin' => 'bankWireTransfer'
							);
							$rlActions -> insertOne( $insert_phrases, 'lang_keys' );
						}

						// edit description's values
						$c_query = $rlDb -> fetch( array('ID'), array( 'Key' => 'banner_plans+des+' . $f_key, 'Code' => $allLangs[$key]['Code'] ), null, null, 'lang_keys', 'row' );
						if ( !empty( $c_query ) )
						{
							if ( !empty( $f_description[$allLangs[$key]['Code']] ) )
							{
								$lang_keys_des[] = array(
									'where' => array(
										'Code' => $allLangs[$key]['Code'],
										'Key' => 'payment_details+des+' . $f_key
									),
									'fields' => array(
										'Value' => $f_description[$allLangs[$key]['Code']]
									)
								);
							}
							else 
							{
								$rlDb -> query( "DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'payment_details+des+{$f_key}' AND `Code` = '{$allLangs[$key]['Code']}'" );
							}
						}
						else 
						{
							if ( !empty( $f_description[$allLangs[$key]['Code']] ) )
							{
								$lang_keys_des = array(
									'Code' => $allLangs[$key]['Code'],
									'Module' => 'common',
									'Status' => 'active',
									'Key' => 'payment_details+des+' . $f_key,
									'Value' => $f_description[$allLangs[$key]['Code']],
									'Plugin' => 'bankWireTransfer'
								);
								$rlActions -> insertOne( $lang_keys_des, 'lang_keys' );
							}
						}
					}

					$rlActions -> update($lang_keys_des, 'lang_keys');
					$action = true;

					$message = $lang['bwt_item_edited'];
					$aUrl = array( "controller" => $controller, 'module' => 'payment_details' );
				}

				if ( $action )
				{
					$reefless -> loadClass('Notice');
					$rlNotice -> saveNotice($message);
					$reefless -> redirect($aUrl);
				}
			}
		}
	}
}
else
{
	// additional bread crumb step
	$bcAStep = $lang['bwt_payment_wire_transfer'];

	$rlXajax -> registerFunction( array('deleteItem', $rlBankWireTransfer, 'ajaxDeleteItem') );
}
?>
