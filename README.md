1. Workshop 15
2. Workshop 16

Tech stack:
Map: 60%
OO & Design: Cohesive JS/PHP OO architecture with clear patterns and re‑use.
AJAX: 3+ high‑quality, efficient endpoints with robust error handling and caching where appropriate.
Security: Strong input validation, CSRF/URL tokens, output encoding; shows threat‑modelling decisions.
Data: JSON/XML via extended DB classes; clear schemas.
Map/UX: Highly sophisticated, real‑time updates(Leaflet), smooth interactions (e.g., list to map focus, clustering), reliable geolocation, fully responsive; scales to 100s of items.
Comments: Consistent, meaningful, maintainable.
Strictly no prohibited tools; exemplary database usage and performance tuning.
Hosted on poseidon.salford.ac.uk using a MariaDB database

Live Search: 40%
OO & Design: Elegant, reusable classes; clear pattern.
AJAX: Multiple endpoints (3+ if appropriate) enabling sophisticated interactions (e.g., debounced live search with ranking, or windowed infinite scrolling with virtualisation).
Security: Comprehensive validation and tokenisation; robust input sanitisation.
Data: JSON/XML contracts; paginated/filtered payloads.
Performance: Excellent memory and network efficiency for 100s+ items.
Comments: Consistently excellent. No prohibited tools.
Hosted on poseidon.salford.ac.uk using a MariaDB database


FEATURE 1: Set Up MariaDB on Poseidon

(Poseidon Server, MariaDB, PHP)
- Set up MariaDB database on Poseidon server
- Design table schemas (pets, users, sightings)
- Add indexes on searchable columns
- Host at your-domain.poseidon.salford.ac.uk/clientserver/
- Optimised queries & performance tuning

  ---
FEATURE 2: Plan OOP Architecture

(JS Classes, PHP Classes, MVC Pattern)
- JS classes: MapController, Pet, Sighting, SearchHandler, AjaxService,
  CacheManager
- PHP: Extended base DB class for all models
- Clear MVC design pattern throughout
- Consistent, meaningful comments everywhere
- Plan class relationships before writing code

  ---
FEATURE 3: Create Test Data & Accounts

(MariaDB, PHP, ChatGPT for mock data)
- 100s of realistic pet records with real Manchester/Salford lat/lng
  coordinates
- Realistic sighting comments & locations
- Admin account: admin (Manager/Owner)
- User account: Lee (User)
- Strong passwords (12+ chars, uppercase, lowercase, numbers, symbols)
- Database indexes on searchable columns

  ---
FEATURE 4: Build AJAX Data Layer

(JS, fetch/XMLHttpRequest, JSON, PHP)
- 3+ endpoints minimum (get pets, get sightings, add sighting, search,
  filters)
- Client-side caching (avoid re-fetching unchanged data)
- Robust error handling on every request (loading states, error messages,
  retries)
- Defined JSON schemas/contracts for all requests & responses
- No page reloads — all data via AJAX

  ---
FEATURE 5: Security (Baked In From Day 1)

(JS, PHP, CSRF Tokens, Input Sanitisation, Output Encoding, Parameterised
Queries)
- CSRF/URL tokens on all AJAX requests
- XSS & SQL injection prevention
- Output encoding on all user-submitted content
- Image resizing for uploads
- Strong password policy enforced
- Threat-modelling document — explain WHY you chose each security measure

  ---
FEATURE 6: Interactive Pet Map

(JS, Mapping Library, Geolocation API, AJAX, JSON, PHP, MariaDB)
- Display all missing pets as markers on a map
- Each marker has a popup showing pet info
- Map centers on user's current location via Geolocation API
- Marker clustering for 100s of items
- Clicking a pet from list moves/zooms the map
- Real-time updates — new sightings appear without refresh
- When a user adds a sighting → the marker appears on the map instantly via
  AJAX, no page reload
  - When a user adds a comment → it shows up in the list immediately
  - Map updates without refreshing the page
    INSERT INTO sightings (pet_id, lat, lng, comment) VALUES
    (1, 53.4808, -2.2426, 'Spotted near Piccadilly Gardens'),
    (2, 53.4831, -2.2489, 'Seen outside Manchester Arndale'),
    (3, 53.4751, -2.2530, 'Running along Deansgate'),
    (4, 53.4878, -2.2900, 'Near Salford Quays tram stop'),
    (5, 53.4722, -2.2945, 'Outside Old Trafford stadium');

  ---
FEATURE 7: Pet Sightings List

(JS, AJAX, JSON, PHP, HTML, CSS)
- List view of all missing pets alongside the map
- Each item shows key pet details
- Selecting an item focuses the corresponding map marker
- Paginated loading — not all records at once
- Data loaded via AJAX (JSON)

  ---
FEATURE 8: Add Sighting (Auth Only)

(JS, AJAX, JSON, PHP, MariaDB, CSRF Tokens)
- Logged-in users add sighting comment + location
- Stored via AJAX POST
- Sighting appears on map in real-time after submission
- CSRF token, input validation, output encoding
- Smooth UX — no page reload, clear success/error feedback

  ---
FEATURE 9: Live Search (Chosen Feature — 40% of marks)

(JS, AJAX, JSON, PHP, MariaDB, CSS)
- Debounced real-time search as user types
- Search ranking by relevance
- Multiple filters (pet type, location, date, status)
- Paginated results — don't dump everything to the browser
- 3+ AJAX endpoints (search query, filter options, suggestions/autocomplete)
- Memory efficient — clear old results, limit DOM nodes
- JSON contracts for all search request/response payloads

  ---
FEATURE 10: Responsive Design & Polish

(CSS, Media Queries, HTML)
- Fully responsive across desktop, tablet, mobile
- Map resizes properly on all screens
- Clean, accessible UI
- Final testing with 100s of records

Backend (PHP)

- MVC — already required from Assignment 1
    - Model: DB classes extending a base class (e.g., BaseModel → PetModel,
      SightingModel)
    - View: .phtml template files
    - Controller: handles requests, calls models, returns JSON or renders views

Frontend (JavaScript)

- MVC or similar separation applied in JS too:
    - Model: classes that handle data & AJAX calls (Pet, Sighting, AjaxService)
    - View: classes that handle DOM rendering (MapView, ListView, SearchView)
    - Controller: classes that tie them together (MapController,
      SearchController)

Additional patterns to use:
Pattern: Singleton
Where: AjaxService, CacheManager
Why: One instance handling all requests/cache
────────────────────────────────────────
Pattern: Observer
Where: Map ↔ List interaction
Why: When list item clicked, map updates. When sighting added, both list & map

    update
────────────────────────────────────────
Pattern: Repository
Where: PHP Models
Why: All DB access goes through model classes, never raw queries in
controllers
  ---
In your code comments, explicitly name the pattern:

// Controller pattern — handles interaction between MapView and Pet model
class MapController {
constructor(mapView, petModel) {
this.mapView = mapView;
this.petModel = petModel;
}
}

// Repository pattern — all pet DB operations extend BaseModel
class PetModel extends BaseModel {
public function findAll() { ... }
}

Naming the patterns in comments shows the marker you understand what you're
doing, not just coding blindly.
