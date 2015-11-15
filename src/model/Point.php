<?php
/**
 * Created by PhpStorm.
 * User: masoud
 * Date: 10/30/15
 * Time: 12:41 PM
 */

namespace geo\src\model;


class Point
{
    public $title;
    public $lattitude;
    public $langtiude;

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getLattitude()
    {
        return $this->lattitude;
    }

    /**
     * @param mixed $lattitude
     */
    public function setLattitude($lattitude)
    {
        $this->lattitude = $lattitude;
    }

    /**
     * @return mixed
     */
    public function getLangtiude()
    {
        return $this->langtiude;
    }

    /**
     * @param mixed $langtiude
     */
    public function setLangtiude($langtiude)
    {
        $this->langtiude = $langtiude;
    }

}