<?php

namespace App\Http\DAO;
use Illuminate\Support\Facades\DB;

/**
 * Class CommoditiesDAO
 */
class CommoditiesDAO implements ICommoditiesDAO
{
    public function listCommodities($lang)
    {
        $commodities = DB::select("
            SELECT code_imf as code, name_$lang, source_$lang, unite_$lang
            FROM api_imf_liste
            UNION
            SELECT nc8txt as code, name_$lang, source_$lang, unite_$lang
            FROM api_customs_liste
            ");

        foreach($commodities as $index => $commodity)
        {
            $code = $commodity->code;
            if (strncmp($code, 'P', 1) == 0)
            {
                $prix = $this->getImfPrices($code);
            }
            else if (strncmp($code, 'nc8_', 4) == 0)
            {
                $prix = $this->getNc8Prices($code);
            }
            $commodities[$index]->prices = $prix;
        }

        return $commodities;
    }

    public function getCommodity($code, $lang)
    {
        $commodity = DB::select("
            SELECT code_imf as code, name_$lang, source_$lang, unite_$lang
            FROM api_imf_liste
            WHERE code_imf = '$code'
            UNION
            SELECT nc8txt as code, name_$lang, source_$lang, unite_$lang
            FROM api_customs_liste
            WHERE nc8txt = '$code'
            ");

        $code = $commodity->code;
        if (strncmp($code, 'P', 1) == 0)
        {
            $prix = $this->getImfPrices($code);
        }
        else if (strncmp($code, 'nc8_', 4) == 0)
        {
            $prix = $this->getNc8Prices($code);
        }
        $commodity->prices = $prix;

        return $commodity;
    }

    public function getImfPrices($code)
    {
        $prices = DB::select("
            SELECT (
                CASE
                WHEN imf_list.currency = 'USD / EUR'
                    THEN imf_data.valeur/taux_change.valeur
                WHEN imf_list.currency = 'EUR / EUR'
                    THEN imf_data.valeur
                END
                ) * imf_list.factor as price, dates.mois as month, dates.annee as year
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
            ", [$code]);

        return $prices;
    }

    public function getNc8Prices($code)
    {
        $prices = DB::select("
            SELECT global.prix as price, global.volume, dates.mois as month, dates.annee as year, global.flux as flow
            FROM api_customs_data_global as global
            INNER JOIN api_customs_nc8 as nc8 ON global.id_code_nc8 = nc8.id
            INNER JOIN api_dates as dates ON global.id_date = dates.id
            INNER JOIN api_customs_liste as customs_liste ON nc8.code_nc8 = customs_liste.nc8
            WHERE customs_liste.nc8txt = ?
            ORDER BY dates.id;
        ", [$code]);

        return $prices;
    }
}
