# Test Accounts — FitForFunDB

## Inloggen via Gebruikersnaam

Na het uitvoeren van `database_setup.sql` kun je inloggen met de volgende accounts:

| Gebruikersnaam | Wachtwoord  | Rol            | Naam           |
|----------------|-------------|----------------|----------------|
| `janj`         | `password1` | Lid            | Jan Jansen     |
| `saradev`      | `password2` | Lid            | Sara de Vries  |
| `keesb`        | `password3` | Administrator  | Kees Bakker    |
| `emmaj`        | `password4` | Medewerker     | Emma Janssen   |
| `alik`         | `password5` | Gastgebruiker  | Ali Khan       |

## Instructies

1. Open `setup_database.php` in de browser om de database aan te maken
2. Ga naar `login.php`
3. Log in met een **Gebruikersnaam** en **Wachtwoord** uit bovenstaande tabel
4. Leden en Gastgebruikers worden doorgestuurd naar `profile.php`
5. Administrators worden doorgestuurd naar `admin.php`

## Database: FitForFunDB

De database bevat de volgende tabellen:
- **gebruiker** — Alle gebruikersaccounts (met bcrypt gehashte wachtwoorden)
- **rol** — Koppeling tussen gebruikers en hun rollen
- **medewerker** — Medewerkers van de sportschool
- **lid** — Leden met contactgegevens (email, mobiel)
- **les** — Groepslessen met planning en prijzen
- **reservering** — Reserveringen voor lessen