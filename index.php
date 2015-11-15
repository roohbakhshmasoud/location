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
$locator->findAddress("افسر");die;
