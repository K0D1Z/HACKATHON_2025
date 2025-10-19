<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kariera w Płocku</title>
</head>
<body>
    <div id="panelgorny">
        <a><b>Formularz</b></a>
        <a>Oferty Pracy</a>
        <a>Oferty Kształcenia</a>
        <a>Dodaj Ofertę</a>
        <?php // Logowanie
            session_start();
            if (!isset($_SESSION['nazwa_uzytkownika'])) {
                echo '<a href="login.php">Logowanie </a>';
                echo '<a href="rejestracja.php">Rejestracja </a>';
            }
            else {
                echo '<a href="wyloguj.php">Wyloguj </a>';
                echo htmlspecialchars($_SESSION['nazwa_uzytkownika']);
            }
        ?>
    </p>
    </div>
    <div id="oferty">
        <?php
            
            $conn = new mysqli ('127.0.0.1', 'root', '', 'hackathon');

        ?>
    </div>
</body>
</html>