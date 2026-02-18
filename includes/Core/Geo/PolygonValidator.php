<?php
declare(strict_types=1);

namespace Snappbox\Core\Geo;

defined('ABSPATH') || exit;

/**
 * Professional Geographic Utility for Polygon Validation (Geofencing).
 */
class PolygonValidator {

    /**
     * Check if a point (lng, lat) is inside a polygon.
     *
     * @param array $point [longitude, latitude]
     * @param array $polygon Array of [longitude, latitude] points representing a polygon boundary.
     * @return bool
     */
    public function is_point_in_polygon(array $point, array $polygon): bool {
        $x      = (float) ($point[0] ?? 0); // longitude
        $y      = (float) ($point[1] ?? 0); // latitude
        $inside = false;
        $count  = \count($polygon);

        if ($count < 3) {
            return false;
        }

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = (float) $polygon[$i][0];
            $yi = (float) $polygon[$i][1];
            $xj = (float) $polygon[$j][0];
            $yj = (float) $polygon[$j][1];

            $intersect = (($yi > $y) !== ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 0.0000001) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}
