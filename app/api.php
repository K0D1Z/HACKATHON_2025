<?php

// Nagłówki CORS i konfiguracja odpowiedzi
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Konfiguracja Połączenia z Bazą Danych (PDO)
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
    echo json_encode(['message' => 'Błąd połączenia z bazą danych.']);
    exit;
}

// Odczyt danych wejściowych
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['message' => 'Nie otrzymano danych z ankiety.']);
    exit;
}

$profileCode = $input['profileCode'] ?? 'R-I';
$mainProfile = explode('-', $profileCode)[0];
$preferences = $input['preferences'] ?? [];
$strengths = $input['strengths'] ?? [];
$educationAndGoals = $input['educationAndGoals'] ?? [];
$educationLevel = $educationAndGoals['q_education_level'] ?? 'brak';
$futurePath = $educationAndGoals['q_future_path'] ?? 'brak';
$preferredIndustry = $educationAndGoals['q_industry'] ?? 'Brak';

// Logika Biznesowa i mechanizm fallback
try {
    $wasFallbackNeeded = false;
    $matchedJobs = [];

    // KROK 1: Pierwsza próba - profil + branża
    if ($preferredIndustry !== 'Brak') {
        $stmt = $pdo->prepare("SELECT * FROM jobs WHERE riasec_type = ? AND industry = ? LIMIT 5");
        $stmt->execute([$mainProfile, $preferredIndustry]);
        $matchedJobs = $stmt->fetchAll();
    }

    // KROK 2: Druga próba (fallback) - tylko profil
    if (empty($matchedJobs)) {
        $wasFallbackNeeded = true;
        $stmt = $pdo->prepare("SELECT * FROM jobs WHERE riasec_type = ? LIMIT 5");
        $stmt->execute([$mainProfile]);
        $matchedJobs = $stmt->fetchAll();
    }
    
    if (empty($matchedJobs)) {
        http_response_code(404);
        echo json_encode(['message' => 'Nie znaleziono pasujących zawodów.']);
        exit;
    }

    // KROK 3: Zbierz szczegółowe informacje
    $detailedCareerPaths = [];
    foreach ($matchedJobs as $job) {
        // Ścieżki edukacji
        $eduStmt = $pdo->prepare("SELECT e.name FROM education e JOIN jobs_education je ON e.education_id = je.education_id WHERE je.job_id = ?");
        $eduStmt->execute([$job['job_id']]);
        $qualifications = $eduStmt->fetchAll(PDO::FETCH_COLUMN);

        // Firmy
        $companyStmt = $pdo->prepare("SELECT c.name FROM companies c JOIN jobs_companies jc ON c.company_id = jc.company_id WHERE jc.job_id = ?");
        $companyStmt->execute([$job['job_id']]);
        $employers = implode(', ', $companyStmt->fetchAll(PDO::FETCH_COLUMN));

        $detailedCareerPaths[] = [
            "title" => $job['name'],
            "description" => "Średnie zarobki: {$job['avg_salary']} PLN. Branża: {$job['industry']}.",
            "qualifications" => !empty($qualifications) ? $qualifications : ["Brak danych"],
            "employers" => !empty($employers) ? $employers : "Brak danych o firmach w tej branży"
        ];
    }

    // KROK 4: Komunikacja z Google Gemini API
    $apiKey = "AIzaSyAN8l0SjS659W0-3uV4EVPNmEXnYuNhRdY";

    if (empty($apiKey) || $apiKey === 'WSTAW_SWOJ_KLUCZ') { // Dodano sprawdzenie dla potencjalnego placeholder'a
        // Tryb demonstracyjny
        echo json_encode([
            "profileTitle" => "Analiza Profilu: {$profileCode} [Backend DEMO]",
            "profileDescription" => "Raport został wygenerowany przez serwer PHP. Wstaw klucz API, aby uzyskać spersonalizowaną treść od AI.",
            "pathsIntro" => "Oto propozycje ścieżek kariery, które pasują do Twojego profilu.",
            "careerPaths" => array_slice($detailedCareerPaths, 0, 4),
            "nextSteps" => ["Sprawdź szczegóły w Bazie Zawodów.", "Odwiedź dni otwarte na uczelniach.", "Skontaktuj się z doradcą zawodowym."]
        ]);
        exit;
    }

    // Budowanie promptu do AI
    $systemPrompt = "Jesteś 'Kompasem Kariery Płock', eksperckim doradcą zawodowym. Twoim zadaniem jest wygenerowanie spersonalizowanego raportu w formie ciągłego, naturalnego tekstu. Nie używaj form, które mogą sugerować płeć odbiorcy. Odpowiedź musi być wyłącznie obiektem JSON. Kategorycznie zabronione jest używanie jakichkolwiek list, punktorów czy emoji. Całość musi być spójnym tekstem w każdej sekcji.\n\nStruktura JSON musi być następująca:\n{\n  \"profileTitle\": \"string\",\n  \"profileDescription\": \"string\",\n  \"pathsIntro\": \"string\",\n  \"careerPaths\": [\n    {\n      \"title\": \"string\",\n      \"description\": \"string\",\n      \"qualifications\": [\"string\"],\n      \"employers\": \"string\"\n    }\n  ],\n  \"nextSteps\": [\"string\"]\n}";

    $userPrompt = "Proszę o wygenerowanie raportu kariery. Nie używaj form sugerujących płeć odbiorcy (np. Pan/Pani).\n\nDane wejściowe:\n- Dominujący profil RIASEC: {$profileCode}\n- Wykształcenie: {$educationLevel}\n- Cel edukacyjny: {$futurePath}\n- Preferowana branża: {$preferredIndustry}\n- Mocne strony: " . json_encode($strengths) . "\n- Preferencje środowiska: " . json_encode($preferences) . "\n\nDopasowane ścieżki kariery (wybrane z bazy danych):\n" . json_encode($detailedCareerPaths, JSON_UNESCAPED_UNICODE);
    
    if ($wasFallbackNeeded && $preferredIndustry !== 'Brak') {
        $userPrompt .= "\n\nWAŻNA INFORMACJA DLA AI: Nie udało się znaleźć zawodów, które jednocześnie pasują do profilu użytkownika ('{$mainProfile}') i wybranej branży ('{$preferredIndustry}'). Powyższe ścieżki bazują na profilu RIASEC. W sekcji 'pathsIntro' delikatnie wyjaśnij, że propozycje są szersze niż preferencje branżowe, ale wciąż pasują do osobowości.";
    }

    $userPrompt .= "\n\nTwoim zadaniem jest wybranie 4 najbardziej pasujących ścieżek z powyższej listy, stworzenie dla nich motywujących opisów i wygenerowanie sekcji 'Następne kroki'. Pamiętaj o kategorycznym zakazie używania list i punktorów. Zwróć wynik w wymaganym formacie JSON.";

    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key={$apiKey}";
    $payload = [
        'systemInstruction' => ['parts' => [['text' => $systemPrompt]]],
        'contents' => [['parts' => [['text' => $userPrompt]]]],
        'generationConfig' => ['responseMimeType' => "application/json"]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception("Błąd API Gemini: " . $response);
    }

    $result = json_decode($response, true);
    $reportJsonText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
    
    if (!$reportJsonText) {
        throw new Exception("Otrzymano pustą odpowiedź z API.");
    }

    header('Content-Type: application/json');
    echo $reportJsonText;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Wystąpił błąd serwera podczas generowania raportu.']);
}

?>
