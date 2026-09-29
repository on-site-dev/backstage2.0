<?php 

require_once('../config.php');
require_once('../includes/db.php');
require_once('../classes/clsUser.php');

// Determine the HTTP Method and request action
$method = $_SERVER['REQUEST_METHOD'];


// determine which action to do 
switch (strtoupper($method)) {

    case 'GET':
        // get the rows(s)
        echo read();
        break;

    case 'POST':
        // update the row
        echo update();
        break;

    case 'PUT':
        // create the row
        echo create();
        break;
        
    case 'DELETE':
        // delete the row
        echo delete();
        break;
        
    default:
        // Code to execute if no cases match
        // Handle unsupported HTTP request methods
        http_response_code(405); // Method Not Allowed
        echo json_encode(["message" => "Method $method not allowed."]);

}



function create() {

    $json = "";
    $found = false;

    try {
        http_response_code(200); // good
        $json = json_encode(["message" => "create not yet done."]);
    }
    catch (EXCEPTION $err) {
        http_response_code(404); // Not Found
        $json = json_encode(["message" => $err]);
    }
    
    return $json;
}



function read() {

    $json = "";
    $users = array();
    $foundUser = false;
    $sql = "SELECT u.id,
                u.first_name,
                u.last_name,
                u.email,
                u.password,
                u.ref_id,
                a.owner_type,
                a.owner_id,
                a.address_type,
                a.address,
                a.city,
                a.county,
                a.state,
                a.zip,
                a.country,    
                u.status,
                u.created_by,
                u.created_date,
                u.modified_by,
                u.modified_date
            FROM users u join addresses a
                on a.owner_id = u.id ";        

    // Check if a specific id was requested (e.g., api.php?id=2)
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $sql .= " WHERE u.id=" . $id . " ";
    } 

    $sql .= " ORDER BY ID;";

    $rows = getSourceRows($sql);

    if (count($rows) > 0) {
        $foundUser = true;
        foreach ($rows as $row) {
            $user = new clsUser();
            $user->load($row);
            $users[] = $user;
        }
    }

    if ($foundUser) {
        http_response_code(200); // OK
        $json = json_encode($users);
    } 
    else {
        http_response_code(404); // Not Found
        $json = json_encode(["message" => "User not found."]);
    }

    return $json;


} 



function update() {

    $json = "";
    $found = false;

    try {
        http_response_code(200); // good
        $json = json_encode(["message" => "Update not yet done."]);
    }
    catch (EXCEPTION $err) {
        http_response_code(404); // Not Found
        $json = json_encode(["message" => $err]);
    }
    
    return $json;
}




function delete() {

    $json = "";
    $found = false;

    try {
        http_response_code(200); // good
        $json = json_encode(["message" => "delete not yet done."]);
    }
    catch (EXCEPTION $err) {
        http_response_code(404); // Not Found
        $json = json_encode(["message" => $err]);
    }
    
    return $json;
}



?>
