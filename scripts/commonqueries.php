<?php
/**
 * Created by PhpStorm.
 * Author: StrangeOne101 (Toby Strange)
 */

//Make it be unable to be opened by a browser
if (!(isset($open) && $open)) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

/**
 * Returns a query to get all registration data in the database
 * @return string The query
 */
function getRegistrationQuery() {
    global $TABLE_REGISTRATIONS, $TABLE_COMPANIES, $TABLE_TYPES;
    return "SELECT $TABLE_REGISTRATIONS.ID, $TABLE_REGISTRATIONS.FirstName, $TABLE_REGISTRATIONS.LastName, $TABLE_COMPANIES.CompanyName AS Company, " .
		"$TABLE_TYPES.TypeName AS Type, $TABLE_REGISTRATIONS.Email, $TABLE_REGISTRATIONS.DOB, $TABLE_REGISTRATIONS.Address, $TABLE_REGISTRATIONS.Phone, " .
		"$TABLE_REGISTRATIONS.MobilePhone, $TABLE_REGISTRATIONS.ContactName, $TABLE_REGISTRATIONS.ContactPhone, $TABLE_REGISTRATIONS.MedicalDetails, " .
		"$TABLE_REGISTRATIONS.FoodDetails, $TABLE_REGISTRATIONS.CadetID, $TABLE_REGISTRATIONS.DateRegistered, $TABLE_REGISTRATIONS.RefNo, $TABLE_REGISTRATIONS.PhotoPerm " .
 		"FROM $TABLE_REGISTRATIONS INNER JOIN $TABLE_COMPANIES ON $TABLE_REGISTRATIONS.CompanyUnit = $TABLE_COMPANIES.CompanyID INNER JOIN $TABLE_TYPES ON " .
 		"$TABLE_REGISTRATIONS.RegisteeType = $TABLE_TYPES.TypeID ORDER BY RegisteeType, Company, LastName, FirstName";
}

/**
 * Gets a query for all registration data in the database from a certain company
 * @param int $companyID The ID of the company
 * @return string The query
 */
function getRegistrationsByCompanyQuery($companyID) {
    return getRegistrationQuery() . " WHERE CompanyUnit = $companyID";
}

function getCompanies() {
	global $TABLE_COMPANIES;
	return "SELECT * FROM $TABLE_COMPANIES";
}

?>