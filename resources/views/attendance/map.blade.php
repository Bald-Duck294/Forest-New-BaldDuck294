@php
    $hideGlobalFilters = true;
@endphp
@extends('layouts.app')

@section('title', 'Attendance Map')

@section('content')

    <div style="height:85vh;width:100%">
        <div id="map" style="height:100%"></div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        var map = L.map('map').setView([18.5204, 73.8567], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        let sites = @json($sites);
        let guards = @json($guards);

        sites.forEach(site => {

            let address = `${site.address}, ${site.city}, ${site.state}, ${site.pincode}`;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
                .then(res => res.json())
                .then(data => {

                    if (data.length === 0) return;

                    let lat = parseFloat(data[0].lat);
                    let lng = parseFloat(data[0].lon);

                    let staff = guards[site.id] || [];
                    let names = staff.map(g => g.name).join("<br>");

                    L.marker([lat, lng])
                        .addTo(map)
                        .bindPopup(`
            <b>${site.name}</b><br>
            Client: ${site.client_name}<br>
            Guards: ${staff.length}<br><br>
            ${names}
        `);

                });

        });
    </script>

@endsection
