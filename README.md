# B.Tech Timetable Generator

A web-based automated timetable scheduling system built with **PHP** and **MySQL**. This application solves the constraint satisfaction problem of scheduling classes, labs, and faculty without conflicts.

## 🚀 Features
* **Automatic Generation:** Generates conflict-free schedules for multiple departments (CSE, ECE, MECH, etc.).
* **Lab Constraints:** Handles 3-hour continuous lab sessions.
* **Load Balancing:** Ensures faculty members are not overloaded with continuous morning shifts.
* **Conflict Detection:** Prevents double-booking of rooms and faculty across different sections.
* **PDF & Excel Export:** Download printable timetables with section-wise headers.
* **Faculty Workload:** automated calculation of faculty teaching hours.

## 🛠️ Tech Stack
* **Frontend:** HTML5, CSS3 (Custom responsive design)
* **Backend:** PHP (Native)
* **Database:** MySQL

## ⚙️ How to Run
1.  Install **XAMPP** or **WAMP**.
2.  Clone or Download this repository to `C:\xampp\htdocs\timetable`.
3.  Import the database:
    * Open `http://localhost/phpmyadmin`.
    * Create a database named `btech_timetable`.
    * Import `btech_timetable.sql` (included in this repo).
4.  Open `http://localhost/timetable` in your browser.

## 📸 Screenshots
*(You can upload screenshots later to the 'images' folder and link them here)*
