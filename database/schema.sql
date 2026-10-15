-- Hidden Ceylon Travel & Tourism Website — Database Schema
-- Import this whole file in phpMyAdmin / MySQL CLI to set up the project.

CREATE DATABASE IF NOT EXISTS hidden_ceylon_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hidden_ceylon_db;

-- ---------------------------------------------------------
-- Admin users (backend login)
-- ---------------------------------------------------------
DROP TABLE IF EXISTS activity_log;
DROP TABLE IF EXISTS admin_users;
CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default login: username = admin / password = Admin@123
-- (change this after first login — the hash below is bcrypt for "Admin@123")
INSERT INTO admin_users (username, password_hash, full_name) VALUES
('admin', '$2y$10$JDsSA1JttVykF2D0esS95uk7poeRKVt0Lw7Hi.PKP.YY9iqRCm7Sy', 'Site Administrator');

-- ---------------------------------------------------------
-- Destinations
-- ---------------------------------------------------------
DROP TABLE IF EXISTS destinations;
CREATE TABLE destinations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  region ENUM('Cultural Triangle','Hill Country','South Coast','Wildlife') NOT NULL,
  tag VARCHAR(150) NOT NULL,
  short_desc VARCHAR(255) NOT NULL,
  long_desc TEXT NOT NULL,
  best_time VARCHAR(100) DEFAULT NULL,
  duration VARCHAR(100) DEFAULT NULL,
  highlight VARCHAR(150) DEFAULT NULL,
  icon VARCHAR(50) DEFAULT 'fa-map-pin',
  gradient VARCHAR(255) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO destinations (name, region, tag, short_desc, long_desc, best_time, duration, highlight, icon, gradient, sort_order) VALUES
('Sigiriya','Cultural Triangle','Ancient rock fortress','5th-century citadel atop a 200m granite rock, with frescoes and water gardens.','Built by King Kashyapa in the 5th century, Sigiriya rises 200 metres above the surrounding jungle. The climb passes the famous Sigiriya frescoes and the Mirror Wall before reaching the lion''s-paw entrance and the ruined summit palace.','Jan – Mar (cool, dry mornings)','Half-day visit','UNESCO World Heritage Site','fa-chess-rook','linear-gradient(155deg,#0E2C4C,#1E6FA8 55%,#3FA9E8)',1),
('Ella','Hill Country','Nine Arch Bridge & tea','Misty ridgelines, the iconic railway bridge, and endless tea estate walks.','A small hill town wrapped in tea estates, best known for the Nine Arch Bridge and the Kandy–Ella railway. Little Adam''s Peak and Ella Rock offer easy sunrise hikes with views across the valley.','Jan – Mar & Jul – Sep','2–3 days','Scenic train stop','fa-train','linear-gradient(155deg,#0B2138,#2E9ED4 60%,#8FD4F0)',2),
('Kandy','Hill Country','Temple of the Tooth','The last royal capital, a sacred lake, and the Temple of the Sacred Tooth Relic.','Sri Lanka''s last royal capital before colonial rule, set around a man-made lake. The Temple of the Sacred Tooth Relic (Sri Dalada Maligawa) is the island''s most important Buddhist shrine.','Year-round','1–2 days','UNESCO World Heritage Site','fa-place-of-worship','linear-gradient(155deg,#14264A,#3B5B9A 55%,#7FC8F5)',3),
('Galle Fort','South Coast','Colonial ramparts','17th-century Dutch fort walls, boutique cafés, and lighthouse sunsets.','A fortified old town built by the Portuguese and expanded by the Dutch in the 17th century. Ramparts, a working lighthouse, and narrow lanes of cafés and boutique shops make it an easy half-day walk.','Dec – Apr','1 day','UNESCO World Heritage Site','fa-anchor','linear-gradient(155deg,#0E2C4C,#1D6B73 55%,#3FA9E8)',4),
('Yala National Park','Wildlife','Leopards & elephants','Sri Lanka''s most visited park — the highest leopard density in the world.','Sri Lanka''s most visited national park, spanning coastal scrub and lagoons. Yala Block 1 has one of the highest wild leopard densities recorded anywhere, alongside elephants, sloth bears, and crocodiles.','Feb – Jun (dry season)','1–2 safari days','Best leopard sightings','fa-paw','linear-gradient(155deg,#102A44,#14507C 55%,#3FA9E8)',5),
('Mirissa','South Coast','Whales & palm beaches','Blue whale watching season, coconut-tree hill viewpoint, and calm surf breaks.','A crescent beach town on the south coast, known for early-morning blue whale watching boats, the Coconut Tree Hill viewpoint, and a laid-back stretch of cafés along the sand.','Nov – Apr (whale season)','2 days','Blue whale watching','fa-water','linear-gradient(155deg,#071B30,#1D6B73 55%,#7FC8F5)',6),
('Nuwara Eliya','Hill Country','Little England','Colonial-era hill station ringed by tea plantations at 1,868m elevation.','Known as ''Little England'', this hill station at 1,868m elevation is ringed by tea plantations and colonial-era bungalows, with a distinctly cool climate year-round.','Apr (Sri Lankan New Year season)','1–2 days','Highest town in Sri Lanka','fa-mug-hot','linear-gradient(155deg,#0B2138,#1E6FA8 55%,#8ED0F2)',7),
('Anuradhapura','Cultural Triangle','Sacred ancient city','First capital of ancient Sri Lanka, home to the Sri Maha Bodhi sacred fig tree.','The first ancient capital of Sri Lanka, dating back over 2,000 years. Home to the Sri Maha Bodhi, grown from a cutting of the original Bodhi tree, plus vast dagobas and monastery ruins.','May – Sep','1 full day','UNESCO World Heritage Site','fa-monument','linear-gradient(155deg,#0E2C4C,#14507C 55%,#3FA9E8)',8),
('Trincomalee','Wildlife','Untouched east coast','Natural deep-water harbour, coral reefs, and Nilaveli''s quiet white-sand beaches.','Home to one of the world''s finest natural deep-water harbours. Nearby Nilaveli and Uppuveli beaches offer calm, uncrowded white sand, and Pigeon Island has some of the best snorkelling on the east coast.','May – Sep (east coast season)','2–3 days','Pigeon Island snorkelling','fa-umbrella-beach','linear-gradient(155deg,#071B30,#1D6B73 60%,#8ED0F2)',9);

INSERT INTO destinations (name, region, tag, short_desc, long_desc, best_time, duration, highlight, icon, gradient, sort_order) VALUES
('Dambulla','Cultural Triangle','Golden cave temple','Five painted cave temples filled with Buddha statues, set into a huge granite rock.','A cave monastery complex carved into a giant granite rock and used as a Buddhist temple for over 2,000 years. Five caves hold more than 150 Buddha statues and ceilings covered in painted murals, and the climb up rewards you with views across the plains.','Year-round (go early morning)','Half-day visit','UNESCO World Heritage Site','fa-place-of-worship','linear-gradient(155deg,#0E2C4C,#1E6FA8 55%,#3FA9E8)',10),
('Polonnaruwa','Cultural Triangle','Medieval capital','The ruined royal city of the 11th–13th centuries, with the rock-cut Gal Vihara Buddhas.','Sri Lanka''s capital from the 11th to the 13th century, now a sprawling archaeological park beside the Parakrama Samudra reservoir. The Gal Vihara rock-cut Buddha statues, the royal palace ruins and the circular Vatadage are best explored by bicycle.','Feb – Sep (dry season)','Full-day visit','UNESCO World Heritage Site','fa-landmark','linear-gradient(155deg,#0B2138,#2E9ED4)',11),
('Horton Plains','Hill Country','World''s End cliff','A cool high plateau with a sheer 870m drop at World''s End and Baker''s Falls.','A cool, windswept national park plateau above 2,000m. A loop trail of about 9.5km leads to World''s End, a sheer drop of roughly 870 metres, and on to Baker''s Falls. Clouds roll in by mid-morning, so start at dawn for the clearest views.','Jan – Mar (clearest views)','Half-day (dawn start)','World''s End viewpoint','fa-mountain','linear-gradient(155deg,#102A44,#7FC8F5)',12),
('Adam''s Peak','Hill Country','Sunrise pilgrimage','A night climb up thousands of steps to a sacred summit and dawn views.','Sri Pada, a 2,243m sacred peak revered by Buddhists, Hindus, Muslims and Christians. Pilgrims and trekkers climb thousands of steps through the night to reach the summit for sunrise, when the mountain casts a triangular shadow across the land. The climbing season runs roughly December to May.','Dec – May (pilgrimage season)','Overnight climb','Sunrise from the summit','fa-person-hiking','linear-gradient(155deg,#0E2C4C,#3FA9E8)',13),
('Haputale','Hill Country','Tea ridge viewpoints','A quiet hill town with Lipton''s Seat and sweeping views over tea estates.','A quieter alternative to Ella, perched on a ridge on the southern edge of the hill country. Lipton''s Seat looks out over the tea estates, and on clear mornings the view stretches toward the southern plains. The Dambatenne tea factory is on the way up.','Jan – Mar (clear mornings)','1–2 days','Lipton''s Seat sunrise','fa-mug-hot','linear-gradient(155deg,#0E2C4C,#1E6FA8 55%,#3FA9E8)',14),
('Unawatuna','South Coast','Calm crescent bay','A sheltered bay beside Galle with swimmable water and a jungle beach nearby.','A curved, sheltered bay a short ride from Galle Fort, with calm swimming for much of the dry season. Jungle Beach and the Japanese Peace Pagoda are a short distance away, and snorkelling spots lie close to shore.','Nov – Apr','2–3 days','Swimmable bay near Galle','fa-umbrella-beach','linear-gradient(155deg,#0B2138,#2E9ED4)',15),
('Hikkaduwa','South Coast','Reefs & sea turtles','Coral reefs, turtles in the shallows, and beginner-friendly surf.','A lively beach town on the south-west coast known for snorkelling over coral, green sea turtles close to shore, and gentle surf breaks for beginners. Glass-bottom boats run out to the reef.','Nov – Apr','2 days','Snorkelling with turtles','fa-fish','linear-gradient(155deg,#102A44,#7FC8F5)',16),
('Arugam Bay','South Coast','East-coast surf town','A laid-back surf town with Sri Lanka''s best-known point break.','A relaxed surf town on the east coast, home to Main Point, the island''s best-known surf break. Its season runs opposite to the south-west coast, from roughly May to September, and the birdlife of Kumana National Park is within day-trip distance.','May – Sep','3 days','Main Point surf break','fa-water','linear-gradient(155deg,#0E2C4C,#3FA9E8)',17),
('Udawalawe','Wildlife','Elephant safari','Open grassland where wild elephant herds are reliably seen from a jeep.','A national park of open grassland and scrub around a large reservoir, famous for dependable elephant sightings on jeep safaris. The nearby Elephant Transit Home cares for orphaned calves before they are released back into the wild.','May – Sep (dry season)','Half-day safari','Reliable elephant herds','fa-paw','linear-gradient(155deg,#0E2C4C,#1E6FA8 55%,#3FA9E8)',18),
('Sinharaja Rainforest','Wildlife','Rainforest trek','Sri Lanka''s last big tract of primary rainforest, rich in endemic birds and wildlife.','A UNESCO-listed lowland rainforest in the south-west, home to a large share of Sri Lanka''s endemic birds, butterflies and amphibians. Guided walks follow shaded trails past streams and giant trees; expect leeches and rain, and go early for birdsong.','Jan – Apr, Aug – Sep (drier months)','1–2 days','Endemic bird watching','fa-tree','linear-gradient(155deg,#071B30,#1D6B73 60%,#8ED0F2)',19);

UPDATE destinations SET image='assets/images/destinations/sinharaja.jpg' WHERE name='Sinharaja Rainforest';
-- Destination photos (files live in assets/images/destinations/)
UPDATE destinations SET image='assets/images/destinations/sigiriya.jpg' WHERE name='Sigiriya';
UPDATE destinations SET image='assets/images/destinations/ella.jpg' WHERE name='Ella';
UPDATE destinations SET image='assets/images/destinations/kandy.jpg' WHERE name='Kandy';
UPDATE destinations SET image='assets/images/destinations/galle-fort.jpg' WHERE name='Galle Fort';
UPDATE destinations SET image='assets/images/destinations/yala.jpg' WHERE name='Yala National Park';
UPDATE destinations SET image='assets/images/destinations/mirissa.jpg' WHERE name='Mirissa';
UPDATE destinations SET image='assets/images/destinations/nuwara-eliya.jpg' WHERE name='Nuwara Eliya';
UPDATE destinations SET image='assets/images/destinations/anuradhapura.jpg' WHERE name='Anuradhapura';
UPDATE destinations SET image='assets/images/destinations/trincomalee.jpg' WHERE name='Trincomalee';

-- Photos for the extra destinations
UPDATE destinations SET image='assets/images/destinations/dambulla.jpg' WHERE name='Dambulla';
UPDATE destinations SET image='assets/images/destinations/polonnaruwa.jpg' WHERE name='Polonnaruwa';
UPDATE destinations SET image='assets/images/destinations/horton-plains.jpg' WHERE name='Horton Plains';
UPDATE destinations SET image='assets/images/destinations/adams-peak.jpg' WHERE name='Adam''s Peak';
UPDATE destinations SET image='assets/images/destinations/haputale.jpg' WHERE name='Haputale';
UPDATE destinations SET image='assets/images/destinations/unawatuna.jpg' WHERE name='Unawatuna';
UPDATE destinations SET image='assets/images/destinations/hikkaduwa.jpg' WHERE name='Hikkaduwa';
UPDATE destinations SET image='assets/images/destinations/arugam-bay.jpg' WHERE name='Arugam Bay';
UPDATE destinations SET image='assets/images/destinations/udawalawe.jpg' WHERE name='Udawalawe';

-- ---------------------------------------------------------
-- Packages
-- ---------------------------------------------------------
DROP TABLE IF EXISTS packages;
CREATE TABLE packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  days_label VARCHAR(30) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  description VARCHAR(255) NOT NULL,
  items_json TEXT NOT NULL COMMENT 'JSON array of inclusion bullet strings',
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO packages (name, days_label, price, description, items_json, sort_order) VALUES
('Cultural Triangle Explorer','4 Days',420.00,'Sigiriya, Anuradhapura and Polonnaruwa — ancient capitals, one route.','["Sigiriya Rock sunrise climb","Anuradhapura sacred sites","Village lunch & bullock cart ride","Private AC vehicle throughout"]',1),
('Hill Country Tea Trail','3 Days',310.00,'Kandy to Ella by scenic rail, through the heart of tea country.','["Temple of the Sacred Tooth Relic","Kandy–Ella scenic train (reserved seats)","Nine Arch Bridge & tea factory tour","Little Adam''s Peak sunrise hike"]',2),
('Southern Coast Getaway','4 Days',390.00,'Galle Fort, Mirissa whales, and a stretch of do-nothing beach time.','["Galle Fort walking tour","Mirissa blue whale watching cruise","Beachfront boutique stay","Free day for surfing or relaxing"]',3),
('Wildlife Safari Adventure','3 Days',460.00,'Two safari drives in Yala plus Udawalawe for guaranteed elephant sightings.','["2 Yala jeep safaris (dawn & dusk)","Udawalawe elephant safari","Stay at a safari-edge eco-lodge","Professional wildlife tracker guide"]',4),
('Full Island Grand Tour','10 Days',1180.00,'Everything — the triangle, the hills, the south, and the wild east.','["All featured destinations","Private driver-guide for 10 days","Handpicked 3–4★ stays included","Fully customizable day-by-day"]',5);

-- ---------------------------------------------------------
-- Booking / contact enquiries (submitted from the public site)
-- ---------------------------------------------------------
DROP TABLE IF EXISTS enquiries;
CREATE TABLE enquiries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  travel_date DATE DEFAULT NULL,
  travelers VARCHAR(30) DEFAULT NULL,
  package_interest VARCHAR(150) DEFAULT NULL,
  message TEXT DEFAULT NULL,
  status ENUM('new','contacted','closed') DEFAULT 'new',
  ip_address VARCHAR(45) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Simple activity log for the admin panel
-- ---------------------------------------------------------
CREATE TABLE activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT DEFAULT NULL,
  action VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
