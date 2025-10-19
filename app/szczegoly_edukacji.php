<?php
    session_start();

    $education = null;
    $jobs = [];
    $error_message = '';

    // Logika budowania linku powrotnego
    $query_params = $_GET;
    unset($query_params['education_id']); 
    
    // Sprawdzenie, czy URL zawiera parametry spersonalizowanej ankiety
    $is_from_survey = isset($query_params['profil']) || isset($query_params['branza']) || isset($query_params['edukacja']) || isset($query_params['cel']);

    if ($is_from_survey) {
        $back_url = 'oferty.php';
    } else {
        $back_url = 'ofertyksztalcenia.php';
    }

    if (!empty($query_params)) {
        $back_url .= '?' . http_build_query($query_params);
    }
    
    // Sprawdzamy, czy ID oferty zostało przekazane
    if (!isset($_GET['education_id']) || !is_numeric($_GET['education_id'])) {
        $error_message = "Nie wybrano oferty lub podano nieprawidłowy identyfikator.";
    } else {
        $education_id = (int)$_GET['education_id'];

        $conn = new mysqli('127.0.0.1', 'root', '', 'hackathon');
        if ($conn->connect_error) {
            $error_message = "Błąd połączenia z bazą danych."; // Uproszczony komunikat
        } else {
            $conn->set_charset("utf8");

            // ZAPYTANIE 1: Pobranie głównych danych
            $sql_main = "SELECT name, type, duration_months, required_input 
                         FROM education 
                         WHERE education_id = ?";
            
            $stmt_main = $conn->prepare($sql_main);
            $stmt_main->bind_param("i", $education_id);
            $stmt_main->execute();
            $result_main = $stmt_main->get_result();

            if ($result_main && $result_main->num_rows > 0) {
                $education = $result_main->fetch_assoc();
                $stmt_main->close();

                // ZAPYTANIE 2: Pobranie stanowisk
                $sql_jobs = "SELECT j.name, j.avg_salary
                             FROM jobs j
                             INNER JOIN jobs_education je ON j.job_id = je.job_id
                             WHERE je.education_id = ?
                             ORDER BY j.name";
                
                $stmt_jobs = $conn->prepare($sql_jobs);
                $stmt_jobs->bind_param("i", $education_id);
                $stmt_jobs->execute();
                $result_jobs = $stmt_jobs->get_result();

                if ($result_jobs && $result_jobs->num_rows > 0) {
                    while ($job = $result_jobs->fetch_assoc()) {
                        $jobs[] = $job;
                    }
                }
                $stmt_jobs->close();

            } else {
                $error_message = "Oferta kształcenia o podanym ID nie została znaleziona.";
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
    <title>KIERUNEKPLOCK.PL - Szczegóły</title>
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
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        .survey-step {
            display: none;
        }
        .survey-step.active {
            display: block;
        }
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
        }
        main {
            flex-grow: 1;
        }
        /* STYLE DLA EKRANU ŁADOWANIA */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #f7f7f7;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.3s ease-out, visibility 0.3s;
        }
        .spinner {
            border: 4px solid rgba(59, 89, 152, 0.2);
            border-top: 4px solid #3b5998;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
        }
        .loading-text {
            color: #3b5998;
            font-weight: 600;
        }
        .hidden-overlay {
            opacity: 0 !important;
            visibility: hidden !important;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <link rel="icon" href="img/logo.png" type="image/x-icon">
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased">
    <div id="loading-overlay" class="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text">Ładowanie...</div>
    </div>

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
            <button id="mobile-menu-open-btn" class="text-white p-2 rounded-md hover:bg-white hover:bg-opacity-20 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                <span class="sr-only">Otwórz menu</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>
    </nav>
    
    <div class="hidden md:block bg-white"> 
        <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap justify-center gap-x-6 gap-y-2 md:gap-x-8">
            <a href="index.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue transition-colors hover:border-b-2 hover:border-brand-blue">STRONA GŁÓWNA</a>
            <a href="form.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue transition-colors hover:border-b-2 hover:border-brand-blue">ANKIETA</a>
            <a href="ofertypracy.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue transition-colors hover:border-b-2 hover:border-brand-blue">OFERTY PRACY</a>
            <a href="ofertyksztalcenia.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue transition-colors hover:border-b-2 hover:border-brand-blue">OFERTY EDUKACJI</a>
            <a href="slownikzawodow.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">SŁOWNIK ZAWODÓW</a>
            <a href="dodajoferte.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue transition-colors hover:border-b-2 hover:border-brand-blue">DODAJ OFERTĘ</a>
        </div>
    </div>

    <div id="mobile-menu" class="md:hidden fixed inset-0 bg-white z-[100] transform translate-x-full transition-transform duration-300 ease-in-out">
        <div class="flex flex-col h-full text-gray-800">
            
            <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center w-full">
                <a href="index.php" class="flex items-center space-x-2">
                    <img src="img/logo.png" alt="Logo Kierunek Płock" class="w-12 h-12 rounded-full">
                    <h1 class="text-xl font-bold uppercase tracking-wide text-gray-900">KIERUNEKPLOCK.PL</h1>
                </a>
                <button id="mobile-menu-close-btn" class="p-2 rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-blue">
                    <span class="sr-only">Zamknij menu</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-col space-y-5 p-6 mt-10 text-center">
                <a href="index.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">STRONA GŁÓWNA</a>
                <a href="form.php" class="text-lg font-semibold text-brand-blue border-b-2 border-brand-blue pb-1">ANKIETA</a>
                <a href="ofertypracy.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">OFERTY PRACY</a>
                <a href="ofertyksztalcenia.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">OFERTY EDUKACJI</a>
                <a href="slownikzawodow.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">SŁOWNIK ZAWODÓW</a>
                <a href="dodajoferte.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">DODAJ OFERTĘ</a>
            </div>

            <div class="mt-auto border-t border-gray-200 p-6 text-center space-y-4">
                <?php if (isset($_SESSION['nazwa_uzytkownika'])): ?>
                    <span class="block text-lg font-medium">Witaj, <?php echo htmlspecialchars($_SESSION['nazwa_uzytkownika']); ?></span>
                    <a href="wyloguj.php" class="block w-full max-w-xs mx-auto bg-plock-red text-white py-2 px-4 rounded-md font-semibold hover:bg-opacity-90">Wyloguj</a>
                <?php else: ?>
                    <a href="login.php" class="block w-full max-w-xs mx-auto bg-brand-blue text-white py-2 px-4 rounded-md font-semibold hover:bg-opacity-90">Gość / Zaloguj się</a>
                    <a href="rejestracja.php" class="block w-full max-w-xs mx-auto bg-gray-200 text-gray-800 py-2 px-4 rounded-md font-semibold hover:bg-gray-300">Rejestracja</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

    <main class="flex-grow container mx-auto p-4 mt-8">
        
        <a href="<?php echo htmlspecialchars($back_url); ?>" class="inline-block mb-4 text-brand-blue hover:underline font-medium" style="color: #3b5998;">
            &larr; Powrót do listy ofert
        </a>

        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-xl w-full max-w-4xl mx-auto">
            
            <?php if (!empty($error_message)): ?>
                <h1 class="text-3xl font-bold text-red-600 text-center">Błąd</h1>
                <p class="text-gray-600 text-center mt-4"><?php echo htmlspecialchars($error_message); ?></p>

            <?php elseif ($education): ?>
                <h1 class="text-3xl md:text-4xl font-bold text-brand-blue mb-2" style="color: #3b5998;"><?php echo htmlspecialchars($education['name']); ?></h1>
                <h2 class="text-2xl font-semibold text-gray-700 mb-6"><?php echo htmlspecialchars($education['type']); ?></h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 border-t border-gray-200 pt-6">
                    <div>
                        <span class="block text-sm font-medium text-gray-500">Czas trwania</span>
                        <span class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($education['duration_months']); ?> miesięcy</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500">Wymagane na wejściu</span>
                        <span class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($education['required_input']); ?></span>
                    </div>
                </div>

                <h3 class="text-2xl font-semibold text-gray-800 mt-8 mb-4 border-t border-gray-200 pt-6">Powiązane stanowiska pracy:</h3>
                <?php if (!empty($jobs)): ?>
                    <ul class="list-disc list-inside space-y-2">
                        <?php foreach ($jobs as $job): ?>
                            <li class="text-gray-700">
                                <span class="font-semibold"><?php echo htmlspecialchars($job['name']); ?></span>
                                (średnia pensja: <span class="font-medium text-green-600"><?php echo number_format($job['avg_salary'], 0, ',', ' '); ?> PLN</span>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500">Brak powiązanych stanowisk pracy dla tej oferty kształcenia.</p>
                <?php endif; ?>

            <?php endif; ?>

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
    // Funkcja ukrywająca nakładkę po załadowaniu WSZYSTKICH zasobów
    window.addEventListener('load', function() {
        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden-overlay');
        }
    });

    // Funkcja przechwytująca linki do natychmiastowego pokazania nakładki
    document.addEventListener('DOMContentLoaded', function() {
        const loadingOverlay = document.getElementById('loading-overlay');
        
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Ignoruj: puste linki, zewnętrzne strony, kotwice (#), linki do plików, _blank
                if (!href || href.startsWith('http') || href.startsWith('#') || this.target === '_blank' || href.match(/\.(pdf|zip|jpg|png)$/i)) {
                    return;
                }
                
                // Jeśli link prowadzi do tego samego pliku, nie blokujemy nawigacji
                if (href.split('?')[0] === window.location.pathname.split('/').pop() && !href.includes('?')) {
                    return;
                }

                e.preventDefault(); 
                
                if (loadingOverlay) {
                    // Natychmiast pokaż loader
                    loadingOverlay.classList.remove('hidden-overlay');
                }
                
                // Uruchomienie nawigacji po krótkim opóźnieniu (100ms)
                setTimeout(() => {
                    window.location.href = href;
                }, 100); 
            });
        });
        
        // Kod do obsługi menu mobilnego (z animacją)
        const mobileMenuOpenBtn = document.getElementById('mobile-menu-open-btn');
        const mobileMenuCloseBtn = document.getElementById('mobile-menu-close-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenuOpenBtn) mobileMenuOpenBtn.addEventListener('click', () => {
            mobileMenu.classList.remove('translate-x-full');
            document.body.style.overflow = 'hidden'; 
        });
        if (mobileMenuCloseBtn) mobileMenuCloseBtn.addEventListener('click', () => {
            mobileMenu.classList.add('translate-x-full');
            document.body.style.overflow = ''; 
        });
    });
</script>
</body>
</html>
