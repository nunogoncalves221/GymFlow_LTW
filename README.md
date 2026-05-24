# GymFlow - ltw16g03

## Team Members
- Maria Luísa Ferreira (202406040)
- Nuno Gonçalves (202403355)
- Raquel Silva (202404971)

## First Delivery 

### Project Structure
- pages/index.html - Home page
- pages/class.html - Classes page
- pages/trainer.html - Trainer profile
- css/style.css - Main stylesheet

## Features

**All users:**
- [x] Register a new account.
- [x] Log in and out.
- [x] Edit their profile, including name, username, password, and profile photo.

**Members:**
- [x] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [x] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [x] View trainer profiles, including their specializations and the classes they teach.
- [x] Check the current availability of equipment in the main training area.
- [x] Leave ratings and reviews for classes they have attended.

**Trainers:**
- [ ] Manage their public profile, including bio, specializations, and certifications.
- [ ] View the roster of members enrolled in their classes.
- [ ] Track and manage their assigned class schedule.

**Admins:**
- [x] Manage members and trainers (create, update, and deactivate accounts).
- [x] Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- [x] Manage equipment in the main training area (add, update availability status, and remove items).
- [x] Elevate a user to admin status.
- [ ] Oversee and ensure the smooth operation of the entire system.

**Extra:**
- [ ] Something extra (e.g., personal training bookings, membership plans, waitlist, ...).

## Running

    sqlite3 database/database.db < database/database.sql

    php -S localhost:9000

## Credentials

- admin/p4s5w0rd
- member/1234
- trainer/1234

## Mockups

![Mockups](docs/mockup.png)

## Database Schema

![Database](docs/database.png)