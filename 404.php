<?
session_start();
include_once "functions.inc.php";
error_reporting(0);

/* 
	Modified by teTuku - www.tetuku.com
	
	AGC Extractor and Displayer
	(c) 2007-2010  Scriptol.com - Licence Mozilla 1.1.
	AGClib.php
	
	Requirements:
	- PHP 5.
	- A AGC feed.
	
	Using the library:
	Insert this code into the page that displays the AGC feed:
	
	<?php
	require_once("AGClib.php");
	echo AGC_Display("http://www.xul.fr/AGC.xml", 15);
	? >
	
*/

$RSS_Content = array();

function AGC_Tags($item, $type)
{
		$y = array();
		$tnl = $item->getElementsByTagName("title");
		$tnl = $tnl->item(0);
		$title = $tnl->firstChild->textContent;

		$tnl = $item->getElementsByTagName("link");
		$tnl = $tnl->item(0);
		$link = $tnl->firstChild->textContent;
		
		$tnl = $item->getElementsByTagName("pubDate");
		$tnl = $tnl->item(0);
		$date = $tnl->firstChild->textContent;		

		$tnl = $item->getElementsByTagName("description");
		$tnl = $tnl->item(0);
		$description = $tnl->firstChild->textContent;

		$y["title"] = $title;
		$y["link"] = $link;
		$y["date"] = $date;		
		$y["description"] = $description;
		$y["type"] = $type;
		
		return $y;
}


function AGC_Channel($channel)
{
	global $RSS_Content;

	$items = $channel->getElementsByTagName("item");
	
	// Processing channel
	
	$y = AGC_Tags($channel, 0);		// get description of channel, type 0
	array_push($RSS_Content, $y);
	
	// Processing articles
	
	foreach($items as $item)
	{
		$y = AGC_Tags($item, 1);	// get description of article, type 1
		array_push($RSS_Content, $y);
	}
}

function AGC_Retrieve($url)
{
	global $RSS_Content;

	$doc  = new DOMDocument();

	$doc->load($url);

	$channels = $doc->getElementsByTagName("channel");
	
	$RSS_Content = array();
	
	foreach($channels as $channel)
	{
		 AGC_Channel($channel);
	}
	
}


function AGC_RetrieveLinks($url)
{
	global $RSS_Content;

	$doc  = new DOMDocument();
	$doc->load($url);

	$channels = $doc->getElementsByTagName("channel");
	
	$RSS_Content = array();
	
	foreach($channels as $channel)
	{
		$items = $channel->getElementsByTagName("item");
		foreach($items as $item)
		{
			$y = AGC_Tags($item, 1);	// get description of article, type 1
			array_push($RSS_Content, $y);
		}
		 
	}

}


function AGC_Links($url, $size = 15, $filter='')
{
	global $RSS_Content;

	$page = "<ul  class=\"paging2\">";

	AGC_RetrieveLinks($url);
	if($size > 0)
		$recents = array_slice($RSS_Content, 0, $size + 1);

	foreach($recents as $article)
	{
		$type = $article["type"];
		if($type == 0) continue;
		$title = $article["title"];
		$link = "http://www.tetuku.com/landingpage.php?url=".base64_encode($article["link"]);
		$description = strip_tags($article["description"]);
		if (ereg("\[",$description)) {$pecah = @explode("[",$description); $description = $pecah[0];}
		if (ereg("..",$description)) {$pecah = @explode("..",$description); $description = $pecah[0];}
		if ($filter == '') {
		$page .= "<li><a href=\"$link\" title=\"$description\" target=\"_blank\">$title</a></li>\n";
							} else if (ereg($filter,$article["link"]) || ereg($filter,$article["title"]) || ereg($filter,$article["description"])) {
		$page .= "<li><a href=\"$link\" title=\"$description\" target=\"_blank\">$title</a></li>\n";
							}
	}

	$page .="</ul>\n";

	return $page;
	
}



function AGC_Display($url, $size = 15, $site = 0, $withdate = 0, $filter='')
{
	global $RSS_Content;

	$opened = false;
	$page = "";
	$site = (intval($site) == 0) ? 1 : 0;

	AGC_Retrieve($url);
	if($size > 0)
		$recents = array_slice($RSS_Content, $site, $size + 1 - $site);

	foreach($recents as $article)
	{
		$type = $article["type"];
		if($type == 0)
		{
			if($opened == true)
			{
				$page .="</ul>\n";
				$opened = false;
			}
			$page .="<b>";
		}
		else
		{
			if($opened == false) 
			{
				$page .= "<ul  class=\"paging2\">\n";
				$opened = true;
			}
		}
		if ($filter == '') {
		$title = $article["title"];
		$link = "http://www.tetuku.com/landingpage.php?url=".base64_encode($article["link"]);
		$page .= "<li><a href=\"$link\" target=\"_blank\">$title</a>";
		if($withdate)
		{
      $date = $article["date"];
      $page .=' <span class="rssdate">'.$date.'</span>';
    }
		$description = strip_tags($article["description"],'<p><br><img>');
		if (ereg("\[",$description)) {$pecah = @explode("[",$description); $description = $pecah[0];}
		if (ereg("..",$description)) {$pecah = @explode("..",$description); $description = $pecah[0];}
		if($description != false)
		{
			$page .= "<br><span class='rssdesc'>$description</span>";
		}
		$page .= "</li>\n";			
		} else if (ereg($filter,$article["link"]) || ereg($filter,$article["title"]) || ereg($filter,$article["description"])) {
		
		$title = $article["title"];
		$link = "http://www.tetuku.com/landingpage.php?url=".base64_encode($article["link"]);
		$page .= "<li><a href=\"$link\" target=\"_blank\">$title</a>";
		if($withdate)
		{
      $date = $article["date"];
      $page .=' <span class="rssdate">'.$date.'</span>';
    	}
		$description = strip_tags($article["description"],'<p><br><img>');
		if (ereg("\[",$description)) {$pecah = @explode("[",$description); $description = $pecah[0];}
		if (ereg("..",$description)) {$pecah = @explode("..",$description); $description = $pecah[0];}
		if($description != false)
		{
			$page .= "<br><span class='rssdesc'>$description</span>";
		}
		$page .= "</li>\n";			
		}
				
		if($type==0)
		{
			$page .="</b><br />";
		}

	}

	if($opened == true)
	{	
		$page .="</ul>\n";
	}
	return $page."\n";
	
}



function AGC_Img($url, $size = 15, $filter='')
{
	global $RSS_Content;

	$page = "<ul  class=\"paging2\">";

	AGC_RetrieveLinks($url);
	if($size > 0)
		$recents = array_slice($RSS_Content, 0, $size + 1);

	foreach($recents as $article)
	{
		$type = $article["type"];
		if($type == 0) continue;
		$title = $article["title"];
		$link = "http://www.tetuku.com/landingpage.php?url=".base64_encode($article["link"]);
		$description = strip_tags($article["description"],'<p><br><img>');
		if ($filter == '') {
		$page .= "<li class=\"besar\"><a href=\"$link\" target=\"_blank\">$title</a><br />$description</li>\n";
							} else if (ereg($filter,$article["link"]) || ereg($filter,$article["title"]) || ereg($filter,$article["description"])) {
		$page .= "<li class=\"besar\"><a href=\"$link\" target=\"_blank\">$title</a><br />$description</li>\n";
							}
	}

	$page .="</ul>\n";

	return $page;
	
}

if (!eregi(base64_decode(strrev("1tWd0VGd")),read_file('footer.inc.php'))) {die(base64_decode(strrev("+E2L802bj5SdrVHdlRnL3d3d+02bj5SdrVHdlRnL3d3dv8iOwRHdo1jZlJHagEGPg8GdgsmbpxGIlZ3btVmcgQ3Ju9GZgU2chVGbQBiLu4CZlR3YlRXZkBCduVWbldmbpJnZulGI0h2ZpJXew92Q")));}

echo "<h3>".str_replace("-"," ",$_GET['id_tiny'])."</h3><p>&nbsp;</p>";
?>
<!-- AddThis Button BEGIN -->
<div class="addthis_toolbox addthis_default_style ">
<a class="addthis_button_preferred_1"></a>
<a class="addthis_button_preferred_2"></a>
<a class="addthis_button_preferred_3"></a>
<a class="addthis_button_preferred_4"></a>
<a class="addthis_button_compact"></a>
</div>
<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4da0331c4c0cfcd8"></script>
<!-- AddThis Button END -->
<?
$tags = urlencode(str_replace("-"," ",$_GET['id_tiny']));
$result = urldecode(strip_tags(AGC_Display('http://pipes.yahoo.com/pipes/pipe.run?_id=d1c6f35f62809e9552fdfa046a216a37&_render=rss&tags='.$tags),'<li>'));
$result = str_replace('li>','p>',$result);
echo preg_replace('/[^A-Za-z0-9_ `~!@#$%^&*()\-+=|\\:;"\',.<>\/?]/','',$result);
?>
<h4>Related Archives:</h4>
<? 
$result = urldecode(strip_tags(AGC_Display('http://pipes.yahoo.com/pipes/pipe.run?_id=d1c6f35f62809e9552fdfa046a216a37&_render=rss&tags='.$tags),'<a>'));
if (eregi('Warning: DOMDocument::load()',$result)) {$result = "AGC is temporary unavailable to produce output result";}
$result = strip_tags(ereg_replace('a>[^<]*<a','a><br /><a',$result),'<br>');
$result = preg_replace('/[^A-Za-z0-9_ `~!@#$%^&*()\-+=|\\:;"\',.<>\/?]/','',$result);
$result = explode("<br />",$result);
$zebra = - 1;
for($i=0;$i<count($result);$i++) {
$zebra = $zebra * (- 1) ;
if ($zebra > 0) { echo "<div style=\"background-color:#DDDDDD; padding:2; \">";} else if ($zebra < 0) {echo "<div style=\"background-color:#EEEEEE; padding:2; \">";}
echo "<a href=\"".substr(preg_replace('/[^A-Za-z0-9_]/','-',$result[$i]),0,111)."\">".$result[$i]."</a>";
echo "</div>";
}
?>