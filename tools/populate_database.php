<?php

// open the source connection
define('SOURCE_DB_SERVER', 'localhost');
define('SOURCE_DB_SERVERPORT', '3306');
define('SOURCE_DB_USERNAME', 'dba');
define('SOURCE_DB_PASSWORD', 'dba');
define('SOURCE_DB_NAME', 'oss-test');


// open the target connection
define('TARGET_DB_SERVER', 'localhost');
define('TARGET_DB_SERVERPORT', '3306');
define('TARGET_DB_USERNAME', 'dba');
define('TARGET_DB_PASSWORD', 'dba');
define('TARGET_DB_NAME', 'backstage20');


header('Content-Type: text/html; charset=utf-8');
ini_set('default_charset', 'UTF-8');

// copy the projects from the source to the target
$sourceconn = mysqli_connect(SOURCE_DB_SERVER, SOURCE_DB_USERNAME, SOURCE_DB_PASSWORD, SOURCE_DB_NAME, SOURCE_DB_SERVERPORT);
$targetconn = mysqli_connect(TARGET_DB_SERVER, TARGET_DB_USERNAME, TARGET_DB_PASSWORD, TARGET_DB_NAME, TARGET_DB_SERVERPORT);

// load the source sites
$numrows = loadSites($sourceconn, $targetconn);
echo('<h1>Total Sites: ' . $numrows . '</h1>');

// load the source users
$numrows = loadUsers($sourceconn, $targetconn);
echo('<h1>Total users: ' . $numrows . '</h1>');

// load the source projects
$numrows = loadProjects($sourceconn, $targetconn);
echo('<h1>Total projects: ' . $numrows . '</h1>');

// close the connections
mysqli_close($sourceconn);
mysqli_close($targetconn);

echo('<h1>Done.</h1>');

?>




<?php 

    function loadSites($sourceconn, $targetconn) {

        $rowcount = 0;

        try {
            if ($sourceconn) {
                $sql = "SELECT * 
                        FROM os2_sites
                        ORDER BY id;
                        ";

                $rows = getSourceRows($sourceconn, $sql);

                // now insert into the new projects table
                if ($targetconn) {
                    foreach ($rows as $row) {
                        $newid = insertSite($targetconn, $row);
                        if ($newid > 0) {
                            $rowcount++;
                        }
                    }
                }

            }
        }
        catch (Exception $err) {
            error_log('Error in get of target rows: ' . $err);
        }

        return $rowcount;
    }



    function loadProjects($sourceconn, $targetconn) {

        $rowcount = 0;

        try {
            if ($sourceconn) {
                $sql = "SELECT * 
                        FROM os2_projects 
                        ORDER BY id;
                        ";

                $rows = getSourceRows($sourceconn, $sql);

                // now insert into the new projects table
                if ($targetconn) {
                    foreach ($rows as $row) {
                        $newid = insertProject($targetconn, $row);
                        if ($newid > 0) {
                            $rowcount++;
                        }
                    }
                }

            }
        }
        catch (Exception $err) {
            error_log('Error in get of target rows: ' . $err);
        }

        return $rowcount;
    }


    function loadUsers($sourceconn, $targetconn) {

        $rowcount = 0;

        try {
            if ($sourceconn) {
                $sql = "SELECT * 
                        FROM os2_users
                        ORDER BY id;
                        ";

                $rows = getSourceRows($sourceconn, $sql);

                // now insert into the new projects table
                if ($targetconn) {
                    foreach ($rows as $row) {
                        $newid = insertUser($targetconn, $row);
                        if ($newid > 0) {
                            $rowcount++;
                        }
                    }
                }

            }
        }
        catch (Exception $err) {
            error_log('Error in get of target rows: ' . $err);
        }

        return $rowcount;
    }


    function getSourceRows($sourceconn, $sql): array {

        $rows = [];
        try {

            if ($sourceconn) {
                mysqli_set_charset($sourceconn, 'utf8mb4'); // important
                mysqli_query($sourceconn, "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

                if ($res = mysqli_query($sourceconn, $sql)) {
                    while ($r = mysqli_fetch_assoc($res)) {
                        $rows[] = $r;
                    }
                    mysqli_free_result($res);
                }

            }
        }
        catch (Exception $err) {
            error_log('Error in get of os2 projects: ' . $err);
        }

        return $rows;
    }



    function insertSite($targetconn, $row) {

        $insertedid = 0;

        try {
            if ($targetconn) {
                mysqli_set_charset($targetconn, 'utf8mb4'); // important

                // insert into the sites table
                $sql = "INSERT INTO sites (
                            ref_site_id,
                            client_signifier,
                            name,
                            notes,
                            active,
                            contract_id,
                            install_date,
                            start_date,
                            production_level,
                            master_site,
                            site_type,
                            no_prepaid_expiration,
                            use12mo_prepaid_expiration,
                            exclude_billing_report,
                            branded_site,
                            status,
                            created_by,
                            created_date,
                            modified_by,
                            modified_date)
                        VALUES ("; 

                $sql .= "" . $row['id'] . "," .
                        "'000.000.000.000'," . 
                        "'" . $row['site_name'] . "'," . 
                        "'" . $row['notes'] . "'," . 
                        "'" . $row['active'] . "'," . 
                        "0," . 
                        "'" . $row['install_date'] . "'," . 
                        "'" . $row['start_date'] . "'," . 
                        "'" . $row['production_level'] . "'," .
                        "'" . $row['master_site'] . "'," .
                        "'" . $row['site_type'] . "'," . 
                        "'" . $row['no_prepaid_expiration'] . "'," . 
                        "'" . $row['use12mo_prepaid_expiration'] . "'," . 
                        "'" . $row['exclude_billing_report'] . "'," . 
                        "'" . $row['branded_site'] . "'," . 
                        "'" . $row['status'] . "'," . 
                        "'" . $row['created_by'] . "'," . 
                        "'" . $row['created_date'] . "'," . 
                        "'" . $row['modified_by'] . "'," . 
                        "'" . $row['modified_date'] . "'" . 
                        ")";

                // add the row
                try {
                    $stmt = $targetconn->prepare($sql);

                    // "ssi" means: string, string, integer
                    // $stmt->bind_param("ss", $row['project_name'], $row['description']); 

                    // Execute the statement
                    $stmt->execute();

                    // get the id just created 
                    $sql = "SELECT LAST_INSERT_ID() as lastid;";
                    $result = mysqli_query($targetconn, $sql);
                    if ($result) {
                        $r = mysqli_fetch_assoc($result);
                        $insertedid = $r['lastid'];
                    }

                    // now, add the address entry
                    $sql = "INSERT INTO addresses
                                (owner_type,
                                owner_id,
                                address_type,
                                address,
                                city,
                                county,
                                state,
                                zip,
                                country,
                                status,
                                created_by,
                                created_date,
                                modified_by,
                                modified_date)
                            VALUES (";
                    $sql .= "'S'," .
                            "" . $insertedid . "," . 
                            "'P'," .
                            "?," . 
                            "'" . $row['city'] . "'," . 
                            "''," .
                            "'" . $row['state'] . "'," . 
                            "'" . $row['zip'] . "'," . 
                            "'USA'," .
                            "'" . $row['status'] . "'," . 
                            "'" . $row['created_by'] . "'," . 
                            "'" . $row['created_date'] . "'," . 
                            "'" . $row['modified_by'] . "'," . 
                            "'" . $row['modified_date'] . "'" . 
                            ")";

                    $stmt = $targetconn->prepare($sql);

                    // "ssi" means: string, string, integer
                    $stmt->bind_param("s", $row['address']); 

                    // Execute the statement
                    $stmt->execute();

                }
                catch (EXCEPTION $stmterr) {
                    error_log('Err in stmt execute: ' . $stmterr);
                }
            }
        }
        catch (EXCEPTION $err) {
            error_log('Error: ' . $err);
        }

        return $insertedid;
    }


    function insertProject($targetconn, $row) {

        $insertedid = 0;

        try {
            if ($targetconn) {
                mysqli_set_charset($targetconn, 'utf8mb4'); // important

                $sql = "INSERT INTO backstage20.projects
                        (site_id,
                        project_name,
                        project_type,
                        project_status,
                        description,
                        initial_creation_date,
                        due_date,
                        ref_project_id,
                        site_customer_id,
                        status,
                        created_by,
                        created_date,
                        modified_by,
                        modified_date)
                        VALUES ("; 

                $sql .= "" . $row['site_id'] . "," .
                        "?," . 
                        "'" . $row['project_type'] . "'," . 
                        "'" . $row['status'] . "'," . 
                        "?," . 
                        "'" . $row['creation_date'] . "'," . 
                        "'" . $row['due_date'] . "'," . 
                        "" . $row['id'] . "," .
                        "" . $row['site_customer_id'] . "," .
                        "'" . $row['rowstatus'] . "'," . 
                        "'" . $row['created_by'] . "'," . 
                        "'" . $row['created_date'] . "'," . 
                        "'" . $row['modified_by'] . "'," . 
                        "'" . $row['modified_date'] . "'" . 
                        ")";

                // add the row
                try {
                    $stmt = $targetconn->prepare($sql);

                    // "ssi" means: string, string, integer
                    $stmt->bind_param("ss", $row['project_name'], $row['description']); 

                    // Execute the statement
                    $stmt->execute();

                    // get the id just created 
                    $sql = "SELECT LAST_INSERT_ID() as lastid;";
                    $result = mysqli_query($targetconn, $sql);
                    if ($result) {
                        $r = mysqli_fetch_assoc($result);
                        $insertedid = $r['lastid'];
                    }


                }
                catch (EXCEPTION $stmterr) {
                    error_log('Err in stmt execute: ' . $stmterr);
                }
            }
        }
        catch (EXCEPTION $err) {
            error_log('Error: ' . $err);
        }

        return $insertedid;
    }


    function insertUser($targetconn, $row) {

        $insertedid = 0;

        try {
            if ($targetconn) {
                mysqli_set_charset($targetconn, 'utf8mb4'); // important

                $sql = "INSERT INTO backstage20.users
                        (first_name,
                        last_name,
                        email,
                        password,
                        ref_id,
                        status,
                        created_by,
                        created_date,
                        modified_by,
                        modified_date)
                        VALUES ("; 

                $sql .= "?," .
                        "?," . 
                        "'" . $row['email'] . "'," . 
                        "'" . $row['password'] . "'," . 
                        "" . $row['id'] . "," .
                        "'" . $row['status'] . "'," . 
                        "'" . $row['created_by'] . "'," . 
                        "'" . $row['created_date'] . "'," . 
                        "'" . $row['modified_by'] . "'," . 
                        "'" . $row['modified_date'] . "'" . 
                        ")";

                // add the row
                try {
                    $stmt = $targetconn->prepare($sql);

                    // "ssi" means: string, string, integer
                    $stmt->bind_param("ss", $row['first_name'], $row['last_name']); 

                    // Execute the statement
                    $stmt->execute();

                    // get the id just created 
                    $sql = "SELECT LAST_INSERT_ID() as lastid;";
                    $result = mysqli_query($targetconn, $sql);
                    if ($result) {
                        $r = mysqli_fetch_assoc($result);
                        $insertedid = $r['lastid'];
                    }

                    // now, add the address entry
                    $sql = "INSERT INTO addresses
                                (owner_type,
                                owner_id,
                                address_type,
                                address,
                                city,
                                county,
                                state,
                                zip,
                                country,
                                status,
                                created_by,
                                created_date,
                                modified_by,
                                modified_date)
                            VALUES (";
                    $sql .= "'U'," .
                            "" . $insertedid . "," . 
                            "'P'," .
                            "?," . 
                            "'" . $row['city'] . "'," . 
                            "''," .
                            "'" . $row['state'] . "'," . 
                            "'" . $row['zip'] . "'," . 
                            "'USA'," .
                            "'" . $row['status'] . "'," . 
                            "'" . $row['created_by'] . "'," . 
                            "'" . $row['created_date'] . "'," . 
                            "'" . $row['modified_by'] . "'," . 
                            "'" . $row['modified_date'] . "'" . 
                            ")";

                    $stmt = $targetconn->prepare($sql);

                    // "ssi" means: string, string, integer
                    $stmt->bind_param("s", $row['address']); 

                    // Execute the statement
                    $stmt->execute();

                }
                catch (EXCEPTION $stmterr) {
                    error_log('Err in stmt execute: ' . $stmterr);
                }
            }
        }
        catch (EXCEPTION $err) {
            error_log('Error: ' . $err);
        }

        return $insertedid;
    }


?>