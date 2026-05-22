PRAGMA foreign_keys = ON;

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
    author_name TEXT    NOT NULL,
    rating      INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5),
    comment     TEXT    NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS equipment (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         TEXT    NOT NULL,
    type         TEXT    NOT NULL DEFAULT '',
    quantity     INTEGER NOT NULL DEFAULT 1,
    available    INTEGER NOT NULL DEFAULT 1 CHECK(available BETWEEN 0 AND 1)
);

INSERT INTO equipment (id, name, type, quantity, available) VALUES
(1, 'Treadmill',      'Cardio',    8, 1),
(2, 'Stationary Bike','Cardio',    6, 1),
(3, 'Rowing Machine', 'Cardio',    4, 1),
(4, 'Barbell Set',    'Weights',  10, 1),
(5, 'Dumbbell Rack',  'Weights',   5, 1),
(6, 'Weight Bench',   'Weights',   8, 1),
(7, 'Pull-up Bar',    'Functional',4, 1),
(8, 'Kettlebells',    'Functional',12, 1);

-- Users
CREATE TABLE IF NOT EXISTS users (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    name     TEXT    NOT NULL,
    username TEXT    NOT NULL UNIQUE,
    password TEXT    NOT NULL,
    type     TEXT    NOT NULL DEFAULT 'member' CHECK(type IN ('member', 'trainer', 'admin', 'deactivated'))
);

-- Enrollments
CREATE TABLE IF NOT EXISTS enrollments (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    class_id   INTEGER NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    created_at TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, class_id)
);

INSERT INTO users (id, name, username, password, type) VALUES
(1, 'Admin',        'admin',   '05d467af7608358ce68eb3423f4849fd56034105', 'admin'),
(2, 'Member',       'member',  '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'member'),
(3, 'Ana Silva',    'ana',     '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'trainer'),
(4, 'Carlos Mota',  'carlos',  '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'trainer'),
(5, 'Marta Lima',   'marta',   '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'trainer'),
(6, 'João Pires',   'joao',    '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'trainer'),
(7, 'Rita Costa',   'rita',    '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'trainer');

-- Trainers
INSERT INTO trainers (id, name, bio, specializations, certifications, user_id) VALUES
(1, 'Ana Silva',   'Yoga and wellness specialist with 10 years of experience. Passionate about helping people find balance through movement and breathwork.', 'Yoga · Zumba · Meditation', 'RYT-200 Yoga Certification, Zumba Instructor License', 3),
(2, 'Carlos Mota', 'Carlos has over 8 years of experience as a personal trainer, specialising in high-intensity workouts. Certified in HIIT, Crossfit and Sports Nutrition.', 'HIIT · Crossfit · Box Fit', 'HIIT Specialist, Crossfit Level 1, Sports Nutrition Certificate', 4),
(3, 'Marta Lima',  'Pilates instructor and physiotherapist with a focus on core strength and injury rehabilitation.', 'Pilates · Meditation', 'Pilates Method Alliance, BSc Physiotherapy', 5),
(4, 'João Pires',  'Cycling and cardio expert. João brings high energy and technical expertise to every spinning session.', 'Spinning · Cycling', 'Spinning Instructor Certification, Level 3 Personal Trainer', 6),
(5, 'Rita Costa',  'Functional fitness coach specialising in crossfit and outdoor training. Rita loves pushing limits safely.', 'Crossfit · Functional Fitness', 'Crossfit Level 2, NSCA-CPT', 7);

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
(10, 'Spinning Pro',       'Spinning',   'High-energy indoor cycling class with interval training. Improve cardiovascular fitness and burn calories fast.',         45, 1,  4, '/docs/images/spinning_pro.jpg',       'Monday',    '07:00'),
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
INSERT INTO reviews (id, class_id, author_name, rating, comment) VALUES
(1,  4, 'Sofia Rodrigues', 5, 'Amazing class! Carlos is very motivating and the intensity is just right.'),
(2,  4, 'Miguel Ferreira', 4, 'Great class, very complete. Could use more exercise variety but I highly recommend it.'),
(3,  4, 'Inês Marques',    5, 'I''ve been coming for 3 months and the results are obvious. Great group energy.'),
(4,  1, 'Sofia Rodrigues', 5, 'Ana is incredible. The class leaves me feeling refreshed and centered every single time.'),
(5,  7, 'Beatriz Santos',  5, 'Marta is an exceptional instructor. My back pain has improved significantly.'),
(6, 11, 'Rui Oliveira',    4, 'Intense and fun. João really knows how to keep the energy up throughout the session.');
