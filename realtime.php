<?php
//Author: Bo Wang


ini_set('memory_limit','512M');
require_once 'vendor/autoload.php';

use transit_realtime\FeedMessage;

class RealTimeInfo{
   public $tripId = "";
   public $routeId = "";
   public $delay = "";
   public $time = "";

}

class VechileInfo{
   public $lat = "";
   public $lon = "";
}

function getSSLPage($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSLVERSION,3); 
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

 $arrContextOptions=array(
	"ssl"=>array(
		"verify_peer"=>false,
		"verify_peer_name"=>false,
	),
);

$method = $_GET["method"];

//$pstopId = $_GET["stopId"];

//$tripId  = $_GET["tripId"];


//$pstopId = '5773';

//echo getSSLPage("https://gtfsrt.api.translink.com.au/feed");

if ($method=="getStopInfo"){
$pstopId = $_GET["stopId"];
		getStopInfo($pstopId);
}else{
$tripId  = $_GET["tripId"];
		getVechilePosition($tripId);
}

//getVechilePosition("5607777-BCC2015-BCC_FUL-Mon-Thu-06");

function getStopInfo($pstopId){
 $arrContextOptions=array(
        "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
        ),
);


//$fh = fopen("realtime.data", "rb");
//$data = fread($fh, filesize("realtime.data"));
//fclose($fh);
//$feed = unserialize($data);


$data = file_get_contents("https://gtfsrt.api.translink.com.au/feed",false, stream_context_create($arrContextOptions));

$feed = new FeedMessage();
$feed->parse($data);


$array = array();

foreach ($feed->getEntityList() as $entity) {
  if ($entity->hasTripUpdate()) {
	  $tripUpdate = $entity->getTripUpdate();
	 if ($tripUpdate->hasStopTimeUpdate()){
		foreach($tripUpdate->getStopTimeUpdateList() as &$timeUpdate){
				$trip=$tripUpdate->getTrip();
				$stopId=$timeUpdate->getStopId();
				//if (strcasecmp($stopId,$pstopId) == 0){ 
				if($stopId==$pstopId){
				   //var_dump($trip);
				   $e = new RealTimeInfo();
				   $e->tripId = $trip->getTripId();
				   $e->routeId = $trip->getRouteId();
				   if ($timeUpdate->hasArrival()){
				   	$e->delay = $timeUpdate->getArrival()->getDelay();
				   	$e->time = $timeUpdate->getArrival()->getTime();
					
				   }
			           if ($e->delay!=""&&$e->delay>-600){	
				   	array_push($array,$e);  
				   }
				}       
		}       
	 }
	//error_log("trip: " . $entity->getId());
  }
}

echo json_encode($array);
}


function getVechilePosition($tripId){
 $arrContextOptions=array(
        "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
        ),
);
$data = file_get_contents("https://gtfsrt.api.translink.com.au/feed",false, stream_context_create($arrContextOptions));

$feed = new FeedMessage();
$feed->parse($data);


$array = array();

foreach ($feed->getEntityList() as $entity) {
  if ($entity->hasVehicle()) {
	  $vehicle = $entity->getVehicle();
		
				$trip=$vehicle->getTrip();
				$ptripId = $trip->getTripId();
				if($ptripId==$tripId){
				   $e = new VechileInfo();
				   $e->lat = $vehicle->getPosition()->getLatitude();
				   $e->lon = $vehicle->getPosition()->getLongitude();
				   
				   array_push($array,$e);
				   break;
				}       
		 
	 
	//error_log("trip: " . $entity->getId());
  }
}

echo json_encode($array);
}


?>

