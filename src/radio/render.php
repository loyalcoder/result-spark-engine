<?php

/**
 * Dynamic render callback for Radio Field block
 */

$attrs = $attributes ?? [];

// Attributes
$label        = $attrs['label'] ?? '';
$options      = $attrs['options'] ?? [];
$name         = $attrs['name'] ?? '';
$isRequired   = ! empty($attrs['isRequired']);
$showLabel    = ! empty($attrs['showLabel']);
$defaultValue = $attrs['defaultValue'] ?? '';
$useTaxonomy  = ! empty($attrs['useTaxonomy']);
$postType    = $attrs['postType'] ?? '';
$taxonomy    = $attrs['taxonomy'] ?? '';

// Unique ID (equivalent to uuid in editor)
$baseId = $name . '-' . uniqid();
?>

<div class="spark-engine-input-wrapper-block">

    <?php if ($showLabel && $label) : ?>
        <label class="spark-engine-input-label" for="<?php echo esc_attr($baseId); ?>">
            <?php echo esc_html($label); ?>
            <?php if ($isRequired) : ?>
                <span class="spark-engine-input-required">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <div class="spark-engine-check-input-wrapper">

        <?php if (! $useTaxonomy) : ?>

            <?php if (! empty($options)) : ?>
                <?php foreach ($options as $index => $option) :
                    $optionId  = $baseId . '-' . $index;
                    $isChecked = isset($option['value']) && $option['value'] === $defaultValue;
                ?>
                    <div class="spark-engine-form-check">
                        <input
                            type="radio"
                            name="<?php echo esc_attr($name); ?>"
                            id="<?php echo esc_attr($optionId); ?>"
                            value="<?php echo esc_attr($option['value'] ?? ''); ?>"
                            <?php checked($isChecked); ?>
                            <?php echo $isRequired ? 'required' : ''; ?>
                            class="spe-radio-filed" />
                        <label
                            class="spark-engine-input-label"
                            for="<?php echo esc_attr($optionId); ?>">
                            <?php echo esc_html($option['label'] ?? ''); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="spe-not-found">No Data Found</div>
            <?php endif; ?>

        <?php else : ?>

            <?php
            $terms = [];

            if ($taxonomy && taxonomy_exists($taxonomy)) {
                $terms = get_terms([
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => false,
                ]);
            }
            ?>

            <?php if (! is_wp_error($terms) && ! empty($terms)) : ?>
                <?php foreach ($terms as $index => $term) :
                    $termId = $baseId . '-' . $index;
                ?>
                    <div class="spark-engine-form-check">
                        <input
                            type="radio"
                            name="<?php echo esc_attr($name); ?>"
                            id="<?php echo esc_attr($termId); ?>"
                            value="<?php echo esc_attr($term->slug); ?>"
                            <?php echo $isRequired ? 'required' : ''; ?>
                            class="spe-radio-filed" />
                        <label
                            class="spark-engine-input-label"
                            for="<?php echo esc_attr($termId); ?>">
                            <?php echo esc_html($term->name); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="spe-not-found">No Data Found</div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>