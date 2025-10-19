<?php
    session_start();
    
    // Połączenie z bazą
    $conn = new mysqli('127.0.0.1', 'root', '', 'hackathon');
    if ($conn->connect_error) {
        die("<p class='text-red-600 col-span-full'>Błąd połączenia z bazą danych: " . $conn->connect_error . "</p>");
    }
    $conn->set_charset("utf8");

    // --- LOGIKA PAGINACJI I WYSZUKIWANIA ---

    $limit_na_strone = 24;

    // === POPRAWKA WYSZUKIWANIA ===
    // 1. Pobierz termin wyszukiwania
    $search_term = $_GET['q'] ?? '';
    // Konwertuj termin na małe litery
    $search_param = '%' . strtolower($search_term) . '%';

    // 2. Przygotuj klauzulę WHERE (używając LOWER())
    $where_clause = "";
    $params_count = [];
    $params_main = [];

    if (!empty($search_term)) {
        // Wyszukuj w 3 kolumnach, używając LOWER()
        $where_clause = " WHERE LOWER(name) LIKE ? OR LOWER(type) LIKE ? OR LOWER(required_input) LIKE ?";
        $params_count = [$search_param, $search_param, $search_param];
        $params_main = [$search_param, $search_param, $search_param];
    }
    // === KONIEC POPRAWKI WYSZUKIWANIA ===


    // 3. Pobierz ŁĄCZNĄ liczbę wyników
    $sql_count = "SELECT COUNT(*) AS total FROM education $where_clause";
    
    $stmt_count = $conn->prepare($sql_count);
    if (!empty($search_term)) {
        $stmt_count->bind_param(str_repeat('s', count($params_count)), ...$params_count);
    }
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit_na_strone);
    $stmt_count->close();

    // 4. Ustal bieżącą stronę
    $strona = (int)($_GET['strona'] ?? 1);
    if ($strona < 1) $strona = 1;
    if ($strona > $total_pages && $total_pages > 0) $strona = $total_pages;

    // 5. Oblicz OFFSET
    $offset = ($strona - 1) * $limit_na_strone;

    // 6. Przygotuj parametry dla głównego zapytania
    $params_main[] = $limit_na_strone;
    $params_main[] = $offset;
    $types_main = str_repeat('s', count($params_count)) . 'ii';

    // 7. Główne zapytanie SQL
    $sql = "SELECT education_id, name, type, duration_months, required_input 
            FROM education 
            $where_clause
            ORDER BY name
            LIMIT ? OFFSET ?;";

    $stmt = $conn->prepare($sql);
    if ($stmt === FALSE) {
        die("<p class='text-red-600 col-span-full'>Błąd przygotowania zapytania: " . $conn->error . "</p>");
    }
    
    $stmt->bind_param($types_main, ...$params_main);
    $stmt->execute();
    $result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kierunek Płock - Edukacja</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
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
<body class="bg-gray-100 font-sans">

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

    <div class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex flex-wrap justify-center gap-x-6 gap-y-2 md:gap-x-10">
            <a href="ofertypracy.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">OFERTY PRACY</a>
            <a href="ofertyksztalcenia.php" class="text-lg font-semibold text-brand-blue border-b-2 border-brand-blue" style="color: #3b5998; border-color: #3b5998;">OFERTY EDUKACJI</a>
            <a href="dodajoferte.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">DODAJ OFERTĘ</a>
            <a href="formularz.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">FORMULARZ</a>
        </div>
    </div>

    <main class="container mx-auto mt-8 p-4">

        <form method="GET" action="ofertyksztalcenia.php" class="mb-8">
            <div class="flex shadow-sm rounded-lg">
                <input type="text" id="wyszukiwanie" name="q" placeholder="Wpisz nazwę kursu, typ, wymagania..." 
                       class="w-full p-4 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-brand-blue" 
                       style="border-color: #3b5998;" 
                       value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit" class="p-4 bg-brand-blue text-white rounded-r-lg font-semibold hover:opacity-90" style="background-color: #3b5998;">Szukaj</button>
            </div>
        </form>

        <div id="oferty" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="oferta-blok bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between">';
                        
                        echo '<div>';
                        echo '<span class="inline-block bg-gray-200 text-brand-blue text-xs font-semibold px-2.5 py-0.5 rounded-full uppercase mb-2" style="color: #3b5998;">' . htmlspecialchars($row['type']) . '</span>';
                        echo '<h3 class="text-2xl font-bold text-brand-blue mb-2" style="color: #3b5998;">' . htmlspecialchars($row['name']) . '</h3>';
                        
                        echo '<p class="text-sm text-gray-500 mt-3">Wymagania: <span class="font-medium text-gray-700">' . htmlspecialchars($row['required_input']) . '</span></p>';
                        echo '<p class="text-xl font-bold text-gray-800 my-3">Czas trwania: ' . htmlspecialchars($row['duration_months']) . ' mies.</p>';
                        echo '</div>';
                        
                        // === POPRAWKA: Przycisk "Szczegóły" został odkomentowany ===
                        // Założyłem, że strona ze szczegółami to 'szczegoly_edukacja.php'
                        echo '<a href="szczegoly_edukacji.php?education_id=' . $row['education_id'] . '" class="inline-block mt-4 bg-brand-blue text-white text-center py-2 px-5 rounded-lg font-semibold hover:opacity-90 transition-opacity" style="background-color: #3b5998;">Szczegóły</a>';
                        
                        echo '</div>';
                    }
                } else {
                    echo "<p class='text-gray-600 col-span-full text-center text-lg'>Nie znaleziono żadnych wyników.</p>";
                }
                $stmt->close();
                $conn->close();
            ?>
        </div>
        
        <div class="mt-12 flex flex-wrap wrap justify-center items-center gap-2">
            <?php if ($total_pages > 1): ?>
                <?php $query_string = !empty($search_term) ? '&q=' . urlencode($search_term) : ''; ?>

                <?php if ($strona > 1): ?>
                    <a href="ofertyksztalcenia.php?strona=<?php echo $strona - 1; ?><?php echo $query_string; ?>" class="py-2 px-3 sm:px-4 text-sm sm:text-base bg-white text-gray-700 rounded-lg shadow-sm hover:bg-gray-100 transition-colors">Poprzednia</a>
                <?php else: ?>
                    <span class="py-2 px-3 sm:px-4 text-sm sm:text-base bg-gray-200 text-gray-400 rounded-lg shadow-sm cursor-not-allowed">Poprzednia</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="ofertyksztalcenia.php?strona=<?php echo $i; ?><?php echo $query_string; ?>" 
                       class="py-2 px-3 sm:px-4 text-sm sm:text-base rounded-lg shadow-sm <?php echo ($i == $strona) ? 'text-white font-bold' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>"
                       style="<?php if($i == $strona) echo 'background-color: #3b5998;'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($strona < $total_pages): ?>
                    <a href="ofertyksztalcenia.php?strona=<?php echo $strona + 1; ?><?php echo $query_string; ?>" class="py-2 px-3 sm:px-4 text-sm sm:text-base bg-white text-gray-700 rounded-lg shadow-sm hover:bg-gray-100 transition-colors">Następna</a>
                <?php else: ?>
                    <span class="py-2 px-3 sm:px-4 text-sm sm:text-base bg-gray-200 text-gray-400 rounded-lg shadow-sm cursor-not-allowed">Następna</span>
                <?php endif; ?>
            <?php endif; ?>
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