# Event Horizon – README

<!-- Login Credentials -->

<!-- User Login -->
Email: [ineshfernando643@gmail.com]
Password: [200605]

<!-- Admin Login -->
Email: [admin@gmail.com]
Password: [admin123]


Instructions:
1. Import the SQL database file into phpMyAdmin.
2. Update database connection settings in includes/db_connection.php.
3. Start the local server (XAMPP/WAMP).
4. Open the project in browser.


<!-- User side Project Demonstration -->

### 🔹 1. Searching & Viewing Clubs/Events

“First, here is the Clubs page, where users can search clubs and events.
The Homepage shows only the latest 4 clubs.
When I open a club, the events are displayed in two sections:

* Upcoming Events
* Past Events — which are automatically moved when the event date has passed.

There are also dynamic redirections, such as:

* Clicking an event → goes to that event’s description page
* Clicking something on the calendar → redirects to the correct event page dynamically.”

---------------------------------------------------------------

### 🔹 2. Calendar Features

“Here is the calendar feature:

* The calendar displays all events visually.
* It handles multiple events on the same day.
* Events are recognized as past or upcoming in real time, based on today’s date.”

---------------------------------------------------------------

### 🔹 3. Past Events Handling

“I also implemented the Past Events system:

* If an event date is before today, it automatically appears under Past Events.”

---------------------------------------------------------------


### 🔹 4. Reminder System

“For the Reminder System:

* Users can add reminders for any event.
* After adding, users are redirected to a confirmation page saying *Reminder Added Successfully*.
* All reminders are shown in the Reminders page.
* Users can delete reminders.

Important validations:

* No reminders can be set for past events.
* No duplicate reminders for the same event.

After login:

* The system checks upcoming reminders and shows a popup with the event name, date, and time.”

---------------------------------------------------------------

### 🔹 5. User Authentication

“Users can register with their email, log in, and log out.
Login triggers the Reminder Checker, which shows any upcoming reminders for that user.”

---------------------------------------------------------------
---------------------------------------------------------------
---------------------------------------------------------------

<!-- Admin Side Demonstration -->

### 🔹 1. Admin Login

“For admin access, I used simple hardcoded credentials during development:

* Email: [admin@gmail.com]
* Password: [admin123]

---------------------------------------------------------------

### 🔹 2. Dashboard Overview

“The Admin Dashboard displays:

* Total clubs
* Total events
* Total past events
* Total reminders

This gives a quick summary of all system records.”

---------------------------------------------------------------

### 🔹 3. Club Management

“The Admin can:

* Add clubs
* Edit clubs
* Delete clubs

I also implemented cascading deletion:

* Deleting a club deletes all events under that club automatically.”

---------------------------------------------------------------

### 🔹 4. Event Management

“For events, the admin can:

* Create events
* Edit events
* Delete events

Validations added:

* No duplicate event names
* Cannot create events with past dates.”

---------------------------------------------------------------

### 🔹 5. Club Highlight Management

“The Admin can also manage Club Highlights:

* Add club highlights
* Edit club highlights
* Delete club highlights

All fully functional.”

---------------------------------------------------------------

## 🛠 3. Technical Implementation (Short)

“I developed this system using:

* PHP (Vanilla) for backend logic
* MySQL for database
* HTML, CSS, JS for UI
* PHP Sessions for authentication
* Date-based logic for separating past vs future events
* Redirect-based routing for dynamic pages

All features are fully connected to the database.”

---------------------------------------------------------------

## 4. Blockers I Faced

“Some blockers I faced during the sprint were:

* Handling the event movement from upcoming to past using date comparison.
* Preventing duplicate reminders and events required extra validation logic.
* Building the calendar and mapping multiple events on the same date.
* Managing cascading delete between clubs and events.

I solved these using SQL constraints, proper PHP checks, and date handling.”
