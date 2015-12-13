<?php

namespace App\Http\DAO;
use Illuminate\Support\Facades\DB;

/**
 * Class CommoditiesDAO
 */
class CommoditiesDAO implements ICommoditiesDAO
{
    /**
     * @param $properties
     * @return mixed
     * @internal param $lang
     */
    public function listCommodities($properties)
    {
        $lang = $properties['lang'];

        $query = "
            SELECT nc8txt as code, name_$lang, source_$lang, unite_$lang
            FROM api_customs_liste
        ";

        if (empty($properties['country']))
        {
            $query .= "
                UNION
                SELECT code_imf as code, name_$lang, source_$lang, unite_$lang
                FROM api_imf_liste
            ";
        }

        $commodities = DB::select($query);

        foreach($commodities as $index => $commodity)
        {
            $code = $commodity->code;
            if (strncmp($code, 'P', 1) == 0)
            {
                $data = $this->getImfData($code, $properties);
            }
            else if (strncmp($code, 'nc8_', 4) == 0)
            {
                if (empty($properties['country']))
                    $data = $this->getNc8DataGlobal($code, $properties);
                else
                    $data = $this->getNc8DataCountry($code, $properties);
            }
            if (!empty($data))
                $commodities[$index]->data = $data;
            else
                unset($commodities[$index]);

        }

        return $commodities;
    }

    /**
     * @param $code
     * @param $properties
     * @return mixed
     * @internal param $lang
     */
    public function getCommodity($code, $properties)
    {
        $lang = $properties['lang'];

        $query = "
            SELECT code_imf as code, name_$lang, source_$lang, unite_$lang
            FROM api_imf_liste
            WHERE code_imf = '$code'
            UNION
            SELECT nc8txt as code, name_$lang, source_$lang, unite_$lang
            FROM api_customs_liste
            WHERE nc8txt = '$code'
        ";

        $commodity = DB::select($query);

        if (isset($commodity[0]))
            $commodity = $commodity[0];
        if (!empty($commodity)) {
            $code = $commodity->code;
            if (strncmp($code, 'P', 1) == 0)
            {
                $data = $this->getImfData($code, $properties);
            }
            else if (strncmp($code, 'nc8_', 4) == 0)
            {
                if (empty($properties['country']))
                    $data = $this->getNc8DataGlobal($code, $properties);
                else
                    $data = $this->getNc8DataCountry($code, $properties);
            }
            if (!empty($data))
                $commodity->data = $data;
            else
                unset($commodity);
        }

        return $commodity;
    }

    /**
     * @param $code
     * @param $properties
     * @return mixed
     */
    public function getImfData($code, $properties)
    {
        $query = "
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
        ";

        if (!empty($properties['from_date']) && preg_match('/^\d{4}_\d{2}$/', $properties['from_date']))
        {
            preg_match('/^(\d{4})_(\d{2})$/', $properties['from_date'], $matches);
            $year = $matches[1];
            $month = $matches[2];
            $id_date = $this->getIdFromDate($year, $month);
            $id_date = $id_date[0]->id;
            $query .= "AND imf_data.id_date >= '$id_date' ";
        }

        if (!empty($properties['to_date']) && preg_match('/^\d{4}_\d{2}$/', $properties['to_date']))
        {
            preg_match('/^(\d{4})_(\d{2})$/', $properties['to_date'], $matches);
            $year = $matches[1];
            $month = $matches[2];
            $id_date = $this->getIdFromDate($year, $month);
            $id_date = $id_date[0]->id;
            $query .= "AND imf_data.id_date <= '$id_date' ";
        }

        $query .= "ORDER BY dates.id";

        $data = DB::select($query, [$code]);

        return $data;
    }

    /**
     * @param $code
     * @param $properties
     * @return mixed
     */
    public function getNc8DataGlobal($code, $properties)
    {
        $query = "
            SELECT global.prix as price, global.volume, dates.mois as month, dates.annee as year, global.flux as flow
            FROM api_customs_data_global as global
            INNER JOIN api_customs_nc8 as nc8 ON global.id_code_nc8 = nc8.id
            INNER JOIN api_dates as dates ON global.id_date = dates.id
            INNER JOIN api_customs_liste as customs_liste ON nc8.code_nc8 = customs_liste.nc8
            WHERE customs_liste.nc8txt = ?
        ";

        if (!empty($properties['from_date']) && preg_match('/^\d{4}_\d{2}$/', $properties['from_date']))
        {
            preg_match('/^(\d{4})_(\d{2})$/', $properties['from_date'], $matches);
            $year = $matches[1];
            $month = $matches[2];
            $id_date = $this->getIdFromDate($year, $month);
            $id_date = $id_date[0]->id;
            $query .= "AND global.id_date >= '$id_date' ";
        }

        if (!empty($properties['to_date']) && preg_match('/^\d{4}_\d{2}$/', $properties['to_date']))
        {
            preg_match('/^(\d{4})_(\d{2})$/', $properties['to_date'], $matches);
            $year = $matches[1];
            $month = $matches[2];
            $id_date = $this->getIdFromDate($year, $month);
            $id_date = $id_date[0]->id;
            $query .= "AND global.id_date <= '$id_date' ";
        }

        if ($properties['flow'] == 'i')
            $query .= "AND global.flux = 1 ";
        else if ($properties['flow'] == 'e')
            $query .= "AND global.flux = 0 ";

        $query .= "ORDER BY dates.id";

        $data = DB::select($query, [$code]);

        return $data;
    }

    public function getNc8DataCountry($code, $properties)
    {
        $query = "
            SELECT data_pays.prix as price, pays.code_pays as country_code, data_pays.volume, dates.mois as month, dates.annee as year, data_pays.flux as flow
            FROM api_customs_data_pays as data_pays
            INNER JOIN api_customs_pays as pays ON data_pays.id_code_pays = pays.id
            INNER JOIN api_customs_niv niv ON niv.id_code_nc8 = data_pays.id_code_nc8
              AND niv.flux = data_pays.flux
              AND niv.id_code_pays = data_pays.id_code_pays
            INNER JOIN api_customs_nc8 as nc8 ON data_pays.id_code_nc8 = nc8.id
            INNER JOIN api_dates as dates ON data_pays.id_date = dates.id
            INNER JOIN api_customs_liste as customs_liste ON nc8.code_nc8 = customs_liste.nc8
            WHERE customs_liste.nc8txt = ?
        ";

        if (!empty($properties['from_date']) && preg_match('/^\d{4}_\d{2}$/', $properties['from_date']))
        {
            preg_match('/^(\d{4})_(\d{2})$/', $properties['from_date'], $matches);
            $year = $matches[1];
            $month = $matches[2];
            $id_date = $this->getIdFromDate($year, $month);
            $id_date = $id_date[0]->id;
            $query .= "AND data_pays.id_date >= '$id_date' ";
        }

        if (!empty($properties['to_date']) && preg_match('/^\d{4}_\d{2}$/', $properties['to_date']))
        {
            preg_match('/^(\d{4})_(\d{2})$/', $properties['to_date'], $matches);
            $year = $matches[1];
            $month = $matches[2];
            $id_date = $this->getIdFromDate($year, $month);
            $id_date = $id_date[0]->id;
            $query .= "AND data_pays.id_date <= '$id_date' ";
        }
        
        if ($properties['country'] != 'all')
            $query .= "
                AND pays.code_pays = '" . $properties['country'] . "'
                AND niv.niv = 0
            ";

        if ($properties['flow'] == 'i')
            $query .= "AND data_pays.flux = 1 ";
        else if ($properties['flow'] == 'e')
            $query .= "AND data_pays.flux = 0 ";

        $data = DB::select($query, [$code]);

        return $data;
    }

    public function getIdFromDate($year, $month)
    {
        $query = "
            SELECT id
            FROM api_dates
            WHERE mois = ?
            AND annee = ?
        ";

        $id = DB::select($query, [$month, $year]);

        return $id;
    }
}
