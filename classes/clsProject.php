<?php 

class clsProject {

    public $id;
    public $site_id;
    public $project_name;
    public $project_type;
    public $project_status;
    public $description;
    public $initial_creation_date;
    public $due_date;
    public $ref_project_id;
    public $site_customer_id;
    public $status;
    public $created_by;
    public $created_date;
    public $modified_by;
    public $modified_date;


    // Constructor method
    public function __construct() {

        try {
            $this->id = 0;
            $this->site_id = '';
            $this->project_name = '';
            $this->project_type = '';
            $this->project_status = '';
            $this->description = '';
            $this->initial_creation_date = '';
            $this->due_date = '';
            $this->ref_project_id = '';
            $this->site_customer_id = '';
            $this->status = '';
            $this->created_by = '';
            $this->created_date = '';
            $this->modified_by = '';
            $this->modified_date = '';        
        }
        catch (EXCEPTION $err) {
            error_log('Error in Project constructor');
        }

        return;

    }



    public function load($row) {
        $status = true;

        try {
            $this->id = $row['id'];
            $this->site_id = $row['site_id'];
            $this->project_name = $row['project_name'];
            $this->project_type = $row['project_type'];
            $this->project_status = $row['project_status'];
            $this->description = $row['description'];
            $this->initial_creation_date = $row['initial_creation_date'];
            $this->due_date = $row['due_date'];
            $this->ref_project_id = $row['ref_project_id'];
            $this->site_customer_id = $row['site_customer_id'];
            $this->status = $row['status'];
            $this->created_by = $row['created_by'];
            $this->created_date = $row['created_date'];
            $this->modified_by = $row['modified_by'];
            $this->modified_date = $row['modified_date'];    

        }
        catch (EXCEPTION $err) {
            $status = false;
        }

        return $status;
    }



}


?>

