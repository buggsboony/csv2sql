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
    /******  Création de la table $tablename ***********/
    CREATE TABLE `$tablename` ( ";
    $lines = array();
    //Parcourir les excels letters 
    $mores=array(); //les constraints et autres keys ...
    $columns = array();
    $datatypes = array();
    foreach($columns_infos as $excel_letter => $infos):
        //var_dump($excel_letter);
        //var_dump($infos);
        $column = $infos["column"];
        $datatype = $infos["datatype"];
        if( isset( $infos["more"] ) ) 
        {
            $more = $infos["more"];
            $mores[] = $more; //Empiler
        }
       $line[]= "";
    endforeach;

    $create .=");\n";
}//gen_create




$creates=array();
$inserts=array();
//Parcourir les correspondances
foreach($correspondances as $tablename => $colsinfo):
 
    $creates[]= gen_create($tablename, $colsinfo);

endforeach;

?>