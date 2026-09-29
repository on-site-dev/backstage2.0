<?php 

class clsUser {

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $ref_id;
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
    public $created_by;
    public $created_date;
    public $modified_by;
    public $modified_date;


    // Constructor method
    public function __construct() {

        try {
            $this->id = '';
            $this->first_name = '';
            $this->last_name = '';
            $this->email = '';
            $this->password = '';
            $this->ref_id = 0;
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
            $this->created_by = '';
            $this->created_date = '';
            $this->modified_by = '';
            $this->modified_date = '';            
        }
        catch (EXCEPTION $err) {
            error_log('Error in User constructor');
        }

        return;

    }



    public function load($row) {
        $status = true;

        try {
            $this->id = $row['id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->ref_id = $row['ref_id'];
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

