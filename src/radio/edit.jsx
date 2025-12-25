import { InspectorControls, RichText, useBlockProps } from "@wordpress/block-editor";
import {
	PanelBody,
	TextControl,
	Button,
	IconButton,
	ToggleControl,
} from "@wordpress/components";
import { plus, trash } from "@wordpress/icons";
import { v4 as uuidv4 } from "uuid";

export default function Edit({ attributes, setAttributes }) {
	const { label, options, name, required, defaultValue } = attributes;
	const blockProps = useBlockProps();
	const id = uuidv4();

	const updateOption = (index, key, value) => {
		const newOptions = options.map((opt, i) =>
			i === index ? { ...opt, [key]: value } : opt,
		);
		setAttributes({ options: newOptions });
	};

	const addOption = () => {
		setAttributes({
			options: [...options, { label: "New option", value: "" }],
		});
	};

	const removeOption = (index) => {
		setAttributes({
			options: options.filter((_, i) => i !== index),
		});
	};

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title="Radio Settings">
					<TextControl
						label="Field Label"
						value={label}
						onChange={(value) => setAttributes({ label: value })}
					/>

					<TextControl
						label="Field Name"
						value={name}
						onChange={(value) => setAttributes({ name: value })}
					/>

					<ToggleControl
						label="Required"
						checked={required}
						onChange={(value) => setAttributes({ required: value })}
					/>

					<TextControl
						label="Default Value"
						value={defaultValue}
						onChange={(value) => setAttributes({ defaultValue: value })}
					/>
				</PanelBody>
			</InspectorControls>
			<InspectorControls>
				<PanelBody title="Radio Options" initialOpen>
					{options.map((option, index) => (
						<div
							key={index}
							style={{
								borderBottom: "1px solid #ddd",
								paddingBottom: 8,
								marginBottom: 8,
							}}
						>
							<TextControl
								label={`Label`}
								value={option.label}
								onChange={(value) => updateOption(index, "label", value)}
							/>

							<TextControl
								label={`Value`}
								value={option.value}
								onChange={(value) => updateOption(index, "value", value)}
							/>

							<IconButton
								icon={trash}
								label="Remove option"
								onClick={() =>
									confirm("Are you sure you want to remove this option?") &&
									removeOption(index)
								}
								isDestructive
							/>
						</div>
					))}

					<Button variant="primary" icon={plus} onClick={addOption}>
						Add Option
					</Button>
				</PanelBody>
			</InspectorControls>

			<div className="radio-input">
				<label>
					{label}
					{required && <span className="required">*</span>}
				</label>

				{options.map((option, index) => (
					<label key={index} htmlFor={`${id}-${index}-${name}`}>
						<input
							type="radio"
							name={name}
							id={`${id}-${index}-${name}`}
							value={option.value}
							defaultChecked={defaultValue === option.value}
							required={required}
						/>
						<span>{option.label}</span>
					</label>
				))}
			</div>
		</div>
	);
}
