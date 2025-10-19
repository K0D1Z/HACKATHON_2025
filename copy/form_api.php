<?php
// --- Nagłówki CORS ---
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- Konfiguracja Połączenia z Bazą Danych ---
$db_host = 'localhost';
$db_name = 'csv_db 7';
$db_user = 'root';
$db_pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Błąd połączenia z bazą danych: ' . $e->getMessage()]);
    exit;
}

// --- Logika Biznesowa ---
$input = json_decode(file_get_contents('php://input'), true);

$profile = $input['profile'] ?? 'R-I';
$profileParts = explode('-', $profile);
$mainProfile = $profileParts[0];
$secondaryProfile = $profileParts[1] ?? $mainProfile; // Użyj drugiego typu lub powtórz pierwszy

$industry = $input['industry'] ?? 'Brak';
$goal = $input['goal'] ?? 'brak';

try {
    // --- Krok 1: Znajdź pasujące zawody z logiką fallback ---
    $matchedJobs = [];
    // Wyszukaj zawody pasujące do GŁÓWNEGO lub DRUGIEGO typu RIASEC
    $sqlJobsBase = "SELECT job_id, name, avg_salary FROM jobs WHERE riasec_type IN (?, ?)";
    $paramsJobsBase = [$mainProfile, $secondaryProfile];

    // Próba 1: Znajdź zawody pasujące do profilu ORAZ wybranej branży
    if ($industry !== 'Brak') {
        $sqlJobsAttempt1 = $sqlJobsBase . " AND industry = ?";
        $paramsJobsAttempt1 = array_merge($paramsJobsBase, [$industry]);
        
        $jobStmt = $pdo->prepare($sqlJobsAttempt1 . " LIMIT 20");
        $jobStmt->execute($paramsJobsAttempt1);
        $matchedJobs = $jobStmt->fetchAll();
    }

    // Fallback: Jeśli nic nie znaleziono, szukaj zawodów pasujących TYLKO do profilu
    if (empty($matchedJobs)) {
        $jobStmt = $pdo->prepare($sqlJobsBase . " LIMIT 20");
        $jobStmt->execute($paramsJobsBase);
        $matchedJobs = $jobStmt->fetchAll();
    }

    $jobIds = array_column($matchedJobs, 'job_id');
    
    $results = ['jobs' => [], 'education' => []];

    if (!empty($jobIds)) {
        $placeholders = implode(',', array_fill(0, count($jobIds), '?'));
        
        // --- Krok 2: Znajdź firmy dla znalezionych zawodów ---
        $sqlCompanies = "
            SELECT j.name as job_name, j.avg_salary, c.name as company_name, c.location, c.offers_internships
            FROM companies c
            JOIN jobs_companies jc ON c.company_id = jc.company_id
            JOIN jobs j ON jc.job_id = j.job_id
            WHERE jc.job_id IN ($placeholders)
            ORDER BY c.offers_internships DESC, j.avg_salary DESC
            LIMIT 24
        "; // Zwiększony limit
        
        $companyStmt = $pdo->prepare($sqlCompanies);
        $companyStmt->execute($jobIds);
        $results['jobs'] = $companyStmt->fetchAll();

        // --- Krok 3: Znajdź ścieżki edukacji z logiką fallback ---
        $sqlEducationBase = "
            SELECT j.name as job_name, e.name as education_name, e.type, e.duration_months
            FROM education e
            JOIN jobs_education je ON e.education_id = je.education_id
            JOIN jobs j ON je.job_id = j.job_id
            WHERE je.job_id IN ($placeholders)
        ";
        $paramsEducation = $jobIds;
        $educationResults = [];

        // Próba 1: Znajdź edukację pasującą do celu użytkownika (studia, kurs etc.)
        if ($goal !== 'brak') {
             $sqlEducationAttempt1 = $sqlEducationBase . " AND e.type = ?";
             $goalMapping = ['kurs' => 'Kurs', 'studia' => 'Studia', 'technikum' => 'Technikum'];
            if (isset($goalMapping[$goal])) {
                $paramsEducationAttempt1 = array_merge($paramsEducation, [$goalMapping[$goal]]);
                $eduStmt = $pdo->prepare($sqlEducationAttempt1 . " ORDER BY e.duration_months ASC LIMIT 24");
                $eduStmt->execute($paramsEducationAttempt1);
                $educationResults = $eduStmt->fetchAll();
            }
        }
        
        // Fallback: Jeśli nic nie znaleziono, pokaż wszystkie typy edukacji
        if (empty($educationResults)) {
             $eduStmt = $pdo->prepare($sqlEducationBase . " ORDER BY e.duration_months ASC LIMIT 24");
             $eduStmt->execute($paramsEducation);
             $educationResults = $eduStmt->fetchAll();
        }

        $results['education'] = $educationResults;
    }

    echo json_encode($results);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Błąd serwera: ' . $e->getMessage()]);
}
?>
