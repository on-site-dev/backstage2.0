<?php 

class clsSite {

    public $id;
    public $ref_site_id;
    public $client_signifier;
    public $name;
    public $notes;
    public $active;
    public $contract_id;
    public $install_date;
    public $start_date;
    public $production_level;
    public $master_site;
    public $site_type;
    public $no_prepaid_expiration;
    public $use12mo_prepaid_expiration;
    public $exclude_billing_report;
    public $branded_site;
    public $owner_type;
    public $owner_id;
    public $address_type;
    public $address;
    public $city;
    public $county;
    public $state;
    public $zip;
    public $country;
    public $status;
    public $created_date;
    public $created_by;
    public $modified_date;
    public $modified_by;


    // Constructor method
    public function __construct() {

        try {
            $this->id = 0;
            $this->ref_site_id = 0;
            $this->client_signifier = '';
            $this->name = '';
            $this->notes = '';
            $this->active = '';
            $this->contract_id = '';
            $this->install_date = '';
            $this->start_date = '';
            $this->production_level = '';
            $this->master_site = 0;
            $this->site_type = '';
            $this->no_prepaid_expiration = '';
            $this->use12mo_prepaid_expiration = '';
            $this->exclude_billing_report = '';
            $this->branded_site = '';
            $this->owner_type = '';
            $this->owner_id = 0;
            $this->address_type = '';
            $this->address = '';
            $this->city = '';
            $this->county = '';
            $this->state = '';
            $this->zip = '';
            $this->country = '';
            $this->status = '';
            $this->created_date = '';
            $this->created_by = '';
            $this->modified_date = '';
            $this->modified_by = '';          
        }
        catch (EXCEPTION $err) {
            error_log('Error in Site constructor');
        }

        return;

    }



    public function load($row) {
        $status = true;

        try {
            $this->id = $row['id'];
            $this->ref_site_id = $row['ref_site_id'];
            $this->client_signifier = $row['client_signifier'];
            $this->name = $row['name'];
            $this->notes = $row['notes'];
            $this->active = $row['active'];
            $this->contract_id = $row['contract_id'];
            $this->install_date = $row['install_date'];
            $this->start_date = $row['start_date'];
            $this->production_level = $row['production_level'];
            $this->master_site = $row['master_site'];
            $this->site_type = $row['site_type'];
            $this->no_prepaid_expiration = $row['no_prepaid_expiration'];
            $this->use12mo_prepaid_expiration = $row['use12mo_prepaid_expiration'];
            $this->exclude_billing_report = $row['exclude_billing_report'];
            $this->branded_site = $row['branded_site'];
            $this->owner_type = $row['owner_type'];
            $this->owner_id = $row['owner_id'];
            $this->address_type = $row['address_type'];
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->county = $row['county'];
            $this->state = $row['state'];
            $this->zip = $row['zip'];
            $this->country = $row['country'];
            $this->status = $row['status'];
            $this->created_date = $row['created_date'];
            $this->created_by = $row['created_by'];
            $this->modified_date = $row['modified_date'];
            $this->modified_by = $row['modified_by'];

        }
        catch (EXCEPTION $err) {
            $status = false;
        }

        return $status;
    }



}


?>

