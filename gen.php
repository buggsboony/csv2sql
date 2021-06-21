<style>

    textarea{

        width : 100%;
        border:solid 1px #212444;
        background-color: #212744;
        color: #3cc632;
        height: 100%;
    }

</style>



<textarea>
<?php
// -------------  http://localhost:8080/gen.php

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

function getColumns($columns_infos)
{
    $columns=array();    
    foreach($columns_infos as $excel_letter => $infos):
        //var_dump($excel_letter);
        //var_dump($infos);
        $column = $infos->column;
        $columns[$excel_letter]=$column;
    endforeach;
    return $columns;
}//getColumns


function gen_insert( $tablename, $insert_values)
{
    if( count( $insert_values ) <0){ echo " Pas de donnée, gen_insert renvoie null."; return null;}
    $columns = array_keys( $insert_values[0] );
    $columns_str = "`".implode("`,`",$columns)."`";
$sql = "/* Insertion de donnée dans la table '$tablename' */
INSERT INTO `$tablename`
($columns_str) VALUES
";
    //var_dump( $sql ); die("check sql");
    $values=array();
    $sql_values = array();
    foreach($insert_values as $col=>$assoc_arr)
    {        
        $row_values = array_values($assoc_arr);
         $one_row_values = "('". implode("','", $row_values)."')";        
         //var_dump( $one_row_values ); die("check one_row_values");
         $sql_values [] = $one_row_values;
    }//next 
    $values_str = implode("\n,", $sql_values)."\n";    
    $sql.=$values_str.";";

    return $sql;
}//gen_insert

//convert to excel letter
function excel_letter($i)
{
    return chr($i+64);
}//convert to excel letter


//var_dump( letter_excel("Z") ); die("pause test");
function letter_excel($L)
{
    return ord($L)-64;
}//convert to excel letter

$creates=array();
$inserts=array();
//Parcourir les correspondances
foreach($correspondances as $tablename => $colsinfo):
    $create_table = gen_create($tablename, $colsinfo);
    //echo "create table $tablename: \n ";var_dump($create_table); //die("pause 1 tab");
    $creates[] = $create_table;    
    //Récupérer et ranger par table, la structure pour préparer les colonnes de l'insert
    $columns = getColumns($colsinfo); //Out, variable de récupération des colonnes.    
    $table_columns[$tablename]=$columns;

    //Afficher ça la requete create :
    echo "/********************  CREATE TABLE  ***********************************************/  \n  $create_table\n\n\n";
endforeach;

//echo "table cols:"; var_dump( $table_columns ); die("check columns");


function gen_from_csv( $table_columns, $csv_file="GDom.xls", $tablename = "gdom",  $sep="\t", $data_path ="./data")
{         
    $fullname=$data_path."/".$csv_file;
    $row = 1;
    if(!file_exists($fullname) ){ echo "Le fichier '$csv_file' n'existe pas!\n"; return; }
    $columns_htable = $table_columns[$tablename] ;//Récupérer la correspondance Excel => Colonnes nommées
    $insert_values = array();

    if (($handle = fopen($fullname, "r")) !== FALSE) 
    {
<<<<<<< HEAD
        while (($data = fgetcsv($handle, $max_line=null, "\t")) !== FALSE) {
=======
        while (($data = fgetcsv($handle, $maxlinelen=null, "\t")) !== FALSE) {
>>>>>>> 45a4a85f0e1d7aef2a1e9affcf4a51ee2064eaca
            // $num = count($data);
            // //echo "<p> $num champs à la ligne $row: <br /></p>\n";
            // $row++;
            
            // for ($c=0; $c < $num; $c++) {
            //     //echo $data[$c] . "\n";

            // }
            $datasql = array();
            foreach( $data as $col_index=>$value):
                //echo "col_index="; var_dump($col_index);
                //echo "value="; var_dump($value);
                $L = excel_letter($col_index+1);
                //echo "LETTER="; var_dump($L);
                if( array_key_exists($L, $columns_htable) )
                {
                    $colname = $columns_htable[$L];

                    
                    //Régler le prob d'encodage :
                    //$utf8_line = mb_convert_encoding($line, "Windows-1252", "UTF-8"); //Fonctionne avec presque toutes les tables sauf "diplomes" (/part_line_47_2_.sql) et autres
         
                    $final_value = mb_convert_encoding($value, "UTF-8", "Windows-1252"); //Fonctionne avec Diplomes, tous les acents sont là + encoding "utf8"  OUURAA fonctionne avec toutes les tables
                            
                    $datasql[$colname]= addslashes($final_value);  
                }         
            endforeach;
            //echo " data sql:"; var_dump( $datasql );*
            $insert_values[] = $datasql;
            //if($row>=1)break;
        }
        fclose($handle);
    } //CSV read

    $sql_insert = gen_insert($tablename, $insert_values);

    //echo "sqlinsert : \n"; var_dump( $sql_insert);
    echo "/********************  REQUETE INSERT ************************************/\n   $sql_insert \n\n\n";

}//gen from csv file



//Détection des colonnes automatiquement
function gen_insert_from_csv($csv_file="GDom.xls", $tablename = "gdom", $force_values=array(), $sep=";", $data_path ="./data")
{         
    $fullname=$data_path."/".$csv_file;
    $row = 1;

    //$columns_htable = $table_columns[$tablename] ;//Récupérer la correspondance Excel => Colonnes nommées
    $insert_values = array();

    $lc = 0; $columns=array();
    if (($handle = fopen($fullname, "r")) !== FALSE) 
    {
        while (($data = fgetcsv($handle, $max_line=null, $sep )) !== FALSE) {         
            $datasql = array();      
            $lc++;
            foreach( $data as $col_index=>$value):
                // echo "col_index="; var_dump($col_index);
                // echo "value="; var_dump($value);
                
                $final_value=$value;
                //$final_value = mb_convert_encoding($value, "UTF-8", "Windows-1252"); //Fonctionne avec Diplomes, tous les acents sont là + encoding "utf8"  OUURAA fonctionne avec toutes les tables
                if($lc>1)
                {                             
                    $colname= $columns[$col_index];                    
                    $datasql[$colname]= addslashes($final_value);  
 
                }else
                {
                    //Remplir les colonnes
                    $columns[$col_index]=$final_value;
                }
                 
            endforeach;
            if( $lc>1 ) 
            {
                //LEs ajout fait par le code
                foreach( $force_values as $force_name => $force_value  )
                {
                    $datasql[$force_name] = $force_value;
                }
                //echo " data sql:"; var_dump( $datasql ); die("paus");
                $insert_values[] = $datasql;
            }
            //if($row>=1)break;    
        }//endwhile
        fclose($handle);
    } //CSV read

    $sql_insert = gen_insert($tablename, $insert_values);

    //echo "sqlinsert : \n"; var_dump( $sql_insert);
    echo "/********************  REQUETE INSERT ************************************/\n   $sql_insert \n\n\n";
}//gen from csv file

//gen_from_csv( $table_columns, $csv_file="GDom.xls", $tablename = "gdom",  $sep="\t", $data_path ="./data");

//gen_from_csv( $table_columns, $csv_file="FCod.xls", $tablename = "fcod",  $sep="\t", $data_path ="./data");


$now=date("Y-m-d");
gen_insert_from_csv($csv_file="Navires_export_stationpilotage.csv", $tablename = "stpf_navires",  $force_values=array("dateMaj"=>$now),
 $sep=";", $data_path ="./data");
 

<<<<<<< HEAD
=======
gen_from_csv( $table_columns, $csv_file="FCod.xls", $tablename = "fcod",  $sep="\t", $data_path ="./data");
>>>>>>> 45a4a85f0e1d7aef2a1e9affcf4a51ee2064eaca

?>

</textarea>
