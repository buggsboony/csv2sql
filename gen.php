<?php


$correspondance="correspondances.json";
$content = file_get_contents($correspondance);
//echo "json file :"; var_dump($content);

$correspondances = json_decode($content);

//echo " corresp JSON: ";var_dump( $correspondances);
if($correspondances===null)
{
    echo "Oups, JSON invalide";
}




function gen_create($tablename, $columns_infos)
{
    $create = "
/******  Création de la table '$tablename' ***********/
    CREATE TABLE `$tablename` ( \n ";
    $lines = array();
    //Parcourir les excels letters 
    $mores=array(); //les constraints et autres keys ...
    $columns = array();
    $datatypes = array();
    foreach($columns_infos as $excel_letter => $infos):
        //var_dump($excel_letter);
        //var_dump($infos);
        $column = $infos->column;
        $datatype = $infos->datatype;
        if( isset( $infos->more ) ) 
        {
            $more = $infos->more;
            $clean_more = str_replace("{{column}}",$column,$more);
            $mores[] = $clean_more; //Empiler
        }
        $lines[]= "`$column` ".$datatype;        
    endforeach;

    $create.= implode(",\n", $lines)."\n";
    if( count($mores)>0 )
    {  
        $create.= ",". implode(",\n", $mores)."\n";
    }
    $create .="\n);\n";

    return $create;
}//gen_create


function gen_insert()
{


}//gen_insert




$creates=array();
$inserts=array();
//Parcourir les correspondances
foreach($correspondances as $tablename => $colsinfo):
 
    $create_table = gen_create($tablename, $colsinfo);
    echo "create table $tablename: \n ";var_dump($create_table); //die("pause 1 tab");
    $creates[] = $create_table;
    //Récupérer et ranger par table, la structure pour préparer les colonnes de l'insert
endforeach;


$csv_file="";

$row = 1;
if (($handle = fopen("test.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        echo "<p> $num champs à la ligne $row: <br /></p>\n";
        $row++;
        for ($c=0; $c < $num; $c++) {
            echo $data[$c] . "<br />\n";
        }
    }
    fclose($handle);
}
?>