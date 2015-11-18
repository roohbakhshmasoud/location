<?php

namespace geo\src;


use geo\src\interfaces\Locator;
use geo\src\model\Point;
use geoPHP;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Repository\Contracts\ModelRepository;

class LocatorManager implements Locator
{
    private $repository;

    function __construct($model)
    {
        $this->repository = new ModelRepository($model);
    }

    public function findAddress($key)
    {
        $result = $this->repository->select("SELECT CONCAT( ExtractValue( geoLocation.detail, '/city') , ', ', ExtractValue( geoLocation.detail, '/region' ) , ', ', ExtractValue( geoLocation.detail, '/section' ) ) AS title, id
                                FROM geoLocation
                                WHERE geoLocation.detail LIKE '%$key%'");

        return $result;
    }

    public function getList($type, $id)
    {

        $q = "SELECT  ExtractValue(geoLocation.detail , '/city') as city,
                      ExtractValue(geoLocation.detail , '/region')  as region,
                      ExtractValue(geoLocation.detail , '/section')  as section
                      from geoLocation WHERE geoLocation.id = $id;";
        $geoAdd = $this->repository->select($q);

        $geoAdd = $geoAdd[0];

        $geoAdd->city = trim($geoAdd->city);
        $geoAdd->region = trim($geoAdd->region);
        $geoAdd->section = trim($geoAdd->section);

        $query="SELECT * from location WHERE location.location_type = '$type' AND
                                           ExtractValue(location.location_trace , '/city')  = '$geoAdd->city' AND
										   ExtractValue(location.location_trace , '/region')  = '$geoAdd->region' "." AND
										   (ExtractValue(location.location_trace , '/section')  LIKE  '% $geoAdd->section%' OR
										    ExtractValue(location.location_trace , '/additional')  LIKE  '%$geoAdd->section%')";

//        var_dump($query);die;
      //  $query = "select * FROM location WHERE location.geolocation_id = $id AND location.location_type = '$type'";
        return $this->repository->select($query);
    }

    public function getSection($region)
    {
    }

    public function nearest(Point $point, $type, $distance)
    {

        $query = 'SELECT
                        *,(
                        6371000 * acos (
                            cos ( radians(' . $point->getLattitude() . ') )
                            * cos( radians( latitude ) )
                            * cos( radians( longitude) - radians(' . $point->getLangtiude() . ') )
                            + sin ( radians(' . $point->getLattitude() . ') )
                            * sin( radians( latitude) )
                        )
                    ) AS distance
                FROM
                location
                WHERE
                location_type = ' . "'$type'" . '
                HAVING distance < ' . $distance . '
                ORDER BY distance';

        return $this->repository->findByQuery($query);

    }

    public function pointInPolygon($polygons, Point $point)
    {
        $jsonIterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator(json_decode($polygons, TRUE)),
            RecursiveIteratorIterator::SELF_FIRST);

        foreach ($jsonIterator as $key => $val) {
            $region = null;
            if ($key == 'features') {
                if (is_array($val)) {
                    foreach ($val as $key => $val) {
                        $val = (object)$val;
                        if (property_exists($val, 'properties')) {
                            $per = (object)$val->properties;
                            $region = $per->Region;
                        }
                        if (property_exists($val, 'geometry')) {
                            $multipoint = geoPHP::load(json_encode($val->geometry), 'json');
                            $multipoint_points = $multipoint->getComponents();
                            $json = $multipoint_points[0]->out('json');
                            $json = json_decode($json);
                            $polygon = new \League\Geotools\Polygon\Polygon(($json->coordinates));
                            if ($polygon->pointInPolygon(new \League\Geotools\Coordinate\Coordinate([$point->getLangtiude(), $point->getLattitude()])))
                                return array("region" => $region);
                        } else
                            throw new \Exception("point not found !!!!!");
                    }
                }
            }
        }
    }


    public function getPolygonGeoJson($region, $section)
    {
        if ($region != null) {
            return $this->repository->find($region);
        }
    }
}