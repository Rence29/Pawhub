<?php
// ml_service.php

function getMatchPrediction($input_data) {
    // URL of your Flask API's prediction endpoint
    // IMPORTANT: Replace with the actual URL where your Flask app is running
    // If Flask is running on the same machine, 'http://127.0.0.1:5000' is common.
    // Ensure the port (5000) matches what you configured in api.py
    // Local
    $flask_api_url = 'http://127.0.0.1:5000/predict_match';
    // Deployed
    // $flask_api_url = 'https://pawhub-ml-1.onrender.com/predict_match';

    // Encode the input data to JSON format
    $json_input = json_encode($input_data);

    // Initialize cURL session
    $ch = curl_init($flask_api_url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_POST, true);           // Set as POST request
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_input); // Attach the JSON data
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json_input)
    ]);

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        error_log("cURL Error: " . $error_msg); // Log the error for debugging
        return ['match_result' => 'Error: API Unreachable'];
    }

    // Get HTTP status code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Close cURL session
    curl_close($ch);

    // Decode the JSON response
    $prediction_data = json_decode($response, true);

    // Check if the prediction was successful and extract the match_result
    if ($http_code == 200 && isset($prediction_data['match_result'])) {
        return ['match_result' => $prediction_data['match_result']];
    } else {
        // Log the full response for debugging if an error occurs
        error_log("ML API Response Error (HTTP $http_code): " . ($response ?: 'No response body'));
        error_log("ML API Prediction Data: " . print_r($prediction_data, true)); // Log the decoded data
        return ['match_result' => 'Error: Prediction Failed'];
    }
}
?>