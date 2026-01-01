<?php
$attrs = $attributes ?? [];

$label = $attrs['label'] ?? '';
$name = $attrs['name'] ?? '';
$options = $attrs['options'] ?? [];
$isRequired = !empty($attrs['isRequired']);

echo '<div class="spark-engine-input-wrapper-block">';

if ($label) {
    echo '<label>' . esc_html($label) . '</label>';
}

foreach ($options as $opt) {
    printf(
        '<label>
			<input type="radio" name="%s" value="%s" %s />
			%s
		</label>',
        esc_attr($name),
        esc_attr($opt['value']),
        $isRequired ? 'required' : '',
        esc_html($opt['label'])
    );
}

echo '</div>';
