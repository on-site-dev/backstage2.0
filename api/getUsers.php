<?php 

require_once('../config.php');
require_once '../includes/db.php';

error_log('DB connections:');
error_log('Host: ' . DB_HOST);
error_log('Port: ' . DB_SERVERPORT);
error_log('DB: ' . DB_NAME);
error_log('User: ' . DB_USER);
error_log('Pwd: ' . DB_PASS);

// Determine the HTTP Method and request action
$method = $_SERVER['REQUEST_METHOD'];

// Open connection
$dbconnection = dbConnect();

if ($method === 'GET') {

    // Check if a specific user was requested (e.g., api.php?id=2)
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $foundUser = null;
        $users = array();

        // get the user 
        $user = new clsUser();

        foreach ($users as $user) {
            if ($user['id'] === $id) {
                $foundUser = $user;
                break;
            }
        }

        if ($foundUser) {
            http_response_code(200); // OK
            echo json_encode($foundUser);
        } 
        else {
            http_response_code(404); // Not Found
            echo json_encode(["message" => "User not found."]);
        }
    } 
    else {
        // No ID specified, return all users
        http_response_code(200); // OK
        echo json_encode($users);
    }

} 
else {
    // Handle unsupported HTTP request methods
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Method not allowed. Use GET."]);
}

?>
