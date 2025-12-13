/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from "@wordpress/i18n";
import { Eye, Info } from "lucide-react";
import { v4 as uuidv4 } from "uuid";
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { InnerBlocks, ColorPaletteControl } from "@wordpress/block-editor";
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	TextControl,
} from "@wordpress/components";
/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import "./editor.scss";
import { useId } from "react";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const {
		placeholder,
		type,
		isRequired,
		label,
		passwordToggle,
		showLabel,
		name,
		help,
		labelPosition,
		labelColor,
		helpColor,
	} = attributes;
	const blockProps = useBlockProps();
	const id = uuidv4();
	const placeholderInput = [
		"text",
		"search",
		"email",
		"password",
		"url",
		"number",
		"tel",
	];

	const colorSettings = [
		{
			label: __("Label Color", "borobazar-helper"),
			color: labelColor,
			onChange: (value) => setAttributes({ labelColor: value }),
		},
		{
			label: __("Help Color", "borobazar-helper"),
			color: helpColor,
			onChange: (value) => setAttributes({ helpColor: value }),
		},
	];

	return (
		<div {...blockProps}>
			{showLabel && label ? (
				<label htmlFor={`${name}-${id}`}>
					{label}
					{isRequired ? <span>*</span> : ""}
				</label>
			) : (
				""
			)}
			<input
				id={`${name}-${id}`}
				type={type}
				name={name}
				placeholder={placeholder}
			/>
			{passwordToggle ? <Eye /> : ""}
			{help ? (
				<p>
					{help} <Info />
				</p>
			) : (
				""
			)}

			<InspectorControls group="styles">
				<PanelBody
					title={__("Layout Settings", "borobazar-helper")}
					initialOpen={true}
				>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Type"
						value={type}
						options={[
							{ label: "Text", value: "text" },
							{ label: "Time", value: "time" },
							{ label: "URL", value: "url" },
							{ label: "Week", value: "week" },
							{ label: "Color", value: "color" },
							{ label: "Date", value: "date" },
							{ label: "Email", value: "email" },
							{ label: "Month", value: "month" },
							{ label: "Number", value: "number" },
							{ label: "Password", value: "password" },
							{ label: "Search", value: "search" },
							{ label: "Tel", value: "tel" },
						]}
						onChange={(type) => setAttributes({ type })}
					/>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label="Label Position"
						value={labelPosition}
						options={[
							{ label: "Top", value: "top" },
							{ label: "Left", value: "left" },
						]}
						onChange={(labelPosition) => setAttributes({ labelPosition })}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						tagName="div"
						checked={showLabel}
						onChange={(showLabel) => setAttributes({ showLabel })}
						label="Show Label"
					/>
					{placeholderInput?.includes(type) ? (
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							tagName="div"
							label="Placeholder"
							value={placeholder}
							onChange={(placeholder) => setAttributes({ placeholder })}
							placeholder={__(
								"Enter label content here...",
								"borobazar-helper",
							)}
						/>
					) : (
						""
					)}
					{showLabel ? (
						<>
							<TextControl
								__nextHasNoMarginBottom
								__next40pxDefaultSize
								tagName="div"
								value={label}
								label="Label"
								onChange={(label) => setAttributes({ label })}
								placeholder={__(
									"Enter placeholder content here...",
									"borobazar-helper",
								)}
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label="Required"
								checked={isRequired}
								onChange={(isRequired) => setAttributes({ isRequired })}
							/>
						</>
					) : (
						""
					)}
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						tagName="div"
						value={name}
						label="Name"
						onChange={(name) => setAttributes({ name })}
						placeholder={__("Enter name content here...", "borobazar-helper")}
					/>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						tagName="div"
						label="Help"
						value={help}
						onChange={(help) => setAttributes({ help })}
						placeholder={__("Enter help content here...", "borobazar-helper")}
					/>
					{type === "password" ? (
						<ToggleControl
							__nextHasNoMarginBottom
							label="Password Toggle"
							checked={passwordToggle}
							onChange={(passwordToggle) => setAttributes({ passwordToggle })}
						/>
					) : (
						""
					)}
				</PanelBody>

				{/* Color settings */}
				<PanelBody
					title={__("Color settings", "borobazar-helper")}
					initialOpen={false}
				>
					{colorSettings.map((palette) => (
						<ColorPaletteControl
							key={palette.label}
							label={palette.label}
							value={palette.color}
							onChange={palette.onChange}
						/>
					))}
				</PanelBody>
			</InspectorControls>
		</div>
	);
}
