<?php
    session_start();
    
    // Połączenie z bazą
    $conn = new mysqli('127.0.0.1', 'root', '', 'hackathon');
    if ($conn->connect_error) {
        die("<p class='text-red-600 col-span-full'>Błąd połączenia z bazą danych: " . $conn->connect_error . "</p>");
    }
    $conn->set_charset("utf8");

    // LOGIKA PAGINACJI I WYSZUKIWANIA
    $limit_na_strone = 24;
    $search_term = $_GET['q'] ?? '';
    $search_param = '%' . strtolower($search_term) . '%';
    $where_clause = "";
    $params = [];
    if (!empty($search_term)) {
        $where_clause = " WHERE LOWER(j.name) LIKE ? OR LOWER(c.name) LIKE ? OR LOWER(c.location) LIKE ? OR LOWER(c.industry) LIKE ?";
        $params = [$search_param, $search_param, $search_param, $search_param];
    }

    $sql_count = "SELECT COUNT(DISTINCT j.job_id) AS total
                  FROM jobs AS j 
                  LEFT JOIN jobs_companies AS jc ON j.job_id = jc.job_id 
                  LEFT JOIN companies AS c ON jc.company_id = c.company_id
                  $where_clause";
    
    $stmt_count = $conn->prepare($sql_count);
    if (!empty($search_term)) {
        $stmt_count->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
    $stmt_count->close();

    $total_pages = ceil($total_rows / $limit_na_strone);
    $strona = (int)($_GET['strona'] ?? 1);
    if ($strona < 1) $strona = 1;
    if ($strona > $total_pages && $total_pages > 0) $strona = $total_pages;
    $offset = ($strona - 1) * $limit_na_strone;

    $sql_main = " SELECT 
                j.job_id, 
                c.name AS company_name,
                c.industry AS company_industry,
                c.location, 
                j.name AS job_name,
                j.level_required, 
                j.avg_salary 
            FROM 
                jobs AS j 
            LEFT JOIN 
                jobs_companies AS jc ON j.job_id = jc.job_id 
            LEFT JOIN 
                companies AS c ON jc.company_id = c.company_id
            $where_clause
            ORDER BY 
                j.name ASC           
            LIMIT ? OFFSET ?;"; 

    $stmt_main = $conn->prepare($sql_main);
    if ($stmt_main === FALSE) {
        die("<p class='text-red-600 col-span-full'>Błąd przygotowania zapytania: " . $conn->error . "</p>");
    }
    
    $main_params = $params;
    $main_params[] = $limit_na_strone;
    $main_params[] = $offset;
    $types_main = str_repeat('s', count($params)) . 'ii'; 
    
    $stmt_main->bind_param($types_main, ...$main_params);
    $stmt_main->execute();
    $result = $stmt_main->get_result();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KIERUNEKPLOCK.PL - Oferty Pracy</title>
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

        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out forwards;
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
            <button id="mobile-menu-open-btn" class="text-white p-2 rounded-md hover:bg-white hover:bg-opacity-20">
                <span class="sr-only">Otwórz menu</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
            </button>
        </div>
    </nav>
    <div class="hidden md:block bg-white"> 
        <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap justify-center gap-x-6 gap-y-2 md:gap-x-8">
            <a href="index.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">STRONA GŁÓWNA</a>
            <a href="form.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">ANKIETA</a>
            <a href="ofertypracy.php" class="text-base font-semibold text-brand-blue border-b-2 border-brand-blue">OFERTY PRACY</a>
            <a href="ofertyksztalcenia.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">OFERTY EDUKACJI</a>
            <a href="slownikzawodow.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">SŁOWNIK ZAWODÓW</a>
            <a href="dodajoferte.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">DODAJ OFERTĘ</a>
        </div>
    </div>
    <div id="mobile-menu" class="md:hidden fixed inset-0 bg-white z-[100] transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center w-full">
                <a href="index.php" class="flex items-center space-x-2">
                    <img src="img/logo.png" alt="Logo Kierunek Płock" class="w-12 h-12 rounded-full">
                    <h1 class="text-xl font-bold uppercase text-gray-900">KIERUNEKPLOCK.PL</h1>
                </a>
                <button id="mobile-menu-close-btn" class="p-2 rounded-md text-gray-700 hover:bg-gray-100">
                    <span class="sr-only">Zamknij menu</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="flex flex-col space-y-5 p-6 mt-10 text-center">
                <a href="index.php" class="text-lg font-semibold text-gray-600">STRONA GŁÓWNA</a>
                <a href="form.php" class="text-lg font-semibold text-gray-600">ANKIETA</a>
                <a href="ofertypracy.php" class="text-lg font-semibold text-brand-blue border-b-2 border-brand-blue pb-1">OFERTY PRACY</a>
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

    <main class="max-w-5xl mx-auto p-6 mt-10 w-full animate-fadeIn">
        <div class="text-center pb-8 mb-8 border-b border-gray-200">
            <h1 class="text-4xl font-extrabold text-gray-900">Baza Ofert Pracy</h1>
            <p class="text-lg text-gray-600 mt-2 max-w-2xl mx-auto">Przeglądaj i wyszukuj aktualne oferty pracy od lokalnych pracodawców w Płocku i okolicach.</p>
        </div>
        
        <form method="GET" action="ofertypracy.php" class="mb-8 max-w-3xl mx-auto">
            <div class="flex shadow-sm rounded-lg">
                <input type="text" name="q" placeholder="Wpisz firmę, stanowisko, lokalizację..." 
                       class="w-full p-4 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-brand-blue" 
                       value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit" class="p-4 bg-brand-blue text-white rounded-r-lg font-semibold hover:opacity-90 transition-opacity">Szukaj</button>
            </div>
        </form>

        <div id="oferty" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between">';
                        echo '<div>';
                        echo '<h3 class="text-2xl font-bold text-brand-blue mb-2">' . htmlspecialchars($row['job_name']) . '</h3>';
                        
                        if (!empty($row['company_name'])) {
                            echo '<p class="text-lg font-semibold text-gray-800">' . htmlspecialchars($row['company_name']) . '</p>';
                            echo '<p class="text-md text-gray-600 mb-3">' . htmlspecialchars($row['location']) . ' • ' . htmlspecialchars($row['company_industry']) . '</p>';
                        } else {
                            echo '<p class="text-lg font-semibold text-gray-500">Brak przypisanej firmy</p>';
                        }
                        
                        echo '<p class="text-sm text-gray-500">Wymagany poziom: ' . htmlspecialchars($row['level_required']) . '</p>';
                        echo '<p class="text-xl font-bold text-green-600 my-3">' . number_format($row['avg_salary'], 0, ',', ' ') . ' PLN</p>';
                        echo '</div>';
                        echo '<a href="szczegoly.php?job_id=' . $row['job_id'] . '" class="inline-block mt-4 bg-brand-blue text-white text-center py-2 px-5 rounded-lg font-semibold hover:opacity-90 transition-opacity">Szczegóły</a>';
                        echo '</div>';
                    }
                } else {
                    echo "<p class='text-gray-600 col-span-full text-center text-lg'>Nie znaleziono żadnych wyników dla zapytania: \"" . htmlspecialchars($search_term) . "\"</p>";
                }
                $stmt_main->close();
                $conn->close();
            ?>
        </div>
        
        <div class="mt-12 flex flex-wrap justify-center items-center gap-2">
            <?php if ($total_pages > 1): ?>
                <?php $query_string = !empty($search_term) ? '&q=' . urlencode($search_term) : ''; ?>

                <?php if ($strona > 1): ?>
                    <a href="?strona=<?php echo $strona - 1; ?><?php echo $query_string; ?>" class="py-2 px-4 bg-white text-gray-700 rounded-lg shadow-sm hover:bg-gray-100">Poprzednia</a>
                <?php else: ?>
                    <span class="py-2 px-4 bg-gray-200 text-gray-400 rounded-lg shadow-sm cursor-not-allowed">Poprzednia</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?strona=<?php echo $i; ?><?php echo $query_string; ?>" class="py-2 px-4 rounded-lg shadow-sm <?php echo ($i == $strona) ? 'bg-brand-blue text-white font-bold' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($strona < $total_pages): ?>
                    <a href="?strona=<?php echo $strona + 1; ?><?php echo $query_string; ?>" class="py-2 px-4 bg-white text-gray-700 rounded-lg shadow-sm hover:bg-gray-100">Następna</a>
                <?php else: ?>
                    <span class="py-2 px-4 bg-gray-200 text-gray-400 rounded-lg shadow-sm cursor-not-allowed">Następna</span>
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
