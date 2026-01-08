<?php

/**
 * Dynamic render callback for Select Field block
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
$selectId = $name . '-' . uniqid();
?>

<div class="spark-engine-input-wrapper-block">

    <?php if ($showLabel && $label) : ?>
        <label class="spark-engine-input-label" for="<?php echo esc_attr($selectId); ?>">
            <?php echo esc_html($label); ?>
            <?php if ($isRequired) : ?>
                <span class="spark-engine-input-required">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <div class="spark-engine-check-input-wrapper">

        <?php if (! $useTaxonomy) : ?>

            <?php if (! empty($options)) : ?>
                <select
                    class="spe-select-input"
                    name="<?php echo esc_attr($name); ?>"
                    id="<?php echo esc_attr($selectId); ?>"
                    <?php echo $isRequired ? 'required' : ''; ?>>
                    <?php foreach ($options as $option) :
                        $value = $option['value'] ?? '';
                        $labelText = $option['label'] ?? '';
                    ?>
                        <option
                            value="<?php echo esc_attr($value); ?>"
                            <?php selected($defaultValue, $value); ?>>
                            <?php echo esc_html($labelText); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                <select
                    class="spe-select-input"
                    name="<?php echo esc_attr($name); ?>"
                    id="<?php echo esc_attr($selectId); ?>"
                    <?php echo $isRequired ? 'required' : ''; ?>>
                    <?php foreach ($terms as $term) : ?>
                        <option
                            value="<?php echo esc_attr($term->slug); ?>"
                            <?php selected($defaultValue, $term->slug); ?>>
                            <?php echo esc_html($term->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <div class="spe-not-found">No Data Found</div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>