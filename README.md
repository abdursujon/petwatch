# petWatch

A community-driven web application that helps pet owners find their lost pets through crowd-sourced sighting reports and an interactive map.

## What It Does

Pet owners report their lost pets with photos and details. Community members browse the map, and when they spot a lost pet, 
they submit a sighting with the location. The app plots all sightings on an interactive map so owners
can track where their pet has been seen.

## Features

- **Pet Management** — Owners can add, update, and delete lost pet listings with photos, species, breed, color, and description.
- **Sighting Reports** — Users report pet sightings with location (map click or GPS), comment, and address.
- **Interactive Map** — Leaflet-based map with marker clustering showing all sighting locations. Click a marker to see pet details or submit a new sighting.
- **Geolocation** — Browser GPS tracking with a blue dot indicator to help users pinpoint their location.
- **Live Search** — Real-time autocomplete search across pet name, species, breed, color, comment, and address with ranked results.
- **Filtering & Sorting** — Filter sightings by species, sort by name (A–Z / Z–A) or date (newest/oldest).
- **User Authentication** — Login with rate limiting (3 attempts, 2-minute cooldown), secure session cookies, and role-based access (Owner vs regular user).
- **CSRF Protection** — AJAX token validation on all asynchronous requests.

## Tech Stack
- Backend: PHP 7.4+, MariaDB 
- Frontend: JavaScript, Bootstrap 5.2,  Vanilla JS (ES6 modules)
- Maps: Leaflet 1.9.4, Leaflet MarkerCluster 1.5.3 
- Geocoding: Nominatim API (reverse geocoding)

  The geolocation feature works as intended on localhost and HTTPS environments. On the university's Poseidon server, the browser blocks navigator.geolocation entirely because the site is served over HTTP, which modern         
  browsers treat as insecure. This is a server configuration limitation, not a code issue. An IP-based fallback with a consent prompt was implemented as a workaround. 

Hi Lee, geolocation is blocked on all browsers for Poseidon-hosted sites because it runs on HTTP, not HTTPS. 
It works fine on localhost but on Poseidon users can't even choose to allow location. How should we approach this
since it's not something we can control?

So would you test it on local host for geolocation for marking? Cause my geocode works fine on localhost. 
