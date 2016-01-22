<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include('simple_html_dom.php');

function prb(){
    $html=file_get_html("http://journals.aps.org/prb/recent");
    // foreach($pub->find('div.large-9') as $element) {
    //     $item['title']    = $element->find('h5.title a', 0)->plaintext;
    //     $item['authors']  = $element->find('h6.authors', 0)->plaintext;
    //     #$item['abstract'] = $element->find('div.summary p',0))->plaintext;
    //     echo $item['title']."<br/>";
    //     #echo $item['authors'];
    //     }
    echo 'mark';
}


#prb()

$keys=$_POST["keys"];
$orand=$_POST["orand"];
$arr=explode(",",$keys);
$query="";

$size=sizeof($arr);
$i=0;

if ($orand==0){
foreach ($arr as $el){
	$i=$i+1;
	if ($el!="") {
		$query = $query.$el;
	}
	if ($i<=$size-2) {
		  $query=$query."+OR+";
	}
}
}else{
foreach ($arr as $el){
    $i=$i+1;
    if ($el!="") {
        $query = $query.$el;
    }
    if ($i<=$size-2) {
          $query=$query."+AND+";
    }
}
}

$return="";
$limit=20;

$url = 'http://export.arxiv.org/api/query?search_query=all:'.$query.'&sortBy=submittedDate&start=0&max_results='.$limit;

$response = file_get_contents($url);
$xml = new SimpleXMLElement($response);

foreach ($xml->entry as $entry){
    $link=$entry->id;
    $start = strpos($link,'abs');
    $linkpdf = substr_replace($link, 'pdf', $start, 3);
    $linkpdf= $linkpdf.'.pdf';
    $title=$entry->title;
    $date = $entry->published;
    $date = substr($date,0,10);
    $abstract=$entry->summary;
    $authors = "";
    foreach ($entry->author as $author){
        $authors .=$author->name.", ";
    }
    $authors = substr($authors,0,strlen($authors)-2);
    $item='<div class="list-group">
            <div>
                <h6 style="float:right; margin-top:0px;">'.$date.'</h6>
                <h4 class="list-group-item-heading" style="width:87%;"><a href="'.$link.'" target="_blank">'.$title.'</a>
                 <a href="'.$linkpdf.'" target="_blank">[PDF]</a></h4>
                <h5>'.$authors.'</h6>
                <p class="list-group-item-text">'.$abstract.'</p>
            </div>
           </div>';
    $return = $return.$item;
}

echo $return;

?>
