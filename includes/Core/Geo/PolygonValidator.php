<?php
declare(strict_types=1);

namespace Snappbox\Core\Geo;

defined('ABSPATH') || exit;

/**
 * Professional Geographic Utility for Polygon Validation (Geofencing).
 */
class PolygonValidator {

    /**
     * Check if a coordinate is within a GeoJSON-style polygon.
     *
     * @param float  $lat  Latitude
     * @param float  $lng  Longitude
     * @param string $json GeoJSON polygon coordinates (serialized or JSON)
     * @return bool
     */
    public function is_within(float $lat, float $lng, string $json): bool {
        $polygon = \json_decode($json, true);
        if (!\is_array($polygon)) {
            return true; // Default to true if no valid zone is defined
        }

        $inside = false;
        $count  = \count($polygon);

        if ($count < 3) {
            return true;
        }

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = (float) $polygon[$i][0]; // lng
            $yi = (float) $polygon[$i][1]; // lat
            $xj = (float) $polygon[$j][0];
            $yj = (float) $polygon[$j][1];

            $intersect = (($yi > $lat) !== ($yj > $lat))
                && ($lng < ($xj - $xi) * ($lat - $yi) / (($yj - $yi) ?: 0.0000001) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}
