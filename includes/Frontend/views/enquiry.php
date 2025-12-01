<div class="pkun-enquiry-form" id="pkun-enquiry-form">

    <form action="" method="post">

        <div class="form-row">
            <label for="name"><?php _e('Name', 'pkun'); ?></label>

            <input type="text" id="name" name="name" value="" required>
        </div>

        <div class="form-row">
            <label for="email"><?php _e('E-Mail', 'pkun'); ?></label>

            <input type="email" id="email" name="email" value="" required>
        </div>

        <div class="form-row">
            <label for="message"><?php _e('Message', 'pkun'); ?></label>

            <textarea name="message" id="message" required></textarea>
        </div>

        <div class="form-row">

            <?php wp_nonce_field('pkun-enquiry-form'); ?>

            <input type="hidden" class="hidden" name="action" value="pkun_enquiry" />
            <input type="submit" class="submit-enquiry" name="send_enquiry" value="<?php esc_attr_e('Send Enquiry', 'pkun'); ?>" />
        </div>

    </form>
</div>