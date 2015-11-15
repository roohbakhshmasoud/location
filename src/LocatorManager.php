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

    public function getList($type, $region=null , $section=null , $model=null)
    {
        if($type == Type::section && $region != null)
            $list = $this->repository->find($region,true);
        if($type == Type::restaurant && $section != null)
            $list = $this->repository->findBy('location_id',$section);
        if($type == Type::restaurant && $region != null && $model != null)
        {

            $restaurant_repository = new ModelRepository($model);
            $region_id = $this->repository->findBy('region_id',$region);
            
            $ids = null;
            foreach($sections as $sec)
                $ids[] = $sec->id;
            $restaurants = $restaurant_repository->findIn('location_id' , $ids);
            return $restaurants;
        }
        elseif($type == Type::region)
            $list = $this->repository->findBy('parent',null);
        return $list;
    }

    public function getSection($region)
    {
    }

    public function nearest(Point $point, $type,$distance)
    {

        $query = 'SELECT
                        *,(
                        6371000 * acos (
                            cos ( radians('.$point->getLattitude().') )
                            * cos( radians( lattitude ) )
                            * cos( radians( longitude) - radians('.$point->getLangtiude().') )
                            + sin ( radians('.$point->getLattitude().') )
                            * sin( radians( lattitude) )
                        )
                    ) AS distance
                FROM
                metadata
                WHERE
                location_type = '."'$type'".'
                HAVING distance < '.$distance.'
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
                        if(property_exists($val, 'geometry')) {
                            $multipoint = geoPHP::load(json_encode($val->geometry), 'json');
                            $multipoint_points = $multipoint->getComponents();
                            $json = $multipoint_points[0]->out('json');
                            $json = json_decode($json);
                            $polygon = new \League\Geotools\Polygon\Polygon(($json->coordinates));
                            if ($polygon->pointInPolygon(new \League\Geotools\Coordinate\Coordinate([$point->getLangtiude(), $point->getLattitude()])))
                                return array("region" => $region);
                        }
                        else
                            throw new \Exception("point not found !!!!!");
                    }
                }
            }
        }
    }


    public function getPolygonGeoJson($region, $section)
    {
        if($region != null)
        {
            return $this->repository->find($region);
        }
    }
}