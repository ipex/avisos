<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LISENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLVERIFICATIONCODE.CLASS.PHP
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

class rlVerificationCode extends reefless
{	
	function rlVerificationCode() {}

	function updateCodesHook()
	{
		$GLOBALS['reefless'] -> loadClass( 'Actions' );
		$GLOBALS['rlActions'] -> rlAllowHTML = true;

		$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.* ";
		$sql .= "FROM `" . RL_DBPREFIX . "verification_code` AS `T1` ";
		$sql .= "WHERE `T1`.`Status` = 'active' ";
		$sql .= "ORDER BY `T1`.`Date` DESC";

		$verification_code = $this -> getAll( $sql );

		$php = str_replace( '{codes}', serialize( $verification_code ), $this -> buildTemplateHook() );
		$php = str_replace( "'", "''", $php );

		$sql = "UPDATE `" . RL_DBPREFIX . "hooks` SET `Code` = '{$php}' WHERE `Name` = 'specialBlock' AND `Plugin` = 'verificationCode' LIMIT 1";

		if ( $this -> query( $sql ) )
		{
			return true;
		}

		return false;
	}

	function buildTemplateHook()
	{
		$content = <<< FL
		global \$rlDb, \$rlSmarty, \$page_info;

		\$verification_code_header = array();
		\$verification_code_footer = array();

        \$verification_code = <<< VC
		{codes}
VC;

		\$verification_code = unserialize(trim(\$verification_code));

		if ( !empty( \$verification_code ) )
		{
			foreach(\$verification_code as \$key => \$val)
			{
				\$pages_item = !empty(\$val['Pages']) ? explode(",", \$val['Pages']) : array();

				if(\$val['Pages_sticky'] == 1 || in_array(\$page_info['ID'], \$pages_item))
				{
					if(\$val['Position'] == 'header')
					{
						\$verification_code_header[] = \$val;
					}
					elseif(\$val['Position'] == 'footer')
					{
						\$verification_code_footer[] = \$val;
					}
				}
			}

			\$GLOBALS['rlSmarty'] -> assign_by_ref( 'verification_code_header', \$verification_code_header );
			\$GLOBALS['rlSmarty'] -> assign_by_ref( 'verification_code_footer', \$verification_code_footer );
		}
FL;

		return $content;
	}

	function ajaxDeleteItem( $id = false )
	{
		global $_response, $lang;

		if ( !$id )
		{
			return $_response;
		}
		
		$delete = "DELETE FROM `" . RL_DBPREFIX . "verification_code` WHERE `ID` = '{$id}' LIMIT 1";

		if ( $this -> query( $delete ) )
		{
			$this -> updateCodesHook();
		}

		// print message, update grid
		$_response -> script("
			verificationCodeGrid.reload();
			printMessage('notice', '{$lang['item_deleted']}');
		");

		return $_response;
	}
}
