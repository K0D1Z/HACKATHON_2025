<?php

// Konfiguracja obsługi błędów
ini_set('display_errors', 0);
error_reporting(0);

// Nagłówki CORS i typ odpowiedzi
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

try {
    // Połączenie z bazą danych
    $conn = new mysqli('127.0.0.1', 'root', '', 'hackathon');
    
    if ($conn->connect_error) {
        throw new Exception("Błąd połączenia z bazą danych.");
    }
    $conn->set_charset("utf8");

    // Odczyt danych wejściowych
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Błędne dane wejściowe JSON.");
    }

    $industry = $input['industry'] ?? 'Brak';
    $goal = $input['goal'] ?? 'brak';

    $jobs = [];
    $education = [];

    // --- Zapytanie o oferty pracy ---
    if ($industry !== 'Brak') {
        $sql_jobs = "
            SELECT j.job_id, j.name AS job_name, c.name AS company_name, j.avg_salary, c.location, c.offers_internships
            FROM jobs j
            JOIN jobs_companies jc ON j.job_id = jc.job_id
            JOIN companies c ON jc.company_id = c.company_id
            WHERE j.industry = ? 
            LIMIT 9
        ";
        
        $stmt_jobs = $conn->prepare($sql_jobs);
        if ($stmt_jobs === false) {
             throw new Exception("Błąd przygotowania zapytania o pracę.");
        }
        
        $stmt_jobs->bind_param('s', $industry);
        if (!$stmt_jobs->execute()) {
             throw new Exception("Błąd wykonania zapytania o pracę.");
        }
        
        $result_jobs = $stmt_jobs->get_result();
        while ($row = $result_jobs->fetch_assoc()) {
            $jobs[] = $row;
        }
        $stmt_jobs->close();
    }

    // --- Zapytanie o ścieżki edukacji ---
    if ($goal !== 'brak') {
        $sql_edu = "
            SELECT e.education_id, e.name AS education_name, e.type, e.duration_months
            FROM education e
            WHERE 1=1
        ";
        
        $params_edu = [];
        $types_edu = '';
        $education_type_map = ['kurs' => 'Kurs', 'studia' => 'Studia', 'technikum' => 'Technikum'];

        if (isset($education_type_map[$goal])) {
            $sql_edu .= " AND e.type = ?";
            $params_edu[] = $education_type_map[$goal];
            $types_edu .= 's';
        }

        $sql_edu .= " LIMIT 9";

        $stmt_edu = $conn->prepare($sql_edu);
        if ($stmt_edu === false) {
            throw new Exception("Błąd przygotowania zapytania o edukację.");
        }

        if (!empty($params_edu)) {
            $stmt_edu->bind_param($types_edu, ...$params_edu);
        }
        
        if (!$stmt_edu->execute()) {
            throw new Exception("Błąd wykonania zapytania o edukację.");
        }

        $result_edu = $stmt_edu->get_result();
        while ($row = $result_edu->fetch_assoc()) {
            $education[] = $row;
        }
        $stmt_edu->close();
    }

    $conn->close();
    
    echo json_encode(['jobs' => $jobs, 'education' => $education]);

} catch (Exception $e) {
    http_response_code(500);
    // Uproszczony komunikat błędu serwera dla bezpieczeństwa
    echo json_encode(['error' => true, 'message' => 'Wystąpił błąd serwera podczas przetwarzania żądania.']);
}
?>
