<?php 
// kc784 | 4/17
// api helper file
require_once(__DIR__ . "/load_api_keys.php");

/**
 * Send a request to the specified URL with the given method.
 */
function _sendRequest($url, $key, $data = [], $method = 'GET', $isRapidAPI = true, $rapidAPIHost = "")
{
    global $API_KEYS;
    if (!isset($API_KEYS) || !isset($API_KEYS[$key]) || empty($API_KEYS[$key])) {
        throw new Exception("Missing or empty API KEY");
    }
    $headers = [];
    if ($isRapidAPI) {
        $headers = [
            "X-RapidAPI-Host" => $rapidAPIHost,
            "X-RapidAPI-Key" => $API_KEYS[$key],
        ];
    } else {
        $headers = [
            "x-api-key" => $API_KEYS[$key]
        ];
    }
    $callback = fn (string $k, string $v): string => "$k: $v";
    $headers = array_map($callback, array_keys($headers), array_values($headers));

    $curl = curl_init();
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ];
    if ($method == 'GET') {
        $options[CURLOPT_URL] = "$url?" . http_build_query($data);
    } else {
        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($data);
    }
    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    // curl_close($curl);

    if ($err) {
        throw new Exception($err);
    } else {
        return ["status" => 200, "response" => $response];
    }
}

function get($url, $key, $data = [], $isRapidAPI = true, $rapidAPIHost = "")
{
    return _sendRequest($url, $key, $data, 'GET', $isRapidAPI, $rapidAPIHost);
}

function post($url, $key, $data = [], $isRapidAPI = true, $rapidAPIHost = "")
{
    return _sendRequest($url, $key, $data, 'POST', $isRapidAPI, $rapidAPIHost);
}