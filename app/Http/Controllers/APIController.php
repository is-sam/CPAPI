<?php

namespace App\Http\Controllers;

use App\Http\DAO\CommoditiesDAO;
use App\Http\Requests;
use Illuminate\Support\Facades\Input;
use Symfony\Component\HttpFoundation\Response;

class APIController extends Controller
{
    /**
     * @var dao
     */
    protected $dao;

    /**
     * APIController constructor.
     */
    public function __construct(){
        $this->dao = new CommoditiesDAO();
    }

    /**
     * Display a listing of the resource.
     *
     * @param $lang
     * @return \Illuminate\Http\Response
     * @internal param Request $request
     */
    public function index($lang)
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');

        $properties['country'] = Input::get('country');
        $properties['flow'] = Input::get('flow');
        $properties['from_date'] = Input::get('from_date');
        $properties['to_date'] = Input::get('to_date');
        $properties['lang'] = $lang;

        $commodities = $this->dao->listCommodities($properties);

        if (empty($commodities))
            return response('No data found', Response::HTTP_NO_CONTENT);
        $response = json_encode($commodities, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return response($response, Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param $lang
     * @param  int $code
     * @return \Illuminate\Http\Response
     */
    public function show($lang, $code)
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');

        $properties['country'] = Input::get('country');
        $properties['flow'] = Input::get('flow');
        $properties['from_date'] = Input::get('from_date');
        $properties['to_date'] = Input::get('to_date');
        $properties['lang'] = $lang;

        $commodity = $this->dao->getCommodity($code, $properties);

        if (empty($commodity))
            return response('No data found', Response::HTTP_NO_CONTENT);
        $response = json_encode($commodity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return response($response, Response::HTTP_OK);

    }
}
