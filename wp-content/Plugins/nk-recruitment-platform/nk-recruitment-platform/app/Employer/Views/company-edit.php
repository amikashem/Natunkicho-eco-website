<?php

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="wrap">

<h1 class="wp-heading-inline">

Edit Company

</h1>

<hr class="wp-header-end">

<form method="post">

<?php wp_nonce_field('nkrp_company'); ?>

<table class="form-table">

<tr>
<th width="220">Company Name *</th>
<td>
<input
type="text"
name="company_name"
class="regular-text"
required
value="<?php echo esc_attr($company->company_name); ?>">
</td>
</tr>

<tr>
<th>Company Email</th>
<td>
<input
type="email"
name="company_email"
class="regular-text"
value="<?php echo esc_attr($company->company_email); ?>">
</td>
</tr>

<tr>
<th>Website</th>
<td>
<input
type="text"
name="website"
class="regular-text"
value="<?php echo esc_attr($company->website); ?>">
</td>
</tr>

<tr>
<th>Phone</th>
<td>
<input
type="text"
name="phone"
class="regular-text"
value="<?php echo esc_attr($company->phone); ?>">
</td>
</tr>

<tr>
<th>Industry</th>
<td>

<select name="industry">

<?php

$industries = [

'Hospitality',
'Restaurant',
'Hotel',
'Cruise Ship',
'Retail',
'Healthcare',
'Catering',
'Clubs',
'Others'

];

?>

<option value="">Select Industry</option>

<?php foreach ($industries as $industry): ?>

<option
value="<?php echo esc_attr($industry); ?>"
<?php selected($company->industry, $industry); ?>>

<?php echo esc_html($industry); ?>

</option>

<?php endforeach; ?>

</select>

</td>
</tr>

<tr>

<th>Company Size</th>

<td>

<?php

$sizes = [

'1-10',
'11-50',
'51-200',
'201-500',
'500-1000',
'1000+'

];

?>

<select name="company_size">

<option value="">Select Size</option>

<?php foreach ($sizes as $size): ?>

<option
value="<?php echo esc_attr($size); ?>"
<?php selected($company->company_size, $size); ?>>

<?php echo esc_html($size); ?>

</option>

<?php endforeach; ?>

</select>

</td>

</tr> 
<!-- =====================================================
SECTION 2 : Company Images
===================================================== -->

<tr>

<th>Logo</th>

<td>

<input
type="text"
id="company_logo"
name="logo"
class="regular-text"
value="<?php echo esc_attr($company->logo); ?>">

<input
type="button"
id="upload_company_logo"
class="button"
value="Upload Logo">

<div
id="company_logo_preview"
style="margin-top:10px;">

<?php if (!empty($company->logo)) : ?>

<img
src="<?php echo esc_url($company->logo); ?>"
style="max-width:150px;border:1px solid #ddd;padding:5px;border-radius:6px;">

<?php endif; ?>

</div>

</td>

</tr>

<tr>

<th>Cover Image</th>

<td>

<input
type="text"
id="company_cover"
name="cover"
class="regular-text"
value="<?php echo esc_attr($company->cover); ?>">

<input
type="button"
id="upload_company_cover"
class="button"
value="Upload Cover">

<div
id="company_cover_preview"
style="margin-top:10px;">

<?php if (!empty($company->cover)) : ?>

<img
src="<?php echo esc_url($company->cover); ?>"
style="max-width:250px;border:1px solid #ddd;padding:5px;border-radius:6px;">

<?php endif; ?>

</div>

</td>

</tr>

<!-- =====================================================
SECTION 3 : Company Address
===================================================== -->

<tr>

<th>Country</th>

<td>

<input
type="text"
name="country"
class="regular-text"
value="<?php echo esc_attr($company->country); ?>">

</td>

</tr>

<tr>

<th>State / Province</th>

<td>

<input
type="text"
name="state"
class="regular-text"
value="<?php echo esc_attr($company->state); ?>">

</td>

</tr>

<tr>

<th>City</th>

<td>

<input
type="text"
name="city"
class="regular-text"
value="<?php echo esc_attr($company->city); ?>">

</td>

</tr>

<tr>

<th>Address</th>

<td>

<textarea
name="address"
rows="3"
class="large-text"><?php echo esc_textarea($company->address); ?></textarea>

</td>

</tr>

<!-- =====================================================
SECTION 4 : Company Description
===================================================== -->

</table>

<h2>Company Description</h2>

<?php

wp_editor(

    $company->description,

    'company_description',

    [

        'textarea_name' => 'description',

        'textarea_rows' => 10,

        'media_buttons' => false,

    ]

);

?>

<!-- =====================================================
SECTION 5 : Company Status
===================================================== -->

<table class="form-table">

<tr>

<th>Status</th>

<td>

<select name="status">

<option
value="active"
<?php selected($company->status, 'active'); ?>>

Active

</option>

<option
value="inactive"
<?php selected($company->status, 'inactive'); ?>>

Inactive

</option>

<option
value="pending"
<?php selected($company->status, 'pending'); ?>>

Pending Review

</option>

</select>

</td>

</tr>

<tr>

<th>Verification</th>

<td>

<label>

<input
type="checkbox"
name="verified"
value="1"
<?php checked((int) $company->verified, 1); ?>>

Verified Company

</label>

</td>

</tr>

<tr>

<th>Featured</th>

<td>

<label>

<input
type="checkbox"
name="featured"
value="1"
<?php checked((int) $company->featured, 1); ?>>

Featured Company

</label>

</td>

</tr>

</table>

<!-- =====================================================
SECTION 6 : Save Button
===================================================== -->

<?php submit_button('Update Company'); ?>

</form>

</div>