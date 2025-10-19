<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KIERUNEKPLOCK.PL - Strona główna</title>
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
            animation: fadeIn 1s ease-out forwards;
        }
        .fade-in-delay {
            animation: fadeIn 1s ease-out 0.5s forwards;
            opacity: 0;
        }
        .fade-in-delay-2 {
            animation: fadeIn 1s ease-out 1s forwards;
            opacity: 0;
        }
        
        .hero-slideshow {
            position: relative;
            height: 500px;
            overflow: hidden;
        }
        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
        }
        .slide-active {
            opacity: 1;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 10;
        }
        .hero-content {
            position: relative;
            z-index: 20;
        }
        
        .slide-1 { background-image: url('img/1.jpg'); } 
        .slide-2 { background-image: url('img/2.jpeg'); } 
        .slide-3 { background-image: url('img/3.jpg'); } 
        .slide-4 { background-image: url('img/4.jpg'); } 

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
        <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap justify-center gap-x-6 gap-y-2 md:gap-x-12 md:gap-y-4">
            <a href="index.php" class="text-base font-semibold text-brand-blue border-b-2 border-brand-blue">STRONA GŁÓWNA</a>        
            <a href="form.php" class="text-base font-semibold text-gray-600 hover:text-brand-blue transition-colors hover:border-b-2 hover:border-brand-blue">ANKIETA</a>
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
                <a href="index.php" class="text-lg font-semibold text-brand-blue border-b-2 border-brand-blue pb-1">STRONA GŁÓWNA</a>
                <a href="form.php" class="text-lg font-semibold text-gray-600 hover:text-brand-blue transition-colors">ANKIETA</a>
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

<main>
    <section class="hero-slideshow text-white">
        <div class="hero-slide slide-1 slide-active" id="slide1"></div>
        <div class="hero-slide slide-2" id="slide2"></div>
        <div class="hero-slide slide-3" id="slide3"></div>
        <div class="hero-slide slide-4" id="slide4"></div> 
        <div class="overlay"></div>
        <div class="hero-content max-w-6xl mx-auto px-6 py-24 md:py-32 text-center h-full flex flex-col justify-center items-center">
            <h2 class="text-4xl md:text-6xl font-extrabold leading-tight mb-4 fade-in">Znajdź swoją przyszłość w Płocku.</h2>
            <p class="text-lg md:text-xl max-w-3xl mx-auto mb-8 fade-in-delay">
                Wybór ścieżki zawodowej to wielka decyzja. Odkryj swoje naturalne talenty i zobacz, jakie możliwości edukacyjne i zawodowe czekają na Ciebie na lokalnym rynku pracy.
            </p>
            <a href="form.php" class="bg-plock-blue hover:[filter:brightness(110%)] text-white font-bold py-4 px-10 rounded-full text-lg transition-transform transform hover:scale-105 inline-block fade-in-delay-2">
                Odkryj swoje talenty &rarr;
            </a>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <h3 class="text-3xl font-bold mb-2">Twój Kompas Kariery w 3 Krokach</h3>
            <p class="text-gray-600 mb-12 max-w-2xl mx-auto">Nasz proces jest prosty, szybki i stworzony, by dać Ci realne wyniki.</p>
            <div class="grid md:grid-cols-3 gap-10">
                <div class="fade-in">
                    <div class="bg-plock-blue text-white w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                    <h4 class="text-xl font-semibold mb-2">Wypełnij ankietę</h4>
                    <p class="text-gray-600">Odpowiedz na pytania dotyczące Twoich zainteresowań i mocnych stron. To zajmie tylko kilka minut!</p>
                </div>
                <div class="fade-in-delay">
                    <div class="bg-plock-blue text-white w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                    <h4 class="text-xl font-semibold mb-2">Otrzymaj spersonalizowany raport</h4>
                    <p class="text-gray-600">Otrzymasz analizę swojego profilu zawodowego wraz z listą rekomendowanych dla Ciebie ścieżek kariery w Płocku.</p>
                </div>
                <div class="fade-in-delay-2">
                    <div class="bg-plock-blue text-white w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                    <h4 class="text-xl font-semibold mb-2">Przeglądaj dopasowane oferty</h4>
                    <p class="text-gray-600">Sprawdź konkretne oferty pracy, staży, studiów i kursów od lokalnych firm i uczelni, które pasują do Twojego profilu.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="py-16">
         <div class="max-w-6xl mx-auto px-6">
            <h3 class="text-3xl font-bold text-center mb-12">Odkryj zasoby, które dla Ciebie przygotowaliśmy</h3>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <a href="ofertypracy.php" class="block bg-white rounded-lg shadow-lg overflow-hidden group transform hover:-translate-y-2 transition-transform duration-300">
                    <div class="p-6">
                        <h4 class="text-xl font-bold text-brand-blue mb-2 group-hover:text-plock-blue transition-colors">Baza Ofert Pracy</h4>
                        <p class="text-gray-600">Przeglądaj najnowsze oferty pracy i staży od wiodących pracodawców w Płocku i okolicach.</p>
                    </div>
                </a>
                <a href="ofertyksztalcenia.php" class="block bg-white rounded-lg shadow-lg overflow-hidden group transform hover:-translate-y-2 transition-transform duration-300">
                    <div class="p-6">
                        <h4 class="text-xl font-bold text-brand-blue mb-2 group-hover:text-plock-blue transition-colors">Ścieżki Edukacyjne</h4>
                        <p class="text-gray-600">Poznaj ofertę płockich uczelni wyższych, szkół policealnych i techników. Znajdź kierunek dla siebie.</p>
                    </div>
                </a>
                <div class="md:col-span-2 lg:col-span-1 flex justify-center">
                    <a href="slownikzawodow.php" class="block bg-white rounded-lg shadow-lg overflow-hidden group transform hover:-translate-y-2 transition-transform duration-300 w-full md:max-w-sm lg:max-w-full">
                        <div class="p-6">
                            <h4 class="text-xl font-bold text-brand-blue mb-2 group-hover:text-plock-blue transition-colors">Słownik Zawodów</h4>
                            <p class="text-gray-600">Sprawdź, czym charakteryzują się poszczególne zawody, jakie są ich wymagania i średnie zarobki w Płocku.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white border-t">
        <div class="max-w-6xl mx-auto px-6 py-16 text-center">
            <h3 class="text-3xl font-bold mb-4 text-gray-900">Gotowy, by zrobić pierwszy krok?</h3>
            <p class="text-gray-600 max-w-2xl mx-auto mb-8">Twoja przyszłość zawodowa zaczyna się dzisiaj. Wypełnij ankietę i zobacz, co Płock ma Ci do zaoferowania.</p>
            <a href="form.php" class="bg-plock-blue hover:[filter:brightness(110%)] text-white font-bold py-4 px-10 rounded-full text-lg transition-transform transform hover:scale-105 inline-block">
                Rozpocznij Ankietę Teraz
            </a>
        </div>
    </section>

</main>


<footer class="bg-brand-blue text-white p-4" style="background-color: #3b5998;">
    <div class="container mx-auto text-center text-xs uppercase tracking-wider">
        <p>© TRINF HACKATHON 2025</p>
        <p>Dominik Dylewski, Bartek Zakrzewski, Konrad Zatorski</p>
        <p class="mt-2 text-xs"><a href="regulamin.txt" target="_blank" class="text-gray-300 hover:text-white transition-colors uppercase tracking-wider">REGULAMIN</a></p>
     </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loadingOverlay = document.getElementById('loading-overlay');
        
        // Funkcja przechwytująca linki do natychmiastowego pokazania nakładki
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Ignoruj: linki zewnętrzne, kotwice (#), linki do plików, _blank
                if (!href || href.startsWith('http') || href.startsWith('#') || this.target === '_blank' || href.match(/\.(pdf|zip|jpg|png)$/i)) {
                    return;
                }
                
                // Ignoruj, jeśli link prowadzi do tego samego pliku (w celu uniknięcia kolizji z kodem dodaj offerte)
                if (href.split('?')[0] === window.location.pathname.split('/').pop() && !href.includes('?')) {
                    return;
                }

                e.preventDefault(); 
                
                if (loadingOverlay) {
                    loadingOverlay.classList.remove('hidden-overlay');
                }
                
                // Uruchomienie nawigacji po krótkim opóźnieniu
                setTimeout(() => {
                    window.location.href = href;
                }, 100); 
            });
        });

        // Obsługa menu mobilnego
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

        // LOGIKA ANIMACJI ZMIANY OBRAZÓW (7 sekund)
        const slides = document.querySelectorAll('.hero-slide'); 
        let currentSlide = 0;
        const slideInterval = 7000; 

        function nextSlide() {
            if (slides.length === 0) return;

            slides[currentSlide].classList.remove('slide-active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('slide-active');
        }

        if (slides.length > 0) {
            slides[0].classList.add('slide-active');
            setInterval(nextSlide, slideInterval);
        }
    });

    // UKRYJ animację, gdy strona jest w pełni załadowana
    window.addEventListener('load', function() {
        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden-overlay');
        }
    });

</script>
</body>
</html>
