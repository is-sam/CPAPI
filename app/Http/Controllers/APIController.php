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
    private $dao;

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
        $counyty = Input::get('country');
        $flow = Input::get('flow');
        $from_date = Input::get('from_date');
        $to_date = Input::get('to_date');

        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');

        $commodities = $this->dao->listCommodities($lang);

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

        $commodity = $this->dao->getCommodity($code, $lang);

        $response = json_encode($commodity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return response($response, Response::HTTP_OK);

    }
}
