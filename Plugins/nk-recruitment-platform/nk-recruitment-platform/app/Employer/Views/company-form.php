<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Default Values
|--------------------------------------------------------------------------
*/

$company ??= new stdClass();

$company->company_name ??= '';
$company->company_email ??= '';
$company->website ??= '';
$company->phone ??= '';

$company->logo ??= '';
$company->cover ??= '';

$company->industry ??= '';
$company->company_size ??= '';
$company->founded_year ??= '';

$company->country ??= '';
$company->state ??= '';
$company->city ??= '';
$company->address ??= '';

$company->description ??= '';

$company->status ??= 'active';

$company->verified ??= 0;
$company->featured ??= 0;
?>