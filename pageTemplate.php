<?php
    /**
     * Funkcja generująca stronę użytkownika o podanych parametrach.
     * 
     * @param String $title Tytuł strony.
     * @param HTMLString $content Zawartość strony.
     * @param String $username Nazwa użytkownika.
     * @param String $scripts tablica URL do skryptów wywoływanych przez stronę w postaci.
     * @param String $styles tablica URL do cssów używanych przez stronę.
     * @return String Wygenerowany na podstawie parametrów kod strony.
     */
    function genPage($title, $content, $username, $scripts, $styles) {
        $scriptIncludeHtml = "";
        foreach($scripts as $script)
        $scriptIncludeHtml .= "<script src='$script'></script>\n";
        
        $styleIncludeHtml = "";
        foreach($styles as $style)
            $styleIncludeHtml .= "<link rel='stylesheet' href='$style'>\n";

        // TODO do uzupełnienia template
        // na razie skopiowany homepage.php
        // dodatkowo warto by z tego pliku wyłączyć możliwie dużo kodu
        // w szczególności przenieść kod css i skrypt do osobnego pliku
        return <<<ENT
            <!DOCTYPE html>
            <html lang="en-US">
            <head>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta charset="utf-8">
            $styleIncludeHtml
            
            <title>$title</title>
            </head>
            <body>
            
            <div class="grid">
            <div class="navbar">
                <a href="homepage.php">Moje lekcje</a>
                <a href="groups.php">Grupy</a>
                <div class="dropdown">
                    <div class="dropdown-menu-button">
                    $username
                    </div>
                    <div class="dropdown-menu-content">
                        <a href="profile.html">Mój profil</a>
                        <a href="settings.html">Ustawienia</a>
                        <a href="logout.php">Wyloguj się</a>
                    </div>
                </div>
            </div>
            
            <div class="main-content">
                $content
            </div>
            </div>
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"
                    integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
                    crossorigin="anonymous"></script>
                $scriptIncludeHtml
            </body>
            </html>
        ENT;
    }
?>