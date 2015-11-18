<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);


header('Content-Type: text/html; charset=utf-8');

require(__DIR__ . '/vendor/autoload.php');

$p = new \League\Geotools\Point\Point();

$point = new \GeoJson\Geometry\Point([1, 1]);
$point = json_encode($point);
\geo\src\db::prepareDatabase();

$point = new \geo\src\model\Point();
$point->setLattitude(35.7116);
$point->setLangtiude( 51.4069 );

$locator = new \geo\src\LocatorManager('geo\src\model\location');
$polygons = file_get_contents('/home/masoud/Downloads/test.json');

//print_r($locator->nearest($point,\geo\src\Type::restaurant,5000));
//print_r($locator->pointInPolygon($data, $point));
//print_r($locator->getPolygonGeoJson(1,2));die;
//print_r(count($locator->getList(\geo\src\Type::section,20,5)));die;
//print_r(count($locator->getList(\geo\src\Type::region)));
//print_r(count($locator->getList(\geo\src\Type::restaurant , null,465)));
//$locator->findAddress("سیزده");die;


$resturants = json_decode( file_get_contents('/home/masoud/Desktop/test.json'));



//$location = new \geo\src\model\location();
$polygons = \Illuminate\Database\Capsule\Manager::select("SELECT *, AsText(polygon) AS poly FROM geoLocation WHERE geoLocation.detail != '' ");


$existIn = [];
$other = [];

$tehranOther = [];
$otherOther = [];

foreach($polygons as $pol)
{


    $geo = geoPHP::load($pol->poly);
    $multipoint_points = $geo->getComponents();
    $geoJson= json_decode($multipoint_points[0]->out('json'));

    $polygon = new \League\Geotools\Polygon\Polygon(($geoJson->coordinates));



    foreach($resturants as $json)
    {

        if($json->latitude != ''){
            $point = new \geo\src\model\Point();
            $json = (object)$json;
            $point->setLattitude($json->latitude);
            $point->setLangtiude($json->longitude);


            $result = $polygon->pointInPolygon(new \League\Geotools\Coordinate\Coordinate([$point->getLangtiude(), $point->getLattitude()]));
            if($result)
            {
                $existIn [] = $json;
                $query = "insert into `location` (`location_type`, `name`,`location_trace`,`latitude`,`longitude`)
                                values ('RESTAURANT','$json->name' ,'$pol->detail<additional>$json->address</additional>',$json->latitude,$json->longitude)";

                $model = new \geo\src\model\location();
                try{
                    $model->hydrateRaw($query);

                }catch (Exception $ex){

                }
            }
        }
    }
}


$i=0;
foreach($resturants as $resruran){
    $flag = true;
    foreach($existIn as $exin){
        if($exin->id == $resruran->id ){
            $flag = false;
        }
    }

    if($flag){

        $other[] = $resruran;
        }
}




foreach($other as $res){

    $address = explode('،',$res->address);
    $cities = array('کرج', 'تبریز', 'مشهد', 'شهریار', 'پردیس', 'آذربایجان شرقی', 'اصفهان', 'اهواز');

    foreach($cities as $city){
        if (strpos($address[0],$city) !== false  || $address[0]==$city) {
            $otherOther[] = $res;
            break;
        }
    }
}

foreach($other as $oth)
{
    $f = false;
    foreach($otherOther as $o)
    {
        if($o->id == $oth->id){
            $f = true;
            break;
        }

    }

    if(!$f){
        $tehranOther [] = $oth;
    }
}





foreach($otherOther as $odd){

    $address = explode('،',$odd->address,3);
    $query = "insert into `location` (`location_type`, `name`,`location_trace`,`latitude`,`longitude`)
                                  values ('RESTAURANT','$odd->name' ,'<city>$address[0]</city><region></region><section>$address[1]</section><additional>$address[2]</additional>',$odd->latitude,$odd->longitude)";

              $model = new \geo\src\model\location();
              try{
                  $model->hydrateRaw($query);

              }catch (Exception $ex){

              }

}

$polygons = \Illuminate\Database\Capsule\Manager::select("SELECT *, AsText(polygon) AS poly FROM geoLocation WHERE geoLocation.detail = '' ");
foreach($polygons as $pol)
{


    $geo = geoPHP::load($pol->poly);
    $multipoint_points = $geo->getComponents();
    $geoJson= json_decode($multipoint_points[0]->out('json'));

    $polygon = new \League\Geotools\Polygon\Polygon(($geoJson->coordinates));



    foreach($tehranOther as $json)
    {

        if($json->latitude != ''){
            $point = new \geo\src\model\Point();
            $json = (object)$json;
            $point->setLattitude($json->latitude);
            $point->setLangtiude($json->longitude);

            $address = explode('،',$odd->address,2);


            $result = $polygon->pointInPolygon(new \League\Geotools\Coordinate\Coordinate([$point->getLangtiude(), $point->getLattitude()]));
            if($result)
            {
                $existIn [] = $json;
                $query = "insert into `location` (`location_type`, `name`,`location_trace`,`latitude`,`longitude`)
                                values ('RESTAURANT','$json->name' ,'<city>تهران</city><region>منطقه $pol->region_id<section>$address[0]</section></region><additional>$address[1]</additional>',$json->latitude,$json->longitude)";

                                $model = new \geo\src\model\location();
                                try{
                                    $model->hydrateRaw($query);

                                }catch (Exception $ex){

                                }
            }
        }
    }
}