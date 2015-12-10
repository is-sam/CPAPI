<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class APIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        header('Content-Type: application/json');
        $lang = 'fr';
        $commodities = DB::select("
            SELECT code_imf as code, name_$lang, source_$lang, unite_$lang
            FROM api_imf_liste
            UNION
            SELECT nc8txt as code, name_$lang, source_$lang, unite_$lang
            FROM api_customs_liste
            ");
        foreach($commodities as $index => $commodity)
        {
            if (strncmp($commodity->code, 'nc8_', 4))
            {
                $prix = DB::select("
                SELECT (
                    CASE
                    WHEN imf_list.currency = 'USD / EUR'
                        THEN imf_data.valeur/taux_change.valeur
                    WHEN imf_list.currency = 'EUR / EUR'
                        THEN imf_data.valeur
                    END
                    ) * imf_list.factor as prix, dates.mois, dates.annee
                FROM api_imf_data as imf_data
                LEFT JOIN api_imf_liste as imf_list
                ON imf_data.code_imf = imf_list.code_imf
                INNER JOIN (
                    SELECT valeur, id_date
                    FROM api_imf_data
                    WHERE code_imf = 'EURUSD'
                ) as taux_change
                ON imf_data.id_date = taux_change.id_date
                INNER JOIN api_dates as dates
                ON imf_data.id_date = dates.id
                WHERE imf_data.code_imf = ?
                ORDER BY dates.id;
                ", [$commodity->code]);
            }
            else
            {
                $prix = DB::select("
                    SELECT global.prix, global.volume, dates.mois, dates.annee, global.flux
                    FROM api_customs_data_global as global
                    INNER JOIN api_customs_nc8 as nc8 ON global.id_code_nc8 = nc8.id
                    INNER JOIN api_dates as dates ON global.id_date = dates.id
                    INNER JOIN api_customs_liste as customs_liste ON nc8.code_nc8 = customs_liste.nc8
                    WHERE customs_liste.nc8txt = ?
                    ORDER BY dates.id;
                ", [$commodity->code]);
            }
            $prices_field_name = "prices";
            $commodities[$index]->$prices_field_name = $prix;

        }
        if (empty($commodities))
            return response('No data found', Response::HTTP_NO_CONTENT);
        $response = json_encode($commodities, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return response($response, Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $code
     * @return \Illuminate\Http\Response
     */
    public function show($code)
    {
        header('Content-Type: application/json');
        $lang = 'fr';
        $commodity = DB::select("
            SELECT code_imf as code, name_$lang, source_$lang, unite_$lang
            FROM api_imf_liste
            WHERE code_imf = '$code'
            UNION
            SELECT nc8txt as code, name_$lang, source_$lang, unite_$lang
            FROM api_customs_liste
            WHERE nc8txt = '$code'
            ");
        if (isset($commodity[0]))
            $commodity = $commodity[0];
        if (strncmp($commodity->code, 'nc8_', 4))
        {
            $prix = DB::select("
                SELECT (
                    CASE
                    WHEN imf_list.currency = 'USD / EUR'
                        THEN imf_data.valeur/taux_change.valeur
                    WHEN imf_list.currency = 'EUR / EUR'
                        THEN imf_data.valeur
                    END
                    ) * imf_list.factor as prix, dates.mois, dates.annee
                FROM api_imf_data as imf_data
                LEFT JOIN api_imf_liste as imf_list
                ON imf_data.code_imf = imf_list.code_imf
                INNER JOIN (
                    SELECT valeur, id_date
                    FROM api_imf_data
                    WHERE code_imf = 'EURUSD'
                ) as taux_change
                ON imf_data.id_date = taux_change.id_date
                INNER JOIN api_dates as dates
                ON imf_data.id_date = dates.id
                WHERE imf_data.code_imf = ?
                ORDER BY dates.id;
                ", [$commodity->code]);
        }
        else
        {
            $prix = DB::select("
                    SELECT global.prix, global.volume, dates.mois, dates.annee, global.flux
                    FROM api_customs_data_global as global
                    INNER JOIN api_customs_nc8 as nc8 ON global.id_code_nc8 = nc8.id
                    INNER JOIN api_dates as dates ON global.id_date = dates.id
                    INNER JOIN api_customs_liste as customs_liste ON nc8.code_nc8 = customs_liste.nc8
                    WHERE customs_liste.nc8txt = ?
                    ORDER BY dates.id;
                ", [$commodity->code]);
        }
        $prices_field_name = "prices";
        $commodity->$prices_field_name = $prix;
        if (empty($commodity))
            return response('No data found', Response::HTTP_NO_CONTENT);
        $response = json_encode($commodity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return response($response, Response::HTTP_OK);

    }
}
