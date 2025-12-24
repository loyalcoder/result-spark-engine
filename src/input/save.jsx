import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function save({ attributes }) {
	return (
		<>
			<form className="form-builder-generated-form">
				<InnerBlocks.Content />
			</form>
		</>
	);
}
