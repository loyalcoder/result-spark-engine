import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import {
	PanelBody,
	TextControl,
	Button,
	IconButton,
	ToggleControl,
	SelectControl,
} from "@wordpress/components";
import { plus, trash } from "@wordpress/icons";
import { v4 as uuidv4 } from "uuid";
import { useSelect } from "@wordpress/data";
import { store as coreStore } from "@wordpress/core-data";
import { Fragment } from "react/jsx-runtime";
import "./editor.scss";

export default function Edit({ attributes, setAttributes }) {
	const {
		label,
		options,
		name,
		isRequired,
		showLabel,
		defaultValue,
		useTaxonomy,
		postType,
		taxonomy,
	} = attributes;
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

	const terms = useSelect(
		(select) =>
			select(coreStore).getEntityRecords("taxonomy", taxonomy, {
				per_page: -1,
			}),
		[taxonomy],
	);

	const taxonomies = useSelect(
		(select) => select(coreStore).getTaxonomies({ type: postType }),
		[postType],
	);

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
						label="Use Taxonomy"
						checked={useTaxonomy}
						onChange={(value) => setAttributes({ useTaxonomy: value })}
					/>

					<ToggleControl
						label="Show Label"
						checked={showLabel}
						onChange={(value) => setAttributes({ showLabel: value })}
					/>

					<ToggleControl
						label="Required"
						checked={isRequired}
						onChange={(value) => setAttributes({ isRequired: value })}
					/>
					{!useTaxonomy ? (
						<TextControl
							label="Default Value"
							value={defaultValue}
							onChange={(value) => setAttributes({ defaultValue: value })}
						/>
					) : (
						<Fragment>
							<SelectControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label="Post Types"
								value={postType}
								options={[
									{ label: "Students", value: "students" },
									{ label: "Exam", value: "exam" },
								]}
								onChange={(postType) => setAttributes({ postType })}
							/>
							<SelectControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label="Taxonomies"
								value={taxonomy}
								options={taxonomies?.map((tax) => {
									return { label: tax.name, value: tax.slug };
								})}
								onChange={(taxonomy) => setAttributes({ taxonomy })}
							/>
						</Fragment>
					)}
				</PanelBody>
			</InspectorControls>
			{!useTaxonomy ? (
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
			) : (
				""
			)}

			<div className="spark-engine-input-wrapper-block">
				{showLabel && label ? (
					<label className="spark-engine-input-label" htmlFor={`${name}-${id}`}>
						{label}
						{isRequired ? (
							<span className="spark-engine-input-required">*</span>
						) : (
							""
						)}
					</label>
				) : (
					""
				)}
				<div className={`spark-engine-check-input-wrapper`}>
					{!useTaxonomy ? (
						options?.length > 0 ? (
							<select
								className="spe-select-input"
								name={name}
								id={`${id}-${name}`}
								defaultValue={defaultValue}
							>
								{options?.map((option, index) => (
									<option key={index} value={option?.value}>
										{option?.label}
									</option>
								))}
							</select>
						) : (
							<div className="spe-not-found">No Data Found</div>
						)
					) : terms?.length > 0 ? (
						<select
							className="spe-select-input"
							name={name}
							id={`${id}-${name}`}
						>
							{terms?.map((term, index) => (
								<option key={index} value={term?.slug}>
									{term?.name}
								</option>
							))}
						</select>
					) : (
						<div className="spe-not-found">No Data Found</div>
					)}
				</div>
			</div>
		</div>
	);
}
