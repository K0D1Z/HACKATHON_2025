<?php
    // --- CAŁA LOGIKA PRZENIESIONA NA GÓRĘ ---
    session_start();
    
    $error_message = ''; // Zmienna do przechowywania błędów i wyświetlania ich w HTML

    // Sprawdzamy, czy formularz został wysłany
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Używam danych z Twojego skryptu
        $conn = new mysqli('127.0.0.1', 'root', '', 'stronainternetowa'); 
        mysqli_report(MYSQLI_REPORT_OFF);

        if ($conn->connect_error) {
            $error_message = "Błąd połączenia z bazą danych: " . $conn->connect_error;
        } else {
            $nazwa = $_POST['login'] ?? '';
            $haslo = $_POST['haslo'] ?? '';
        
            if (empty($nazwa) || empty($haslo)) {
                $error_message = "Uzupełnij login i hasło!";
            } else {
                $nazwa_esc = $conn->real_escape_string($nazwa);
                $haslo_esc = $conn->real_escape_string($haslo);
                
                // !!! BARDZO WAŻNA UWAGA DOTYCZĄCA BEZPIECZEŃSTWA !!!
                // Twój kod porównuje hasła jako zwykły tekst. Nigdy tak nie rób na produkcyjnej stronie!
                // Zamiast tego, podczas rejestracji zapisz hasło jako: $hash = password_hash($haslo, PASSWORD_DEFAULT);
                // A podczas logowania sprawdzaj je używając: password_verify($haslo, $hash_z_bazy)
                
                // Tymczasowo trzymam się Twojej logiki:
                $sql = "SELECT * FROM uzytkownicy WHERE nazwa = '$nazwa_esc' AND haslo = '$haslo_esc' LIMIT 1";
                $res = $conn->query($sql);

                if ($res && $res->num_rows === 1) {
                    // Użytkownik poprawny
                    $_SESSION['zalogowany'] = true;
                    $_SESSION['nazwa_uzytkownika'] = $nazwa;
                    
                    if ($res) $res->free_result();
                    $conn->close();

                    // Przekierowanie - teraz zadziała, bo jest PRZED HTML
                    header('Location: ofertypracy.php');
                    exit; // Zawsze 'exit' po przekierowaniu
                } else {
                    $error_message = "Błędne dane logowania.";
                }
                if ($res) $res->free_result();
            }
            $conn->close();
        }
    }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie - Kariera w Płocku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Ta sama konfiguracja co poprzednio */
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-blue': '#304A8B',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans flex flex-col min-h-screen">

    <header class="bg-brand-blue text-white shadow-lg" style="background-color: #3b5998;">
        <nav class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="index.php" class="flex items-center space-x-2">
                    <span class="bg-white text-brand-blue rounded-full p-2 w-12 h-12 flex items-center justify-center font-bold text-sm" style="color: #3b5998;">PŁOCK</span>
                    <h1 class="text-xl md:text-2xl font-bold uppercase tracking-wide">KIERUNEKPLOCK.PL</h1>
                </a>
            </div>
            <div class="flex items-center space-x-4 md:space-x-6 text-sm">
                <?php if (isset($_SESSION['nazwa_uzytkownika'])): ?>
                    <span class="font-medium">Witaj, <?php echo htmlspecialchars($_SESSION['nazwa_uzytkownika']); ?></span>
                    <a href="wyloguj.php" class="font-semibold hover:underline uppercase">Wyloguj</a>
                <?php else: ?>
                    <a href="login.php" class="font-semibold hover:underline uppercase">GOŚĆ / ZALOGUJ SIĘ</a>
                    <a href="rejestracja.php" class="font-semibold hover:underline uppercase">REJESTRACJA</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="flex-grow container mx-auto p-4 flex items-center justify-center">
        
        <div class="w-full max-w-md">
            <form method="POST" class="bg-white p-8 rounded-2xl shadow-xl space-y-6">
                <h1 class="text-3xl font-bold text-center text-brand-blue" style="color: #3b5998;">Logowanie</h1>

                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700 mb-1">Login</label>
                    <input type="text" name="login" id="login" class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" style="border-color: #3b5998;">
                </div>

                <div>
                    <label for="haslo" class="block text-sm font-medium text-gray-700 mb-1">Hasło</label>
                    <input type="password" name="haslo" id="haslo" class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" style="border-color: #3b5998;">
                </div>

                <input type="submit" value="Zaloguj się" class="w-full bg-brand-blue text-white py-3 px-5 rounded-lg font-semibold hover:opacity-90 transition-opacity cursor-pointer" style="background-color: #3b5998;">

                <div class="text-center space-y-2">
                    <a href="rejestracja.php" class="text-brand-blue hover:underline font-medium" style="color: #3b5998;">Nie masz konta? Zarejestruj się</a>
                    <a href="ofertypracy.php" class="block text-sm text-gray-500 hover:underline">Cofnij do ofert</a>
                </div>
            </form>
        </div>

    </main>

    <footer class="bg-brand-blue text-white p-4 mt-12" style="background-color: #3b5998;">
        <div class="container mx-auto text-center text-xs uppercase tracking-wider">
            <p>© TRINF HACKATHON 2025</p>
            <p>Dominik Dylewski, Bartek Zakrzewski, Konrad Zatorski</p>
        </div>
    </footer>

</body>
</html>