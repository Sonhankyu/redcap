<?php
$connect = mysqli_connect(
    '127.0.0.1',
    'root',
    'qwer1234',
    'pacsdb',
        '3307');

if (mysqli_connect_errno()) {
    echo "Failed MySQL Connect : " . mysqli_connect_error();
}

$sql = "SELECT pacsdb.files.filepath
        FROM pacsdb.files AS files
        INNER JOIN pacsdb.instance AS inst ON files.instance_fk = inst.pk
        INNER  JOIN pacsdb.series AS series ON inst.series_fk = series.pk
        WHERE series.series_iuid";

$result = mysqli_query($connect, $sql);

while ($row = mysqli_fetch_array($result)) {
    $url_arr[] = '"./files/' . $row[0] . '"';
}
$url = implode(", ", $url_arr);
echo $url;

//while($row = mysqli_fetch_array($result)){
//    array_push($url_arr, $row["filepath"]);
//}
//foreach ($url_arr as $value){
//    $url[] = '"./files/' . $value . '"';
//}
//$urlStr = implode(", ", $url);
//echo $urlStr;

?>
<script>
    var urls = [<?=$url?>];
    console.log(urls);
</script>
