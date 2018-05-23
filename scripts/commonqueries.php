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
    global $TABLE_REGISTRATIONS;
    return "SELECT ID, FirstName, LastName, CompanyUnit, RegisteeType, Email, DOB, Address, Phone,
    MobilePhone, ContactName, ContactPhone, MedicalDetails, FoodDetails, DateRegistered, PhotoPerm FROM $TABLE_REGISTRATIONS";
}

/**
 * Gets a query for all registration data in the database from a certain company
 * @param int $companyID The ID of the company
 * @return string The query
 */
function getRegistrationsByCompanyQuery($companyID) {
    return getRegistrationQuery() . " WHERE CompanyUnut = $companyID";
}