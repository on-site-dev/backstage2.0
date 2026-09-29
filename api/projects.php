<?php 

require_once('../config.php');
require_once('../includes/db.php');
require_once('../classes/clsProject.php');

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
    $found = false;

    try {
        $sql = "SELECT p.id,
                    p.site_id,
                    p.project_name,
                    p.project_type,
                    p.project_status,
                    p.description,
                    p.initial_creation_date,
                    p.due_date,
                    p.ref_project_id,
                    p.site_customer_id,
                    p.status,
                    p.created_by,
                    p.created_date,
                    p.modified_by,
                    p.modified_date
                FROM projects p ";        

        // Check if a specific id was requested (e.g., api.php?id=2)
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $sql .= " WHERE p.id=" . $id . " ";
        } 

        $sql .= " ORDER BY p.id;";

        $rows = getSourceRows($sql);
        $projects = array();

        if (count($rows) > 0) {
            $found = true;
            foreach ($rows as $row) {
                $project = new clsProject();
                $project->load($row);
                $projects[] = $project;
            }
        }

        if ($found) {
            http_response_code(200); // OK
            $json = json_encode($projects);
        } 
        else {
            http_response_code(404); // Not Found
            $json = json_encode(["message" => "Project not found."]);
        }
    }
    catch (EXCEPTION $err) {
        http_response_code(404); // Not Found
        $json = json_encode(["message" => $err]);
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
