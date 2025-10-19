<?php
    session_start();
    
    $error_message = ''; // Zmienna na błędy do wyświetlenia w HTML

    // Obsługa formularza TYLKO gdy został wysłany
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $conn = new mysqli('127.0.0.1', 'root', '', 'stronainternetowa');
        mysqli_report(MYSQLI_REPORT_OFF);

        if ($conn->connect_errno) {
            $error_message = "Błąd połączenia z bazą: " . $conn->connect_error;
        } else {
            // Pobieranie danych z formularza
            $nazwa   = $_POST['login'] ?? '';
            $haslo   = $_POST['haslo'] ?? '';
            $email   = $_POST['email'] ?? '';
            $telefon = $_POST['telefon'] ?? '';

            // Prosta walidacja (można rozbudować)
            if (empty($nazwa) || empty($haslo) || empty($email)) {
                $error_message = "Login, hasło i e-mail są wymagane!";
            } else {
                
                // --- POPRAWKA BEZPIECZEŃSTWA: Haszowanie hasła ---
                $hashed_haslo = password_hash($haslo, PASSWORD_DEFAULT);

                // --- POPRAWKA BEZPIECZEŃSTWA: Prepared Statements (ochrona przed SQL Injection) ---
                $sql = "INSERT INTO uzytkownicy (nazwa, haslo, email, telefon) VALUES (?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                
                // "ssss" oznacza, że wszystkie 4 zmienne są typu string
                $stmt->bind_param("ssss", $nazwa, $hashed_haslo, $email, $telefon);

                if ($stmt->execute()) {
                    // Rejestracja pomyślna - od razu logujemy użytkownika
                    $_SESSION['zalogowany'] = true;
                    $_SESSION['nazwa_uzytkownika'] = $nazwa;
                    
                    $stmt->close();
                    $conn->close();
                    
                    // Przekierowanie (teraz zadziała, bo jest przed HTML)
                    header('Location: ofertypracy.php');
                    exit;
                } else {
                    // Błąd - sprawdzamy, czy to duplikat
                    if ($conn->errno === 1062) {
                        $error_message = "Taki użytkownik lub email już istnieje!";
                    } else {
                        $error_message = "Błąd rejestracji: " . $stmt->error;
                    }
                }
                $stmt->close();
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
    <title>Rejestracja - Kariera w Płocku</title>
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
                    <a href="login.php" class="hover:underline">GOŚĆ / ZALOGUJ SIĘ</a>
                    <a href="rejestracja.php" class="font-semibold hover:underline uppercase">REJESTRACJA</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="flex-grow container mx-auto p-4 flex items-center justify-center">
        
        <div class="w-full max-w-md">
            <form method="POST" class="bg-white p-8 rounded-2xl shadow-xl space-y-4">
                <h1 class="text-3xl font-bold text-center text-brand-blue" style="color: #3b5998;">Strona Rejestracji</h1>

                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700 mb-1">Login <span class="text-red-600">*</span></label>
                    <input type="text" name="login" id="login" required class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" style="border-color: #3b5998;">
                </div>

                <div>
                    <label for="haslo" class="block text-sm font-medium text-gray-700 mb-1">Hasło <span class="text-red-600">*</span></label>
                    <input type="password" name="haslo" id="haslo" required class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" style="border-color: #3b5998;">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail <span class="text-red-600">*</span></label>
                    <input type="email" name="email" id="email" required class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" style="border-color: #3b5998;">
                </div>

                <div>
                    <label for="telefon" class="block text-sm font-medium text-gray-700 mb-1">Telefon (opcjonalnie)</label>
                    <input type="tel" name="telefon" id="telefon" class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" style="border-color: #3b5998;">
                </div>

                <input type="submit" value="Zarejestruj się" class="w-full bg-brand-blue text-white py-3 px-5 rounded-lg font-semibold hover:opacity-90 transition-opacity cursor-pointer mt-2" style="background-color: #3b5998;">

                <div class="text-center space-y-2 pt-2">
                    <a href="login.php" class="text-brand-blue hover:underline font-medium" style="color: #3b5998;">Masz już konto? Zaloguj się</a>
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