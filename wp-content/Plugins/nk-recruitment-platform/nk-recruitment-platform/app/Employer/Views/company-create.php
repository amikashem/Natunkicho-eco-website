<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="wrap">

    <h1><?php esc_html_e('Add Company', 'nk-recruitment'); ?></h1>

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
                        required>
                </td>
            </tr>

            <tr>
                <th>Company Email</th>
                <td>
                    <input
                        type="email"
                        name="company_email"
                        class="regular-text">
                </td>
            </tr>

            <tr>
                <th>Website</th>
                <td>
                    <input
                        type="text"
                        name="website"
                        class="regular-text"
                        placeholder="google.com">
                </td>
            </tr>
            
            <tr>

                <th>Company Logo</th>
                
                <td>
                
                <input
                type="hidden"
                id="company_logo"
                name="logo"
                value="">
                
                <button
                type="button"
                class="button"
                id="upload_company_logo">
                
                Upload Logo
                
                </button>
                
                <div id="company_logo_preview"
                style="margin-top:15px;"></div>
                
                </td>
                
                </tr>
                <tr>

                <th>Company Cover</th>
                
                <td>
                
                <input
                type="hidden"
                id="company_cover"
                name="cover"
                value="">
                
                <button
                type="button"
                class="button"
                id="upload_company_cover">
                
                Upload Cover
                
                </button>
                
                <div
                id="company_cover_preview"
                style="margin-top:15px;"></div>
                
                </td>
                
                </tr>

            <tr>
                <th>Phone</th>
                <td>
                    <input
                        type="text"
                        name="phone"
                        class="regular-text">
                </td>
            </tr>

            <tr>
                <th>Industry</th>
                <td>
                    <select name="industry">
                        <option value="">Select Industry</option>
                        <option>Hospitality</option>
                        <option>Restaurant</option>
                        <option>Hotel</option>
                        <option>Cruise Ship</option>
                        <option>Retail</option>
                        <option>Healthcare</option>
                        <option>Catering</option>
                        <option>Clubs</option>
                        <option>Others</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th>Company Size</th>
                <td>
                    <select name="company_size">
                        <option value="">Select Size</option>
                        <option>1-10</option>
                        <option>11-50</option>
                        <option>51-200</option>
                        <option>201-500</option>
                        <option>500-1000</option>
                        <option>1000+</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th>Founded Year</th>
                <td>
                    <input
                        type="number"
                        name="founded_year"
                        min="1900"
                        max="<?php echo esc_attr(date('Y')); ?>">
                </td>
            </tr>

            <tr>
                <th>Country</th>
                <td>
                    <input
                        type="text"
                        name="country"
                        class="regular-text">
                </td>
            </tr>

            <tr>
                <th>State / Province</th>
                <td>
                    <input
                        type="text"
                        name="state"
                        class="regular-text">
                </td>
            </tr>

            <tr>
                <th>City</th>
                <td>
                    <input
                        type="text"
                        name="city"
                        class="regular-text">
                </td>
            </tr>

            <tr>
                <th>Address</th>
                <td>
                    <textarea
                        name="address"
                        rows="3"
                        class="large-text"></textarea>
                </td>
            </tr>

            <tr>
                <th>Status</th>
                <td>
                    <select name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending Review</option>
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
                            value="1">
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
                            value="1">
                        Featured Company
                    </label>
                </td>
            </tr>

        </table>

        <h2>Company Description</h2>

        <?php

        wp_editor(
            '',
            'company_description',
            [
                'textarea_name' => 'description',
                'textarea_rows' => 10,
                'media_buttons' => false,
            ]
        );

        ?>

        <?php submit_button(__('Save Company', 'nk-recruitment')); ?>

    </form>

</div>