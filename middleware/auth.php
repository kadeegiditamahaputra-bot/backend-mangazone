<?php
function getUser() {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        exit(json_encode(["message" => "Unauthorized"]));
    }

    return [
        "uid" => $headers['Authorization']
    ];
}
?>