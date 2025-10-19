<?php
/*
 * Plik: db.php
 * Konfiguracja połączenia z bazą danych
 */

$servername = "localhost";  // Zazwyczaj 'localhost'
$username = "root";         // Domyślny użytkownik XAMPP
$password = "";             // Domyślne hasło XAMPP (puste)
$dbname = "hackathon";  // Nazwa bazy, którą utworzyłeś w phpMyAdmin

// Utwórz połączenie
$conn = new mysqli($servername, $username, $password, $dbname);

// Ustaw kodowanie na UTF-8
$conn->set_charset("utf8mb4");

// Sprawdź połączenie
if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}
?>