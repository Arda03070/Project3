
CREATE DATABASE IF NOT EXISTS fitforfun 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;


USE fitforfun;

CREATE TABLE IF NOT EXISTS leden (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefoon VARCHAR(20),
    wachtwoord VARCHAR(255) NOT NULL,
    rol ENUM('lid', 'admin') DEFAULT 'lid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB 
DEFAULT CHARSET=utf8mb4 
COLLATE=utf8mb4_general_ci;

-- Testgegevens toevoegen (worden genegeerd als email al bestaat)
-- Admin account (wachtwoord: Admin@123)

INSERT IGNORE INTO leden (naam, email, telefoon, wachtwoord, rol) 
VALUES (
'Admin', 
'admin@fitforfun.nl', 
'0612345678', 
'$2y$10$6zLVxajQc3BQEF2PkOXCm.pAXkJPYMvPvVcUdDbQsdZlLqOOzRQoy', 
'admin'
);


INSERT IGNORE INTO leden (naam, email, telefoon, wachtwoord, rol) 
VALUES (
'Jan Jansen', 
'jan@example.nl', 
'0687654321', 
'$2y$10$6zLVxajQc3BQEF2PkOXCm.pAXkJPYMvPvVcUdDbQsdZlLqOOzRQoy', 
'lid'
);

SELECT * FROM leden;

