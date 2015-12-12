<?php

namespace App\Http\DAO;

/**
 * Interface ICommoditiesDAO
 * @package App\Http\DAO
 */
interface ICommoditiesDAO
{
    public function listCommodities($properties);
    public function getCommodity($code, $properties);
    public function getImfData($code, $properties);
    public function getNc8DataGlobal($code, $properties);
    public function getNc8DataCountry($code, $properties);
    public function getIdFromDate($month, $year);
}