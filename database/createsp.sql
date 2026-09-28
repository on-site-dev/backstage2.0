DELIMITER $$

DROP PROCEDURE `spGetSites`$$

CREATE PROCEDURE `spGetSites`(
    IN  site_filter varchar(100),
    OUT num_rows int
    )
BEGIN
	DECLARE sites varchar(100) default '';
    
    SELECT site_filter;
    
	IF (site_filter != '') THEN 
		BEGIN
			SELECT count(*) INTO num_rows
            FROM sites s
            WHERE FIND_IN_SET(s.id, site_filter) > 0;
            
			SELECT s.id as site_id,
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
				on a.owner_id = s.id 
					and a.owner_type='S'
			WHERE FIND_IN_SET(s.id, site_filter) > 0
            ORDER BY s.id;
        END;
	ELSE 
		BEGIN
			SELECT count(*) INTO num_rows
            FROM sites;
                    
			SELECT s.id as site_id,
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
				on a.owner_id = s.id 
					and a.owner_type='S'
            ORDER BY s.id;
        END;
	END IF;        
   
END$$

DELIMITER ;
