<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<table class="form-table">

<tr>
<th width="220">Company</th>
<td>

<select name="nkrp_company" style="width:350px">
    <option value="">Select Company</option>
</select>

</td>
</tr>

<tr>
<th>Vacancies</th>
<td>

<input
name="nkrp_vacancies"
type="number"
class="small-text"
value="<?php echo esc_attr($data['vacancies'] ?: 1); ?>">

</td>
</tr>

<tr>
<th>Application Deadline</th>
<td>

<input
name="nkrp_deadline"
type="date"
value="<?php echo esc_attr($data['deadline']); ?>">

</td>
</tr>

<tr>
<th>Salary</th>
<td>

<input
name="nkrp_salary"
type="text"
class="regular-text"
value="<?php echo esc_attr($data['salary']); ?>"
placeholder="$40,000 - $60,000">

</td>
</tr>

<tr>
<th>Employment Type</th>
<td>

<select name="nkrp_type">

<?php

$types = [
    'Full Time',
    'Part Time',
    'Contract',
    'Temporary',
    'Remote',
    'Internship'
];

foreach ($types as $type) {

    printf(
        '<option value="%1$s" %2$s>%1$s</option>',
        esc_attr($type),
        selected($data['type'], $type, false)
    );

}

?>

</select>

</td>
</tr>

</table>