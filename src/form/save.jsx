import { InnerBlocks } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";

export default function save({ attributes }) {
	return (
		<form className="spe-form" {...useBlockProps.save()}>
			<div className="spe-form-inner-block-props">
				<InnerBlocks.Content attributes={attributes} />
			</div>
		</form>
	);
}
