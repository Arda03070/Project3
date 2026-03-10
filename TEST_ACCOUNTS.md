# Test Accounts - FitForFun Lidmaatschapssysteem

## Testaccounts aanmaken

Voer deze SQL uit in phpMyAdmin om testaccounts toe te voegen.

SQL:

USE fitforfun;

-- Admin account
INSERT INTO leden (naam, email, telefoon, wachtwoord, rol) 
VALUES ('Admin User', 'admin@fitforfun.nl', '0612345678', 
'$2y$10$6zLVxajQc3BQEF2PkOXCm.pAXkJPYMvPvVcUdDbQsdZlLqOOzRQoy', 'admin');

-- Test leden
INSERT INTO leden (naam, email, telefoon, wachtwoord, rol) 
VALUES ('Jan Jansen', 'jan@example.nl', '0687654321', 
'$2y$10$0M9wk3Yg5q8p1L3R2X7V3e4Z9f6N8B5H2C4D7E1J4M2S9A0b', 'lid');

INSERT INTO leden (naam, email, telefoon, wachtwoord, rol) 
VALUES ('Maria Smeets', 'maria@example.nl', '0698765432', 
'$2y$10$Y6xZ2q9L8R1p5M3K7V4B2e6N9H3J1C4D8F7A2S5X0W3Z6E9Q1T', 'lid');

INSERT INTO leden (naam, email, telefoon, wachtwoord, rol) 
VALUES ('Pieter Groen', 'pieter@example.nl', '0689123456', 
'$2y$10$5kL9p2M6T8R1X4V3Z7Q2e5N1H9J6C2D3F4A8S1X7W2Z5E3Q4T', 'lid');


--------------------------------------------------

LOGIN GEGEVENS

Admin

Email: admin@fitforfun.nl  
Wachtwoord: Admin@123

De admin kan alle leden bekijken, aanpassen en verwijderen.


Testleden

Jan Jansen  
Email: jan@example.nl  
Wachtwoord: Testlid@123

Maria Smeets  
Email: maria@example.nl  
Wachtwoord: Testlid@123

Pieter Groen  
Email: pieter@example.nl  
Wachtwoord: Testlid@123


--------------------------------------------------

DINGEN DIE JE KUNT TESTEN

Registratie

Ga naar:
http://localhost/test/register.php

Maak een nieuw account en probeer daarna in te loggen.


Admin dashboard

Log in via:
http://localhost/test/login.php

Gebruik het admin account. Daarna kom je op:
http://localhost/test/admin.php

Hier kun je bijvoorbeeld:
- leden bekijken
- naam van een lid aanpassen
- een lid verwijderen
- een rol veranderen naar admin


Profiel aanpassen

Log in als normaal lid.

Ga naar:
http://localhost/test/profile.php

Hier kun je:
- je naam wijzigen
- je telefoonnummer wijzigen
- je wachtwoord veranderen


--------------------------------------------------

DATABASE CONTROLEREN

Alle leden bekijken:

SELECT id, naam, email, rol FROM leden;

Admins bekijken:

SELECT id, naam, email FROM leden WHERE rol='admin';


--------------------------------------------------

OPMERKING

De wachtwoorden in de database zijn gehasht met bcrypt.
Je ziet dus niet het echte wachtwoord maar alleen een hash.

Voorbeeld:

$2y$10$6zLVxajQc3BQEF2PkOXCm.pAXkJPYMvPvVcUdDbQsdZlLqOOzRQoy



HANDIGE LINKS

Registreren
http://localhost/test/register.php

Inloggen
http://localhost/test/login.php

Profiel
http://localhost/test/profile.php

Admin panel
http://localhost/test/admin.php

Uitloggen
http://localhost/test/logout.php