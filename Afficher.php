<?php
session_start();
include "dbConn.php";

?>
<!DOCTYPE html>
<html lang="Fr-fr">

<head>
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '\projet\Requirements\head.html'; ?>
    <link rel="stylesheet" href="\projet\Requirements\tablestyle.css">
    <title>Affichage</title>
</head>

<body>
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '\projet\Requirements\header.php';    ?>
    <form class="box" action="./Afficher.php" method="post">
        <h1>Afficher</h1>
        <?php
        $sieges = array();
        $Nbsieges = array(15, 12, 10, 9, 7, 13, 8, 8, 14, 10, 9, 7, 7, 8, 8, 6, 10, 9, 6, 10, 9, 7, 7, 8);
        $i = 0;
        $j = 0;
        for ($i = 0; $i < 24; $i++) {
            $sum = 0;
            for ($j = 0; $j < 7; $j++) {
                $x = $j + 1;
                $y = $i + 1;
                $sth = $db->prepare("SELECT
SUM(voix.nombrevoix) Total,
gouvernorat.nomGouvernorat,
partipolitique.nomParti
FROM
voix
INNER JOIN partipolitique ON voix.idParti = partipolitique.idParti
INNER JOIN gouvernorat ON voix.idGouvernorat = gouvernorat.idGouvernorat
where voix.idParti ='$x' and voix.idGouvernorat ='$y'");
                $sth->execute();
                $resultat = $sth->fetchAll(PDO::FETCH_ASSOC);
                $sieges[$i][$j] = $resultat[0]['Total'];
                $sum += $resultat[0]['Total'];
                if ($j == 6) {
                    $sieges[$i][7] = $Nbsieges[$i];
                    $sieges[$i][8] = $sum;
                }
            }
        }

        $rest = array();
        for ($i = 0; $i < 24; $i++) {
            $quotient = intval($sieges[$i][8] / $sieges[$i][7]);
            for ($j = 0; $j < 7; $j++) {
                $rest[$i][$j] = $sieges[$i][$j] % $quotient;
                $sieges[$i][$j] = intval($sieges[$i][$j] /= $quotient);
            }
        }
        for ($i = 0; $i < 24; $i++) {
            $s = array();
            $arr = 0;
            for ($j = 0; $j < 7; $j++) {
                $s = array_slice($sieges[$i], 0, 7);
                $som = array_sum($s);
                //echo $som . "<br>";
                if ($sieges[$i][7] > $som) {
                    //echo "Nombre siege " . $sieges[$i][7] . "<br>";
                    //echo "Loop Time " . intval($sieges[$i][7] - $som) . "<br>";
                    for ($k = 0; $k < intval($sieges[$i][7] - $som); $k++) {
                        $index = array_search(max($rest[$i]), $rest[$i]);
                        //echo "Max sur ligne" . max($rest[$i]) . "<br>";
                        //echo "ligne incrementée" . $sieges[$i][$index] . "<br>";
                        $sieges[$i][$index] += 1;
                        //echo "ligne incrementée" . $sieges[$i][$index] . "<br>";
                        unset($rest[$i][$index]);
                    }
                }
            }
        }
        $sth = $db->prepare("SELECT * FROM gouvernorat");
        $sth->execute();
        $sth1 = $db->prepare("SELECT * FROM partipolitique");
        $sth1->execute();
        /*Retourne un tableau associatif pour chaque entrée de notre table
 *avec le nom des colonnes sélectionnées en clefs*/
        $resultat0 = $sth->fetchAll(PDO::FETCH_ASSOC);
        $resultat1 = $sth1->fetchAll(PDO::FETCH_ASSOC);
        echo "<center>Résultats des elections par gouvernorats</center><br><br>";
        $str = "";
        $str = $str . "<center><table class='styled-table' border=1>";
        $str = $str . "<caption>Nombre de sièges remportés par chaque parti dans chaque gouvernorat </caption>";
        $str = $str . "<thead>";
        for ($j = 0; $j < 7; $j++) {
            if ($j == 0) {
                $str = $str . "<td></td>";
            }
            $str = $str . "<td><b>" . $resultat1[$j]["nomParti"] . "</b></td>";
            if ($j == 6) {
                $str = $str . "<td><b>Total des Sieges</b> </td>";
            }
        }
        $str = $str . "</thead><tbody>";
        for ($i = 0; $i < 24; $i++) {
            $str = $str . "<tr>";
            $str = $str . "<td><b>" . $resultat0[$i]["nomGouvernorat"] . "</b></td>";
            for ($j = 0; $j < 7; $j++) {
                $str = $str . "<td>" . $sieges[$i][$j] . "</td>";
                if ($j == 6) {
                    $str = $str . "<td><b>" . $sieges[$i][7] . "</b></td>";
                }
            }
            $str = $str .  "</tr>";
        }

        $str = $str . "</tbody></table></center><br><br>";
        echo $str;
        echo "<center>Résultats des elections par parti politique</center><br><br>";
        $sth = $db->prepare("SELECT * FROM partipolitique");
        $sth->execute();
        $resultat = $sth->fetchAll(PDO::FETCH_ASSOC);
        $str = "";
        $str = $str . "<center><table class='styled-table' border=1>";
        $str = $str . "<caption>Nombre de sièges remportés par chaque parti dans chaque gouvernorat </caption>";
        $str = $str . "<tr>";
        $somme = 0;
        for ($j = 0; $j < 7; $j++) {
            $str = $str . "<td><b>" . $resultat[$j]["nomParti"] . "</b></td>";
        }
        $str = $str . "</tr>";
        $str = $str . "<tr>";

        for ($j = 0; $j < 7; $j++) {
            //$str = $str . "<td><b>" . $resultat[$j]["nomParti"] . "</b></td><tr>";
            //$str = $str . "</th>";
            for ($i = 0; $i < 24; $i++) {
                $somme += $sieges[$i][$j];
            }
            $str = $str . "<td>" . $somme . "</td>";
            $somme = 0;
        }
        $str = $str . "</tr>";
        $str = $str . "</table></center>";
        echo $str;
        /*print "<pre>";
        print_r($sieges);
        print "</pre><br>";*/
        ?>
    </form>
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '\projet\Requirements\footer.php';
    ?>
</body>

</html>