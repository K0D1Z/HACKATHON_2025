<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KIERUNEKPLOCK.PL - Dopasowane Oferty</title>
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
    <link rel="apple-touch-icon" href="img/logo.png" sizes="180x180">

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

    <main class="max-w-6xl mx-auto p-6 mt-10">
        <section id="offers-section" class="fade-in">
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">Twoje Spersonalizowane Oferty</h1>
                <p id="subtitle" class="text-lg text-gray-600 max-w-3xl mx-auto">Na podstawie Twoich odpowiedzi przygotowaliśmy listę pasujących ofert pracy, kierunków studiów i kursów.</p>
            </div>

            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b-2 border-plock-blue pb-2">Propozycje Pracy</h2>
                <div id="jobs-container" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="loader-container col-span-full flex justify-center py-10"><div class="loader spinner"></div></div>
                </div>
                <p id="no-jobs-message" class="hidden text-center text-gray-500 py-10">Nie znaleziono ofert pracy idealnie pasujących do wszystkich Twoich kryteriów. Spróbuj ponownie z innymi opcjami w ankiecie!</p>
            </div>

            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b-2 border-plock-blue pb-2">Ścieżki Edukacji i Rozwoju</h2>
                <div id="education-container" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="loader-container col-span-full flex justify-center py-10"><div class="loader spinner"></div></div>
                </div>
                <p id="no-education-message" class="hidden text-center text-gray-500 py-10">Nie znaleziono propozycji edukacyjnych idealnie pasujących do wszystkich Twoich kryteriów.</p>
            </div>
        </section>

        <section id="error-section" class="hidden text-center py-20 fade-in">
            <h2 class="text-2xl font-semibold text-red-600 mb-2">Wystąpił Błąd</h2>
            <p id="error-message" class="text-lg text-gray-600 max-w-2xl mx-auto"></p>
            <a href="form.php" class="mt-6 inline-block bg-plock-blue text-white font-bold py-3 px-8 rounded-full transition-transform transform hover:scale-105">Wróć do ankiety</a>
        </section>

    </main>

    <script>
    document.addEventListener('DOMContentLoaded', async () => {
        const jobsContainer = document.getElementById('jobs-container');
        const educationContainer = document.getElementById('education-container');
        const noJobsMessage = document.getElementById('no-jobs-message');
        const noEducationMessage = document.getElementById('no-education-message');
        const errorSection = document.getElementById('error-section');
        const errorMessage = document.getElementById('error-message');
        const offersSection = document.getElementById('offers-section');
        const loadingOverlay = document.getElementById('loading-overlay'); 

        const params = new URLSearchParams(window.location.search);
        const surveyData = {
            profile: params.get('profil'),
            industry: params.get('branza'),
            education: params.get('edukacja'),
            goal: params.get('cel')
        };

        // Sprawdzanie czy są dane, na podstawie których można wyszukać
        if (!surveyData.industry) { 
            showError("Brak danych z ankiety do wyszukania ofert. Proszę wypełnić ją ponownie.");
            return;
        }
        
        try {
            const response = await fetch('oferty_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(surveyData)
            });

            if (!response.ok) {
                 const errorJson = await response.json();
                 // Używamy ujednoliconego komunikatu błędu z API (jeśli dostępny)
                 throw new Error(`Błąd serwera: ${response.status}. Treść: ${errorJson.message || 'Błąd API.'}`);
            }

            const data = await response.json();
            renderJobs(data.jobs);
            renderEducation(data.education);

        } catch (error) {
            console.error("Błąd pobierania ofert:", error);
            showError(error.message);
        }

        function renderJobs(jobs) {
            const loaders = jobsContainer.querySelectorAll('.loader-container');
            loaders.forEach(loader => loader.remove());

            if (!jobs || jobs.length === 0) {
                noJobsMessage.classList.remove('hidden');
                return;
            }
            jobsContainer.innerHTML = '';
            jobs.forEach(job => {
                const jobCard = document.createElement('div');
                // Budowanie linku do szczegółów z zachowaniem parametrów ankiety
                const detailsLink = `szczegoly.php?job_id=${job.job_id}&profil=${surveyData.profile}&branza=${surveyData.industry}&edukacja=${surveyData.education}&cel=${surveyData.goal}`;

                jobCard.className = 'oferta-blok bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between';
                jobCard.innerHTML = `
                    <div>
                        <span class="inline-block bg-blue-100 text-brand-blue text-xs font-semibold px-2.5 py-0.5 rounded-full uppercase mb-2" style="color: #3b5998;">PRACA</span>
                        <h3 class="text-2xl font-bold text-brand-blue mb-2" style="color: #3b5998;">${job.job_name}</h3>
                        <p class="text-lg font-semibold text-gray-800">${job.company_name}</p>
                        <p class="text-md text-gray-600 mb-3">${job.location}</p>
                        <p class="text-xl font-bold text-green-600 my-3">${new Intl.NumberFormat('pl-PL').format(job.avg_salary)} PLN</p>
                    </div>
                    <a href="${detailsLink}" class="inline-block mt-4 bg-brand-blue text-white text-center py-2 px-5 rounded-lg font-semibold hover:opacity-90 transition-opacity" style="background-color: #3b5998;">Szczegóły</a>
                `;
                jobsContainer.appendChild(jobCard);
            });
        }

        function renderEducation(education) {
            const loaders = educationContainer.querySelectorAll('.loader-container');
            loaders.forEach(loader => loader.remove());

             if (!education || education.length === 0) {
                noEducationMessage.classList.remove('hidden');
                return;
            }
            educationContainer.innerHTML = '';
            education.forEach(edu => {
                const eduCard = document.createElement('div');
                // Budowanie linku do szczegółów z zachowaniem parametrów ankiety
                const detailsLink = `szczegoly_edukacji.php?education_id=${edu.education_id}&profil=${surveyData.profile}&branza=${surveyData.industry}&edukacja=${surveyData.education}&cel=${surveyData.goal}`;

                // Ujednolicony styl kafelka
                eduCard.className = 'oferta-blok bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between';
                eduCard.innerHTML = `
                    <div>
                        <span class="inline-block bg-gray-200 text-brand-blue text-xs font-semibold px-2.5 py-0.5 rounded-full uppercase mb-2" style="color: #3b5998;">${edu.type}</span>
                        <h3 class="text-2xl font-bold text-brand-blue mb-2" style="color: #3b5998;">${edu.education_name}</h3>
                        <p class="text-xl font-bold text-gray-800 my-3">Czas trwania: ${edu.duration_months} mies.</p>
                    </div>
                    <a href="${detailsLink}" class="inline-block mt-4 bg-brand-blue text-white text-center py-2 px-5 rounded-lg font-semibold hover:opacity-90 transition-opacity" style="background-color: #3b5998;">Szczegóły</a>
                `;
                educationContainer.appendChild(eduCard);
            });
        }

        function showError(message) {
            offersSection.classList.add('hidden');
            errorMessage.innerHTML = message;
            errorSection.classList.remove('hidden');
        }
        
        // --- KLUCZOWA LOGIKA ANIMACJI ŁADOWANIA ---
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                if (!href || href.startsWith('http') || href.startsWith('#') || this.target === '_blank' || href.match(/\.(pdf|zip|jpg|png)$/i)) {
                    return;
                }
                
                // Ignorujemy nawigację na tej samej stronie (linki do kotwic są ignorowane wyżej)
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
    });

    // Funkcja ukrywająca nakładkę po załadowaniu WSZYSTKICH zasobów
    window.addEventListener('load', function() {
        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden-overlay');
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
