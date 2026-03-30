<!DOCTYPE html>
<html>

<head>
    <title>Attendance Map</title>
    <style>
        #map {
            height: 100vh;
            width: 100%;
        }

        .info-window {
            padding: 10px;
            font-family: Arial, sans-serif;
        }

        /* Container for buttons */
        .button-container {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 5;
            display: flex;
            gap: 10px;
        }

        .button-container button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .open-btn {
            background-color: #28a745;
            color: #fff;
        }

        .close-btn {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=places,drawing,geometry">
    </script>
</head>

<body>
    <!-- Buttons to open/close all InfoWindows -->
    <div class="button-container">
        <button class="open-btn" onclick="openAllInfoWindows()">Open All InfoWindows</button>
        <button class="close-btn" onclick="closeAllInfoWindows()">Close All InfoWindows</button>
    </div>
    <div id="map"></div>
    <script>
        var map;
        var markers = [];
        var allInfoWindows = [];

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 10,
                center: {
                    lat: 0,
                    lng: 0
                } // Default center; will be adjusted later
            });

            var bounds = new google.maps.LatLngBounds();

            // Attendance data and geofences passed from Laravel
            const attendanceData = @json($attendanceData);
            const geofences = @json($geofences);

            // Loop through attendanceData and add markers with InfoWindows
            attendanceData.forEach(item => {
                if (item.location) {
                    const location = JSON.parse(item.location);
                    if (location.lat && location.lng) {
                        const marker = new google.maps.Marker({
                            position: {
                                lat: location.lat,
                                lng: location.lng
                            },
                            map: map,
                            title: `Attendance Date: ${item.date}`
                        });

                        const infoWindow = new google.maps.InfoWindow({
                            content: `<div class="info-window">
                          <strong>Name:</strong> ${item.name}<br>
                          <strong>Site:</strong> ${item.site_name}<br>
                          <strong>Date:</strong> ${item.date}<br>
                          <strong>Punch-In:</strong> ${item.entry_time || 'Not marked'}<br>
                          <strong>Punch-Out:</strong> ${item.exit_time || 'Not marked'}
                        </div>`
                        });

                        // Open the specific InfoWindow on marker click
                        marker.addListener('click', () => {
                            infoWindow.open(map, marker);
                        });

                        // Store marker and its associated InfoWindow
                        markers.push(marker);
                        allInfoWindows.push(infoWindow);

                        bounds.extend(marker.position);
                    }
                }
            });

            // Adjust map bounds to fit all markers
            map.fitBounds(bounds);

            // Geofences: add shapes and attach InfoWindow on click (not stored in global array)
            geofences.forEach(data => {
                let shape;
                if (data.type === 'Circle') {
                    const center = JSON.parse(data.center);
                    shape = new google.maps.Circle({
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: "#FF0000",
                        fillOpacity: 0.35,
                        map: map,
                        center: {
                            lat: center.lat,
                            lng: center.lng
                        },
                        radius: +data.radius,
                    });
                } else {
                    shape = new google.maps.Polygon({
                        paths: JSON.parse(data.poly_lat_lng),
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: "#FF0000",
                        fillOpacity: 0.05,
                        map: map,
                    });
                }

                const infoWindowContent = `<div class="info-window">
                                      <b>Geo:</b> ${data.name}<br>
                                      <b>Site:</b> ${data.site_name}
                                    </div>`;

                // Show geofence info on shape click
                google.maps.event.addListener(shape, 'click', (event) => {
                    const infoWindow = new google.maps.InfoWindow();
                    infoWindow.setContent(infoWindowContent);
                    if (data.type === 'Circle') {
                        const center = JSON.parse(data.center);
                        infoWindow.setPosition({
                            lat: center.lat,
                            lng: center.lng
                        });
                    } else {
                        infoWindow.setPosition(event.latLng);
                    }
                    infoWindow.open(map);
                });
            });
        }

        // Function to open all stored InfoWindows for attendance markers
        function openAllInfoWindows() {
            markers.forEach((marker, index) => {
                allInfoWindows[index].open(map, marker);
            });
        }

        // Function to close all stored InfoWindows
        function closeAllInfoWindows() {
            allInfoWindows.forEach(infoWindow => {
                infoWindow.close();
            });
        }

        window.onload = initMap;
    </script>
</body>

</html>
