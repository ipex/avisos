<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLRSS.CLASS.PHP
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

class rlRss extends reefless
{
	/**
	* @var items list
	**/
	var $items = array('title', 'link', 'description');
	
	var $items_number = 5;
	var $mXmlParser = null;
	var $mLevel = null;
	var $mTag = null;
	var $mKey = null;
	var $mItem = false;
	var $mRss = array();

	/**
	* clear data
	**/
	function clear()
	{
		$this->mXmlParser = null;
		$this->mLevel = null;
		$this->mTag = null;
		$this->mKey = null;
		$this->mItem = false;
		$this->mRss = array();
	}
	
	/**
	* start element for parser
	*
	* @param string $parser - parser object
	* @param string $name - item name
	* 
	**/
	function startElement($parser, $name) 
	{
		$this->mLevel++;
		$this->mTag = strtolower($name);
		
		if('item' == $this->mTag)
		{
			$this->mItem = true;
			$this->mKey++;
		}
	}

	/**
	* end element for parser
	*
	* @param string $parser - parser object
	* @param string $name - item name
	* 
	**/
	function endElement($parser, $name) 
	{
		$this->mLevel--;
		
		if('item' == $this->mTag)
		{
			$this->mItem = false;
		}
	}

	/**
	* data collection
	*
	* @param string $parser - parser object
	* @param string $data - item data
	*
	**/
	function charData($parser, $data)
	{
		if ( $this->mKey <= $this->items_number )
		{
			$data = trim($data);

			$items = $this -> items;
			foreach ($items as $item)
			{
				if( $item == $this -> mTag && $this->mItem )
				{
					if(!empty($data))
					{
						$this -> mRss[$this->mKey][$item] .= $data;
					}
				}
			}
		}
	}

	/**
	* create parser
	*
	* @param string $content - content data
	* 
	**/
	function createParser($content)
	{
		$this->mXmlParser = xml_parser_create();

		xml_set_element_handler($this->mXmlParser, array(&$this, "startElement"), array(&$this, "endElement"));
		xml_set_character_data_handler($this->mXmlParser, array(&$this, "charData"));

		xml_parse($this->mXmlParser, $content);

		xml_parser_free($this->mXmlParser);
	}

	/**
	* get RSS content
	* 
	**/
	function getRssContent()
	{
		return $this->mRss;
	}
}