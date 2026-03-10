# FitForFun Lidmaatschapssysteem – Setup Gids

## 1. PHP en MySQL Voorbereiding
- Installeer lokale server: XAMPP, WAMP, MAMP, etc.
- Start **Apache** en **MySQL**
- Open phpMyAdmin: `http://localhost/phpmyadmin`

## 2. Database Aanmaken

### Methode 1 – Automatisch
- Open: `http://localhost/test/setup_database.php`
- Je ziet: "✓ Database 'fitforfun' en tabel 'leden' succesvol aangemaakt!"
- Fout? Check MySQL credentials in `db_config.php`

### Methode 2 – Handmatig via phpMyAdmin
```sql
CREATE DATABASE IF NOT EXISTS fitforfun CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE fitforfun;

CREATE TABLE IF NOT EXISTS leden (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefoon VARCHAR(20),
    wachtwoord VARCHAR(255) NOT NULL,
    rol ENUM('lid', 'admin') DEFAULT 'lid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Eerste admin (optioneel)
INSERT INTO leden (naam, email, telefoon, wachtwoord, rol) 
VALUES ('Admin', 'admin@fitforfun.nl', '0612345678', '$2y$10$...', 'admin');