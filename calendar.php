<?php


/* API
 * params: 
 *  - district:
 *    type: int
 *    range: 1..4
 *
 *  - categories:
      type: string
      values: List of comma separated values
      possible values: "Abfall", "Biogene Abfälle", "Christbäume", "Grubengut", "Häckseldienst", "Karton", "Metall", "Papiersammlung", "Sonderabfall"
 */

 include("common.php");
 
 
 
 function parse_input() {
    global $max_district_id, $available_categories;
    $district = null;
    $categories = [];
 
    
    /* Parse District */
    if (isset($_GET["district"])) {
        if (is_numeric($_GET["district"]) and $_GET["district"] <= $max_district_id) {
            $district = "Kreis " . $_GET["district"];
        }
        else {
                throw new Exception('Ung&uuml;ltiger Kreis ausgew&auml;hlt!');
        }
    }
    else {
        throw new Exception('Kein Kreis ausgewählt!');
    }
    
    if ($district == null) {
            throw new Exception('Ung&uuml;ltiger Kreis ausgew&auml;hlt!');
    }

    
    /* Parse categories */
    if (isset($_GET["categories"])) {
        $categorie_data = explode(",", $_GET["categories"]);
        foreach ($categorie_data as $c) {
//             echo("$c<br>");
            if (in_array($c, $available_categories)) {
                array_push($categories, $c);
            }
            else {
            // Disabled exception dueto disables "Biogene Abfälle"
//                 throw new Exception("Categories not valid ('$c' is unknown)!");
            }
        }
    }
    else {
        throw new Exception('Keine Kategorie ausgewählt!');
    }
    
    if ($categories == []) {
        throw new Exception('Keine Kategorie ausgewählt!');
    }
    
    
    return ["district" => $district, "categories" => $categories];
 }
 

 
 function get_ics($input) {
    global $database_file, $ics_header, $ics_footer;
    
    $ics = "";
    
    $data = file_get_contents($database_file);
    $data = json_decode($data,true);
//     echo("<pre>");
//     echo(count($data["Kreis 2"]));
//     print_r($data);
//     print_r($data["Kreis 2"]);
//     print_r(count($data[$input["district"]]["categories"]));
//     print_r($data[$input["district"]]["categories"]);
//     exit();
    
    
    
    
    
    /* All data in district */
    $district_data = $data[$input["district"]]["categories"];
    
    
    foreach ($district_data as $category => $category_data) { // Each category
//         echo("$category:\n");
        
        if (in_array($category, $input["categories"])) {
//             print_r($category_data);
            
            foreach($category_data["events"] as $event) {
//                 print_r($event);
//                 echo($event["date"]);

//                 $ics .= $event["vevent"] . "\n\n";
                $eventData = $event["vevent"];
                
                /* adjust some data */
                $eventData = str_replace($input["district"] . "\\, ", "", $eventData); // Remove "Kreis 1\,"
                
                /* Append to calendar */
                $ics .= $eventData . "\n";
                
//                 echo("## event\n\n\n");
            }
//             
// 
        }
//         echo("## category_data\n\n\n");
    }
    
    
    
    /* Add Header and Footer */
//     $ics = $ics_header . "\n\n" . $ics . $ics_footer;
    $ics = $ics_header . "\n" . $ics . $ics_footer;
    
      
    
     /* RFC 5545 expects CRLF, see chapter 3.1. Content Lines */
     // TODO there is still an error: Lines not delimited by CRLF sequence near line # 1
//      $ics = str_replace("\n", "\r\n", $ics);
    
//     echo($ics);
    return $ics;
 
 }
 
 

 
 try {
    $input = parse_input();
 }
 catch (Exception $e) {
    echo("Es ist ein Problem aufgetreten: " . $e->getMessage());
    exit();
 }
 
 

//  echo("district: " . $input["district"] . "<br>");
//  echo("categories: ");
//  print_r($input["categories"]);



 $ics = get_ics($input);
 
 
  header('Content-type: text/calendar');
  echo($ics);


?>
