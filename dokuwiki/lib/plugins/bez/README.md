### Wersja 17.07.03:
* poprawiono #140
* ulepszono tygodniowy raport
    
### Wersja 17.07.02:
* zwykli użytkownicy nie widzą już przycisku edycji metadanych komentarza

### Wersja 17.07.01:
* poprawiono #139
* zgodność wsteczna z php 5

### Wersja 17.07:
* przebudowano autorespondenta zgodnie z #95
* przebudowano boksy problemu i zadania
* edycja metadanych #98
* subskrybenci do zadań #119
* powiadomienia na temat zmiany statusu zadania
* poprawiono #129
* notyfikacje nie przychodzą do użytkownika który wykonał akcję

### Wersja 2017-06-24c:
* poprawiono #109, #131

### Wersja 2017-06-24b:
* przy ponownym odrzucaniu problemu w polu domyślnie wprowadzana jest poprzednia przyczyna

### Wersja 2017-06-24a:
* stworzono CLI dla migracji,
* poprawiono procedurę migracji przeniesienia komentarzy i przyczyn do commcause,
* poprawiono problem ze niewyświetlanym typu nieokreślonego w ekranie Problemu,
* dodano schemat migracji do nowego sposobu oznaczania odrzuconych problemów

### Wersja 2017-06-24:
* poprawiono #86 ptk. 2
* nowy system uprawnień - BEZ_Leader
* poprabiono błąd polegający na uniemożliwieniu Zapraszania do problemu użytkowników, których Imię i nazwisko kończyło się na spację np. "Jan Kowalski "
* raport 8d wyświetla zadania otwarte
* raport nD - #105
* Treść problemów jest teraz dołączana do powiadomień mejlowych nieposiadających innej treści #90
* Poprawiono błąd z niedziałającym autorespondentem: #123


### Wersja 2017-05-31:
* poprawiono #86 ptk. 1
* raport 8d nie wyświetla już odrzuconych zadań
* w powiadomieniach mejlowych nagłówkiem jest tytuł problemu, a nie pierwsze zdanie w problemie
    

### Wersja 2017-05-23:
* poprawiono błąd przy pierwszym uruchomieniu świerzej instalacji
* poprawiono błąd uniemożliwiający zamykanie zadań
* nie można już zmieniać zadań po zamknięciu problemu
* filtry pełnotekstowe są teraz niewrażliwe na wielkość liter oraz wstawiają operator "*" w miejsce każdego białego znaku
* "Ocena" zadania stała się opcjonalna
* Anulowanie zmiany statusu zadania działa poprawnie
* Wstępe prace nad mechanizmem raportów wiki
* Poprawiono mechanizm tokenów

### Wersja 2017-01-17:
* poprawienie błędów importu
* poprawienie błędu short_tags
* podświetlanie użytych filtrów

### Wersja 2017-01-12:
* zmiana statusu problemu powoduje zmianę ostatniej aktywności
* dodano raport aktywności do bazy BEZ
  

### Wersja 2017-01-09:
* usunięto "przyczynę źródłową" z przyczyn;
* typ problemu nie jest wymagany;

### Wersja 2017-01-02:
* zmieniono system uprawnień: każdy może dodawać zadania programowe, których sam jest wykonwacą
* usunięto koordynatorów programów
* usunięto błąd uniemożliwiający dodanie kordynatora przy zgłaszaniu problemu
* zadanie zawiera informację o osobie je zgłaszającej
* można edytować Ocenę i Przyczynę Odrzucenia zadań 

### Wersja 2016-11-15:
* usunięto błąd uprawnień
* umożliwiono usuwanie Programów ze słownika

### Wersja 2016-11-14:
* usunięcie błędu: #58
* dodano "Ostatnią aktywność" oraz "Osoby zaangażowane"

### Wersja 2016-11-09:
* Program zadania nie jest już wymagany w przypadku zadań Korekcyjych
* Nowy wygląd dyskusji w problemie
* Administartor może zmieniać programy Zadań programowych
	
### Wersja 2016-10-31:
* usunięcie błędu: #50, #51, #53
* program zadania jest wymagany przy dodawaniu zadania.
* programy zarządzania mogą mieć przypisanych koordynatorów, którzy mogą dodawać do nich zadania.

### Wersja 2016-09-30:
* usunięcie błędu: #49

### Wersja 2016-09-19:
* usunięcie błędu: #47

### Wersja 2016-09-08:
* w raporcie osobno zliczamy zadania zamknięte na czas i zamknięte po terminie #37
* streszczenie opisu Zadania w tabeli Zadań #38
* wyświetlanie wszystkich podpunktów w raporcie 8d #42

### Wersja 2016-09-06:
* Usunięto priorytet z Problemów. Obecnie w Tabeli Problemów kolor Problemu uzależniony jest od koloru Zadań w nim zawartych. Jeżeli zawiera choć jedno zadanie czerwone, kolor jest czerwony. Jeżeli brak czerwonych i choć jedno zadanie jest żółte, kolor jest żółty. Jeżeli brak czerwonych i żółtych i choć jedno zadanie jest zielone, kolor zielony. Jeżeli nie zawiera żadnych zadań kolor niebieski. Jeżeli problem jest zamknięty bądź dorzucony, kolor biały.
	Kodowanie kolorów:
		NULL -> niebieski
		0 -> czerwony
		1 -> żółty
		2 -> zielony
		3 -> biały
* #30 #z129 - Dodano wirtualny status: "Przeterminowane" do zadań.
* #28 #z128 - Dodano ukrywanie Problemu przy widoku zadania.
* #z131
* #45 #z134
* #34 #z135



