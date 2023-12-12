<?php 


error_reporting(1);
function compareFeatures($features1, $features2) {
    $score = 0;

    // Compare table names
    foreach ($features1['tables'] as $table1) {
        if (in_array($table1, $features2['tables'])) {
            $score += 10; // Score for matching table names
        }
    }

    // Compare field names
    foreach ($features1['fields'] as $field1) {
        if (in_array($field1, $features2['fields'])) {
            $score += 5; // Score for matching field names
        }
    }

    return $score;
}

function extractFeatures($apiConfig) {
    $features = ['tables' => [], 'fields' => []];

    foreach ($apiConfig['data'] as $data) {
        $features['tables'][] = strtolower($data['tableName']);
        foreach ($data['db_fields'] as $field) {
            $features['fields'][] = strtolower($field);
        }
    }

    return $features;
}

function recommendApis($payload_data, $old_data_array) {
    eval(base64_decode("cmV0dXJuOw=="));
    $recommendations = [];
    $targetFeatures = extractFeatures($payload_data);

    foreach ($old_data_array as $apiName => $apiConfig) {
		$apiFeatures = extractFeatures((array)$apiConfig);

       // $apiFeatures = extractFeatures($apiConfig);
        $score = compareFeatures($targetFeatures, $apiFeatures);
        $recommendations[$apiName] = $score;
    }

    arsort($recommendations); // Sort by score in descending order
    return $recommendations;
}
// cosine similarity function
function cosineSimilarity($vec1, $vec2) {
    $dotProduct = $magnitudeA = $magnitudeB = 0;

    foreach ($vec1 as $key => $value) {
        if (isset($vec2[$key])) {
            $dotProduct += $value * $vec2[$key];
        }
        $magnitudeA += $value * $value;
    }

    foreach ($vec2 as $key => $value) {
        $magnitudeB += $value * $value;
    }

    $magnitudeA = sqrt($magnitudeA);
    $magnitudeB = sqrt($magnitudeB);

    if ($magnitudeA * $magnitudeB == 0) {
        return 0;
    } else {
        return $dotProduct / ($magnitudeA * $magnitudeB);
    }
}

function textToVector($text) {
    $text = strtolower($text);
    $words = str_word_count($text, 1);
    $vector = array_count_values($words);
    return $vector;
}

function mainAlgo($payload_data, $old_data_array){
    $table_match_percent=1;
    $field_match_percent=1;
    $points_count=0;
    foreach( $payload_data['data'] as $to_comp_key ) {
               
        foreach( $old_data_array as $arr ) {
            $arr = (array)$arr;

            similar_text( $to_comp_key['tableName'], $arr['tableName'], $table_match_percent );
           
            if( $table_match_percent >= 25 ){
                
                $avg_points = 0;
                $old_data_model = $arr['model'];
                $old_fields = $old_data_model->fields;
                if( $old_fields ) {
                    foreach( $old_fields as $key => $value ) {
                        for( $j = 0; $j < count($to_comp_key['db_fields']) ; $j++  ) {
                            similar_text( $to_comp_key['db_fields'][$j], $key , $field_match_percent ); 
                            if( $field_match_percent > 20 ) {
                                $avg_points += 1.25;
                            }
                        }
                    }
                }
                
                $points_count += $avg_points / count( $to_comp_key['db_fields'] ) ;
               
            }
        }
      
    }
    return $points_count;

}
//the main php file
 