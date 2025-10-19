<?php
session_start();
require 'db.php'; 

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $form_type = $_POST['form_type'] ?? '';

        switch ($form_type) {
            case 'edukacja':
                $sql = "INSERT INTO education (name, type, duration_months, required_input) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssis", $_POST['name'], $_POST['type'], $_POST['duration_months'], $_POST['required_input']);
                $stmt->execute();
                $message = "Pomyślnie dodano nową ofertę edukacji!";
                break;

            case 'firma':
                $sql = "INSERT INTO companies (name, industry, location, offers_internships) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $_POST['name'], $_POST['industry'], $_POST['location'], $_POST['offers_internships']);
                $stmt->execute();
                $message = "Pomyślnie dodano nową firmę!";
                break;

            case 'umiejetnosc':
                $sql = "INSERT INTO skills_1 (name, type) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $_POST['name'], $_POST['type']);
                $stmt->execute();
                $message = "Pomyślnie dodano nową umiejętność!";
                break;

            case 'praca':
                $conn->begin_transaction();
                
                $sql_job = "INSERT INTO jobs (name, level_required, avg_salary, industry) VALUES (?, ?, ?, ?)";
                $stmt_job = $conn->prepare($sql_job);
                $stmt_job->bind_param("ssis", $_POST['job_name'], $_POST['level_required'], $_POST['avg_salary'], $_POST['industry']);
                $stmt_job->execute();
                $new_job_id = $conn->insert_id;
                $stmt_job->close();

                $sql_company = "INSERT INTO jobs_companies (job_id, company_id) VALUES (?, ?)";
                $stmt_company = $conn->prepare($sql_company);
                $stmt_company->bind_param("ii", $new_job_id, $_POST['company_id']);
                $stmt_company->execute();
                $stmt_company->close();
                
                if (!empty($_POST['skill_ids'])) {
                    $sql_skill = "INSERT INTO jobs_skills (job_id, skill_id) VALUES (?, ?)"; 
                    $stmt_skill = $conn->prepare($sql_skill);
                    foreach ($_POST['skill_ids'] as $skill_id) {
                        $stmt_skill->bind_param("ii", $new_job_id, $skill_id);
                        $stmt_skill->execute();
                    }
                    $stmt_skill->close();
                }

                if (!empty($_POST['education_ids'])) {
                    $sql_edu = "INSERT INTO jobs_education (job_id, education_id) VALUES (?, ?)";
                    $stmt_edu = $conn->prepare($sql_edu);
                    foreach ($_POST['education_ids'] as $education_id) {
                        $stmt_edu->bind_param("ii", $new_job_id, $education_id);
                        $stmt_edu->execute();
                    }
                    $stmt_edu->close();
                }

                $conn->commit();
                $message = "Pomyślnie dodano nową ofertę pracy i jej powiązania!";
                break;
        }

        if (isset($stmt)) {
            $stmt->close();
        }

    } catch (Exception $e) {
        if (isset($conn) && $conn->in_transaction) {
             $conn->rollback();
        }
        $error = "Wystąpił błąd podczas przetwarzania formularza.";
    }
}

$typ_formularza = $_GET['typ'] ?? 'praca';

$companies_list = null;
$skills_list = null;
$education_list = null;
$industries_list = null; 

if ($typ_formularza == 'praca') {
    $companies_list = $conn->query("SELECT company_id, name FROM companies ORDER BY name");
    $skills_list = $conn->query("SELECT skill_id, name FROM skills_1 ORDER BY name");
    $education_list = $conn->query("SELECT education_id, name FROM education ORDER BY name");
    $industries_list = $conn->query("SELECT DISTINCT industry FROM jobs WHERE industry IS NOT NULL AND industry != '' ORDER BY industry");
}

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KIERUNEKPLOCK.PL - Dodaj Ofertę</title>
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
        
        .checkbox-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #d1d5db; 
            border-radius: 0.375rem;
            background-color: #fff;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem; 
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
            border-bottom: 1px solid #f3f4f6; 
        }

        .checkbox-item:last-child {
            border-bottom: none;
        }

        .checkbox-item input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .checkbox-item span {
            flex-grow: 1;
            cursor: pointer;
            font-size: 0.875rem;
            color: #374151;
            transition: color 0.2s;
        }
        
        .checkbox-item[data-checked="true"] {
            background-color: #3b5998;
        }

        .checkbox-item[data-checked="true"] span {
            color: white; 
            font-weight: 600;
        }
        
        .checkbox-item:hover {
            background-color: #f3f4f6; 
        }
        
        .checkbox-item[data-checked="true"]:hover {
            background-color: #3b5998; 
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
        <div class="hidden lg:flex items-center space-x-4 md:space-x-6 text-sm">
            <?php if (isset($_SESSION['nazwa_uzytkownika'])): ?>
                <span class="font-medium">Witaj, <?php echo htmlspecialchars($_SESSION['nazwa_uzytkownika']); ?></span>
                <a href="wyloguj.php" class="font-semibold hover:underline uppercase">Wyloguj</a>
            <?php else: ?>
                <a href="login.php" class="hover:underline">Gość / Zaloguj się</a>
                <a href="rejestracja.php" class="font-semibold hover:underline uppercase">Rejestracja</a>
            <?php endif; ?>
        </div>
        <div class="lg:hidden">
            <button id="mobile-menu-open-btn" class="text-white p-2 rounded-md hover:bg-white hover:bg-opacity-20">
                <span class="sr-only">Otwórz menu</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
            </button>
        </div>
    </nav>
    <div class="hidden lg:block bg-white"> 
        <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap justify-center gap-x-6 gap-y-2 md:gap-x-12 md:gap-y-4">
            <a href="index.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">STRONA GŁÓWNA</a>
            <a href="form.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">ANKIETA</a>
            <a href="ofertypracy.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">OFERTY PRACY</a>
            <a href="ofertyksztalcenia.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">OFERTY EDUKACJI</a>
            <a href="slownikzawodow.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue hover:border-b-2 hover:border-brand-blue">SŁOWNIK ZAWODÓW</a>
            <a href="dodajoferte.php" class="text-base font-semibold text-brand-blue border-b-2 border-brand-blue">DODAJ OFERTĘ</a>
        </div>
    </div>
    <div id="mobile-menu" class="lg:hidden fixed inset-0 bg-white z-[100] transform translate-x-full transition-transform duration-300">
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
                <a href="ofertypracy.php" class="text-lg font-semibold text-gray-600">OFERTY PRACY</a>
                <a href="ofertyksztalcenia.php" class="text-lg font-semibold text-gray-600">OFERTY EDUKACJI</a>
                <a href="slownikzawodow.php" class="text-lg font-semibold text-gray-600">SŁOWNIK ZAWODÓW</a>
                <a href="dodajoferte.php" class="text-lg font-semibold text-brand-blue border-b-2 border-brand-blue pb-1">DODAJ OFERTĘ</a>
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

    <main class="max-w-6xl mx-auto p-6 mt-10 w-full animate-fadeIn">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-gray-900">Dodaj Ofertę</h1>
            <p class="text-lg text-gray-600 mt-2">Zarządzaj bazą danych ofert pracy, edukacji, firm i umiejętności.</p>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 max-w-3xl mx-auto my-6 flex items-center" role="alert">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <p class="font-bold"><?php echo $message; ?></p>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 max-w-3xl mx-auto my-6 flex items-center" role="alert">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p><span class="font-bold">Błąd!</span> <?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <!-- Zmiana klas dla lepszego wyglądu mobilnego -->
        <div class="my-8 bg-white rounded-lg shadow-md p-2 mx-auto flex flex-col sm:flex-row flex-wrap justify-center gap-2 max-w-3xl">
            <a href="?typ=praca" class="text-sm font-semibold rounded-md py-2 px-4 text-center transition-colors w-full sm:w-auto flex-1 <?php echo $typ_formularza == 'praca' ? 'bg-brand-blue text-white shadow' : 'text-gray-600 hover:bg-gray-200'; ?>">Dodaj Ofertę Pracy</a>
            <a href="?typ=edukacja" class="text-sm font-semibold rounded-md py-2 px-4 text-center transition-colors w-full sm:w-auto flex-1 <?php echo $typ_formularza == 'edukacja' ? 'bg-brand-blue text-white shadow' : 'text-gray-600 hover:bg-gray-200'; ?>">Dodaj Ofertę Edukacji</a>
            <a href="?typ=firma" class="text-sm font-semibold rounded-md py-2 px-4 text-center transition-colors w-full sm:w-auto flex-1 <?php echo $typ_formularza == 'firma' ? 'bg-brand-blue text-white shadow' : 'text-gray-600 hover:bg-gray-200'; ?>">Dodaj Firmę</a>
            <a href="?typ=umiejetnosc" class="text-sm font-semibold rounded-md py-2 px-4 text-center transition-colors w-full sm:w-auto flex-1 <?php echo $typ_formularza == 'umiejetnosc' ? 'bg-brand-blue text-white shadow' : 'text-gray-600 hover:bg-gray-200'; ?>">Dodaj Umiejętność</a>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-lg shadow-xl max-w-3xl mx-auto">
        <?php switch ($typ_formularza): case 'praca': ?>
            <h2 class="text-2xl font-bold text-brand-blue mb-6">Nowa Oferta Pracy</h2>
            <p class="text-gray-600 text-sm mb-4">Uzupełnij poniższe pola, aby dodać nową ofertę pracy do bazy danych. Jeśli na listach brakuje potrzebnych pozycji, dodaj je w odpowiednich zakładkach.</p>
            <form action="dodajoferte.php?typ=praca" method="POST" class="space-y-6">
                <input type="hidden" name="form_type" value="praca">
                <fieldset class="border p-4 rounded-lg">
                    <legend class="text-lg font-semibold px-2">Informacje o Stanowisku</legend>
                    <div class="space-y-4 p-2">
                        <div><label for="job_name" class="block font-bold mb-1">Nazwa stanowiska:</label><input type="text" id="job_name" name="job_name" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue" required></div>
                        <div><label for="level_required" class="block font-bold mb-1">Wymagany poziom:</label>
                            <select id="level_required" name="level_required" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue">
                                <option>Kurs</option><option>Liceum</option><option>Technikum</option><option>Studia</option><option>Zasadnicza szkoła zawodowa</option>
                            </select>
                        </div>
                        <div><label for="avg_salary" class="block font-bold mb-1">Średnie zarobki (PLN):</label><input type="number" id="avg_salary" name="avg_salary" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue" required></div>
                        
                        <div><label for="industry" class="block font-bold mb-1">Branża:</label>
                            <select id="industry" name="industry" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue" required>
                                <option value="">-- Wybierz branżę z listy --</option>
                                <?php if ($industries_list): ?>
                                    <?php while ($row = $industries_list->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($row['industry']); ?>"><?php echo htmlspecialchars($row['industry']); ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                    </div>
                </fieldset>
                <fieldset class="border p-4 rounded-lg">
                    <legend class="text-lg font-semibold px-2">Powiązania</legend>
                    <div class="space-y-4 p-2">
                        <div><label for="company_id" class="block font-bold mb-1">Nazwa firmy:</label>
                            <select id="company_id" name="company_id" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue" required>
                                <option value="">-- Wybierz firmę z listy --</option>
                                <?php $companies_list->data_seek(0); while ($row = $companies_list->fetch_assoc()): ?><option value="<?php echo $row['company_id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option><?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block font-bold mb-1">Wymagane umiejętności:</label>
                            <input type="text" id="search_skills" placeholder="Wyszukaj..." 
                                   onkeyup="filterCheckboxes(this.value, 'skills_list')" 
                                   class="shadow-sm border rounded w-full py-2 px-3 mb-2 focus:outline-none focus:ring-2 focus:ring-brand-blue">
                            <div id="skills_list" class="checkbox-list">
                                <?php $skills_list->data_seek(0); while ($row = $skills_list->fetch_assoc()): ?>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="skill_ids[]" value="<?php echo $row['skill_id']; ?>" onchange="updateCustomStyle(this)">
                                        <span><?php echo htmlspecialchars($row['name']); ?></span>
                                    </label>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold mb-1">Preferowane wykształcenie:</label>
                            <input type="text" id="search_education" placeholder="Wyszukaj..." 
                                   onkeyup="filterCheckboxes(this.value, 'education_list')" 
                                   class="shadow-sm border rounded w-full py-2 px-3 mb-2 focus:outline-none focus:ring-2 focus:ring-brand-blue">
                            <div id="education_list" class="checkbox-list">
                                <?php $education_list->data_seek(0); while ($row = $education_list->fetch_assoc()): ?>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="education_ids[]" value="<?php echo $row['education_id']; ?>" onchange="updateCustomStyle(this)">
                                        <span><?php echo htmlspecialchars($row['name']); ?></span>
                                    </label>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <button type="submit" class="w-full bg-brand-blue hover:opacity-90 text-white font-bold py-3 px-4 rounded-lg text-lg transition-opacity">Dodaj Ofertę Pracy</button>
            </form>
            <?php break; ?>

        <?php case 'edukacja': ?>
            <h2 class="text-2xl font-bold text-brand-blue mb-6">Nowa Oferta Edukacji</h2>
            <form action="dodajoferte.php?typ=edukacja" method="POST" class="space-y-4">
                <input type="hidden" name="form_type" value="edukacja">
                <div><label for="name" class="block font-bold mb-1">Nazwa (np. kierunku, kursu):</label><input type="text" id="name" name="name" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue" required></div>
                <div><label for="type" class="block font-bold mb-1">Typ:</label>
                    <select id="type" name="type" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue"><option>Kurs</option><option>Studia</option><option>Technikum</option></select>
                </div>
                <div><label for="duration_months" class="block font-bold mb-1">Czas trwania (w miesiącach):</label><input type="number" id="duration_months" name="duration_months" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue" required></div>
                <div><label for="required_input" class="block font-bold mb-1">Wymagania wstępne:</label>
                    <select id="required_input" name="required_input" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue"><option>Brak</option><option>Podstawówka</option><option>Matura</option></select>
                </div>
                <button type="submit" class="w-full bg-brand-blue hover:opacity-90 text-white font-bold py-3 px-4 rounded-lg text-lg transition-opacity">Dodaj Ofertę Edukacji</button>
            </form>
            <?php break; ?>

        <?php case 'firma': ?>
            <h2 class="text-2xl font-bold text-brand-blue mb-6">Nowa Firma</h2>
            <form action="dodajoferte.php?typ=firma" method="POST" class="space-y-4">
                <input type="hidden" name="form_type" value="firma">
                <div><label for="name" class="block font-bold mb-1">Nazwa firmy:</label><input type="text" id="name" name="name" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue" required></div>
                <div><label for="industry" class="block font-bold mb-1">Branża:</label><input type="text" id="industry" name="industry" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue" required></div>
                <div><label for="location" class="block font-bold mb-1">Lokalizacja:</label><input type="text" id="location" name="location" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue" required></div>
                <div><label for="offers_internships" class="block font-bold mb-1">Oferuje staże?</label>
                    <select id="offers_internships" name="offers_internships" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue"><option>Tak</option><option>Nie</option></select>
                </div>
                <button type="submit" class="w-full bg-brand-blue hover:opacity-90 text-white font-bold py-3 px-4 rounded-lg text-lg transition-opacity">Dodaj Firmę</button>
            </form>
            <?php break; ?>

        <?php case 'umiejetnosc': ?>
            <h2 class="text-2xl font-bold text-brand-blue mb-6">Nowa Umiejętność</h2>
            <form action="dodajoferte.php?typ=umiejetnosc" method="POST" class="space-y-4">
                <input type="hidden" name="form_type" value="umiejetnosc">
                <div><label for="name" class="block font-bold mb-1">Nazwa umiejętności:</label><input type="text" id="name" name="name" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue" required></div>
                <div><label for="type" class="block font-bold mb-1">Typ umiejętności:</label>
                    <select id="type" name="type" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-blue"><option>Hard</option><option>Soft</option></select>
                </div>
                <button type="submit" class="w-full bg-brand-blue hover:opacity-90 text-white font-bold py-3 px-4 rounded-lg text-lg transition-opacity">Dodaj Umiejętność</button>
            </form>
        <?php endswitch; ?>
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
        function updateCustomStyle(checkbox) {
            const label = checkbox.parentElement;
            label.setAttribute('data-checked', checkbox.checked ? 'true' : 'false');
        }

        function filterCheckboxes(searchTerm, listId) {
            const list = document.getElementById(listId);
            const items = list.getElementsByTagName('label'); 
            const term = searchTerm.toLowerCase();

            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                const span = item.querySelector('span');
                const labelText = span ? (span.textContent || span.innerText) : '';
                
                if (labelText.toLowerCase().indexOf(term) > -1) {
                    item.style.display = "flex"; 
                } else {
                    item.style.display = "none"; 
                }
            }
        }
        
        window.addEventListener('load', function() {
            const loadingOverlay = document.getElementById('loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.classList.add('hidden-overlay');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const loadingOverlay = document.getElementById('loading-overlay');
            
            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    
                    if (!href || href.startsWith('http') || href.startsWith('#') || this.target === '_blank' || href.match(/\.(pdf|zip|jpg|png)$/i)) {
                        return;
                    }
                    
                    if (href.split('?')[0] === window.location.pathname.split('/').pop() && !href.includes('?')) {
                        return;
                    }

                    e.preventDefault(); 
                    
                    if (loadingOverlay) {
                        loadingOverlay.classList.remove('hidden-overlay');
                    }
                    
                    setTimeout(() => {
                        window.location.href = href;
                    }, 100); 
                });
            });

            // Inicjalizacja: Ustawienie atrybutu 'data-checked' dla zachowania stanu po przeładowaniu strony
            document.querySelectorAll('.checkbox-item input[type="checkbox"]').forEach(checkbox => {
                updateCustomStyle(checkbox);
                checkbox.addEventListener('change', function() {
                    updateCustomStyle(this);
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
<?php
$conn->close();
?>
