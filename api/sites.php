<?php 

require_once('../config.php');
require_once('../includes/db.php');
require_once('../classes/clsSite.php');

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
        $sql = "SELECT s.id,
                    s.ref_site_id,
                    s.client_signifier,
                    s.name,
                    s.notes,
                    s.active,
                    s.contract_id,
                    s.install_date,
                    s.start_date,
                    s.production_level,
                    s.master_site,
                    s.site_type,
                    s.no_prepaid_expiration,
                    s.use12mo_prepaid_expiration,
                    s.exclude_billing_report,
                    s.branded_site,
                    a.owner_type,
                    a.owner_id,
                    a.address_type,
                    a.address,
                    a.city,
                    a.county,
                    a.state,
                    a.zip,
                    a.country,
                    s.status,
                    s.created_date,
                    s.created_by,
                    s.modified_date,
                    s.modified_by
                FROM sites s join addresses a
                    on a.owner_id = s.id  ";        

        // Check if a specific id was requested (e.g., api.php?id=2)
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $sql .= " WHERE s.id=" . $id . " ";
        } 

        $sql .= " ORDER BY s.id;";

        $rows = getSourceRows($sql);
        $sites = array();

        if (count($rows) > 0) {
            $found = true;
            foreach ($rows as $row) {
                $site = new clsSite();
                $site->load($row);
                $sites[] = $site;
            }
        }

        if ($found) {
            http_response_code(200); // OK
            $json = json_encode($sites);
        } 
        else {
            http_response_code(404); // Not Found
            $json = json_encode(["message" => "Site not found."]);
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
