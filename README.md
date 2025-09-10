# WP Flight Aggregator

A simple WordPress plugin that aggregates flights from two external APIs, allows booking, and manages conflicts through an admin interface.

## 1. How APIs are handled
- The plugin connects to two external APIs (API A & API B) defined in constants (WFA_API_A_URL and WFA_API_B_URL).
- Flights from both sources are fetched, merged, and filtered by origin, destination, and date.
- Any API fetch errors are logged in /wp-content/logs/flight-errors.log for debugging.
- Flights are displayed on the front end via the [flight_search] shortcode.

## 2. How conflicts are detected
- When a booking is made, it is stored in the custom database table wp_flight_bookings.
- After saving, the Conflict Handler runs checks for:
  - Duplicate bookings (same passenger, flight, or timing).
  - Pricing mismatches between API A and API B.
- If a conflict is found, it is stored in the wp_booking_conflicts table.
- Conflicts are automatically cleaned up by WP Cron if unresolved after 1 hour.

## 3. Admin panel structure overview
- The plugin adds a top-level admin menu called Conflict Manager.
- From the Conflict Manager page the admin can:
  - See all unresolved conflicts listed.
  - Compare API A vs API B data side-by-side.
  - Choose API A, API B, or enter a manual override as the final decision.
  - Add a resolution note.
  - Save the resolution, which marks the conflict as resolved in the database.
- Admins receive an email notification when a new conflict is logged.

## Installation
1. Upload the plugin to your WordPress site (/wp-content/plugins/wp-flight-aggregator/).
2. Activate the plugin from the Plugins menu.
3. Add the shortcode [flight_search] to any page to enable flight search and booking.