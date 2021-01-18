# isowiki-docker
Schemat wersjonowania:
1. Każdy build posiada przypisaną wersję zgodnie ze schematem: X.Y.Z
2. Zmiana "Z" oznacza aktualizację jednej z wtyczek dokuwiki lub drobne zmiany w Dockerfile
2. Zmiana "Y" oznacza nowy build dokuwiki bez zmiany struktury bazy danych żadnej ze wtyczek
3. Zmiana "X" oznacza zmianę struktury bazy danych którejś ze wtyczek. Należy zachować szczególną ostrożność.