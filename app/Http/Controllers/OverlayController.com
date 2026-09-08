<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OverlayController extends Controller
{
    public function index($tahun)
    {
        $rows = DB::select(
            "
            SELECT
                namobj AS kelurahan,
                wadmkc AS kecamatan,
                tahun,
                kepadatan_penduduk,
                luas_rth,
                luas_kelurahan,
                persentase_rth,
                ST_AsGeoJSON(geom) as geom
            FROM vw_overlay_spasial
            WHERE tahun = ?
            ",
            [$tahun]
        );

        $features = [];

        foreach ($rows as $row) {
            $geomJson = $row->geom;

            $geometry = is_string($geomJson) ? json_decode($geomJson) : $geomJson;

            if (!is_object($geometry) || empty($geometry->type) || !property_exists($geometry, 'coordinates')) {
                Log::warning('OverlayController: geojson geometry invalid/empty', [
                    'tahun' => $tahun,
                    'kelurahan' => $row->kelurahan,
                    'kecamatan' => $row->kecamatan,
                    'geom_type' => is_object($geometry) ? ($geometry->type ?? null) : null,
                ]);
                continue;
            }

            // Leaflet menerima geometry object; pastikan coordinates ada.
            $geometry->coordinates = (array) ($geometry->coordinates ?? []);

            $features[] = [
                'type' => 'Feature',
                'geometry' => $geometry,
                'properties' => [
                    'kelurahan'      => $row->kelurahan,
                    'kecamatan'      => $row->kecamatan,
                    'kepadatan'      => $row->kepadatan_penduduk,
                    'luas_rth'       => round(((float) $row->luas_rth) / 100, 3),
                    'luas_kelurahan' => round(((float) $row->luas_kelurahan) / 100, 3),
                    'persentase_rth' => $row->persentase_rth,
                    'tahun'          => $row->tahun,
                ],
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}