import { InnerBlocks } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";

export default function save({ attributes }) {
	return <InnerBlocks.Content attributes={attributes} />;
}
