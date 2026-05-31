PRAGMA foreign_keys = ON;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT    NOT NULL,
    username        TEXT    NOT NULL UNIQUE,
    password        TEXT    NOT NULL,
    type            TEXT    NOT NULL DEFAULT 'member' CHECK(type IN ('member', 'trainer', 'admin', 'deactivated')),
    profile_picture TEXT    NOT NULL DEFAULT '/docs/profile_pictures/default.jpg',
    plan_id         INTEGER REFERENCES membership_plans(id)
);

CREATE TABLE IF NOT EXISTS trainers (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT    NOT NULL,
    bio             TEXT    NOT NULL DEFAULT '',
    specializations TEXT    NOT NULL DEFAULT '',
    certifications  TEXT    NOT NULL DEFAULT '',
    user_id         INTEGER REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS classes (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         TEXT    NOT NULL,
    type         TEXT    NOT NULL,
    description  TEXT    NOT NULL DEFAULT '',
    duration_min INTEGER NOT NULL,
    capacity     INTEGER NOT NULL DEFAULT 20,
    trainer_id   INTEGER REFERENCES trainers(id),
    image        TEXT,
    day_of_week  TEXT    NOT NULL,
    time         TEXT    NOT NULL
);

CREATE TABLE IF NOT EXISTS reviews (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id    INTEGER NOT NULL REFERENCES classes(id),
    user_id     INTEGER NOT NULL DEFAULT 0 REFERENCES users(id),
    rating      INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5),
    comment     TEXT    NOT NULL DEFAULT '',
    created_at  TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP

);

CREATE TABLE IF NOT EXISTS equipment (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    name     TEXT    NOT NULL,
    type     TEXT    NOT NULL DEFAULT '',
    quantity INTEGER NOT NULL DEFAULT 1,
    in_use   INTEGER NOT NULL DEFAULT 0
);

-- Enrollments
CREATE TABLE IF NOT EXISTS enrollments (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    class_id   INTEGER NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    created_at TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, class_id)
);

-- Membership Plans
CREATE TABLE IF NOT EXISTS membership_plans (
    id                       INTEGER PRIMARY KEY AUTOINCREMENT,
    name                     TEXT    NOT NULL,
    price                    REAL    NOT NULL DEFAULT 0,
    description              TEXT    NOT NULL DEFAULT '',
    max_enrollments_per_week INTEGER,          -- NULL = unlimited
    access_level             INTEGER NOT NULL DEFAULT 1,
    is_active                INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS membership_plan_features (
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    plan_id INTEGER NOT NULL REFERENCES membership_plans(id) ON DELETE CASCADE,
    feature TEXT    NOT NULL
);

-- Membership change requests
CREATE TABLE IF NOT EXISTS membership_requests (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    plan_id    INTEGER NOT NULL REFERENCES membership_plans(id) ON DELETE CASCADE,
    status     TEXT    NOT NULL DEFAULT 'pending'
                       CHECK(status IN ('pending', 'approved', 'rejected')),
    created_at TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Nutrition Plans
CREATE TABLE IF NOT EXISTS nutrition_plans (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    trainer_id  INTEGER NOT NULL REFERENCES trainers(id) ON DELETE CASCADE,
    member_id   INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title       TEXT    NOT NULL,
    description TEXT    NOT NULL DEFAULT '',
    calories    INTEGER,
    created_at  TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS nutrition_meals (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    nutrition_plan_id INTEGER NOT NULL REFERENCES nutrition_plans(id) ON DELETE CASCADE,
    name             TEXT    NOT NULL,
    time_of_day      TEXT    NOT NULL DEFAULT '',
    calories         INTEGER NOT NULL DEFAULT 0,
    notes            TEXT    NOT NULL DEFAULT ''
);

-- Equipment Reservations
CREATE TABLE IF NOT EXISTS equipment_reservations (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id      INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    equipment_id INTEGER NOT NULL REFERENCES equipment(id) ON DELETE CASCADE,
    date         TEXT    NOT NULL,
    start_time   TEXT    NOT NULL,
    end_time     TEXT    NOT NULL,
    status       TEXT    NOT NULL DEFAULT 'active'
                         CHECK(status IN ('active', 'cancelled'))
);

-- Disputes & Feedback
CREATE TABLE IF NOT EXISTS disputes (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id        INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    category       TEXT    NOT NULL CHECK(category IN ('equipment_malfunction', 'class_cancellation', 'billing', 'staff_conduct', 'other')),
    description    TEXT    NOT NULL,
    status         TEXT    NOT NULL DEFAULT 'open' CHECK(status IN ('open', 'in_review', 'resolved')),
    admin_response TEXT,
    created_at     TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    responded_at   TEXT
);

INSERT INTO users (id, name, username, password, type) VALUES
(1, 'Admin',           'admin',   '$2y$10$YiGX7nmnZBTAx0oKm7Bcg.b.FjaSU0O7qZA/uvF8VCKA/FNI.uk0S', 'admin'),
(2, 'Member',          'member',  '$2y$10$sJLmymNsI.hUjzCkeEsk/Os9H3iKqg/nuefPdKvpPVBrj/cua4gSO', 'member'),
(13,'trainer',         'trainer', '$2y$10$jeuaPkJgUw2aJorKZZi1P.RPyaQT8q..OkKGMWc.ARuvfsi2OQm6q', 'trainer'),
(3, 'Ana Silva',       'ana',     '$2y$10$W3tzUEUSrbm2.s4.78dKdOqw.WcyFlmOpxVUx/449Av/51.yLaGKG', 'trainer'),
(4, 'Carlos Mota',     'carlos',  '$2y$10$v.dUaXE2XMuV/bMBGzxwPOP9Xz1RAs.5GL1LBduzX3L/7Fw3oCoje', 'trainer'),
(5, 'Marta Lima',      'marta',   '$2y$10$9UZW2joJ0p8j6q9zWAohEODyB342yrCYxxNO9JDMFM98KpXq54/42', 'trainer'),
(6, 'João Pires',      'joao',    '$2y$10$uUNef1H0.g/QY9McyeM9eu7Lt5XgZ.ZQPUzmvWb.WRy10OnsotjAy', 'trainer'),
(7, 'Rita Costa',      'rita',    '$2y$10$dL3FIuZ3zj4RqcQzSgHaU.D0GFJC95STGxuAbhi9CIfmLQ1ZDiKSi', 'trainer'),
(8, 'Sofia Rodrigues', 'sofia',   '$2y$10$km9lyo8wbGlC5cyKnSdhDeTNcriPCXYu0B9sQAKEpqMnw495GrhkO', 'member'),
(9, 'Miguel Ferreira', 'miguel',  '$2y$10$x2wFhhUF/zjGZhWvx.ZoVetILdqo2EDHCuejUUx9sC/3pUpYm1nzS', 'member'),
(10,'Inês Marques',    'ines',    '$2y$10$0bvCm/v9C/zqsWQBmDtAa.6DDadUJhvYdc8/G1a5ab35gQjh7U2/e', 'member'),
(11,'Beatriz Santos',  'beatriz', '$2y$10$30vWYynxk8v/Hext3q03IuFa3T3ZR0TAOHVgMoMc2YioVS1RTivri', 'member'),
(12,'Rui Oliveira',    'rui',     '$2y$10$ndNHuZC.EYka6BhcmvNXCOxlkfNRpXajuJ8hgiGk0f7mP6C5EiUnq', 'member');

-- Trainers
INSERT INTO trainers (id, name, bio, specializations, certifications, user_id) VALUES
(1, 'Ana Silva',   'Yoga and wellness specialist with 10 years of experience. Passionate about helping people find balance through movement and breathwork.', 'Yoga · Zumba · Meditation', 'RYT-200 Yoga Certification, Zumba Instructor License', 3),
(2, 'Carlos Mota', 'Carlos has over 8 years of experience as a personal trainer, specialising in high-intensity workouts. Certified in HIIT, Crossfit and Sports Nutrition.', 'HIIT · Crossfit · Box Fit', 'HIIT Specialist, Crossfit Level 1, Sports Nutrition Certificate', 4),
(3, 'Marta Lima',  'Pilates instructor and physiotherapist with a focus on core strength and injury rehabilitation.', 'Pilates · Meditation', 'Pilates Method Alliance, BSc Physiotherapy', 5),
(4, 'João Pires',  'Cycling and cardio expert. João brings high energy and technical expertise to every spinning session.', 'Spinning · Cycling', 'Spinning Instructor Certification, Level 3 Personal Trainer', 6),
(5, 'Rita Costa',  'Functional fitness coach specialising in crossfit and outdoor training. Rita loves pushing limits safely.', 'Crossfit · Functional Fitness', 'Crossfit Level 2, NSCA-CPT', 7),
(6, 'trainer',     'General fitness trainer.', 'General Fitness', 'Personal Trainer Certification', 13);

-- Classes
INSERT INTO classes (id, name, type, description, duration_min, capacity, trainer_id, image, day_of_week, time) VALUES
( 1, 'Yoga Flow',          'Yoga',       'A relaxing yet invigorating yoga session that improves flexibility, balance and mindfulness. Suitable for all levels.', 60, 20,  1, '/docs/images/yoga_flow.jpg',          'Monday',    '08:00'),
( 2, 'Yoga Flow',          'Yoga',       'A relaxing yet invigorating yoga session that improves flexibility, balance and mindfulness. Suitable for all levels.', 60, 20,  1, '/docs/images/yoga_flow.jpg',          'Wednesday', '17:00'),
( 3, 'Yoga Flow',          'Yoga',       'A relaxing yet invigorating yoga session that improves flexibility, balance and mindfulness. Suitable for all levels.', 60, 15,  3, '/docs/images/yoga_flow.jpg',          'Saturday',  '09:00'),
( 4, 'HIIT Intense',       'HIIT',       'A high-intensity interval training session combining strength, cardio and explosive movements. Suitable for all levels.', 45, 20,  2, '/docs/images/hiit_intense.jpg',       'Tuesday',   '18:30'),
( 5, 'HIIT Intense',       'HIIT',       'A high-intensity interval training session combining strength, cardio and explosive movements. Suitable for all levels.', 45, 20,  5, '/docs/images/hiit_intense.jpg',       'Thursday',  '07:00'),
( 6, 'HIIT Intense',       'HIIT',       'A high-intensity interval training session combining strength, cardio and explosive movements. Suitable for all levels.', 45, 20,  2, '/docs/images/hiit_intense.jpg',       'Saturday',  '10:00'),
( 7, 'Pilates Core',       'Pilates',    'Strengthen your core and improve posture with this guided pilates session. Great for back pain relief and flexibility.',  50, 15,  3, '/docs/images/pilates_core.jpg',       'Wednesday', '10:00'),
( 8, 'Pilates Core',       'Pilates',    'Strengthen your core and improve posture with this guided pilates session. Great for back pain relief and flexibility.',  50, 15,  3, '/docs/images/pilates_core.jpg',       'Friday',    '09:00'),
( 9, 'Pilates Core',       'Pilates',    'Strengthen your core and improve posture with this guided pilates session. Great for back pain relief and flexibility.',  50, 15,  1, '/docs/images/pilates_core.jpg',       'Sunday',    '11:00'),
(10, 'Spinning Pro',       'Spinning',   'High-energy indoor cycling class with interval training. Improve cardiovascular fitness and burn calories fast.',         45, 25,  4, '/docs/images/spinning_pro.jpg',       'Monday',    '07:00'),
(11, 'Spinning Pro',       'Spinning',   'High-energy indoor cycling class with interval training. Improve cardiovascular fitness and burn calories fast.',         45, 25,  4, '/docs/images/spinning_pro.jpg',       'Thursday',  '19:00'),
(12, 'Spinning Pro',       'Spinning',   'High-energy indoor cycling class with interval training. Improve cardiovascular fitness and burn calories fast.',         45, 25,  5, '/docs/images/spinning_pro.jpg',       'Friday',    '18:00'),
(13, 'Crossfit WOD',       'Crossfit',   'Workout of the Day — varied functional movements performed at high intensity. Build strength, endurance and agility.',    60, 15,  5, '/docs/images/crossfit_wod.jpg',       'Friday',    '07:00'),
(14, 'Crossfit WOD',       'Crossfit',   'Workout of the Day — varied functional movements performed at high intensity. Build strength, endurance and agility.',    60, 15,  2, '/docs/images/crossfit_wod.jpg',       'Tuesday',   '07:00'),
(15, 'Crossfit WOD',       'Crossfit',   'Workout of the Day — varied functional movements performed at high intensity. Build strength, endurance and agility.',    60, 15,  5, '/docs/images/crossfit_wod.jpg',       'Sunday',    '09:00'),
(16, 'Zumba Dance',        'Zumba',      'Fun and energetic dance workout blending Latin rhythms with easy-to-follow moves. No dance experience needed.',           55, 25,  1, '/docs/images/zumba.jpg',              'Tuesday',   '19:00'),
(17, 'Zumba Dance',        'Zumba',      'Fun and energetic dance workout blending Latin rhythms with easy-to-follow moves. No dance experience needed.',           55, 25,  1, '/docs/images/zumba.jpg',              'Saturday',  '11:00'),
(18, 'Box Fit',            'Boxing',     'Boxing-inspired fitness class combining punching combinations with bodyweight exercises. Great cardio and stress relief.', 60, 20,  2, '/docs/images/box_fit.jpg',            'Tuesday',   '20:00'),
(19, 'Box Fit',            'Boxing',     'Boxing-inspired fitness class combining punching combinations with bodyweight exercises. Great cardio and stress relief.', 60, 20,  2, '/docs/images/box_fit.jpg',            'Thursday',  '18:00'),
(20, 'Guided Meditation',  'Meditation', 'A calming session focused on breathwork, mindfulness and mental clarity. Perfect for stress reduction and recovery.',     40, 20,  3, '/docs/images/guided_meditation.jpg',  'Wednesday', '19:00'),
(21, 'Guided Meditation',  'Meditation', 'A calming session focused on breathwork, mindfulness and mental clarity. Perfect for stress reduction and recovery.',     40, 20,  3, '/docs/images/guided_meditation.jpg',  'Sunday',    '09:00');

-- Reviews
INSERT INTO reviews (id, class_id, user_id, rating, comment) VALUES
(1,  4,  8, 5, 'Amazing class! Carlos is very motivating and the intensity is just right.'),
(2,  4,  9, 4, 'Great class, very complete. Could use more exercise variety but I highly recommend it.'),
(3,  4, 10, 5, 'I''ve been coming for 3 months and the results are obvious. Great group energy.'),
(4,  1,  8, 5, 'Ana is incredible. The class leaves me feeling refreshed and centered every single time.'),
(5,  7, 11, 5, 'Marta is an exceptional instructor. My back pain has improved significantly.'),
(6, 11, 12, 4, 'Intense and fun. João really knows how to keep the energy up throughout the session.');

INSERT INTO equipment (id, name, type, quantity, in_use) VALUES
(1, 'Treadmill',      'Cardio',    8, 3),
(2, 'Stationary Bike','Cardio',    6, 0),
(3, 'Rowing Machine', 'Cardio',    4, 4),
(4, 'Barbell Set',    'Weights',  10, 2),
(5, 'Dumbbell Rack',  'Weights',   5, 0),
(6, 'Weight Bench',   'Weights',   8, 5),
(7, 'Pull-up Bar',    'Functional',4, 0),
(8, 'Kettlebells',    'Functional',12, 7);

-- Seed plans
INSERT INTO membership_plans (id, name, price, description, max_enrollments_per_week, access_level) VALUES
(1, 'Basic',   9.99,  'Perfect to get started. Access to selected classes with a weekly limit.', 2, 1),
(2, 'Standard', 19.99, 'Great for regular gym-goers. More classes, more flexibility.', 5, 2),
(3, 'Premium', 29.99, 'Unlimited access to all classes and priority enrollment.', NULL, 3);

INSERT INTO membership_plan_features (plan_id, feature) VALUES
(1, '2 classes per week'),
(1, 'Access to Yoga and Meditation classes'),
(1, 'Locker room access'),
(2, '5 classes per week'),
(2, 'Access to all class types'),
(2, 'Locker room access'),
(2, 'Guest pass once a month'),
(3, 'Unlimited classes'),
(3, 'Access to all class types'),
(3, 'Priority enrollment'),
(3, 'Locker room access'),
(3, 'Guest pass twice a month'),
(3, 'Personal trainer consultation');
