-- Run this ONCE in phpMyAdmin (SQL tab) if you already imported the database and don't want to re-run setup.php
ALTER TABLE destinations ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER gradient;

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
