<?
function deg_to_hms($deg)
{
	settype($h, "integer");
	settype($m, "integer");
	settype($s, "double");
	settype($str, "string");
	$deg = $deg/15;
	$h = floor($deg);
 	$deg = ($deg-$h)*60;
 	$m = floor($deg);
 	$deg = ($deg-$m)*60;
 	$s = $deg;
 	$str = "";
 	$str = sprintf("%02d:%02d:%05.3f", $h, $m, $s);
 	return $str;
}

function deg_to_gms($deg)
{
	settype($h, "integer");
	settype($m, "integer");
	settype($s, "double");
	settype($str, "string");
	$h = floor($deg);
 	$deg = abs(($deg-$h)*60);
 	$m = floor($deg);
 	$deg = ($deg-$m)*60;
 	$s = $deg;
 	$str = "";
 	$str = sprintf("%02d:%02d:%05.3f", $h, $m, $s);
 	return $str;
}

 
/* Соединяемся с базой данных */
$hostname = "localhost"; // название/путь сервера, с MySQL
$username = "fitsreader"; // имя пользователя (в Denwer`е по умолчанию "root")
$password = "fitsreader"; // пароль пользователя (в Denwer`е по умолчанию пароль отсутствует, этот параметр можно оставить пустым)
$dbName = "ccdobs_nap"; // название базы данных
$observerName=urldecode($_GET['observerName']);
$date0=$_GET['date0'];
$date1=$_GET['date1'];

$daysNum = 0;
$obsNum = 0;
$obsNum1=0;
$durTot=0;
$longDaysNum=0;
$expTot=0;


 
/* Таблица MySQL, в которой хранятся данные */
$table = "fitsheader";
$tableObs = "observers";
 
/* Создаем соединение */
mysql_connect($hostname, $username, $password) or die ("Не могу создать соединение");
 
/* Выбираем базу данных. Если произойдет ошибка - вывести ее */
mysql_select_db($dbName) or die (mysql_error());
//mysql_query('set names utf8');

//$query = "SELECT observer, realName FROM $tableObs WHERE realName = $observerName ";
$query = "SELECT * FROM ".$tableObs;// WHERE realName = 'Бережной А.А.' ";
echo($query);
//echo("\n<br>");
$res = mysql_query($query) or die(mysql_error());

$query = "SELECT DISTINCT obsDate, observer FROM $table WHERE observer=";
while ($row = mysql_fetch_array($res)) {
	if($row['realName']==$observerName) $query += " and observer=".$row['observer'];
//	obsList[] = $row['observer'];	
	
	}
	
$query += " and obsDate>$date0 and obsDate<$date1 order by obsDate"; 	
 
/* Составляем запрос*/
//$query = "SELECT DISTINCT obsDate, observer FROM $table WHERE observer=$observer and obsDate>$date0 and obsDate<$date1 order by obsDate";

echo($query);
 
/* Выполняем запрос. Если произойдет ошибка - вывести ее. */
$res = mysql_query($query) or die(mysql_error());
 
/* Выводим данные из таблицы */
echo ("
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
 
<head>
 
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
 
    <title>Наблюдения $observer</title>
 
<style type=\"text/css\">
<!--
body { font: 12px Georgia; color: #666666; }
h3 { font-size: 16px; text-align: center; }
table { width: 700px; border-collapse: collapse; margin: 0px auto; background: #E6E6E6; }
td { padding: 3px; text-align: center; vertical-align: middle; }
.buttons { width: auto; border: double 1px #666666; background: #D6D6D6; }
-->
</style>
 
</head>
 
<body>
 <a href=\"index.php\" ><img src=\"img/68495187_diary.jpg\" width=\"800\" height=\"150\" alt=\"\" /></a> <br />
<h3>Наблюдения $observer</h3>
<h3>За период $date0 - $date1<h3>
 
<table border=\"1\" cellpadding=\"0\" cellspacing=\"0\">
 <tr style=\"border: solid 1px #000\">
  <td align=\"center\"><b>Дата наблюдения</b></td>
  <td align=\"center\"><b>Кол-во кадров</b></td>
  <td align=\"center\"><b>Длительность</b></td>
  
 </tr>
");
 
/* Цикл вывода данных из базы конкретных полей */
while ($row = mysql_fetch_array($res)) {

$obsDate1 = $row['obsDate'];
	$query1 = "SELECT obsDate, DATETIMEOBS, observer, mjdEpoch, exptime FROM $table WHERE observer=$observer and obsDate='$obsDate1' order by DATETIMEOBS";
//echo $query1."<br>";
	$res1 = mysql_query($query1) or die(mysql_error());
	$obsNum1=0;
	$dur1=0;
	while ($row1 = mysql_fetch_array($res1)) {
			if($obsNum1==0)
			{
				$time0=$row1['mjdEpoch'] - ($row1['exptime']/60.0/60.0/24.0/2.0);
				$time1=$time0 + ($row1['exptime']/60.0/60.0/24.0/2.0);
			}
			else $time1=$row1['mjdEpoch'] + ($row1['exptime']/60.0/60.0/24.0/2.0);
			$obsNum1 += 1;
			$expTot += ($row1['exptime'])/60.0/60.0;
		}
		
		$daysNum++;
	$obsNum += $obsNum1;
	$dur1 = ((float)$time1 - (float)$time0)*24.0;
	if($dur1>8) $longDaysNum++;

	$durTot += $dur1;
    echo "<tr>\n";
    echo "<td><a href=\"daily.php?obsDate='".$row['obsDate']."'\">".$row['obsDate']."</td>\n";
    echo "<td>".$obsNum1."</td>\n";
echo "<td>".$dur1."</td>\n";
    echo "</tr>\n";
}

echo "<tr style=\"border: solid 1px #000 \" bgcolor=\"#7de890\">";
echo "<td colspan=3>Итого:<br></tr>";
echo "<tr style=\"border: solid 1px #000 \">";
echo "<td colspan=2>Наблюдательных ночей</td><td>".$daysNum."</td></tr>";
echo "<tr style=\"border: solid 1px #000 \">";
echo "<td colspan=2>Из них длительных (>8 часов)</td><td>".$longDaysNum."</td></tr>";
echo "<tr style=\"border: solid 1px #000 \">";
echo "<td colspan=2>Общая длительность наблюдений (часов)</td><td>".$durTot."</td></tr>";
echo "<tr style=\"border: solid 1px #000 \">";
echo "<td colspan=2>Получено кадров</td><td>".$obsNum."</td></tr>";
echo "<tr style=\"border: solid 1px #000 \">";
echo "<td colspan=2>Общая длительность экспозиций (часов)</td><td>".$expTot."</td></tr>";
echo "<tr style=\"border: solid 1px #000 \">";
echo "<td colspan=2>Общая длительность экспозиций/наблюдений</td><td>".($expTot/$durTot)."</td></tr>";

/*
echo "<tr style=\"border: solid 1px #000 \" bgcolor=\"#7de890\">";
echo "<td>".$daysNum."</td>\n";
echo "<td>".$obsNum."</td>\n";
echo "<td>".$durTot."</td>\n";
echo "</tr>\n";
 */
echo ("</table>\n");
 
/* Закрываем соединение */
mysql_close();
 
/* Выводим ссылку возврата */
echo ("<div style=\"text-align: center; margin-top: 10px;\"><a href=\"index.php\">На главную</a></div>");
 
?>
