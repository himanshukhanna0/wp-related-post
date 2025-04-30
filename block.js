const { registerBlockType } = wp.blocks;
const { TextControl } = wp.components;
const { useBlockProps } = wp.blockEditor;

// This registers a new block in the Gutenberg editor
registerBlockType('rpb/related-posts', {
    title: 'Related Posts',
    icon: 'admin-post', // Dashicon used in the block list
    category: 'widgets', // Where it shows up in the editor

    // This defines the fields/attributes the block supports
    attributes: {
        title: {
            type: 'string',
            default: '',
        },
    },

    // This is what shows up when you're editing the block in the admin
    edit({ attributes, setAttributes }) {
        const blockProps = useBlockProps();

        return (
            <div {...blockProps}>
                <TextControl
                    label="Block Title"
                    value={attributes.title}
                    onChange={(val) => setAttributes({ title: val })}
                />
                <p>This block will display related posts when published.</p>
            </div>
        );
    },

    // No need to save any HTML — we'll render it with PHP on the front end
    save() {
        return null;
    }
});
