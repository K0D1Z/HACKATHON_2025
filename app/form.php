<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KIERUNEKPLOCK.PL - Ankieta</title>
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
            <a href="form.php" class="text-base font-semibold text-brand-blue border-b-2 border-brand-blue">ANKIETA</a>
            <a href="ofertypracy.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue transition-colors hover:border-b-2 hover:border-brand-blue">OFERTY PRACY</a>
            <a href="ofertyksztalcenia.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue transition-colors hover:border-b-2 hover:border-brand-blue">OFERTY EDUKACJI</a>
            <a href="slownikzawodow.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue transition-colors hover:border-b-2 hover:border-brand-blue">SŁOWNIK ZAWODÓW</a>
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
    
        <div id="progress-nav-bar" class="hidden border-t border-gray-200 bg-gray-50 py-4">
            <div class="max-w-4xl mx-auto px-6">
                <div class="flex justify-between items-center relative">
                    
                    <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-300 transform -translate-y-1/2 mx-10 sm:mx-16">
                        <div id="progress-bar" class="bg-plock-blue h-1 transition-all duration-500 ease-out" style="width: 0%"></div>
                    </div>

                    <div class="flex justify-between w-full relative z-10">
                        
                        <button type="button" data-step="0" class="step-nav-dot flex flex-col items-center">
                            <div id="step-dot-0" class="w-8 h-8 rounded-full bg-plock-blue text-white font-bold flex items-center justify-center ring-4 ring-blue-200 hover:ring-plock-blue transition duration-200">1</div>
                            <span class="text-xs mt-2 text-plock-blue font-medium whitespace-nowrap hidden sm:block">Zainteresowania</span>
                        </button>

                        <button type="button" data-step="1" class="step-nav-dot flex flex-col items-center cursor-not-allowed">
                            <div id="step-dot-1" class="w-8 h-8 rounded-full bg-gray-400 text-white font-bold flex items-center justify-center ring-4 ring-gray-100 hover:ring-plock-blue transition duration-200">2</div>
                            <span class="text-xs mt-2 text-gray-500 font-medium whitespace-nowrap hidden sm:block">Mocne Strony</span>
                        </button>

                        <button type="button" data-step="2" class="step-nav-dot flex flex-col items-center cursor-not-allowed">
                            <div id="step-dot-2" class="w-8 h-8 rounded-full bg-gray-400 text-white font-bold flex items-center justify-center ring-4 ring-gray-100 hover:ring-plock-blue transition duration-200">3</div>
                            <span class="text-xs mt-2 text-gray-500 font-medium whitespace-nowrap hidden sm:block">Wykształcenie i Cel</span>
                        </button>

                        <button type="button" data-step="3" class="step-nav-dot flex flex-col items-center cursor-not-allowed">
                            <div id="step-dot-3" class="w-8 h-8 rounded-full bg-gray-400 text-white font-bold flex items-center justify-center ring-4 ring-gray-100 hover:ring-plock-blue transition duration-200">4</div>
                            <span class="text-xs mt-2 text-gray-500 font-medium whitespace-nowrap hidden sm:block">Preferencje Pracy</span>
                        </button>

                    </div>
                </div>
            </div>
        </div>

    <main class="max-w-6xl mx-auto p-6 mt-10">

        <div id="validation-alert" 
             class="fixed top-24 right-6 max-w-sm w-full bg-red-600 text-white p-4 rounded-lg shadow-lg z-[100] transform translate-x-[calc(100%+24px)] transition-transform duration-300 ease-in-out">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h4 class="font-bold">Brakujące odpowiedzi</h4>
                    <p class="text-sm">Wypełnij wszystkie podświetlone pytania, aby przejść dalej.</p>
                </div>
                <button id="close-alert-btn" class="absolute top-1 right-1 p-1 text-red-100 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <section id="welcome-section" class="text-center fade-in">
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">Znajdź swoją ścieżkę kariery w Płocku.</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-8">
                Wybór studiów i pracy to jedna z najważniejszych decyzji. Nasza ankieta (ok. 5 min) pomoże Ci odkryć Twoje mocne strony i dopasować je do realnych możliwości w Płocku.
            </p>
            <button
                id="start-survey-btn"
                class="bg-plock-blue text-white font-bold py-3 px-8 rounded-full text-lg transition-all duration-300 ease-in-out transform hover:scale-105 hover:[filter:brightness(90%)]">
                Rozpocznij Ankietę
            </button>
            <img src="img/path.png" alt="Ilustracja ścieżki kariery" class="mx-auto mt-8 max-w-sm">
        </section>

        <section id="survey-container" class="hidden max-w-4xl mx-auto fade-in">
            <form id="career-survey">
                <div id="survey-step-1" class="survey-step active">
                    <div class="bg-white p-6 md:p-8 rounded-lg shadow-lg">
                        <h3 class="text-2xl font-semibold mb-2 text-center">MODUŁ 1: Zainteresowania</h3>
                        <p class="text-center text-gray-600 mb-6">Oceń, jak bardzo lubisz wykonywać poniższe czynności. Bądź szczery – nie ma tu złych odpowiedzi!</p>
                        
                        <div class="flex flex-col md:flex-row justify-between text-xs text-gray-600 mb-4 px-4 py-2 bg-gray-50 rounded-md space-y-1 md:space-y-0">
                            <span class="text-center">(1) Zdecydowanie nie lubię</span>
                            <span class="text-center">(3) Neutralnie</span>
                            <span class="text-center">(5) Zdecydowanie lubię</span>
                        </div>
                        
                        <div class="space-y-6">
                            
                            <h4 class="font-bold text-lg text-plock-blue pt-4 border-t border-gray-200">1/6: Działanie i Narzędzia</h4>
                            <div>
                                <label class="block text-gray-700 mb-3">1. Jak bardzo lubisz składać meble, naprawiać proste usterki techniczne lub pracować z narzędziami?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_1" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_1" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_1" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_1" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_1" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">2. Jak bardzo lubisz przebywać na zewnątrz, pracować fizycznie lub zajmować się roślinami/zwierzętami?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_2" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_2" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_2" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_2" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_2" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">3. Jak bardzo lubisz obsługiwać maszyny, urządzenia lub brać udział w procesach produkcyjnych?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_3" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_3" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_3" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_3" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_3" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>

                            <h4 class="font-bold text-lg text-plock-blue pt-4 border-t border-gray-200">2/6: Analiza i Odkrycia</h4>
                            <div>
                                <label class="block text-gray-700 mb-3">4. Jak bardzo lubisz szukać ukrytych wzorców w danych lub analizować skomplikowane informacje?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_4" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_4" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_4" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_4" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_4" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">5. Jak bardzo lubisz czytać o najnowszych odkryciach naukowych lub przeprowadzać eksperymenty?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_5" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_5" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_5" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_5" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_5" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">6. Jak bardzo lubisz rozwiązywać zadania, które wymagają długiego myślenia i logicznego podejścia?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_6" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_6" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_6" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_6" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_6" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>

                            <h4 class="font-bold text-lg text-plock-blue pt-4 border-t border-gray-200">3/6: Kreatywność i Estetyka</h4>
                            <div>
                                <label class="block text-gray-700 mb-3">7. Jak bardzo lubisz pisać, rysować, projektować grafiki lub tworzyć inną treść wizualną?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_7" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_7" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_7" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_7" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_7" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">8. Jak bardzo cenisz sobie swobodę twórczą i możliwość wyrażania własnej, unikalnej wizji?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_8" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_8" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_8" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_8" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_8" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">9. Jak bardzo interesujesz się wystrojem wnętrz, modą, filmem lub muzyką?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_9" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_9" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_9" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_9" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_9" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>

                            <h4 class="font-bold text-lg text-plock-blue pt-4 border-t border-gray-200">4/6: Ludzie i Pomoc</h4>
                            <div>
                                <label class="block text-gray-700 mb-3">10. Jak bardzo lubisz doradzać innym, pomagać im w problemach lub mediować w sporach?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_10" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_10" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_10" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_10" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_10" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">11. Jak bardzo lubisz uczyć, przekazywać wiedzę lub organizować spotkania dla innych osób?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_11" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_11" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_11" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_11" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_11" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">12. Jak bardzo czujesz satysfakcję, gdy Twoje działania realnie poprawiają samopoczucie innych ludzi?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_12" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_12" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_12" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_12" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_12" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>

                            <h4 class="font-bold text-lg text-plock-blue pt-4 border-t border-gray-200">5/6: Liderowanie i Inicjatywa</h4>
                            <div>
                                <label class="block text-gray-700 mb-3">13. Jak bardzo lubisz kierować grupą, negocjować warunki lub przekonywać innych do swoich pomysłów?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_13" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_13" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_13" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_13" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_13" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">14. Jak bardzo pociąga Cię wizja zarządzania własnym biznesem lub dużą, ambitną inicjatywą?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_14" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_14" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_14" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_14" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_14" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">15. Jak bardzo lubisz występować publicznie i czujesz się komfortowo w roli lidera?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_15" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_15" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_15" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_15" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_15" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            
                            <h4 class="font-bold text-lg text-plock-blue pt-4 border-t border-gray-200">6/6: Organizacja i Dane</h4>
                            <div>
                                <label class="block text-gray-700 mb-3">16. Jak bardzo lubisz układać plany, harmonogramy lub dbać o porządek w dokumentach i plikach?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_16" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_16" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_16" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_16" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_16" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">17. Jak bardzo cenisz sobie pracę, w której panują jasne zasady, a zadania są precyzyjnie określone?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_17" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_17" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_17" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_17" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_17" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">18. Jak bardzo lubisz pracować z liczbami, prowadzić budżet lub sprawdzać dokładność danych?</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_18" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_18" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_18" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_18" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_riasec_18" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="survey-step-2" class="survey-step">
                    <div class="bg-white p-6 md:p-8 rounded-lg shadow-lg">
                        <h3 class="text-2xl font-semibold mb-2 text-center">MODUŁ 2: Mocne Strony i Kompetencje</h3>
                        <p class="text-center text-gray-600 mb-6">Teraz pomyśl o swoich naturalnych talentach. Oceń, jak bardzo poniższe stwierdzenia pasują do Ciebie.</p>

                        <div class="flex flex-col md:flex-row justify-between text-xs text-gray-600 mb-4 px-4 py-2 bg-gray-50 rounded-md space-y-1 md:space-y-0">
                            <span class="text-center">(1) Zupełnie się nie zgadzam</span>
                            <span class="text-center">(3) Neutralnie</span>
                            <span class="text-center">(5) W pełni się zgadzam</span>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-gray-700 mb-3">19. Potrafię jasno wyrażać swoje myśli i skutecznie współpracować w grupie.</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_19" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_19" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_19" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_19" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_19" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">20. Zanim podejmę decyzję, dokładnie sprawdzam wszystkie fakty i możliwe konsekwencje.</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_20" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_20" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_20" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_20" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_20" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">21. Chętnie przejmuję inicjatywę i organizuję działania innych.</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_21" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_21" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_21" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_21" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_21" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">22. Nawet w stresujących sytuacjach potrafię zachować spokój i koncentrację.</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_22" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_22" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_22" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_22" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_22" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">23. Często mam niekonwencjonalne pomysły na rozwiązanie starych problemów.</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_23" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_23" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_23" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_23" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_23" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">24. Szybko przyswajam wiedzę i umiem samodzielnie uczyć się nowych umiejętności (np. obsługa programu).</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_24" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_24" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_24" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_24" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_24" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">25. Jestem znany z dotrzymywania terminów i solidnego wykonywania obowiązków.</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_25" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_25" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_25" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_25" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_25" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">26. Łatwo rozumiem emocje i potrzeby innych osób.</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_26" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_26" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_26" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_26" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_26" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-3">27. Wolę działać i robić coś namacalnego, niż tylko o tym rozmawiać.</label>
                                <div class="flex flex-wrap justify-center sm:justify-around gap-2">
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_27" value="1" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">1</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_27" value="2" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">2</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_27" value="3" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">3</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_27" value="4" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">4</span></label>
                                    <label class="cursor-pointer"><input type="radio" name="q_strength_27" value="5" class="hidden peer"><span class="flex items-center justify-center w-10 h-10 rounded-full text-gray-600 bg-gray-200 hover:bg-blue-200 peer-checked:bg-plock-blue peer-checked:text-white transition-all duration-200">5</span></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="survey-step-3" class="survey-step">
                    <div class="bg-white p-6 md:p-8 rounded-lg shadow-lg">
                        <h3 class="text-2xl font-semibold mb-2 text-center">MODUŁ 3: Wykształcenie i Cel</h3>
                        <p class="text-center text-gray-600 mb-6">Określ swoje dotychczasowe wykształcenie oraz plany na przyszłość, abyśmy mogli lepiej dopasować rekomendacje.</p>

                        <div class="space-y-8">
                            
                            <div>
                                <label class="block text-xl font-semibold mb-3 text-gray-800">Twoje wykształcenie:</label>
                                <div class="space-y-3">
                                    <div class="relative">
                                        <input type="radio" name="q_education_level" id="q_education_level_A" value="brak" class="hidden peer">
                                        <label for="q_education_level_A" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue"><span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span></span>
                                            <span class="text-gray-700">Brak / W trakcie nauki</span>
                                        </label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_education_level" id="q_education_level_B" value="podstawowe" class="hidden peer">
                                        <label for="q_education_level_B" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue"><span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span></span>
                                            <span class="text-gray-700">Podstawowe / Zawodowe</span>
                                        </label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_education_level" id="q_education_level_C" value="srednie" class="hidden peer">
                                        <label for="q_education_level_C" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue"><span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span></span>
                                            <span class="text-gray-700">Średnie (posiadana matura)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xl font-semibold mb-3 text-gray-800">Jeżeli interesuje Cię dalsza nauka, to w jakiej formie?</label>
                                <div class="space-y-3">
                                    <div class="relative">
                                        <input type="radio" name="q_future_path" id="q_future_path_A" value="kurs" class="hidden peer">
                                        <label for="q_future_path_A" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue"><span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span></span>
                                            <span class="text-gray-700">Kursy zawodowe</span>
                                        </label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_future_path" id="q_future_path_B" value="studia" class="hidden peer">
                                        <label for="q_future_path_B" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue"><span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span></span>
                                            <span class="text-gray-700">Studia wyższe</span>
                                        </label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_future_path" id="q_future_path_C" value="technikum" class="hidden peer">
                                        <label for="q_future_path_C" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue"><span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span></span>
                                            <span class="text-gray-700">Szkoła policealna / Technikum</span>
                                        </label>
                                    </div>
                                     <div class="relative">
                                        <input type="radio" name="q_future_path" id="q_future_path_D" value="brak" class="hidden peer">
                                        <label for="q_future_path_D" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue"><span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span></span>
                                            <span class="text-gray-700">Brak preferencji / nie interesuje mnie dalsza edukacja</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xl font-semibold mb-3 text-gray-800">W której branży najchętniej byś pracował/a?</label>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-center">
                                    <div class="relative">
                                        <input type="radio" name="q_industry" id="q_industry_A" value="Administracja" class="hidden peer">
                                        <label for="q_industry_A" class="block p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">Administracja</label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_industry" id="q_industry_B" value="IT" class="hidden peer">
                                        <label for="q_industry_B" class="block p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">IT</label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_industry" id="q_industry_C" value="Edukacja" class="hidden peer">
                                        <label for="q_industry_C" class="block p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">Edukacja</label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_industry" id="q_industry_D" value="Ekonomia" class="hidden peer">
                                        <label for="q_industry_D" class="block p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">Ekonomia</label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_industry" id="q_industry_E" value="Inżynieria" class="hidden peer">
                                        <label for="q_industry_E" class="block p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">Inżynieria</label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_industry" id="q_industry_F" value="Brak" class="hidden peer">
                                        <label for="q_industry_F" class="block p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">Brak preferencji</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div id="survey-step-4" class="survey-step">
                    <div class="bg-white p-6 md:p-8 rounded-lg shadow-lg">
                        <h3 class="text-2xl font-semibold mb-2 text-center">MODUŁ 4: Preferencje Środowiska Pracy</h3>
                        <p class="text-center text-gray-600 mb-6">Na koniec kilka pytań o to, w jakim otoczeniu czujesz się najlepiej. Wybierz opcję, która jest Ci bliższa.</p>

                        <div class="space-y-8">
                            
                            <div>
                                <label class="block text-xl font-semibold mb-3 text-gray-800">28. Które środowisko pracy bardziej Ci odpowiada?</label>
                                <div class="space-y-3">
                                    <div class="relative">
                                        <input type="radio" name="q_pref_28" id="q_pref_28_A" value="A" class="hidden peer">
                                        <label for="q_pref_28_A" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue">
                                                <span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                                            </span>
                                            <span class="text-gray-700">A. Duże zakłady produkcyjne/techniczne (np. przemysł chemiczny, energetyka).</span>
                                        </label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_pref_28" id="q_pref_28_B" value="B" class="hidden peer">
                                        <label for="q_pref_28_B" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue">
                                                <span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                                            </span>
                                            <span class="text-gray-700">B. Biura, instytucje lub małe firmy usługowe/handlowe.</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xl font-semibold mb-3 text-gray-800">29. Jakie otoczenie preferujesz?</label>
                                <div class="space-y-3">
                                    <div class="relative">
                                        <input type="radio" name="q_pref_29" id="q_pref_29_A" value="A" class="hidden peer">
                                        <label for="q_pref_29_A" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue">
                                                <span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                                            </span>
                                            <span class="text-gray-700">A. Praca głównie samodzielna lub z maszynami/danymi.</span>
                                        </label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_pref_29" id="q_pref_29_B" value="B" class="hidden peer">
                                        <label for="q_pref_29_B" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue">
                                                <span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                                            </span>
                                            <span class="text-gray-700">B. Praca z ludźmi: doradzanie, obsługa, zarządzanie, nauczanie.</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xl font-semibold mb-3 text-gray-800">30. Co jest dla Ciebie najważniejsze przy wyborze zawodu?</label>
                                <div class="space-y-3">
                                    <div class="relative">
                                        <input type="radio" name="q_pref_30" id="q_pref_30_A" value="A" class="hidden peer">
                                        <label for="q_pref_30_A" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue">
                                                <span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                                            </span>
                                            <span class="text-gray-700">A. Wysokie i stabilne zarobki.</span>
                                        </label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_pref_30" id="q_pref_30_B" value="B" class="hidden peer">
                                        <label for="q_pref_30_B" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue">
                                                <span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                                            </span>
                                            <span class="text-gray-700">B. Poczucie misji, pomaganie innym lub rozwijanie swojej pasji.</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xl font-semibold mb-3 text-gray-800">31. Jakie tempo pracy preferujesz?</label>
                                <div class="space-y-3">
                                    <div class="relative">
                                        <input type="radio" name="q_pref_31" id="q_pref_31_A" value="A" class="hidden peer">
                                        <label for="q_pref_31_A" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue">
                                                <span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                                            </span>
                                            <span class="text-gray-700">A. Szybkie, zmienne, pełne nieprzewidzianych wyzwań.</span>
                                        </label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_pref_31" id="q_pref_31_B" value="B" class="hidden peer">
                                        <label for="q_pref_31_B" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue">
                                                <span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                                            </span>
                                            <span class="text-gray-700">B. Spokojne, stabilne, oparte na stałych procedurach.</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xl font-semibold mb-3 text-gray-800">32. Jaki rodzaj edukacji jest dla Ciebie bardziej atrakcyjny?</label>
                                <div class="space-y-3">
                                    <div class="relative">
                                        <input type="radio" name="q_pref_32" id="q_pref_32_A" value="A" class="hidden peer">
                                        <label for="q_pref_32_A" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue">
                                                <span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                                            </span>
                                            <span class="text-gray-700">A. Studia wyższe (tytuł inżyniera, magistra).</span>
                                        </label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_pref_32" id="q_pref_32_B" value="B" class="hidden peer">
                                        <label for="q_pref_32_B" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue">
                                                <span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                                            </span>
                                            <span class="text-gray-700">B. Kursy zawodowe, certyfikaty, szkoły policealne (szybkie wejście na rynek pracy).</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xl font-semibold mb-3 text-gray-800">33. Co daje Ci większą satysfakcję?</label>
                                <div class="space-y-3">
                                    <div class="relative">
                                        <input type="radio" name="q_pref_33" id="q_pref_33_A" value="A" class="hidden peer">
                                        <label for="q_pref_33_A" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue">
                                                <span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                                            </span>
                                            <span class="text-gray-700">A. Tworzenie lub budowanie czegoś namacalnego (produkt, maszyna, konstrukcja).</span>
                                        </label>
                                    </div>
                                    <div class="relative">
                                        <input type="radio" name="q_pref_33" id="q_pref_33_B" value="B" class="hidden peer">
                                        <label for="q_pref_33_B" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-blue-50 transition cursor-pointer peer-checked:border-plock-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <span class="w-5 h-5 rounded-full border-2 border-gray-300 mr-4 flex-shrink-0 flex items-center justify-center peer-checked:border-plock-blue">
                                                <span class="w-2.5 h-2.5 rounded-full bg-plock-blue opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></span>
                                            </span>
                                            <span class="text-gray-700">B. Uporządkowanie informacji, organizowanie procesów lub obsługa klienta.</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end mt-10">
                    <button
                        type="button"
                        id="prev-btn"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-8 rounded-full transition duration-300 mr-auto"
                    >
                        Wstecz
                    </button>
                    <button
                        type="button"
                        id="next-btn"
                        class="bg-plock-blue text-white font-bold py-3 px-8 rounded-full transition-all duration-300 hover:[filter:brightness(90%)]"
                    >
                        Dalej
                    </button>
                </div>
            </form>
        </section>
        
        <section id="loading-section" class="hidden text-center fade-in py-20">
            <div class="flex justify-center items-center mb-6">
                <svg class="animate-spin -ml-1 mr-3 h-10 w-10 text-plock-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Analizujemy Twoje odpowiedzi...</h2>
            <p class="text-lg text-gray-600">Nasz serwer przetwarza Twój profil i dobiera rekomendacje.</p>
        </section>

        <section id="report-section" class="hidden max-w-5xl mx-auto fade-in">
            <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">
                <div class="p-6 md:p-10 bg-gray-50 border-b-4 border-plock-blue">
                     <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 text-center">Twój Spersonalizowany Kompas Kariery</h2>
                </div>
                <div class="p-6 md:p-10">
                    <div class="flex flex-col md:flex-row gap-8 items-center mb-10 pb-10 border-b">
                        <div>
                            <h3 id="report-profile-title" class="text-2xl font-bold text-gray-800 mb-3">
                                Analiza Twojego Profilu
                            </h3>
                            <p id="report-profile-description" class="text-gray-600 leading-relaxed"></p>
                        </div>
                    </div>

                    <div class="mb-10 pb-10 border-b">
                        <h3 class="text-2xl font-bold text-gray-800 mb-3 border-b-2 border-gray-200 pb-2">
                            Twoje Ścieżki w Płocku
                        </h3>
                         <p id="report-paths-intro" class="text-gray-600 leading-relaxed mb-6"></p>
                        <div id="report-paths-container" class="grid md:grid-cols-2 gap-6"></div>
                    </div>
                    
                    <div class="mb-10 pb-10 border-b">
                         <h3 class="text-2xl font-bold text-gray-800 mb-3 border-b-2 border-gray-200 pb-2">
                            Pierwsze Kroki i Rozwój
                        </h3>
                        <p class="text-gray-600 leading-relaxed mb-4">Twój profil jest bardzo ceniony na płockim rynku pracy. Oto co możesz zrobić teraz:</p>
                        <ul id="report-next-steps-list" class="space-y-3"></ul>
                    </div>

                    <div class="text-center bg-gray-50 p-8 rounded-xl">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Przejdź od planu do działania!</h3>
                        <p class="text-gray-600 max-w-2xl mx-auto mb-6">Raport to dopiero początek. Przygotowaliśmy dla Ciebie spersonalizowaną listę ofert pracy, kierunków studiów i kursów, które pasują do Twojego profilu. Zobacz, jakie konkretne możliwości czekają na Ciebie już dziś.</p>
                        <a id="offers-link" href="#" class="inline-block bg-plock-blue text-white font-bold py-3 px-8 rounded-full text-lg transition-all duration-300 ease-in-out transform hover:scale-105 hover:[filter:brightness(110%)]">
                            Zobacz spersonalizowane oferty &rarr;
                        </a>
                    </div>
                    
                    <div class="text-center mt-8">
                        <button id="reset-btn" class="text-sm text-gray-500 hover:text-gray-700 hover:underline">Wypełnij ankietę ponownie</button>
                    </div>

                </div>
            </div>
        </section>
        </main>

<script>
    window.addEventListener('load', function() {
        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden-overlay');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const welcomeSection = document.getElementById('welcome-section');
        const surveyContainer = document.getElementById('survey-container');
        const loadingSection = document.getElementById('loading-section');
        const reportSection = document.getElementById('report-section');
        const startSurveyBtn = document.getElementById('start-survey-btn');
        const careerSurveyForm = document.getElementById('career-survey');
        const resetBtn = document.getElementById('reset-btn');
        const nextBtn = document.getElementById('next-btn');
        const prevBtn = document.getElementById('prev-btn');
        const surveySteps = document.querySelectorAll('.survey-step');
        const progressBar = document.getElementById('progress-bar');
        const progressNavBar = document.getElementById('progress-nav-bar');
        const stepNavDots = document.querySelectorAll('.step-nav-dot');
        const validationAlert = document.getElementById('validation-alert');
        const closeAlertBtn = document.getElementById('close-alert-btn');
        const offersLink = document.getElementById('offers-link');
        
        const loadingOverlay = document.getElementById('loading-overlay'); 

        let alertTimeout;
        let currentStep = 0;
        const totalSteps = 4;
        let maxCompletedStep = 0;
        let userSurveyData = {};

        const stepQuestions = [
            Array.from({ length: 18 }, (_, i) => `q_riasec_${i + 1}`),
            Array.from({ length: 9 }, (_, i) => `q_strength_${i + 19}`),
            ['q_education_level', 'q_future_path', 'q_industry'],
            Array.from({ length: 6 }, (_, i) => `q_pref_${i + 28}`)
        ];

        function showMainSection(sectionToShow) {
            welcomeSection.classList.add('hidden');
            surveyContainer.classList.add('hidden');
            loadingSection.classList.add('hidden');
            reportSection.classList.add('hidden');
            progressNavBar.classList.toggle('hidden', sectionToShow !== surveyContainer);
            if (sectionToShow) {
                sectionToShow.classList.remove('hidden');
                setTimeout(() => window.scrollTo({ top: 0, behavior: 'smooth' }), 50);
            }
        }
        
        function updateDotStyles() {
             stepNavDots.forEach((dot, index) => {
                const dotDiv = document.getElementById(`step-dot-${index}`);
                const dotSpan = dot.querySelector('span');
                
                if (index <= currentStep) {
                    dotDiv.classList.add('bg-plock-blue');
                    dotDiv.classList.remove('bg-gray-400');
                } else {
                    dotDiv.classList.add('bg-gray-400');
                    dotDiv.classList.remove('bg-plock-blue');
                }

                if (index <= maxCompletedStep) {
                    dot.classList.remove('cursor-not-allowed');
                    dotSpan.classList.add('text-plock-blue');
                    dotSpan.classList.remove('text-gray-500');
                } else {
                    dot.classList.add('cursor-not-allowed');
                    dotSpan.classList.remove('text-plock-blue');
                    dotSpan.classList.add('text-gray-500');
                }
                
                if (index === currentStep) {
                    dotDiv.classList.add('ring-plock-blue', 'ring-offset-2', 'ring-4');
                } else {
                    dotDiv.classList.remove('ring-plock-blue', 'ring-offset-2', 'ring-4');
                }
            });
        }

        function showStep(stepIndex) {
            surveySteps.forEach((step, index) => step.classList.toggle('active', index === stepIndex));
            // Używamy maxCompletedStep w mianowniku, aby pasek postępu osiągnął 100% na ostatnim, ukończonym kroku
            progressBar.style.width = `${(stepIndex / (totalSteps - 1)) * 100}%`;
            updateDotStyles();
            prevBtn.parentElement.classList.toggle('justify-end', stepIndex === 0);
            prevBtn.parentElement.classList.toggle('justify-between', stepIndex > 0);
            prevBtn.classList.toggle('hidden', stepIndex === 0);
            nextBtn.textContent = (stepIndex === totalSteps - 1) ? 'Generuj Raport' : 'Dalej';
            setTimeout(() => window.scrollTo({ top: 0, behavior: 'smooth' }), 50);
        }
        
        function showAlert() {
            clearTimeout(alertTimeout);
            validationAlert.classList.remove('translate-x-[calc(100%+24px)]');
            alertTimeout = setTimeout(hideAlert, 5000);
        }

        function hideAlert() {
            clearTimeout(alertTimeout);
            validationAlert.classList.add('translate-x-[calc(100%+24px)]');
        }
        
        function isStepValid(stepIndex) {
            hideAlert();
            const currentStepElement = surveySteps[stepIndex];
            if (!currentStepElement) return false;
            const questionsToValidate = stepQuestions[stepIndex];
            let firstUnanswered = null;
            let isValid = true;
            for (const groupName of questionsToValidate) {
                if (!currentStepElement.querySelector(`input[name="${groupName}"]:checked`)) {
                    isValid = false;
                    const unansweredInput = currentStepElement.querySelector(`input[name="${groupName}"]`);
                    if (unansweredInput) {
                         const unansweredContainer = unansweredInput.closest('div.space-y-6 > div, div.space-y-8 > div');
                         if (unansweredContainer) {
                            if (!firstUnanswered) firstUnanswered = unansweredContainer;
                            unansweredContainer.classList.add('bg-red-50', 'rounded-lg', 'p-2');
                            setTimeout(() => unansweredContainer.classList.remove('bg-red-50', 'rounded-lg', 'p-2'), 2500);
                        }
                    }
                }
            }
            if (!isValid) {
                if (firstUnanswered) firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
                showAlert();
            }
            return isValid;
        }

        function processSurveyData() {
            const formData = new FormData(careerSurveyForm);
            const scores = { R: 0, I: 0, A: 0, S: 0, E: 0, C: 0 };
            const riasecMapping = { 'q_riasec_1': 'R', 'q_riasec_2': 'R', 'q_riasec_3': 'R', 'q_riasec_4': 'I', 'q_riasec_5': 'I', 'q_riasec_6': 'I', 'q_riasec_7': 'A', 'q_riasec_8': 'A', 'q_riasec_9': 'A', 'q_riasec_10': 'S', 'q_riasec_11': 'S', 'q_riasec_12': 'S', 'q_riasec_13': 'E', 'q_riasec_14': 'E', 'q_riasec_15': 'E', 'q_riasec_16': 'C', 'q_riasec_17': 'C', 'q_riasec_18': 'C' };
            const strengths = {};
            const preferences = {};
            const educationAndGoals = {};

            for (const [key, value] of formData.entries()) {
                if (key.startsWith('q_riasec_')) {
                    scores[riasecMapping[key]] += parseInt(value);
                } else if (key.startsWith('q_strength_')) {
                    strengths[key] = parseInt(value);
                } else if (key.startsWith('q_pref_')) {
                    preferences[key] = value;
                } else if (['q_education_level', 'q_future_path', 'q_industry'].includes(key)) {
                    educationAndGoals[key] = value;
                }
            }
            const sortedScores = Object.entries(scores).sort(([,a],[,b]) => b-a);
            const profileCode = `${sortedScores[0][0]}-${sortedScores[1][0]}`;
            
            userSurveyData = { profileCode, preferences, strengths, educationAndGoals };
            return userSurveyData;
        }

        async function getReportFromBackend(userData) {
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(userData)
                });
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Błąd odpowiedzi serwera: ${response.status}. Treść: ${errorText}`);
                }
                return await response.json();
            } catch (error) {
                console.error("Błąd komunikacji z backendem:", error);
                return { error: true, message: `Nie udało się połączyć z serwerem. Sprawdź konsolę (F12) w celu uzyskania szczegółów. Szczegóły błędu: ${error.message}` };
            }
        }
        
        function renderReport(reportData) {
            if (reportData.error) {
                showMainSection(welcomeSection);
                alert(`Wystąpił błąd krytyczny:\n\n${reportData.message}\n\nOdśwież stronę i spróbuj ponownie.`);
                return;
            }

            document.getElementById('report-profile-title').textContent = reportData.profileTitle || "Analiza Twojego Profilu";
            document.getElementById('report-profile-description').textContent = reportData.profileDescription || "Brak opisu.";
            document.getElementById('report-paths-intro').textContent = reportData.pathsIntro || "";
            
            const pathsContainer = document.getElementById('report-paths-container');
            pathsContainer.innerHTML = '';
            if (reportData.careerPaths && reportData.careerPaths.length > 0) {
                reportData.careerPaths.forEach(path => {
                    const card = document.createElement('div');
                    card.className = 'bg-white p-5 rounded-xl border border-gray-200 hover:shadow-md hover:border-plock-blue transition-all duration-300';
                    card.innerHTML = `
                        <h4 class="font-bold text-lg text-gray-800">${path.title}</h4>
                        <p class="text-sm text-gray-600 mt-1">${path.description}</p>
                        <div class="mt-3 text-xs text-gray-500">
                           <p><strong>Kwalifikacje:</strong> ${path.qualifications.join(', ')}</p>
                           <p class="mt-1"><strong>Pracodawcy:</strong> ${path.employers}</p>
                        </div>
                    `;
                    pathsContainer.appendChild(card);
                });
            }

            const nextStepsList = document.getElementById('report-next-steps-list');
            nextStepsList.innerHTML = '';
            if(reportData.nextSteps && reportData.nextSteps.length > 0) {
                reportData.nextSteps.forEach(step => {
                    const li = document.createElement('li');
                    li.className = "flex items-start";
                    li.innerHTML = `
                        <svg class="w-5 h-5 mr-3 text-green-500 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>${step}</span>
                    `;
                    nextStepsList.appendChild(li);
                });
            }

            const params = new URLSearchParams();
            if (userSurveyData && userSurveyData.profileCode) {
                params.append('profil', userSurveyData.profileCode);
                params.append('branza', userSurveyData.educationAndGoals.q_industry || 'Brak');
                params.append('edukacja', userSurveyData.educationAndGoals.q_education_level || 'brak');
                params.append('cel', userSurveyData.educationAndGoals.q_future_path || 'brak');
                offersLink.href = `oferty.php?${params.toString()}`;
            } else {
                console.error("Brak danych ankiety do wygenerowania linku!");
                offersLink.href = "oferty.php";
            }
        }

        if(startSurveyBtn) {
            startSurveyBtn.addEventListener('click', () => {
                currentStep = 0; maxCompletedStep = 0;
                showMainSection(surveyContainer); showStep(currentStep);
            });
        }

        if(nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (!isStepValid(currentStep)) return;
                maxCompletedStep = Math.max(maxCompletedStep, currentStep); // Aktualizuj maxCompletedStep po udanej walidacji
                if (currentStep < totalSteps - 1) {
                    currentStep++;
                    showStep(currentStep);
                } else {
                    submitSurvey();
                }
            });
        }

        if(prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentStep > 0) {
                    currentStep--;
                    showStep(currentStep);
                }
            });
        }

        if(closeAlertBtn) {
            closeAlertBtn.addEventListener('click', hideAlert);
        }
        
        if(resetBtn) {
            resetBtn.addEventListener('click', () => {
                careerSurveyForm.reset();
                currentStep = 0; maxCompletedStep = 0;
                showMainSection(welcomeSection);
            });
        }

        stepNavDots.forEach(dot => {
            dot.addEventListener('click', function() {
                const targetStep = parseInt(this.getAttribute('data-step'));
                if (targetStep <= maxCompletedStep) {
                    currentStep = targetStep;
                    showStep(currentStep);
                }
            });
        });

        async function submitSurvey() {
            showMainSection(loadingSection);
            const userData = processSurveyData();
            const reportContent = await getReportFromBackend(userData); 
            setTimeout(() => {
                renderReport(reportContent);
                showMainSection(reportSection);
            }, 1500);
        }

        showMainSection(welcomeSection);
        
        // Funkcja przechwytująca linki do natychmiastowego pokazania nakładki
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

        // --- Logika obsługi menu mobilnego (Burger) ---
        const mobileMenuOpenBtn = document.getElementById('mobile-menu-open-btn');
        const mobileMenuCloseBtn = document.getElementById('mobile-menu-close-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuOpenBtn) {
            mobileMenuOpenBtn.addEventListener('click', () => {
                mobileMenu.classList.remove('translate-x-full');
                document.body.style.overflow = 'hidden'; 
            });
        }

        if (mobileMenuCloseBtn) {
            mobileMenuCloseBtn.addEventListener('click', () => {
                mobileMenu.classList.add('translate-x-full');
                document.body.style.overflow = ''; 
            });
        }
        
    });
</script>
<footer class="bg-brand-blue text-white p-4" style="background-color: #3b5998;">
    <div class="container mx-auto text-center text-xs uppercase tracking-wider">
        <p>© TRINF HACKATHON 2025</p>
        <p>Dominik Dylewski, Bartek Zakrzewski, Konrad Zatorski</p>
        <p class="mt-2 text-xs"><a href="regulamin.txt" target="_blank" class="text-gray-300 hover:text-white transition-colors uppercase tracking-wider">REGULAMIN</a></p>
     </div>
</footer>
</body>
</html>
