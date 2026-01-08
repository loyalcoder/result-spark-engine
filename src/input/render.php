<?php

/**
 * Dynamic render callback for Input block
 */

$attrs = $attributes ?? [];

// Attributes with defaults
$placeholder       = $attrs['placeholder'] ?? '';
$type              = $attrs['type'] ?? 'text';
$isRequired        = ! empty($attrs['isRequired']);
$label             = $attrs['label'] ?? '';
$passwordToggle    = ! empty($attrs['passwordToggle']);
$showLabel         = ! empty($attrs['showLabel']);
$name              = $attrs['name'] ?? '';
$help              = $attrs['help'] ?? '';
$labelPosition     = $attrs['labelPosition'] ?? 'top';

// Colors
$labelColor        = $attrs['labelColor'] ?? '';
$helpColor         = $attrs['helpColor'] ?? '';
$borderColor       = $attrs['borderColor'] ?? '';
$backgroundColor   = $attrs['backgroundColor'] ?? '';
$placeholderColor  = $attrs['placeholderColor'] ?? '';

// Unique ID (same idea as uuidv4)
$id = $name . '-' . uniqid();

// Types that support placeholder
$placeholderInput = ['text', 'search', 'email', 'password', 'url', 'number', 'tel'];
?>

<div
    class="wp-block-spark-engine-input"
    style="
		--spe-label-color: <?php echo esc_attr($labelColor); ?>;
		--spe-help-color: <?php echo esc_attr($helpColor); ?>;
		--spe-background-color: <?php echo esc_attr($backgroundColor); ?>;
		--spe-border-color: <?php echo esc_attr($borderColor); ?>;
		--spe-placeholder-color: <?php echo esc_attr($placeholderColor); ?>;
	">
    <div class="spark-engine-input-wrapper-block">

        <?php if ($showLabel && $label && $labelPosition === 'top') : ?>
            <label class="spark-engine-input-label" for="<?php echo esc_attr($id); ?>">
                <?php echo esc_html($label); ?>
                <?php if ($isRequired) : ?>
                    <span class="spark-engine-input-required">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <div class="spark-engine-input-wrapper spark-engine-label-<?php echo esc_attr($labelPosition); ?>">

            <?php if ($showLabel && $label && $labelPosition === 'left') : ?>
                <label class="spark-engine-input-label" for="<?php echo esc_attr($id); ?>">
                    <?php echo esc_html($label); ?>
                    <?php if ($isRequired) : ?>
                        <span class="spark-engine-input-required">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>

            <div class="<?php
                        echo ($type === 'password' && $passwordToggle)
                            ? 'spark-engine-input-password-toggle-true'
                            : '';
                        ?>">

                <input
                    id="<?php echo esc_attr($id); ?>"
                    type="<?php echo esc_attr($type); ?>"
                    name="<?php echo esc_attr($name); ?>"
                    <?php if (in_array($type, $placeholderInput, true)) : ?>
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    <?php endif; ?>
                    <?php echo $isRequired ? 'required' : ''; ?>
                    class="<?php echo $type !== 'color'
                                ? 'spark-engine-input'
                                : 'spark-engine-input-color'; ?>" />

                <?php if ($passwordToggle && $type === 'password') : ?>
                    <div class="spark-engine-input-password-toggle-icon">
                        <!-- Icon rendered via CSS / JS (same as editor) -->
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <?php if ($help) : ?>
            <p class="spark-engine-input-help-text-wrapper">
                <span class="spark-engine-input-help-icon"></span>
                <?php echo esc_html($help); ?>
            </p>
        <?php endif; ?>

    </div>
</div>