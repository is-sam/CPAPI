<?php

namespace App\Http\DAO;

/**
 * Interface ICommoditiesDAO
 * @package App\Http\DAO
 */
interface ICommoditiesDAO
{
    public function listCommodities($lang);
    public function getCommodity($code, $lang);
    public function getImfPrices($code);
    public function getNc8Prices($code);
}
