<?php
$conn = pg_connect("host=localhost dbname=dicom user=postgres password=postgres");
$sql = "SELECT valor_actual,fecha_actualizacion FROM dicom WHERE id_dicom = (SELECT max(id_dicom) FROM dicom)";
$res = pg_query($conn, $sql);
$val = pg_fetch_array($res);
$dolar_ant = floatval($val["valor_actual"]);
$fecha_ant = $val["fecha_actualizacion"];
$f = explode("-",$fecha_ant);
$f1 = explode(" ",$f[2]);
$fecha = $f1[0]."/".$f[1]."/".$f[0];
$doc = new DomDocument;
$doc->validateOnParse = true;
@$doc->loadHtml(file_get_contents('http://www.bcv.org.ve/'));
$bcv = $doc->getElementById('dolar');
$div_dolar = print_r($bcv,true);
$val_dolar = explode( '[textContent]',$div_dolar);
$dolar = floatval(trim(str_replace(')','',str_replace('USD','',str_replace(' X 1.00','',str_replace(',','.',str_replace('.','',str_replace('=>','',$val_dolar[1]))))))));
if ($dolar != $dolar_ant) {
	$mont_var = $dolar - $dolar_ant;
	$por_var = (($dolar * 100) / $dolar_ant)-100;
	$sql1 = "INSERT INTO dicom (valor_actual,valor_previo,monto_variacion,por_variacion) VALUES (".$dolar.",".$dolar_ant.",".$mont_var.",".$por_var.");";
	$res1 = pg_query($conn,$sql1);
	echo "SE HA ACTUALIZADO EL VALOR DEL DICOM DESDE LA PAGINA DEL BCV DE ".number_format($dolar_ant,2,",",".")." A ".number_format($dolar,2,",",".");
} else {
	echo "SE HA MANTENIDO EL PRECIO DEL DOLAR DESDE EL ".$fecha;
}
pg_close($conn);
?>