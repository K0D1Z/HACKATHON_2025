<?php
    session_start();

    // --- CAŁA LOGIKA PHP NA GÓRZE ---
    $education = null;
    $jobs = [];
    $error_message = '';

    // Sprawdzamy, czy ID oferty zostało przekazane
    if (!isset($_GET['education_id']) || !is_numeric($_GET['education_id'])) {
        $error_message = "Nie wybrano oferty lub podano nieprawidłowy identyfikator.";
    } else {
        $education_id = (int)$_GET['education_id'];

        $conn = new mysqli('127.0.0.1', 'root', '', 'hackathon');
        if ($conn->connect_error) {
            $error_message = "Błąd połączenia z bazą danych: " . $conn->connect_error;
        } else {
            $conn->set_charset("utf8");

            // --- ZAPYTANIE 1: Pobranie głównych danych (BEZPIECZNE) ---
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

                // --- ZAPYTANIE 2: Pobranie stanowisk (BEZPIECZNE) ---
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
                        $jobs[] = $job; // Dodajemy do tablicy
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
    <title><?php echo $education ? htmlspecialchars($education['name']) : 'Błąd'; ?> - Kariera w Płocku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'brand-blue': '#304A8B' },
                    fontFamily: { 'sans': ['Inter', 'system-ui', 'sans-serif'] }
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

    <div class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex flex-wrap justify-center gap-x-6 gap-y-2 md:gap-x-10">
            <a href="ofertypracy.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">OFERTY PRACY</a>
            <a href="ofertyksztalcenia.php" class="text-lg font-semibold text-brand-blue border-b-2 border-brand-blue" style="color: #3b5998; border-color: #3b5998;">OFERTY EDUKACJI</a>
            <a href="dodajoferte.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">DODAJ OFERTĘ</a>
            <a href="formularz.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">FORMULARZ</a>
        </div>
    </div>

    <main class="flex-grow container mx-auto p-4 mt-8">
        
        <a href="ofertyksztalcenia.php" class="inline-block mb-4 text-brand-blue hover:underline font-medium" style="color: #3b5998;">
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

    <footer class="bg-brand-blue text-white p-4 mt-12" style="background-color: #3b5998;">
        <div class="container mx-auto text-center text-xs uppercase tracking-wider">
            <p>© TRINF HACKATHON 2025</p>
            <p>Dominik Dylewski, Bartek Zakrzewski, Konrad Zatorski</p>
        </div>
    </footer>
</body>
</html>