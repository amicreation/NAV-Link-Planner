<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Link Engineering Report - {{ $project->name }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #1e40af;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .brand {
            font-size: 22px;
            font-weight: bold;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .subtitle {
            font-size: 13px;
            color: #64748b;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-suitable { background-color: #dcfce7; color: #166534; }
        .badge-marginal { background-color: #fef9c3; color: #854d0e; }
        .badge-not-recommended { background-color: #fee2e2; color: #991b1b; }
        .badge-pass { background-color: #dcfce7; color: #166534; }
        .badge-fail { background-color: #fee2e2; color: #991b1b; }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            border-left: 4px solid #1e40af;
            padding-left: 8px;
            margin-top: 18px;
            margin-bottom: 10px;
            background-color: #f8fafc;
            line-height: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
        }
        .summary-box {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .img-box {
            border: 1px solid #cbd5e1;
            padding: 4px;
            background: #ffffff;
            text-align: center;
            margin-bottom: 15px;
        }
        .img-box img {
            max-width: 100%;
            height: auto;
            max-height: 260px;
        }
        .img-caption {
            font-size: 10px;
            color: #475569;
            margin-top: 4px;
            font-weight: bold;
        }
        .photo-grid {
            width: 100%;
            border: none;
            margin-bottom: 15px;
        }
        .photo-grid td {
            border: none;
            padding: 6px;
            vertical-align: top;
            width: 50%;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <div class="header">
        <table style="border: none; margin: 0;">
            <tr style="border: none;">
                <td style="border: none; padding: 0;">
                    <div class="brand">NAV LINK PLANNER</div>
                    <div class="subtitle">Point-to-Point Link Engineering Report</div>
                </td>
                <td style="border: none; padding: 0; text-align: right;">
                    <strong>Date:</strong> {{ $project->project_date ? \Carbon\Carbon::parse($project->project_date)->format('Y-m-d') : date('Y-m-d') }}<br>
                    <strong>Technology:</strong> {{ strtoupper($project->technology) }}
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Project Metadata</div>
    <table>
        <tr>
            <th>Project Name</th>
            <td>{{ $project->name }}</td>
            <th>Customer Name</th>
            <td>{{ $project->customer_name }}</td>
        </tr>
        <tr>
            <th>Field Engineer</th>
            <td>{{ $project->engineer_name }}</td>
            <th>Technology Mode</th>
            <td>{{ strtoupper($project->technology) }} Point-to-Point</td>
        </tr>
    </table>

    <div class="section-title">Site Specifications</div>
    <table>
        <thead>
            <tr>
                <th>Site Parameter</th>
                <th>Site A ({{ $siteA?->name ?? 'Site A' }})</th>
                <th>Site B ({{ $siteB?->name ?? 'Site B' }})</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Latitude</strong></td>
                <td>{{ number_format((float) ($siteA?->latitude ?? 0), 8) }}°</td>
                <td>{{ number_format((float) ($siteB?->latitude ?? 0), 8) }}°</td>
            </tr>
            <tr>
                <td><strong>Longitude</strong></td>
                <td>{{ number_format((float) ($siteA?->longitude ?? 0), 8) }}°</td>
                <td>{{ number_format((float) ($siteB?->longitude ?? 0), 8) }}°</td>
            </tr>
            <tr>
                <td><strong>Tower Height</strong></td>
                <td>{{ number_format((float) ($siteA?->tower_height ?? 0), 3) }} m</td>
                <td>{{ number_format((float) ($siteB?->tower_height ?? 0), 3) }} m</td>
            </tr>
            <tr>
                <td><strong>Ground Elevation</strong></td>
                <td>{{ number_format((float) (($siteA?->ground_elevation > 0 ? $siteA?->ground_elevation : ($calc['effective_elev_a'] ?? 920.0))), 3) }} m</td>
                <td>{{ number_format((float) (($siteB?->ground_elevation > 0 ? $siteB?->ground_elevation : ($calc['effective_elev_b'] ?? 915.0))), 3) }} m</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Link & Antenna Alignment Geometry</div>
    <table>
        <tr>
            <th>Aerial Distance</th>
            <td><strong>{{ number_format((float) ($calc['distance_km'] ?? 0), 3) }} km</strong></td>
            <th>Line of Sight (LOS)</th>
            <td>
                <span class="badge {{ ($calc['los_status'] ?? '') === 'PASS' ? 'badge-pass' : 'badge-fail' }}">
                    {{ $calc['los_status'] ?? 'PASS' }}
                </span>
            </td>
        </tr>
        <tr>
            <th>Site A Azimuth Heading</th>
            <td><strong>{{ number_format((float) ($calc['azimuth_a_to_b'] ?? 0), 3) }}°</strong></td>
            <th>Site A Mechanical Tilt</th>
            <td><strong>{{ number_format((float) ($calc['tilt_a'] ?? 0), 3) }}°</strong></td>
        </tr>
        <tr>
            <th>Site B Reverse Azimuth</th>
            <td><strong>{{ number_format((float) ($calc['azimuth_b_to_a'] ?? 0), 3) }}°</strong></td>
            <th>Site B Mechanical Tilt</th>
            <td><strong>{{ number_format((float) ($calc['tilt_b'] ?? 0), 3) }}°</strong></td>
        </tr>
        <tr>
            <th>Recommended Tower Height (Site A)</th>
            <td>{{ number_format((float) ($calc['recommended_tower_height_a'] ?? $siteA?->tower_height), 3) }} m</td>
            <th>Recommended Tower Height (Site B)</th>
            <td>{{ number_format((float) ($calc['recommended_tower_height_b'] ?? $siteB?->tower_height), 3) }} m</td>
        </tr>
    </table>

    @if(($project->technology) === 'rf')
    <div class="section-title">Engineering Calculations & Wave Metrics</div>
    <table>
        <tr>
            <th>Max Earth Curvature Drop</th>
            <td>{{ number_format((float) ($calc['earth_curvature_max_m'] ?? 0), 3) }} m</td>
            <th>Min Terrain Clearance</th>
            <td>{{ number_format((float) ($calc['min_clearance_m'] ?? 0), 3) }} m</td>
        </tr>
        <tr>
            <th>Max 1st Fresnel Radius</th>
            <td>{{ number_format((float) ($calc['fresnel_radius_max_m'] ?? 0), 3) }} m</td>
            <th>Recommended Channel Width</th>
            <td>{{ $calc['tech_payload']['recommended_channel_width_mhz'] ?? '40' }} MHz</td>
        </tr>
    </table>
    @endif

    <div class="section-title">Engineering Feasibility & Verdict</div>
    <div class="summary-box">
        <div style="margin-bottom: 8px;">
            <strong>Overall Status:</strong>
            @php $status = $calc['recommendation_status'] ?? 'Suitable'; @endphp
            <span class="badge {{ $status === 'Suitable' ? 'badge-suitable' : ($status === 'Marginal' ? 'badge-marginal' : 'badge-not-recommended') }}">
                {{ $status }}
            </span>
        </div>
        <div>
            {{ $calc['tech_payload']['engineering_summary'] ?? 'Line-of-Sight is clear and link parameters provide robust operational availability.' }}
        </div>
    </div>

    <!-- Map Snapshot Section -->
    @if(!empty($calc['map_image']))
    <div class="section-title">Path Route Map (OpenStreetMap)</div>
    <div class="img-box">
        <img src="{{ $calc['map_image'] }}" alt="Path Map" />
        <div class="img-caption">Site A to Site B Geodesic Vector Path</div>
    </div>
    @endif

    <!-- Elevation Profile Chart Section -->
    @if(!empty($calc['chart_image']))
    <div class="section-title">Terrain & Line of Sight Elevation Profile</div>
    <div class="img-box">
        <img src="{{ $calc['chart_image'] }}" alt="Elevation Profile Chart" />
        <div class="img-caption">Terrain AMSL, Earth Curvature Drop, and Line of Sight Clearance</div>
    </div>
    @endif

    <!-- Additional Site Attachments / Photos Section -->
    @if(!empty($calc['additional_images']) && is_array($calc['additional_images']) && count($calc['additional_images']) > 0)
    <div class="page-break"></div>
    <div class="section-title">Field Survey Attachments & Site Photos</div>
    <table class="photo-grid">
        @foreach(array_chunk($calc['additional_images'], 2) as $row)
        <tr>
            @foreach($row as $photo)
            <td>
                <div class="img-box">
                    <img src="{{ $photo['image'] ?? $photo['url'] ?? '' }}" alt="Site Photo" style="max-height: 200px;" />
                    <div class="img-caption">{{ $photo['caption'] ?? 'Field Photo' }}</div>
                </div>
            </td>
            @endforeach
            @if(count($row) < 2)
            <td></td>
            @endif
        </tr>
        @endforeach
    </table>
    @endif

    <div class="footer">
        Generated by NAV LINK PLANNER &bull; Vendor-Independent Field Engineering System
    </div>

</body>
</html>
