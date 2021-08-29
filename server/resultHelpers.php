<?php

    /**
     * Tworzy wynik w standardowym formacie zwracanym przez serwer.
     * 
     * Standardowo api serwera zwraca wynikowy obiekt w formacie JSON.
     * W atrybucie result zawiera on wynikowy kod.
     * Obiekt może zawierać również dodatkowe parametry,
     * specyficzne dla wyniku konkretnego żądania i opisane w dokumentacji.
     * 
     * @param {Integer} $resultCode Wynikowy kod zwrócony przez działanie akcji serwera.
     *                              Konwencjonalnie 0 to sukces, kolejne liczby naturalne błąd.
     * @param {Array} $additionalProperties Dodatkowe atrybuty do dołączenia do wyniku
     * @return {JSON String} Wynik działania akcji zakodowany w formacie JSON
     */
    function generateResult($resultCode, $additionalProperties = array()) {
        $additionalProperties['result'] = $resultCode;
        return json_encode($additionalProperties);
    }

?>
