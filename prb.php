<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


include('simple_html_dom.php');


$articles=array();

function aps($keys,$orand,$url,$jour){
    global $articles;
    $type=strtoupper($jour);
    $myXMLData=file_get_contents($url);
    $myXMLData = preg_replace('~(</?|\s)([a-z0-9_]+):~is', '$1$2_', $myXMLData);
    $xml=simplexml_load_string($myXMLData);
    foreach($xml->item as $i){
        $article=array();
        $article["title"]=$i->title;
        $article["link"]=$i->link;
        $pdf=str_replace("doi",$jour."/pdf",str_replace("link","journals",$article["link"]));
        $article["pdf"]=$pdf;
        #$html=file_get_html($article["link"]);
        #$article["abstract"]=$html->find('div.content p',0)->plaintext;
        $article["abstract"]=$i->description;
        $article["authors"]=$i->dc_creator;
        $date=explode("T",$i->dc_date);
        $article["date"]=$date[0]; //date
        $article["type"]=$type;

        $bool=0;
        foreach($keys as $key){
            if ($orand==0){
                if ((strpos(strtolower($article["title"]), strtolower($key)) !== false) or (strpos(strtolower($article["abstract"]), strtolower($key)) !== false) or (strpos(strtolower($article["authors"]), strtolower($key)) !== false)) {
                    $bool=1;
                    break;
                }
            } else {
                $bool=1;
                if ((strpos(strtolower($article["title"]), strtolower($key)) !== true) and (strpos(strtolower($article["abstract"]), strtolower($key)) !== true) and (strpos(strtolower($article["authors"]), strtolower($key)) !== true)) {
                    $bool=0;
                    break;
                }                
            }
        }

        if ($bool==1){
            array_push($articles,$article);
        }
    }
}

//$ss=array("stability");
//$orand=0;
//prb($ss,$orand);

function DescSort($item1,$item2)
{
    if ($item1['date'] == $item2['date']) return 0;
    return ($item1['date'] < $item2['date']) ? 1 : -1;
}


function arxiv($arr,$orand){
    global $articles;
    $size=sizeof($arr);
    $i=0;
    $query="";

    #prepare query
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

    #number of returned papers is limited to 20
    $limit=30;

    #arxiv api
    $url = 'http://export.arxiv.org/api/query?search_query=all:'.$query.'&sortBy=submittedDate&start=0&max_results='.$limit;

    #the response is casted into an xml object
    $response = file_get_contents($url);
    $xml = new SimpleXMLElement($response);

    #read information from the xml response
    foreach ($xml->entry as $entry){
        $article=array();
        $article["type"]="arXiv";
        $article["link"]=$entry->id;
        $start = strpos($article["link"],'abs');
        $linkpdf = substr_replace($article["link"], 'pdf', $start, 3);
        $article["pdf"]= $linkpdf.'.pdf';
        $article["title"]=$entry->title;
        $date = $entry->published;
        $article["date"] = substr($date,0,10);
        $article["abstract"]=$entry->summary;
        $authors = "";
        foreach ($entry->author as $author){
            $authors .=$author->name.", ";
        }
        $article["authors"] = substr($authors,0,strlen($authors)-2);

        $newtitle=$article["title"];
        $newabstract=$article["abstract"];
        #mark the keywords in the title/abstract with a yellow background
        /*foreach ($arr as $el){
            $rep = "<span style='background-color:yellow;'>".$el."</span> ";
            if (strpos($el,strtolower($title))!=-1) {
                $newtitle = str_replace($el,$rep,strtolower($newtitle));
            }
            if (strpos($el,strtolower($abstract))!=-1) {
                $rep = "<span style='background-color:yellow;'>".$el."</span> ";
                $newabstract = str_replace($el,$rep,strtolower($newabstract));
            }
        }*/

        #create paper item
        /*$item='<div class="list-group">
                <div>
                    <h6 style="float:right; margin-top:0px;">'.$date.'</h6>
                    <h4 class="list-group-item-heading" style="width:87%;"><a href="'.$link.'" target="_blank">'.$newtitle.'</a>
                     <a href="'.$linkpdf.'" target="_blank">[PDF]</a></h4>
                    <h5>'.$authors.'</h6>
                    <p class="list-group-item-text">'.$newabstract.'</p>
                </div>
               </div>';
        $return = $return.$item;*/

        array_push($articles,$article);

    }

}


function nature($keys,$orand,$url,$jour){
    global $articles;
    $myXMLData=file_get_contents($url);
    $xml=simplexml_load_string($myXMLData);
    //$html=file_get_html("http://feeds.feedburner.com/nature/qBBA?format=xml");
    foreach($xml->item as $i){
        $article=array();
         $article["type"]=$jour;
        $article["link"]=$i->link;
        $article['title']= $i->title;
        $article['abstract']= $i->description;
        $article["link"]=$i->link;
        $article['pdf'] = "";
        $article['authors'] = "";
        $article['date'] = "";
        $bool=0;
        foreach($keys as $key){
            if ($orand==0){
                if ((strpos(strtolower($article["title"]), strtolower($key)) !== false) or (strpos(strtolower($article["abstract"]), strtolower($key)) !== false) or (strpos(strtolower($article["authors"]), strtolower($key)) !== false)) {
                    $bool=1;
                    break;
                }
            } else {
                $bool=1;
                if ((strpos(strtolower($article["title"]), strtolower($key)) !== true) and (strpos(strtolower($article["abstract"]), strtolower($key)) !== true) and (strpos(strtolower($article["authors"]), strtolower($key)) !== true)) {
                    $bool=0;
                    break;
                }                
            }
        }

        if ($bool==1){
            array_push($articles,$article);
        }

    }
}



function output(){
    global $articles;
    $return="";
    usort($articles,'DescSort');

    foreach($articles as $article){
        $item='<div class="list-group">
                <div>
                    <h3 style="text-align: center;margin-top: -22px;height: 20px;line-height: 20px;font-size: 15px;"><span style="background-color:aliceblue; padding:5px;">'.$article["type"].'</span></h3>
                    <h6 style="float:right; margin-top:0px;">'.$article["date"].'</h6>
                    <h4 class="list-group-item-heading" style="width:87%;"><a href="'.$article["link"].'" target="_blank">'.$article["title"].'</a>
                     <a href="'.$article["pdf"].'" target="_blank">[PDF]</a></h4>
                    <h5>'.$article["authors"].'</h6>
                    <p class="list-group-item-text">'.$article["abstract"].'</p>
                </div>
               </div>';
        $return = $return.$item;
    }
    echo $return;
}


#get keywords string from the search
$keys=$_POST["keys"];

#get or/and user selectrion
$orand=$_POST["orand"];


$journals=$_POST["journals"];

#convert keys to array
$arr=explode(",",$keys);

foreach ($journals as $jour){
    switch ($jour){
        case "arxiv":
            arxiv($arr,$orand);
            break;
        case "nature":
            nature($arr,$orand,"http://feeds.nature.com/nphys/rss/current?format=xml","Nature Physics");
            nature($arr,$orand,"http://feeds.nature.com/nphys/rss/aop?format=xml","Nature Physics");
            nature($arr,$orand,"http://feeds.nature.com/nmat/rss/current?format=xml","Nature Materials");
            nature($arr,$orand,"http://feeds.nature.com/nmat/rss/aop?format=xml","Nature Materials");
            nature($arr,$orand,"http://feeds.nature.com/nature/rss/current?format=xml","Nature");
            nature($arr,$orand,"http://feeds.nature.com/nature/rss/aop?format=xml","Nature");
            nature($arr,$orand,"http://feeds.nature.com/nphoton/rss/current?format=xml","Nature Photonics");
            nature($arr,$orand,"http://feeds.nature.com/nphoton/rss/aop?format=xml","Nature Photonics");
            nature($arr,$orand,"http://feeds.nature.com/nnano/rss/current?format=xml","Nature Nanotechnology");
            break;
        case "aps":
            aps($arr,$orand,"http://feeds.aps.org/rss/recent/prb.xml","prb");
            aps($arr,$orand,"http://feeds.aps.org/rss/recent/prl.xml","prl");
            aps($arr,$orand,"http://feeds.aps.org/rss/recent/pra.xml","pra");
            aps($arr,$orand,"http://feeds.aps.org/rss/recent/prc.xml","prc");
            aps($arr,$orand,"http://feeds.aps.org/rss/recent/prd.xml","prd");
            aps($arr,$orand,"http://feeds.aps.org/rss/recent/pre.xml","pre");
            aps($arr,$orand,"http://feeds.aps.org/rss/recent/prx.xml","prx");
            aps($arr,$orand,"http://feeds.aps.org/rss/recent/rmp.xml","rmp");
            aps($arr,$orand,"http://feeds.aps.org/rss/recent/prapllied.xml","prapplied");
    }
}


output();

?>
