<pre>
<?php
include("passwd.php");
$cookie_file_path = "cookie.txt"; // path do przechowywania ciasteczek 
$ch = curl_init();
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file_path); // "The name of the file containing the cookie data ..."
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // "Set CURLOPT_RETURNTRANSFER to TRUE to return the transfer as a string of the return value of curl_exec()"
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // "true to follow any "Location: " header that the server sends as part of the HTTP header."
 
get("https://synergia.librus.pl/loguj/portalRodzina?v=1706533900");
 
$res = post(array(
    "action" => "login",
    "login" => $login,
    "pass" => $passwd
), "https://api.librus.pl/OAuth/Authorization?client_id=46");
$url = "https://api.librus.pl" . json_decode($res)->goTo;
 
curl_setopt($ch, CURLOPT_HEADER, 1);
$res = post(array(
    "command" => "open_synergia_window",
    "commandPayload" => array(
        "url" => 'https:\/\/synergia.librus.pl\/uczen\/index'
    )
), $url);

curl_setopt($ch, CURLOPT_HEADER, 0);
preg_match_all("/https.*code.*/", $res, $code);

get($code[0][0]);

$res = get("https://synergia.librus.pl/przegladaj_oceny/uczen");

$res = str_replace('href="','href="https://synergia.librus.pl',$res);
$res = str_replace('<td >&nbsp;<img class="tooltip helper-icon" alt="Podgląd średniej ocen został wyłączony przez administratora szkoły." title="Podgląd średniej ocen został wyłączony przez administratora szkoły." src="/images/pomoc_ciemna.png" />','<td>średnia śród 1</td>',$res);
$res = str_replace('<td class="center" >&nbsp;<img class="tooltip helper-icon" alt="Podgląd średniej ocen został<br/>wyłączony przez administratora szkoły." title="Podgląd średniej ocen został<br/>wyłączony przez administratora szkoły." src="/images/pomoc_ciemna.png" />','<td>średnia śród 2</td>',$res);
$res = str_replace('<td  class="center">&nbsp;<img class="tooltip helper-icon" alt="Podgląd średniej ocen został<br/>wyłączony przez administratora szkoły." title="Podgląd średniej ocen został<br/>wyłączony przez administratora szkoły." src="/images/pomoc_ciemna.png" />','',$res);
$res = str_replace('<td colspan="2"  class="colspan center"><span>Koniec roku</span></td></tr>','',$res);
//tu znikają style, wyciągam same tabelki
preg_match_all('/<table class="decorated stretch">([\s\S]*)/',$res,$code);
//usunięcie kodu nad tabelkami
$res = $code[0][0];
$res = preg_replace('/<script type="text\/javascript">([\s\S]*?)g ocen<\/option><\/select><\/td><\/tr><\/table><\/td><\/tr><\/table><\/div>/','',$res);
//usunięcie kodu między tabelkami
$res = preg_replace('/<div class="legend left stretch">([\s\S]*)/','',$res);
//usunięcie kodu pod tabelkami
$res = preg_replace('/<img[^>]*>/','',$res);
//usunięcie zdjęć z tabelki
$res = str_replace('<table class="decorated stretch"','znacznik <table class="decorated stretch"',$res);
$res = preg_replace('/znacznik/','',$res,1);
preg_match_all('/(?<=znacznik).*/s', $res, $tabelka_punktowe);
///////////pod $res są obie tabelki, pod $tabelka_punktowe tylko punktowe
$tabelka_punktowe = $tabelka_punktowe[0][0];
$tabelka_punktowe = preg_replace("/<tr class='l.*?Brak ocen.*?<\/table><\/td><\/tr>/",'',$tabelka_punktowe);
//last update: jak usunąć wszystkie z brak ocen, to coś nie działa, spróbuj usunąć line terminators ale nie jestem pewna czy to to
echo $tabelka_punktowe;
exit(0);





$res = str_replace(["\n","\r","\n\r","\t"],"",$res);
preg_match_all("|<td class=.no-border-top spacing.>.+&nbsp;<\/td><\/tr><\/tfoot><\/table>|", $res, $code);
// $css=[
//     "https://synergia.librus.pl/LibrusStyleSheet2.1674858050.css",
//     "https://synergia.librus.pl/LibrusStyleSheet2Light.1637964526.css"
// ]; //włącz librusa na firefoxie, edytor stylów i skopiuj tu wszystkie linki!!!!!
// echo '<link rel="stylesheet" href="./style.css">';
// echo '<style>';
// foreach($css as $a) {
//     echo file_get_contents($a);
// }
// echo '</style>';
echo ($code[0][0]);
//start: <td class="no-border-top s 
//end: &nbsp;<\/td><\/tr><\/tfoot><\/table>


function get($url)
{
    global $ch;
    curl_setopt($ch, CURLOPT_URL, $url); // "The URL to fetch."
    $res = curl_exec($ch);
    return $res;
}
 
function post($fields, $url)
{
    global $ch;
    $POSTFIELDS = http_build_query($fields);
    curl_setopt($ch, CURLOPT_POST, 1); // "true to do a regular HTTP POST."
    curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS); // "The full data to post in a HTTP "POST" operation."
    curl_setopt($ch, CURLOPT_URL, $url);
    $res = curl_exec($ch);
    return $res;
}

