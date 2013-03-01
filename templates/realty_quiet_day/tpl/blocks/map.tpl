<!-- us states map -->
<script type="text/javascript">
var html_prefix = {if $listing_types.listings.Cat_postfix}true{else}false{/if};
var states = new Array();
{foreach from=$categories.listings item='map_cat' name='mapF'}
states["{$map_cat.Key}"] = new Array('{$map_cat.name|html_decode}{if $listing_types.listings.Cat_listing_counter} ({$map_cat.Count}){/if}', '{$map_cat.Path}', '{$map_cat.ID}');
{/foreach}

{literal}
$(document).ready(function(){
	$('#states_map area').each(function(){
		var access = $(this).attr('accesskey');
		
		if ( typeof(states[access]) != 'undefined' )
		{
			$(this).attr('title', states[access][0]).attr('alt', states[access][0]);
			var link = $(this).attr('href');
			
			if (rlConfig['mod_rewrite'])
			{
				link += states[access][1];
				link += html_prefix ? '.html' : '/';
			}
			else
			{
				link += states[access][2];
			}
			$(this).attr('href', link);
		}
	});
});
{/literal}

</script>

<div style="overflow: hidden;text-align: center;padding: 0 0 10px;"><img usemap="#map" alt="" title="" src="{$rlTplBase}img/map.jpg" /></div>
<map name="map" id="states_map">
  <area alt="" accesskey="washington" shape="poly" coords="37,38,42,40,46,42,46,47,53,48,60,49,65,51,80,51,87,53,91,53,94,36,97,21,83,16,55,8,57,17,50,17,40,11,37,21" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="oregon" shape="poly" coords="20,84,27,87,40,92,54,95,65,97,81,101,83,92,85,84,88,78,86,75,88,70,92,63,94,60,91,54,81,52,70,51,60,50,49,48,46,48,44,41,37,37,33,49,27,63,22,73,21,77" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="california" shape="poly" coords="33,184,39,185,46,191,51,192,62,211,77,212,85,214,88,212,86,210,84,204,88,204,87,202,94,196,92,192,92,191,89,187,82,177,71,162,62,150,55,141,48,131,53,108,56,96,38,91,21,86,19,94,13,102,18,111,16,120,17,126,22,135,23,141,24,145,27,148,26,156,30,167,32,175" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="alaska" shape="poly" coords="49,288,41,294,45,296,51,290,58,290,64,286,62,285,57,285,61,282,58,278,66,269,70,271,67,277,75,271,91,276,100,284,107,292,113,299,115,294,107,278,94,273,92,246,90,231,77,228,66,227,55,223,47,225,41,231,37,233,29,235,42,247,28,247,32,252,36,253,42,252,44,256,40,260,36,262,32,265,35,275,41,282" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="hawaii" shape="poly" coords="144,265,148,258,159,268,172,271,183,276,189,280,193,290,199,299,196,302,189,302,186,309,180,303,176,297,163,297,156,293,152,274" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="nevada" shape="poly" coords="49,130,61,147,69,159,77,170,84,181,90,186,93,174,97,174,98,161,100,147,104,132,104,122,107,107,105,105,80,101,57,96,53,110" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="idaho" shape="poly" coords="92,53,95,60,90,68,85,74,89,78,85,87,81,101,96,104,106,106,118,106,128,108,133,109,135,90,137,78,125,79,120,79,120,75,117,72,116,65,113,62,109,65,110,60,113,55,113,52,110,48,104,35,105,26,106,21,99,20,95,31" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="montana" shape="poly" coords="151,29,134,27,119,24,108,22,104,34,110,46,112,50,114,52,113,56,110,63,114,62,117,66,118,69,119,74,120,78,125,78,136,79,137,75,165,76,184,78,195,78,196,47,197,34,175,32" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="wyoming" shape="poly" coords="132,121,134,103,137,87,138,77,159,77,174,78,195,79,195,91,195,103,194,119,194,125,176,124,156,123" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="utah" shape="poly" coords="99,164,119,167,145,170,147,145,149,123,131,121,133,110,120,107,108,106,104,122,101,142" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="arizona" shape="poly" coords="86,215,89,212,84,207,88,204,90,201,95,195,89,185,94,175,96,174,99,165,122,167,145,170,143,202,142,220,141,234,120,233,102,223" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="new_mexico" shape="poly" coords="142,234,144,204,144,188,145,170,170,171,188,172,202,172,202,195,201,229,165,229,162,231,154,232,148,236" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="colorado" shape="poly" coords="150,124,146,169,171,171,193,172,211,172,211,126,194,126,171,124" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="oklahoma" shape="poly" coords="202,173,202,179,231,177,232,199,240,204,249,204,251,208,258,207,262,206,265,208,271,206,279,204,285,207,284,197,281,179,279,171,272,170,248,172,228,173" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="texas" shape="poly" coords="219,255,207,253,197,264,185,255,185,249,179,242,174,238,166,230,203,229,202,211,203,179,232,178,230,198,233,201,239,204,246,205,250,206,250,209,254,209,261,206,265,208,272,206,278,205,286,208,289,209,290,225,296,232,295,239,295,251,285,257,271,267,263,275,258,286,264,297,256,299,241,291,238,281,233,272,225,262" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="kansas" shape="poly" coords="212,139,212,172,238,172,252,172,266,171,279,169,279,152,277,144,275,143,276,138,273,136,256,137,238,137" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="nebraska" shape="poly" coords="195,102,195,125,211,125,211,138,247,137,273,136,267,130,265,121,260,108,256,106,249,104,245,107,242,103,221,104" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="south_dakota" shape="poly" coords="195,104,242,102,246,106,247,104,257,106,260,102,257,97,259,97,258,85,257,77,254,73,256,69,196,69" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="north_dakota" shape="poly" coords="197,34,196,68,256,69,257,59,250,34,220,33" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="minnesota" shape="poly" coords="250,34,256,56,257,66,255,72,257,78,258,89,260,96,273,95,285,95,294,94,303,94,303,90,292,82,290,78,289,70,287,69,292,64,293,57,308,41,299,40,292,40,285,39,280,38,276,39,274,36,267,26,265,33,258,34" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="iowa" shape="poly" coords="269,129,301,126,306,128,308,121,308,115,313,114,313,106,307,101,303,94,285,96,269,96,259,97,260,102,258,106,262,112,266,119" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="wisconsin" shape="poly" coords="302,89,305,99,312,105,332,100,332,81,336,71,329,75,329,63,315,62,302,55,295,58,292,64,287,71,291,77,294,83" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="missouri" shape="poly" coords="282,177,323,172,322,178,328,178,332,166,326,157,318,151,321,145,316,143,314,144,310,138,307,135,306,128,301,126,286,127,268,129,270,133,278,138,276,141,278,147,280,156,280,169" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="arkansas" shape="poly" coords="283,178,323,172,322,178,327,179,322,191,318,199,317,202,317,212,304,213,290,215,290,209,286,208,284,198,283,190" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="louisiana" shape="poly" coords="297,251,303,251,314,253,313,250,322,251,327,257,332,257,336,251,341,255,343,254,338,249,342,247,339,244,333,245,326,244,330,241,336,239,334,235,319,235,317,234,316,229,319,223,320,219,316,213,304,213,295,214,289,215,290,224,294,230,296,233,294,241" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="mississippi" shape="poly" coords="318,210,319,216,321,222,316,230,318,235,334,234,336,239,347,239,345,230,344,222,345,210,344,203,344,187,332,188,323,189,318,197,318,203" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="florida" shape="poly" coords="359,236,358,232,381,227,385,230,406,228,412,231,412,226,416,225,424,239,430,246,432,252,442,274,442,286,435,293,434,286,430,280,425,281,422,274,416,268,413,261,410,254,406,243,393,237,387,239,382,242,374,237,368,237" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="alabama" shape="poly" coords="378,209,380,213,380,223,382,227,366,230,357,232,359,236,356,237,352,234,351,239,347,238,344,230,345,222,345,209,344,202,344,188,349,185,359,185,367,184" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="tennessee" shape="poly" coords="324,188,340,187,360,186,370,184,380,180,379,175,392,168,399,162,400,156,381,162,358,165,343,167,331,169,328,178" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="georgia" shape="poly" coords="380,180,392,178,391,183,400,189,411,196,415,205,418,209,416,217,416,226,410,225,410,231,406,227,398,229,391,231,386,231,381,224,381,212,375,201,372,193,369,186,375,183" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="south_carolina" shape="poly" coords="389,179,403,173,416,173,420,176,427,172,437,182,433,187,433,194,418,210,412,197,406,192,394,184" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="illinois" shape="poly" coords="329,165,334,162,339,163,335,159,340,152,341,139,341,125,338,109,331,101,314,105,314,109,314,114,309,116,309,122,306,128,308,134,311,140,314,145,323,142,319,151" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="kentucky" shape="poly" coords="359,148,354,148,348,153,337,159,339,163,336,163,331,165,333,169,353,166,370,165,379,162,383,161,396,147,389,137,384,135,382,137,371,137,367,135,364,140,363,145" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="indiana" shape="poly" coords="342,130,342,143,340,151,337,159,344,153,350,153,355,148,361,147,363,139,368,134,365,119,361,105,344,108,340,111,340,118" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="north_carolina" shape="poly" coords="401,157,452,145,458,149,461,159,455,167,450,169,444,181,437,182,428,171,419,175,410,172,399,174,388,179,381,180,383,175,395,167" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="virginia" shape="poly" coords="425,130,417,134,413,136,411,146,407,149,402,150,397,148,393,150,383,161,392,159,405,156,415,154,427,151,441,148,448,145,452,144,451,143,446,138,445,133,445,128,439,125,432,122,428,126" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="michigan" shape="poly" coords="345,108,360,105,372,103,376,90,378,90,377,83,372,77,366,82,365,81,366,75,366,66,359,61,353,57,350,62,350,69,347,69,344,72,343,80,345,86,348,95,347,101" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="ohio" shape="poly" coords="381,105,372,104,361,104,365,119,369,135,379,137,384,135,388,138,390,136,392,130,394,129,395,125,402,118,401,109,398,95,387,103" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="west_virginia" shape="poly" coords="395,127,392,134,391,140,396,147,403,150,411,146,412,136,419,133,426,129,430,123,423,123,416,122,415,117,405,118,401,121" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="maryland" shape="poly" coords="442,125,437,123,432,121,427,121,423,122,418,122,416,121,416,119,414,116,421,114,427,112,433,112,438,110,443,110,445,117,444,120,443,122,446,126,450,131,450,136,454,137,483,134,486,141,483,144,467,145,455,140,448,134,447,129" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="pennsylvania" shape="poly" coords="402,91,398,96,403,118,424,113,440,110,448,107,452,100,447,96,448,88,442,84,422,88" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="new_york" shape="poly" coords="429,65,424,70,405,75,408,80,401,90,420,87,441,82,449,86,461,93,471,85,459,85,454,65,452,58,446,42,439,44,433,48,426,57,428,60" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="delaware" shape="poly" coords="455,128,453,136,450,135,451,127,444,125,445,117,443,109,447,107,449,115,455,118,472,118,483,119,485,127,471,126" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="new_jersey" shape="poly" coords="459,111,452,115,448,111,452,100,447,96,448,87,455,90,460,95,486,99,487,111,475,111" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="connecticut" shape="poly" coords="478,83,468,81,464,74,457,76,460,85,467,82,474,85,484,96,497,97,495,87,484,86" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="rhode_island" shape="poly" coords="494,82,473,81,468,80,465,74,469,73,476,75,491,73,502,72,502,81" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="vermont" shape="poly" coords="446,42,455,65,460,66,460,55,457,48,459,43,461,37,451,24,436,27,442,37" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="massachusetts" shape="poly" coords="478,61,472,61,463,64,457,68,456,77,469,74,479,72,488,72,508,64,503,54,488,55,481,63" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="new_hampshire" shape="poly" coords="474,55,470,46,463,33,460,35,460,44,459,49,461,54,460,66,474,60,486,55,506,50,505,37,489,39,487,49,480,54" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
  <area alt="" accesskey="maine" shape="poly" coords="466,21,464,32,475,54,476,44,481,40,483,33,487,36,495,26,491,20,487,17,480,10,476,2,467,3,464,10" href="{$rlBase}{if $config.mod_rewrite}{$pages.lt_listings}/{else}?page={$pages.lt_listings}&amp;category={/if}" />
</map>

<!-- us states map end -->
