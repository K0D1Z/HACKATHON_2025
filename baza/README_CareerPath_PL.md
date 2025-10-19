# CareerPath – Baza danych (wersja strukturalna)

Zestaw danych przedstawiający powiązania pomiędzy zawodami, umiejętnościami, kierunkami edukacji i firmami z uwzględnieniem branż i logicznych zależności.

## 📁 Zawartość archiwum

Plik `synthetic_dataset_careerpath_PL_structured.zip` zawiera następujące pliki `.csv`:

- `jobs.csv` – lista zawodów
- `skills.csv` – lista umiejętności
- `education.csv` – lista kierunków edukacyjnych
- `companies.csv` – lista firm
- `jobs_skills.csv` – relacja zawód–umiejętność
- `jobs_education.csv` – relacja zawód–kierunek edukacyjny
- `jobs_companies.csv` – relacja zawód–firma

## 🔄 Schemat relacji

```
+-----------+          +-------------+           +--------------+
|   jobs    |<-------->| jobs_skills |<--------->|   skills     |
+-----------+          +-------------+           +--------------+
     |
     |
     v
+---------------+        +-----------------+
| jobs_education |<----->|   education      |
+---------------+        +-----------------+

     |
     v
+-----------------+
| jobs_companies  |<---------> companies
+-----------------+           +--------------+
```

## 🗂️ Opisy tabel

### `jobs.csv`
Zawiera listę zawodów:
- `job_id`: ID zawodu
- `name`: nazwa zawodu
- `level_required`: wymagany poziom edukacji
- `avg_salary`: średnie wynagrodzenie brutto (PLN)
- `industry`: branża zawodowa

### `skills.csv`
Zawiera listę umiejętności:
- `skill_id`: ID umiejętności
- `name`: nazwa umiejętności
- `type`: typ umiejętności ("Hard"/"Soft")

### `education.csv`
Zawiera kierunki edukacyjne:
- `education_id`: ID kierunku
- `name`: nazwa kierunku
- `type`: typ edukacji (Technikum, Studia, Kurs)
- `duration_months`: czas trwania (w miesiącach)
- `required_input`: wymagany poziom wejściowy

### `companies.csv`
Zawiera informacje o firmach:
- `company_id`: ID firmy
- `name`: nazwa firmy
- `industry`: branża działalności
- `location`: lokalizacja
- `offers_internships`: czy oferuje praktyki ("Tak"/"Nie")

### `jobs_skills.csv`
Relacja wiele-do-wielu między zawodami a umiejętnościami:
- `job_id`
- `skill_id`

### `jobs_education.csv`
Relacja wiele-do-wielu między zawodami a edukacją:
- `job_id`
- `education_id`

### `jobs_companies.csv`
Relacja wiele-do-wielu między zawodami a firmami:
- `job_id`
- `company_id`
