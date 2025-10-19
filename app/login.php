<?php
    session_start();
    
    $error_message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $conn = new mysqli('127.0.0.1', 'root', '', 'stronainternetowa'); 
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        if ($conn->connect_error) {
            $error_message = "Błąd połączenia z bazą danych.";
        } else {
            $nazwa = $_POST['login'] ?? '';
            $haslo_wprowadzone = $_POST['haslo'] ?? '';
        
            if (empty($nazwa) || empty($haslo_wprowadzone)) {
                $error_message = "Proszę uzupełnić login i hasło.";
            } else {
                try {
                    $sql = "SELECT haslo FROM uzytkownicy WHERE nazwa = ? LIMIT 1";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $nazwa);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        $hashed_haslo_z_bazy = $user['haslo'];

                        if (password_verify($haslo_wprowadzone, $hashed_haslo_z_bazy)) {
                            $_SESSION['zalogowany'] = true;
                            $_SESSION['nazwa_uzytkownika'] = $nazwa;
                            
                            $stmt->close();
                            $conn->close();
        
                            header('Location: index.php'); 
                            exit;
                        } else {
                            $error_message = "Błędny login lub hasło.";
                        }
                    } else {
                        $error_message = "Błędny login lub hasło.";
                    }
                    $stmt->close();
                } catch (Exception $e) {
                    $error_message = "Wystąpił błąd serwera. Spróbuj ponownie później.";
                }
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
    <title>KIERUNEKPLOCK.PL - Logowanie</title>
    <script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'plock-blue': '#47659a',
                    'plock-red': '#e6001d',
                    'brand-blue': '#3b5998',
                },
            },
        },
    };
</script>
    <style>
        html, body { height: 100%; }
        body { display: flex; flex-direction: column; }
        main { flex-grow: 1; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
    </style>
    <link rel="icon" href="img/logo.png" type="image/x-icon">
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased">

<header class="bg-brand-blue text-white sticky top-0 z-50 shadow-lg">
    <nav class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
        <a href="index.php" class="flex items-center space-x-2">
            <img src="img/logo.png" alt="Logo Kierunek Płock" class="w-12 h-12 rounded-full">
            <h1 class="text-xl md:text-2xl font-bold uppercase tracking-wide">KIERUNEKPLOCK.PL</h1>
        </a>
        <div class="hidden md:flex items-center space-x-4 md:space-x-6 text-sm">
            <?php if (isset($_SESSION['nazwa_uzytkownika'])): ?>
                <span class="font-medium">Witaj, <?php echo htmlspecialchars($_SESSION['nazwa_uzytkownika']); ?></span>
                <a href="wyloguj.php" class="font-semibold hover:underline uppercase">Wyloguj</a>
            <?php else: ?>
                <a href="login.php" class="hover:underline">Gość / Zaloguj się</a>
                <a href="rejestracja.php" class="font-semibold hover:underline uppercase">Rejestracja</a>
            <?php endif; ?>
        </div>
        <div class="md:hidden">
            <button id="mobile-menu-open-btn" class="text-white p-2 rounded-md hover:bg-white hover:bg-opacity-20"><span class="sr-only">Otwórz menu</span><svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg></button>
        </div>
    </nav>
    <div class="hidden md:block bg-white"> 
        <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap justify-center gap-x-6 gap-y-2 md:gap-x-8">
            <a href="index.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">STRONA GŁÓWNA</a>
            <a href="form.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">ANKIETA</a>
            <a href="ofertypracy.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">OFERTY PRACY</a>
            <a href="ofertyksztalcenia.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">OFERTY EDUKACJI</a>
            <a href="slownikzawodow.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">SŁOWNIK ZAWODÓW</a>
            <a href="dodajoferte.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">DODAJ OFERTĘ</a>
        </div>
    </div>
    <div id="mobile-menu" class="md:hidden fixed inset-0 bg-white z-[100] transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center w-full">
                <a href="index.php" class="flex items-center space-x-2"><img src="img/logo.png" alt="Logo Kierunek Płock" class="w-12 h-12 rounded-full"><h1 class="text-xl font-bold uppercase text-gray-900">KIERUNEKPLOCK.PL</h1></a>
                <button id="mobile-menu-close-btn" class="p-2 rounded-md text-gray-700 hover:bg-gray-100"><span class="sr-only">Zamknij menu</span><svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <div class="flex flex-col space-y-5 p-6 mt-10 text-center">
                <a href="index.php" class="text-lg font-semibold text-gray-600">STRONA GŁÓWNA</a>
                <a href="form.php" class="text-lg font-semibold text-gray-600">ANKIETA</a>
                <a href="ofertypracy.php" class="text-lg font-semibold text-gray-600">OFERTY PRACY</a>
                <a href="ofertyksztalcenia.php" class="text-lg font-semibold text-gray-600">OFERTY EDUKACJI</a>
                <a href="slownikzawodow.php" class="text-lg font-semibold text-gray-600">SŁOWNIK ZAWODÓW</a>
                <a href="dodajoferte.php" class="text-lg font-semibold text-gray-600">DODAJ OFERTĘ</a>
            </div>
            <div class="mt-auto border-t p-6 text-center space-y-4">
                <?php if (isset($_SESSION['nazwa_uzytkownika'])): ?>
                    <span class="block text-lg font-medium">Witaj, <?php echo htmlspecialchars($_SESSION['nazwa_uzytkownika']); ?></span>
                    <a href="wyloguj.php" class="block w-full max-w-xs mx-auto bg-plock-red text-white py-2 px-4 rounded-md font-semibold">Wyloguj</a>
                <?php else: ?>
                    <a href="login.php" class="block w-full max-w-xs mx-auto bg-brand-blue text-white py-2 px-4 rounded-md font-semibold">Gość / Zaloguj się</a>
                    <a href="rejestracja.php" class="block w-full max-w-xs mx-auto bg-gray-200 text-gray-800 py-2 px-4 rounded-md font-semibold">Rejestracja</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

    <main class="flex-grow container mx-auto p-4 flex items-center justify-center">
        <div class="w-full max-w-md">
            <form method="POST" class="bg-white p-8 rounded-2xl shadow-xl space-y-6 fade-in">
                <h1 class="text-3xl font-bold text-center text-brand-blue">Logowanie</h1>

                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700 mb-1">Login</label>
                    <input type="text" name="login" id="login" required class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-blue">
                </div>

                <div>
                    <label for="haslo" class="block text-sm font-medium text-gray-700 mb-1">Hasło</label>
                    <input type="password" name="haslo" id="haslo" required class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-blue">
                </div>

                <input type="submit" value="Zaloguj się" class="w-full bg-brand-blue text-white py-3 px-5 rounded-lg font-semibold hover:opacity-90 transition-opacity cursor-pointer">

                <div class="text-center space-y-2">
                    <a href="rejestracja.php" class="text-brand-blue hover:underline font-medium">Nie masz konta? Zarejestruj się</a>
                    <a href="ofertypracy.php" class="block text-sm text-gray-500 hover:underline">Cofnij do ofert</a>
                </div>
            </form>
        </div>
    </main>

<footer class="bg-brand-blue text-white p-4" style="background-color: #3b5998;">
    <div class="container mx-auto text-center text-xs uppercase tracking-wider">
        <p>© TRINF HACKATHON 2025</p>
        <p>Dominik Dylewski, Bartek Zakrzewski, Konrad Zatorski</p>
        <p class="mt-2 text-xs"><a href="regulamin.txt" target="_blank" class="text-gray-300 hover:text-white transition-colors uppercase tracking-wider">REGULAMIN</a></p>
     </div>
</footer>
    <script>
        const mobileMenuOpenBtn = document.getElementById('mobile-menu-open-btn');
        const mobileMenuCloseBtn = document.getElementById('mobile-menu-close-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuOpenBtn) {
             mobileMenuOpenBtn.addEventListener('click', () => {
                mobileMenu.classList.remove('translate-x-full');
            });
        }
        
        if (mobileMenuCloseBtn) {
            mobileMenuCloseBtn.addEventListener('click', () => {
                mobileMenu.classList.add('translate-x-full');
            });
        }
    </script>
</body>
</html>
