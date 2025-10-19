<?php
/*
 * Plik: dodajoferte.php
 * Wersja 3.1 - Zmiana nazwy tabeli skills -> skills_1
 * Przepisany z użyciem Tailwind CSS (styl z ofertypracy.php)
 */
    session_start(); // Dodane dla spójności z ofertypracy.php
    
    include 'db.php'; 

    $message = '';
    $error = '';

    // --- CAŁA ORYGINALNA LOGIKA POST ---
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        try {
            $form_type = $_POST['form_type'] ?? '';

            // --- 1. DODAWANIE OFERTY EDUKACJI ---
            if ($form_type == 'edukacja') {
                $sql = "INSERT INTO education (name, type, duration_months, required_input) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) throw new Exception($conn->error);
                $stmt->bind_param("ssis", $_POST['name'], $_POST['type'], $_POST['duration_months'], $_POST['required_input']);
                if ($stmt->execute()) {
                    $message = "Pomyślnie dodano nową ofertę edukacji!";
                } else {
                    throw new Exception($stmt->error);
                }
                $stmt->close();
            }

            // --- 2. DODAWANIE NOWEJ FIRMY ---
            if ($form_type == 'firma') {
                $sql = "INSERT INTO companies (name, industry, location, offers_internships) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) throw new Exception($conn->error);
                $stmt->bind_param("ssss", $_POST['name'], $_POST['industry'], $_POST['location'], $_POST['offers_internships']);
                if ($stmt->execute()) {
                    $message = "Pomyślnie dodano nową firmę!";
                } else {
                    throw new Exception($stmt->error);
                }
                $stmt->close();
            }

            // --- 3. DODAWANIE NOWEJ UMIEJĘTNOŚCI ---
            if ($form_type == 'umiejetnosc') {
                $sql = "INSERT INTO skills_1 (name, type) VALUES (?, ?)"; // Używa skills_1
                $stmt = $conn->prepare($sql);
                if ($stmt === false) throw new Exception($conn->error);
                $stmt->bind_param("ss", $_POST['name'], $_POST['type']);
                if ($stmt->execute()) {
                    $message = "Pomyślnie dodano nową umiejętność!";
                } else {
                    throw new Exception($stmt->error);
                }
                $stmt->close();
            }
            
            // --- 4. DODAWANIE OFERTY PRACY ---
            if ($form_type == 'praca') {
                
                $conn->begin_transaction();

                // Krok 1: Wstaw dane do 'jobs'
                $sql_job = "INSERT INTO jobs (name, level_required, avg_salary, industry) VALUES (?, ?, ?, ?)";
                $stmt_job = $conn->prepare($sql_job);
                if ($stmt_job === false) throw new Exception("Błąd (job): " . $conn->error);
                $stmt_job->bind_param("ssis", $_POST['job_name'], $_POST['level_required'], $_POST['avg_salary'], $_POST['industry']);
                $stmt_job->execute();
                $new_job_id = $conn->insert_id;
                $stmt_job->close();

                // Krok 2: Powiąż z firmą ('jobs_companies')
                $company_id = $_POST['company_id'];
                $sql_company = "INSERT INTO jobs_companies (job_id, company_id) VALUES (?, ?)";
                $stmt_company = $conn->prepare($sql_company);
                if ($stmt_company === false) throw new Exception("Błąd (company): " . $conn->error);
                $stmt_company->bind_param("ii", $new_job_id, $company_id);
                $stmt_company->execute();
                $stmt_company->close();
                
                // Krok 3: Powiąż z umiejętnościami ('jobs_skills')
                $skill_ids = $_POST['skill_ids'] ?? []; 
                $sql_skill = "INSERT INTO jobs_skills (job_id, skill_id) VALUES (?, ?)"; 
                $stmt_skill = $conn->prepare($sql_skill);
                if ($stmt_skill === false) throw new Exception("Błąd (skill): " . $conn->error);
                foreach ($skill_ids as $skill_id) {
                    $stmt_skill->bind_param("ii", $new_job_id, $skill_id);
                    $stmt_skill->execute();
                }
                $stmt_skill->close();

                // Krok 4: Powiąż z edukacją ('jobs_education')
                $education_ids = $_POST['education_ids'] ?? []; 
                $sql_edu = "INSERT INTO jobs_education (job_id, education_id) VALUES (?, ?)";
                $stmt_edu = $conn->prepare($sql_edu);
                if ($stmt_edu === false) throw new Exception("Błąd (education): " . $conn->error);
                foreach ($education_ids as $education_id) {
                    $stmt_edu->bind_param("ii", $new_job_id, $education_id);
                    $stmt_edu->execute();
                }
                $stmt_edu->close();

                $conn->commit();
                $message = "Pomyślnie dodano nową ofertę pracy i jej powiązania!";

            } // Koniec bloku 'praca'

        } catch (Exception $e) {
            if (isset($conn) && $conn->errno) {
                 $conn->rollback();
            }
            $error = "Wystąpił błąd: " . $e->getMessage();
        }
    } // Koniec bloku POST

    // --- POBIERANIE DANYCH DO FORMULARZA PRACY ---
    $companies_list = $conn->query("SELECT company_id, name FROM companies ORDER BY name");
    $skills_list = $conn->query("SELECT skill_id, name FROM skills_1 ORDER BY name"); // Używa skills_1
    $education_list = $conn->query("SELECT education_id, name FROM education ORDER BY name");

    // Co użytkownik chce wyświetlić
    $typ_formularza = $_GET['typ'] ?? 'praca';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Ofertę - Kierunek Płock</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Konfiguracja z ofertypracy.php */
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
            <a href="ofertyksztalcenia.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">OFERTY EDUKACJI</a>
            <a href="dodajoferte.php" class="text-lg font-semibold text-brand-blue border-b-2 border-brand-blue" style="color: #3b5998; border-color: #3b5998;">DODAJ OFERTĘ</a>
            <a href="formularz.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">FORMULARZ</a>
        </div>
    </div>

    <main class="container mx-auto mt-8 p-4">
        
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">System dodawania ofert</h1>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative max-w-3xl mx-auto mb-6" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative max-w-3xl mx-auto mb-6" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <div class="mb-8 flex flex-wrap justify-center gap-x-6 gap-y-3 border-b border-gray-300 pb-4">
            <a href="dodajoferte.php?typ=praca" class="text-lg <?php if($typ_formularza == 'praca') echo 'font-bold text-brand-blue'; else echo 'text-gray-600'; ?>" style="color: <?php if($typ_formularza == 'praca') echo '#3b5998'; ?>;">Dodaj Ofertę Pracy</a>
            <a href="dodajoferte.php?typ=edukacja" class="text-lg <?php if($typ_formularza == 'edukacja') echo 'font-bold text-brand-blue'; else echo 'text-gray-600'; ?>" style="color: <?php if($typ_formularza == 'edukacja') echo '#3b5998'; ?>;">Dodaj Ofertę Edukacji</a>
            <a href="dodajoferte.php?typ=firma" class="text-lg <?php if($typ_formularza == 'firma') echo 'font-bold text-brand-blue'; else echo 'text-gray-600'; ?>" style="color: <?php if($typ_formularza == 'firma') echo '#3b5998'; ?>;">Dodaj nową Firmę</a>
            <a href="dodajoferte.php?typ=umiejetnosc" class="text-lg <?php if($typ_formularza == 'umiejetnosc') echo 'font-bold text-brand-blue'; else echo 'text-gray-600'; ?>" style="color: <?php if($typ_formularza == 'umiejetnosc') echo '#3b5998'; ?>;">Dodaj nową Umiejętność</a>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-lg shadow-lg max-w-3xl mx-auto">

        <?php 
        // --- 1. WYŚWIETLANIE FORMULARZA PRACY (Styl Tailwind) ---
        if ($typ_formularza == 'praca'): 
        ?>
            <h2 class="text-2xl font-bold text-brand-blue mb-6" style="color: #3b5998;">Dodaj nową ofertę pracy</h2>
            <p class="text-gray-600 text-sm mb-4">Jeśli na listach brakuje firmy, umiejętności lub edukacji, dodaj je w odpowiedniej zakładce.</p>
            
            <form action="dodajoferte.php" method="POST">
                <input type="hidden" name="form_type" value="praca">

                <fieldset class="border border-gray-300 p-4 rounded-lg mb-6">
                    <legend class="text-lg font-semibold text-gray-700 px-2">Podstawowe informacje (Tabela: jobs)</legend>
                    
                    <div class="mb-4"><label for="job_name" class="block text-gray-700 text-sm font-bold mb-2">Nazwa stanowiska:</label><input type="text" id="job_name" name="job_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent" required></div>
                    
                    <div class="mb-4"><label for="level_required" class="block text-gray-700 text-sm font-bold mb-2">Wymagany poziom:</label>
                        <select id="level_required" name="level_required" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent">
                            <option value="Kurs">Kurs</option><option value="Liceum">Liceum</option><option value="Technikum">Technikum</option><option value="Studia">Studia</option><option value="Zasadnicza szkoła zawodowa">Zasadnicza szkoła zawodowa</option>
                        </select>
                    </div>
                    
                    <div class="mb-4"><label for="avg_salary" class="block text-gray-700 text-sm font-bold mb-2">Średnie zarobki (liczba):</label><input type="number" id="avg_salary" name="avg_salary" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent" required></div>
                    
                    <div class="mb-4"><label for="industry" class="block text-gray-700 text-sm font-bold mb-2">Branża:</label><input type="text" id="industry" name="industry" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent" required></div>
                </fieldset>

                <fieldset class="border border-gray-300 p-4 rounded-lg mb-6">
                    <legend class="text-lg font-semibold text-gray-700 px-2">Powiązania (Wybierz z list)</legend>
                    
                    <div class="mb-4"><label for="company_id" class="block text-gray-700 text-sm font-bold mb-2">Firma oferująca (Tabela: jobs_companies):</label>
                        <select id="company_id" name="company_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent" required>
                            <option value="">-- Wybierz firmę --</option>
                            <?php while ($row = $companies_list->fetch_assoc()): ?>
                                <option value="<?php echo $row['company_id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4"><label for="skill_ids" class="block text-gray-700 text-sm font-bold mb-2">Wymagane umiejętności (Tabela: jobs_skills):</label><small class="text-gray-600 text-xs italic">Przytrzymaj Ctrl, aby zaznaczyć wiele.</small>
                        <select id="skill_ids" name="skill_ids[]" multiple class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent h-40">
                            <?php while ($row = $skills_list->fetch_assoc()): ?>
                                <option value="<?php echo $row['skill_id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4"><label for="education_ids" class="block text-gray-700 text-sm font-bold mb-2">Wymagana edukacja (Tabela: jobs_education):</label><small class="text-gray-600 text-xs italic">Przytrzymaj Ctrl, aby zaznaczyć wiele.</small>
                        <select id="education_ids" name="education_ids[]" multiple class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent h-40">
                            <?php while ($row = $education_list->fetch_assoc()): ?>
                                <option value="<?php echo $row['education_id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </fieldset>
                
                <button type="submit" class="w-full bg-brand-blue hover:opacity-90 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-opacity text-lg" style="background-color: #3b5998;">Dodaj Ofertę Pracy</button>
            </form>

        <?php 
        // --- 2. WYŚWIETLANIE FORMULARZA EDUKACJI (Styl Tailwind) ---
        elseif ($typ_formularza == 'edukacja'): 
        ?>
            <h2 class="text-2xl font-bold text-brand-blue mb-6" style="color: #3b5998;">Dodaj nową ofertę edukacji</h2>
            <form action="dodajoferte.php" method="POST">
                <input type="hidden" name="form_type" value="edukacja">
                
                <div class="mb-4"><label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nazwa (np. kierunku, kursu):</label><input type="text" id="name" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent" required></div>
                
                <div class="mb-4"><label for="type" class="block text-gray-700 text-sm font-bold mb-2">Typ:</label>
                    <select id="type" name="type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent"><option value="Kurs">Kurs</option><option value="Studia">Studia</option><option value="Technikum">Technikum</option></select>
                </div>
                
                <div class="mb-4"><label for="duration_months" class="block text-gray-700 text-sm font-bold mb-2">Czas trwania (w miesiącach):</label><input type="number" id="duration_months" name="duration_months" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent" required></div>
                
                <div class="mb-4"><label for="required_input" class="block text-gray-700 text-sm font-bold mb-2">Wymagania wstępne:</label>
                    <select id="required_input" name="required_input" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent"><option value="Brak">Brak</option><option value="Podstawówka">Ukończona szkoła podstawowa</option><option value="Matura">Matura</option></select>
                </div>
                
                <button type="submit" class="w-full bg-brand-blue hover:opacity-90 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-opacity text-lg" style="background-color: #3b5998;">Dodaj Ofertę Edukacji</button>
            </form>

        <?php 
        // --- 3. WYŚWIETLANIE FORMULARZA FIRMY (Styl Tailwind) ---
        elseif ($typ_formularza == 'firma'): 
        ?>
            <h2 class="text-2xl font-bold text-brand-blue mb-6" style="color: #3b5998;">Dodaj nową firmę</h2>
            <form action="dodajoferte.php" method="POST">
                <input type="hidden" name="form_type" value="firma">
                
                <div class="mb-4"><label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nazwa firmy:</label><input type="text" id="name" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent" required></div>
                
                <div class="mb-4"><label for="industry" class="block text-gray-700 text-sm font-bold mb-2">Branża (Industry):</label><input type="text" id="industry" name="industry" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent" required></div>
                
                <div class="mb-4"><label for="location" class="block text-gray-700 text-sm font-bold mb-2">Lokalizacja (Location):</label><input type="text" id="location" name="location" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent" required></div>
                
                <div class="mb-4"><label for="offers_internships" class="block text-gray-700 text-sm font-bold mb-2">Oferuje staże?</label>
                    <select id="offers_internships" name="offers_internships" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent"><option value="Tak">Tak</option><option value="Nie">Nie</option></select>
                </div>
                
                <button type="submit" class="w-full bg-brand-blue hover:opacity-90 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-opacity text-lg" style="background-color: #3b5998;">Dodaj Firmę</button>
            </form>

        <?php 
        // --- 4. WYŚWIETLANIE FORMULARZA UMIEJĘTNOŚCI (Styl Tailwind) ---
        elseif ($typ_formularza == 'umiejetnosc'): 
        ?>
            <h2 class="text-2xl font-bold text-brand-blue mb-6" style="color: #3b5998;">Dodaj nową umiejętność</h2>
            <form action="dodajoferte.php" method="POST">
                <input type="hidden" name="form_type" value="umiejetnosc">
                
                <div class="mb-4"><label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nazwa umiejętności:</label><input type="text" id="name" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent" required></div>
                
                <div class="mb-4"><label for="type" class="block text-gray-700 text-sm font-bold mb-2">Typ umiejętności:</label>
                    <select id="type" name="type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent"><option value="Hard">Hard</option><option value="Soft">Soft</option></select>
                </div>
                
                <button type="submit" class="w-full bg-brand-blue hover:opacity-90 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-opacity text-lg" style="background-color: #3b5998;">Dodaj Umiejętność</button>
            </form>
            
        <?php endif; ?>

        </div> </main>

    <footer class="bg-brand-blue text-white p-4 mt-12" style="background-color: #3b5998;">
        <div class="container mx-auto text-center text-xs uppercase tracking-wider">
            <p>© TRINF HACKATHON 2025</p>
            <p>Dominik Dylewski, Bartek Zakrzewski, Konrad Zatorski</p>
        </div>
    </footer>

</body>
</html>
<?php
// Zamknij połączenie z bazą danych na samym końcu
$conn->close();
?>