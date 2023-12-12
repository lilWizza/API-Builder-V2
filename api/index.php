<?php


require 'vendor/autoload.php';


    Flight::route('/', function(){
        require "ui/index.html";
    });
    
    Flight::route('/save/@id:[0-9]+', function ( $id ) {
        if (isset( $_SERVER['HTTP_ORIGIN'] )) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }
    
        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
    
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
            exit(0);
        }
        
        $json = file_get_contents('php://input', TRUE);
     
        $payload_data = json_decode($json, true);

        foreach( $payload_data['config'] as $key => $value ){
            $remove_whitespace = explode( " ", $value );
            is_array( $remove_whitespace ) ? ( $new_value = implode("_",$remove_whitespace) ) : $new_value = $value;
                   
            switch ( $key ) {
                case 'programming_langauge' : 
                    $key = "programming-language";
                    $payload_data['config'][$key] = $value;
                break;
                case 'app_name' : 
                case 'database_name' : 
                case 'database_username' : 
                    $payload_data['config'][$key] = $new_value;
                break;
            }
        }  
        
        $whitelisted_data_payload = [];
        foreach( $payload_data['data'] as $data_key => $data_value ){
            
            $name_wspace = explode( " ", $data_value['tableName'] );
            is_array( $name_wspace ) ? ( $data_value['tableName'] = implode( "_", $name_wspace) ) : $data_value['tableName'] = $data_value['tableName'];
            
            $controller_name_wspace = explode( " ", $data_value['controller'] );
            is_array( $controller_name_wspace ) ? ( $data_value['controller'] = implode( "_", $controller_name_wspace) ) : $data_value['controller'] = $data_value['controller'];

            $model = $data_value['model'];
            if( $model ) {
                foreach( $model as $model_key => $model_value ) {
                    if( is_array( $model_value ) ) {
                        foreach( $model_value as $m_key => $m_val ) {
                            if( ! is_array( $m_val ) ) {
                                $value_w_space = explode( " ", $m_val );
                                is_array( $value_w_space ) ? ( $m_val = implode( "_", $value_w_space) ) : $m_val = $m_val ;
                                if( ! is_numeric( $m_key ) && strpos($m_key, " ") !== false ) {
                                    $remove_key_w_space = explode( " ", $m_key );
                                    if( is_array( $remove_key_w_space ) ) { 
                                        $model_value[implode( "_", $remove_key_w_space)] = $m_val ;
                                        unset($model_value[$m_key]);
                                    }
                                } else if ( is_numeric( $m_key ) ) {
                                    $model_value[$m_key] = $m_val;
                                }  
                            } else if ( is_array( $m_val ) ) {
                                foreach( $m_val as $k => $v ) {
                                    if( is_array( $v ) ) {
                                        foreach ( $v as $f_k => $f_v ) {
                                            $final_replacement = explode( " ", $f_v );
                                            is_array( $final_replacement ) ? ( $v[$f_k] = implode( "_", $final_replacement) ) : $v[$f_k] = $f_v ;
                                
                                        }
                                    }
                                 
                                    $m_val[$k] = $v;
                                }
                                $model_value[$m_key] =  $m_val;

                            }

                        }

                    }
                    $model[$model_key] = $model_value;
                }
                $data_value['model'] = $model;
            }  
            $whitelisted_data_payload[] = $data_value;
        } 
        $payload_data['data'] = $whitelisted_data_payload;  


        chdir( "../");
        $root_dir = getcwd();
        
       
        // error_log(print_r($payload_data, TRUE));
        if ( !is_dir( $root_dir . '/uploads' )) {
            mkdir( 'uploads' );
        }
        chdir("uploads");
        $uploads_root = getcwd();
       
        // Creating seperate folders for project type
        
        $language = $payload_data['config']['programming-language'];
        
        $uc_lang = ucfirst( $language );
       
        $path = $uploads_root ."/" . $uc_lang;
        if ( !is_dir( $path )) {
            mkdir( $path );
        }
    
        if ( !file_exists( $path .'/' . $id . '.json')) {
            fopen( $path . '/' . $id . '.json', "w");
        }
    
        file_put_contents( $path. '/' . $id . '.json', json_encode($payload_data));
    
        echo json_encode([
            'id' => $id
        ]);
        http_response_code(200);
        exit;
    }, 'POST');
    

    Flight::route('/retrieve/@id:[0-9]+', function ( $id ) {
        if (isset( $_SERVER['HTTP_ORIGIN'] )) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }
    
        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
    
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
            exit(0);
        }
        chdir( "C:\\xampp\\htdocs\\API_builder\\uploads\\Laravel");
        
        $search_dir = getcwd();
        $to_match = $id . ".json";
        $json_matches = [];
        $it = new RecursiveDirectoryIterator($search_dir);
        foreach(new RecursiveIteratorIterator($it) as $file) {
            $FILE = array_flip(explode('.', $file));
            if (isset($FILE['json']) ) {
                $dta = explode("\\",$file);
                $count = count($dta);
                $json_name = $dta[$count-1];
                // la yeai eauta bug ho send ss and this code to me too
                if(strpos($json_name, $to_match) !== false)
                {
                    $json_matches[] = $json_name ;
                }
            }
        }
        $json_total = count( $json_matches );
        $json = $json_matches[ $json_total-1 ];
        
        $configuration = file_get_contents( $json );
        echo $configuration;
        http_response_code(200);
        exit;
    }, 'GET');

    Flight::route('/build_project/@id:[0-9]+', function ( $id ) {
        
        if (isset( $_SERVER['HTTP_ORIGIN'] )) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }
    
        $json = file_get_contents('php://input', TRUE);
        $payload_data = json_decode($json, true);
        $language = $payload_data['config']['programming-language'];
      
        chdir( "../uploads");
        $root_dir = getcwd();
       
        
        $uc_lang = ucfirst( $language );
       
        $path = $root_dir ."/" . $uc_lang;
        $json_data = file_get_contents( $path. '/' . $id . '.json');
       
        chdir("../translations");
        
        include_once(getcwd() . "/Application_builder.php");
        
        $app = new Application_builder( $json_data );
        
        $zip_link = $app->build();

        echo json_encode([
            'zip_link' => $zip_link
        ]);
        http_response_code(200);
        exit;
    }, 'POST');
    
    Flight::route('/download_zip/@file:[\w\W.]+', function ($filename) {
       
        if (isset( $_SERVER['HTTP_ORIGIN'] )) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }
        $filename = "{$filename}.zip";
        chdir("../PastProjects/");
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: 0");
            header('Content-Disposition: attachment; filename="'.basename($filename).'"');
            header('Content-Length: ' . filesize($filename));
            header('Pragma: public');

            //Clear system output buffer
            flush();

            //Read the size of the file
            readfile($filename);

            //Terminate from the script
        echo json_encode([
            'success' => "success"
        ]);
        http_response_code(200);
        exit;
    }, 'GET');
    Flight::route('/recommend', function () {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }
    
        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
    
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
            exit(0);
        }
    
        $json = file_get_contents('php://input', TRUE);
    
        $payload_data = json_decode($json, true);
    
        chdir("../");
    
        chdir("uploads");
        $uploads_root = getcwd();
        $language = $payload_data['config']['programming_langauge'];
    
        $uc_lang = ucfirst($language);
    
        $path = $uploads_root . "/" . $uc_lang;
        $files_list = scandir($path);
        // $whitelisted_json = [];
        $highest_points = 0;
        $selected_file = "";
        $matched_fields = " ";
       
        for ($i = 2; $i < count($files_list); $i++) {
            $json_we_have = file_get_contents($path . "/" . $files_list[$i]); // change delimeters
            //file name
            $file_name = explode(".", $files_list[$i])[0];
            $data = (array)json_decode($json_we_have);
    
            $points_count = 0;
    
            // Code to match case the values 1st from the configuration
            $algorithm = $payload_data['config']['algorithm'];
            $match_percent = 0;
            // error_log(print_r($algorithm, TRUE));
            switch ($algorithm) {
                case 'similar_text':
                    similar_text($data['config']->app_name, $payload_data['config']['app_name'], $match_percent);
                    break;
    
                case 'levenshtein':
                    $match_percent = levenshtein($data['config']->app_name, $payload_data['config']['app_name']);
                    break;
    
                case 'cosine':
                    $match_percent = cosineSimilarity($data['config']->app_name, $payload_data['config']['app_name']);
                    error_log("For File : " .strval($file_name) ." :- App Name Matching Percent of Cosine Algorithm  :- ".print_r($match_percent, TRUE));

                    break;
    
                default:
                    // Default to similar_text
                    similar_text($data['config']->app_name, $payload_data['config']['app_name'], $match_percent);
                    break;
            }
    
            if ($match_percent > 20) {
                $points_count += 1;
            }
    
            // Making more tests here and adding the points to the json
            // Now will be comparing the fields and see what point the json will get 
            $old_data_array = (array)$data['data'];
    
            foreach ($payload_data['data'] as $to_comp_key) {
    
                foreach ($old_data_array as $arr) {
                    $arr = (array)$arr;
    
                    $table_match_percent = 0;
    
                    switch ($algorithm) {
                        case 'similar_text':
                            similar_text($to_comp_key['tableName'], $arr['tableName'], $table_match_percent);
                            break;
                        case 'levenshtein':
                            $table_match_percent = levenshtein($to_comp_key['tableName'], $arr['tableName']);
                            break;
                        case 'cosine':
                            $table_match_percent = cosineSimilarity($to_comp_key['tableName'], $arr['tableName']);
                            error_log("For File : " .strval($file_name) ." :- Table Name Matching Percent of Cosine Algorithm :- ".print_r($table_match_percent, TRUE));
                            break;
                        default:
                            // Default to similar_text
                            similar_text($to_comp_key['tableName'], $arr['tableName'], $table_match_percent);
                            break;
                        
                        
                    
                        
                    }
                    if ($table_match_percent > 0) {
                        $points_count += 1;
                }
    
                    if ($table_match_percent >= 1) {
    
                        $avg_points = 0;
                        $old_data_model = $arr['model'];
                        $old_fields = $old_data_model->fields;
    
                        if ($old_fields) {
                            foreach ($old_fields as $key => $value) {
                                foreach ($to_comp_key['db_fields'] as $j => $db_field) {
                                    $field_match_percent = 0;
    
                                    switch ($algorithm) {
                                        case 'similar_text':
                                            similar_text($db_field, $key, $field_match_percent);
                                            break;
    
                                        case 'levenshtein':
                                            $field_match_percent = levenshtein($db_field, $key);
                                            break;
    
                                        case 'cosine':

                                            $field_match_percent = cosineSimilarity($db_field, $key);
                                            
                                            break;
    
                                        default:
                                            similar_text($db_field, $key, $field_match_percent);
                                            break;
                                    }
                                    error_log("For File : " .strval($file_name) ." :-  Field Name Matching Percent of Algorithm for field :- ".$db_field .print_r($field_match_percent, TRUE));
                                    if ($field_match_percent > 0) {
                                        $matched_fields .= $db_field . " , ";
                                        $avg_points += 1.25;
                                    }
                                }
                            }
    
                            $points_count += $avg_points / count($to_comp_key['db_fields']);
                        }
                    }
                }
            }
            error_log("Points Count for this file ". $file_name . " : - : ". strval($points_count));
            //compre $points_coubt with $highest_points and if is higher set it to it
            if (!$highest_points || $points_count > $highest_points) { 
                $highest_points = $points_count;
                error_log("file got match with ".$algorithm ."in file ". $file_name.".json");
                $selected_file = $file_name.".json";
                //get file contents and add value to $selected app, table and field lists

            }
        }
        error_log("The final selected path is : " .$path."/". $selected_file);
        $final_data = file_get_contents($path.'/'.$selected_file);


        error_log("Recommended Json Data configuration of file ". print_r($final_data, TRUE));
        chdir("../translations");
    
        include_once(getcwd() . "/Application_builder.php");
    
        $app = new Application_builder($final_data);
    
        $zip_link = $app->build();   
        echo json_encode([
            'zip_link' => $zip_link



        ]);
    
        http_response_code(200);
        exit;
    }, 'POST');
    
    Flight::route('/match-json', function () {
    
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }
    
        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
    
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
            exit(0);
        }
    
        $json = file_get_contents('php://input', TRUE);
    
        $payload_data = json_decode($json, true);
    
        chdir("../");
    
        chdir("uploads");
        $uploads_root = getcwd();
        $language = $payload_data['config']['programming_langauge'];
    
        $uc_lang = ucfirst($language);
    
        $path = $uploads_root . "/" . $uc_lang;
        $files_list = scandir($path);
        // $whitelisted_json = [];
        $highest_points = 0;
        $selected_file = "";
        $matched_fields = "";
        $highest_section = "";
        for ($i = 2; $i < count($files_list); $i++) {
            $json_we_have = file_get_contents($path . "/" . $files_list[$i]); // change delimeters
            //file name
            $file_name = explode(".", $files_list[$i])[0];
            $data = (array)json_decode($json_we_have);
            $matched_fields .= "<hr><h3> File : " .$file_name.".json</h3>" ;
            $points_count = 0;
    
            // Code to match case the values 1st from the configuration
            $algorithm = $payload_data['config']['algorithm'];
            $match_percent = 0;
            // error_log(print_r($algorithm, TRUE));
            switch ($algorithm) {
                case 'similar_text':
                    similar_text($data['config']->app_name, $payload_data['config']['app_name'], $match_percent);
                    break;
    
                case 'levenshtein':
                    $match_percent = levenshtein($data['config']->app_name, $payload_data['config']['app_name']);
                    break;
    
                case 'cosine':
                    $match_percent = cosineSimilarity($data['config']->app_name, $payload_data['config']['app_name']);
                    error_log("For File : " .strval($file_name) ." :- App Name Matching Percent of Cosine Algorithm  :- ".print_r($match_percent, TRUE));
                    
                    break;
    
                default:
                    // Default to similar_text
                    similar_text($data['config']->app_name, $payload_data['config']['app_name'], $match_percent);
                    break;
            }
    
            if ($match_percent >= 0) {
                $points_count += 1;
                $matched_fields .= "<p> App Name : Comparing <b style='color:red;'> ".$data['config']->app_name."</b> with <b style='color:blue;'>".$payload_data['config']['app_name']." </b> Match Percent:<b> " . $match_percent."</b></p>";
            }
    
            // Making more tests here and adding the points to the json
            // Now will be comparing the fields and see what point the json will get 
            $old_data_array = (array)$data['data'];
    
            foreach ($payload_data['data'] as $to_comp_key) {
    
                foreach ($old_data_array as $arr) {
                    $arr = (array)$arr;
    
                    $table_match_percent = 0;
    
                    switch ($algorithm) {
                        case 'similar_text':
                            similar_text($to_comp_key['tableName'], $arr['tableName'], $table_match_percent) / 100;
                            break;
                        case 'levenshtein':
                            $table_match_percent = levenshtein($to_comp_key['tableName'], $arr['tableName']) / 100;
                            break;
                        case 'cosine':
                            $table_match_percent = cosineSimilarity($to_comp_key['tableName'], $arr['tableName']);
                            error_log("For File : " .strval($file_name) ." :- Table Name Matching Percent of Cosine Algorithm :- ".print_r($table_match_percent, TRUE));
                            break;
                        default:
                            // Default to similar_text
                            similar_text($to_comp_key['tableName'], $arr['tableName'], $table_match_percent);
                            break;
                        
                        
                    
                        
                    }
                    if ($table_match_percent > 0) {
                        $points_count += 1;
                        $matched_fields .= "<p>Table Name: Comparing <b style='color:red;'>   ".$to_comp_key['tableName'] ."</b> with <b style='color:blue;'>". $arr['tableName']." </b> Match Percent:<b> ".$table_match_percent ."</b></p>";
                }
                    // if( $algorithm == "cosine"){
                    //     $comp = 0;
                    // }else if($algorithm == "similar_text"){$comp = 35;}
                    // else{ $comp = 10;}
                    if ($table_match_percent >= 0) {
                        $matched_fields .= "<table><thead><tr><th>Field Name</th><th> Match Percent </th></tr></thead><tbody>";
                        $avg_points = 0;
                        $old_data_model = $arr['model'];
                        $old_fields = $old_data_model->fields;
    
                        if ($old_fields) {
                            foreach ($old_fields as $key => $value) {
                                foreach ($to_comp_key['db_fields'] as $j => $db_field) {
                                    $field_match_percent = 0;
    
                                    switch ($algorithm) {
                                        case 'similar_text':
                                            similar_text($db_field, $key, $field_match_percent);
                                            break;
    
                                        case 'levenshtein':
                                            $field_match_percent = levenshtein($db_field, $key);
                                            break;
    
                                        case 'cosine':

                                            $field_match_percent = cosineSimilarity($db_field, $key);
                                            
                                            break;
    
                                        default:
                                            similar_text($db_field, $key, $field_match_percent);
                                            break;
                                    }
                                    error_log("For File : " .strval($file_name) ." :-  Field Name Matching Percent of Algorithm for field :- ".$db_field .print_r($field_match_percent, TRUE));
                                    // if( $algorithm == "cosine"){
                                    //     $comp = 0;
                                    // }else if($algorithm == "similar_text"){$comp = 35;}
                                    // else{ $comp = 7;}
                                    if ($field_match_percent > 0) {
                                        $matched_fields .= "<tr><td><b style='color:red'>".$db_field."</b> and <b style='color:blue'>".$key."<b> </td>"."<td>".$field_match_percent."</td>"."</tr>";
                                        $avg_points += 0.25;
                                    }
                                    
                                }
                                
                            }
                            $matched_fields .= "</tbody></table>";
                            $points_count += $avg_points / count($to_comp_key['db_fields']);
                            
                        }
                    }
                }
            }
            $matched_fields .= "<p> Points Count for this file ". $file_name . " : - : ". strval($points_count);
            error_log("Points Count for this file ". $file_name . " : - : ". strval($points_count));

            //compre $points_coubt with $highest_points and if is higher set it to it
            if (!$highest_points || $points_count > $highest_points) { 
                $highest_points = $points_count;
                error_log("file got match with ".$algorithm ."in file ". $file_name.".json");
                $selected_file = $file_name.".json";
                $highest_section = "<p><b style='color:red;'>App Name:".$data['config']->app_name."</b> App Similarity: " . $match_percent . "</p>". "<p><b style='color:green;'> ".$arr['tableName']."</b> Table Similarity: " . $table_match_percent . "</p>";
                //get file contents and add value to $selected app, table and field lists

            }
        }
        error_log("The final selected path is : " .$path."/". $selected_file);
        $final_data = file_get_contents($path.'/'.$selected_file);

        // error_log("Recommended Json Data configuration of file ". print_r($final_data, TRUE));
        // //    error_log("Recommended Json Data app name of file ". print_r($final_data['config'], TRUE));
        // //    error_log("Recommended Json Data table of file ". print_r($final_data['data']['tableName'], TRUE));
       
       
        $decoded_data = json_decode($final_data, true);
        $matched_fields .= "</tbody></table>";
        if ($decoded_data !== null) {
            $app_name = $decoded_data['config']['app_name'];
            $table_name = $decoded_data['data'][0]['tableName']; // Assuming there is at least one element in the 'data' array
        
            // Log or use the extracted values as needed
            error_log("Recommended App Name: " . $app_name);
            error_log("Recommended Table Name: " . $table_name);
             http_response_code(200);
            echo json_encode([
                'algorithm' => $algorithm,
                'app_name' => $app_name,
                'table_name' => $table_name,
                'file_name' => $selected_file,
                'fields' => $matched_fields,
                'highest_point' =>$highest_points,
                'highest_section' => $highest_section, 
                'conf' => '<code>' . htmlspecialchars($final_data, ENT_QUOTES, 'UTF-8') . '</code>', 
            ]);
        } else {
            // Handle JSON decoding error
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Failed to decode JSON data']);
        }
        exit;
    }, 'POST');
    
     
Flight::start();

function textToVector($text) {
    $words = explode(" ", $text);
    $vector = [];

    // Implement text to vector logic, e.g., count of each word
    foreach ($words as $word) {
        $vector[$word] = isset($vector[$word]) ? $vector[$word] + 1 : 1;
    }
    // error_log(print_r($vector, TRUE));

    return $vector;
}
// Cosine Similarity function
function cosineSimilarity($text1, $text2) {
    // Tokenize the text into words
    $words1 = explode(' ', $text1);
    $words2 = explode(' ', $text2);
    // Count the occurrences of each word in both texts
    $count1 = array_count_values($words1);
    $count2 = array_count_values($words2);

    // Get the intersection of words
    $intersection = array_intersect_key($count1, $count2);

    // Calculate the dot product
    $dotProduct = 0;
    foreach ($intersection as $word => $count) {
        $dotProduct += $count1[$word] * $count2[$word];
    }

    // Calculate the magnitude of each vector
    $magnitude1 = sqrt(array_sum(array_map(function ($count) {
        return $count * $count;
    }, $count1)));

    $magnitude2 = sqrt(array_sum(array_map(function ($count) {
        return $count * $count;
    }, $count2)));

    // error_log(print_r($magnitude1, TRUE));
    // error_log(print_r($magnitude2, TRUE));

    // Calculate the cosine similarity
    if ($magnitude1 > 0 && $magnitude2 > 0) {
        return $dotProduct / ($magnitude1 * $magnitude2);
    } else {
        return 0; // Handle the case where one or both vectors have zero magnitude
    }
}
