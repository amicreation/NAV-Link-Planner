<?php

namespace App\Services;

class SvgGeneratorService
{
    /**
     * Generates a PNG Data-URI of the Path Route Map.
     * DomPDF cannot render SVG data-URIs correctly (renders them upside-down),
     * so we draw directly to a GD canvas and output as PNG base64.
     */
    public function generateMapPng(
        string $nameA,
        float $latA,
        float $lonA,
        string $nameB,
        float $latB,
        float $lonB,
        float $distKm
    ): string {
        $w = 700;
        $h = 280;

        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);

        // Colors
        $white = imagecolorallocate($img, 255, 255, 255);
        $bgLand = imagecolorallocate($img, 226, 235, 216);         // #e2ebd8 topo green
        $bgLand2 = imagecolorallocate($img, 210, 227, 200);        // #d2e3c8
        $gridLine = imagecolorallocatealpha($img, 148, 163, 184, 80); // #94a3b8 faint
        $blue = imagecolorallocate($img, 30, 64, 175);              // #1e40af
        $darkSlate = imagecolorallocate($img, 15, 23, 42);          // #0f172a
        $slate500 = imagecolorallocate($img, 71, 85, 105);          // #475569
        $border = imagecolorallocate($img, 203, 213, 225);          // #cbd5e1
        $riverBlue = imagecolorallocatealpha($img, 144, 200, 172, 30);
        $roadYellow = imagecolorallocate($img, 245, 158, 11);       // #f59e0b
        $roadWhite = imagecolorallocate($img, 255, 255, 255);
        $red = imagecolorallocate($img, 220, 38, 38);

        // Background
        imagefilledrectangle($img, 0, 0, $w, $h, $bgLand);

        // Lighter terrain patches
        imagefilledellipse($img, 200, 100, 400, 200, $bgLand2);
        imagefilledellipse($img, 500, 200, 300, 150, $bgLand2);

        // River-like curve
        imagesetthickness($img, 12);
        imageline($img, 130, 0, 250, $h, $riverBlue);

        // Roads
        imagesetthickness($img, 3);
        imageline($img, 0, 140, $w, 120, $roadWhite);
        imageline($img, 320, 0, 380, $h, $roadWhite);

        // Dashed road overlay (simulate)
        imagesetthickness($img, 2);
        for ($rx = 0; $rx < $w; $rx += 16) {
            imageline($img, $rx, intval(140 - ($rx / $w) * 20), $rx + 8, intval(140 - (($rx + 8) / $w) * 20), $roadYellow);
        }

        // Grid lines
        imagesetthickness($img, 1);
        for ($gx = 70; $gx < $w; $gx += 100) {
            imagedashedline($img, $gx, 0, $gx, $h, $gridLine);
        }
        for ($gy = 40; $gy < $h; $gy += 60) {
            imagedashedline($img, 0, $gy, $w, $gy, $gridLine);
        }

        // Border
        imagerectangle($img, 0, 0, $w - 1, $h - 1, $border);

        // Header label background
        imagefilledrectangle($img, 12, 12, 292, 38, $darkSlate);
        imagestring($img, 4, 24, 17, "OPENSTREETMAP PATH ROUTE", $white);

        // Distance label background
        imagefilledrectangle($img, 480, 12, 688, 38, $white);
        imagerectangle($img, 480, 12, 688, 38, $border);
        $distText = "Aerial Distance: " . number_format($distKm, 3) . " km";
        imagestring($img, 3, 495, 18, $distText, $blue);

        // Path line (dashed)
        $x1 = 90; $y1 = 180;
        $x2 = 610; $y2 = 100;
        imagesetthickness($img, 4);
        $steps = 30;
        for ($s = 0; $s < $steps; $s += 2) {
            $sx1 = intval($x1 + ($x2 - $x1) * ($s / $steps));
            $sy1 = intval($y1 + ($y2 - $y1) * ($s / $steps));
            $sx2 = intval($x1 + ($x2 - $x1) * (($s + 1) / $steps));
            $sy2 = intval($y1 + ($y2 - $y1) * (($s + 1) / $steps));
            imageline($img, $sx1, $sy1, $sx2, $sy2, $blue);
        }

        // Site A marker
        imagefilledellipse($img, $x1, $y1, 22, 22, $blue);
        imagefilledellipse($img, $x1, $y1, 8, 8, $white);

        // Site A callout box
        imagefilledrectangle($img, $x1 - 70, $y1 + 16, $x1 + 70, $y1 + 52, $white);
        imagerectangle($img, $x1 - 70, $y1 + 16, $x1 + 70, $y1 + 52, $border);
        $labelA = "Site A: " . substr($nameA, 0, 14);
        imagestring($img, 3, $x1 - 60, $y1 + 20, $labelA, $blue);
        $coordA = number_format($latA, 5) . ", " . number_format($lonA, 5);
        imagestring($img, 2, $x1 - 55, $y1 + 36, $coordA, $slate500);

        // Site B marker
        imagefilledellipse($img, $x2, $y2, 22, 22, $darkSlate);
        imagefilledellipse($img, $x2, $y2, 8, 8, $white);

        // Site B callout box
        imagefilledrectangle($img, $x2 - 70, $y2 - 52, $x2 + 70, $y2 - 16, $white);
        imagerectangle($img, $x2 - 70, $y2 - 52, $x2 + 70, $y2 - 16, $border);
        $labelB = "Site B: " . substr($nameB, 0, 14);
        imagestring($img, 3, $x2 - 60, $y2 - 48, $labelB, $darkSlate);
        $coordB = number_format($latB, 5) . ", " . number_format($lonB, 5);
        imagestring($img, 2, $x2 - 55, $y2 - 32, $coordB, $slate500);

        // Compass rose
        imagefilledellipse($img, $w - 35, $h - 40, 30, 30, $white);
        imageellipse($img, $w - 35, $h - 40, 30, 30, $border);
        // N arrow
        imageline($img, $w - 35, $h - 40, $w - 35, $h - 55, $red);
        imagestring($img, 2, $w - 39, $h - 66, "N", $red);

        // Output as PNG base64
        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($pngData);
    }

    /**
     * Generates a PNG Data-URI of the Elevation Profile Chart.
     * Uses GD to render directly to bitmap — avoids DomPDF SVG flip bug.
     */
    public function generateElevationChartPng(array $profilePoints, float $totalDistanceKm): string
    {
        $w = 700;
        $h = 300;
        $padL = 60;
        $padR = 20;
        $padT = 40;
        $padB = 50;

        $plotW = $w - $padL - $padR;
        $plotH = $h - $padT - $padB;

        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);

        // Colors
        $white = imagecolorallocate($img, 255, 255, 255);
        $bgGrid = imagecolorallocate($img, 241, 245, 249);     // #f1f5f9
        $border = imagecolorallocate($img, 203, 213, 225);      // #cbd5e1
        $textDark = imagecolorallocate($img, 15, 23, 42);       // #0f172a
        $textMuted = imagecolorallocate($img, 100, 116, 139);   // #64748b
        $blue = imagecolorallocate($img, 30, 64, 175);          // #1e40af LOS
        $skyBlue = imagecolorallocate($img, 2, 132, 199);       // #0284c7 Fresnel
        $red = imagecolorallocate($img, 220, 38, 38);           // #dc2626 Earth Drop
        $gray = imagecolorallocate($img, 100, 116, 139);        // #64748b Ground
        $grayFill = imagecolorallocatealpha($img, 100, 116, 139, 90); // fill

        // White background
        imagefilledrectangle($img, 0, 0, $w, $h, $white);
        imagerectangle($img, 0, 0, $w - 1, $h - 1, $border);

        if (empty($profilePoints)) {
            $profilePoints = [
                ['dist_km' => 0, 'ground_elevation' => 920, 'effective_ground' => 920, 'los_height' => 950, 'fresnel_60_bottom' => 945],
                ['dist_km' => $totalDistanceKm, 'ground_elevation' => 915, 'effective_ground' => 915, 'los_height' => 945, 'fresnel_60_bottom' => 940],
            ];
        }

        // Compute Y bounds
        $allY = [];
        foreach ($profilePoints as $p) {
            $allY[] = $p['ground_elevation'] ?? 0;
            $allY[] = $p['effective_ground'] ?? 0;
            $allY[] = $p['los_height'] ?? 0;
            $allY[] = $p['fresnel_60_bottom'] ?? 0;
        }
        $minY = floor(min($allY) - 5);
        $maxY = ceil(max($allY) + 5);
        $rangeY = max(10, $maxY - $minY);

        // Mapping functions
        $mapX = fn($dist) => intval($padL + ($dist / max(0.001, $totalDistanceKm)) * $plotW);
        $mapYf = fn($val) => intval($padT + $plotH - (($val - $minY) / $rangeY) * $plotH);

        // Grid lines & Y axis ticks
        $yTicks = 5;
        for ($i = 0; $i <= $yTicks; $i++) {
            $val = $minY + ($rangeY / $yTicks) * $i;
            $y = $mapYf($val);
            imageline($img, $padL, $y, $w - $padR, $y, $bgGrid);
            imagestring($img, 2, 5, $y - 6, round($val) . "m", $textMuted);
        }

        // X axis ticks
        $xTicks = 5;
        $bottomY = $padT + $plotH;
        imageline($img, $padL, $bottomY, $w - $padR, $bottomY, $border);
        for ($i = 0; $i <= $xTicks; $i++) {
            $dist = ($totalDistanceKm / $xTicks) * $i;
            $x = $mapX($dist);
            imageline($img, $x, $bottomY, $x, $bottomY + 4, $border);
            imagestring($img, 2, $x - 12, $bottomY + 8, number_format($dist, 1) . " km", $textMuted);
        }

        // Title
        imagestring($img, 4, $padL, 10, "TERRAIN & LINE OF SIGHT ELEVATION PROFILE", $textDark);

        // Legend (top right)
        $legX = $w - 330;
        imagesetthickness($img, 3);
        imageline($img, $legX, 20, $legX + 15, 20, $blue);
        imagestring($img, 2, $legX + 18, 14, "LOS Ray", $textDark);

        imagesetthickness($img, 2);
        imageline($img, $legX + 75, 20, $legX + 90, 20, $skyBlue);
        imagestring($img, 2, $legX + 93, 14, "60% Fresnel", $textDark);

        imageline($img, $legX + 170, 20, $legX + 185, 20, $red);
        imagestring($img, 2, $legX + 188, 14, "Earth Drop", $textDark);

        imageline($img, $legX + 255, 20, $legX + 270, 20, $gray);
        imagestring($img, 2, $legX + 273, 14, "Ground", $textDark);

        // Draw Ground fill polygon
        $groundPolygon = [$mapX(0), $bottomY]; // bottom left
        foreach ($profilePoints as $p) {
            $groundPolygon[] = $mapX($p['dist_km']);
            $groundPolygon[] = $mapYf($p['ground_elevation']);
        }
        $groundPolygon[] = $mapX($totalDistanceKm);
        $groundPolygon[] = $bottomY; // bottom right

        imagefilledpolygon($img, $groundPolygon, $grayFill);

        // Draw Ground Elevation polyline
        imagesetthickness($img, 2);
        for ($i = 0; $i < count($profilePoints) - 1; $i++) {
            $p1 = $profilePoints[$i];
            $p2 = $profilePoints[$i + 1];
            imageline($img, $mapX($p1['dist_km']), $mapYf($p1['ground_elevation']),
                            $mapX($p2['dist_km']), $mapYf($p2['ground_elevation']), $gray);
        }

        // Draw Effective Ground (Earth Drop) polyline
        for ($i = 0; $i < count($profilePoints) - 1; $i++) {
            $p1 = $profilePoints[$i];
            $p2 = $profilePoints[$i + 1];
            imageline($img, $mapX($p1['dist_km']), $mapYf($p1['effective_ground']),
                            $mapX($p2['dist_km']), $mapYf($p2['effective_ground']), $red);
        }

        // Draw 60% Fresnel Zone Clearance polyline (dashed)
        for ($i = 0; $i < count($profilePoints) - 1; $i++) {
            if ($i % 2 === 0) { // dash effect
                $p1 = $profilePoints[$i];
                $p2 = $profilePoints[$i + 1];
                imageline($img, $mapX($p1['dist_km']), $mapYf($p1['fresnel_60_bottom']),
                                $mapX($p2['dist_km']), $mapYf($p2['fresnel_60_bottom']), $skyBlue);
            }
        }

        // Draw LOS Ray polyline (thick)
        imagesetthickness($img, 3);
        for ($i = 0; $i < count($profilePoints) - 1; $i++) {
            $p1 = $profilePoints[$i];
            $p2 = $profilePoints[$i + 1];
            imageline($img, $mapX($p1['dist_km']), $mapYf($p1['los_height']),
                            $mapX($p2['dist_km']), $mapYf($p2['los_height']), $blue);
        }

        // X axis label
        imagestring($img, 3, intval($w / 2) - 40, $h - 15, "Path Distance (km)", $textMuted);

        // Output as PNG base64
        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($pngData);
    }
}
