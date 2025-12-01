<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="rse-enquiry-form" id="rse-enquiry-form">

    <form action="" method="post">

        <div class="form-row">
            <label for="name"><?php esc_html_e('Name', 'result-spark-engine'); ?></label>

            <input type="text" id="name" name="name" value="" required>
        </div>

        <div class="form-row">
            <label for="email"><?php esc_html_e('E-Mail', 'result-spark-engine'); ?></label>

            <input type="email" id="email" name="email" value="" required>
        </div>

        <div class="form-row">
            <label for="message"><?php esc_html_e('Message', 'result-spark-engine'); ?></label>

            <textarea name="message" id="message" required></textarea>
        </div>

        <div class="form-row">

            <?php wp_nonce_field('rse-enquiry-form'); ?>

            <input type="hidden" class="hidden" name="action" value="rse_enquiry" />
            <input type="submit" class="submit-enquiry" name="send_enquiry" value="<?php esc_attr_e('Send Enquiry', 'result-spark-engine'); ?>" />
        </div>

    </form>
</div>