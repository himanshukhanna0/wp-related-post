// Import WordPress block editor dependencies
const { registerBlockType } = wp.blocks;                         // Used to register the block
const { InspectorControls } = wp.blockEditor || wp.editor;       // Sidebar controls in the block editor
const { PanelBody, TextControl } = wp.components;                // UI components: panel + input
const { createElement: el } = wp.element;                        // Shorthand for creating elements

// Register the block with a unique name (namespace/block-name)
registerBlockType('rpb/related-posts', {
    title: 'Related Posts',     // Display name in block inserter
    icon: 'admin-post',         // Dashicon used for the block icon
    category: 'widgets',        // Block category in the inserter

    // Declare block attributes (data stored with the block)
    attributes: {
        title: {
            type: 'string',     // The title is a string
            default: 'Related Post', // Default value if user doesn't set one
        },
    },

    // Editor UI - what the block shows in the editor
    edit: ({ attributes, setAttributes }) => {
        return el('div', {},

            // Block settings panel (in the right-hand sidebar)
            el(InspectorControls, {},
                el(PanelBody, { title: 'Settings' },
                    el(TextControl, {
                        label: 'Title',                        // Label shown to the user
                        value: attributes.title,              // Bind input to `title` attribute
                        onChange: (val) => setAttributes({ title: val }), // Update attribute on change
                    })
                )
            ),

            // Placeholder content in the main block area
            el('p', {}, 'This block shows 3 related posts on the frontend.') 
        );
    },

    // The block saves no content in post HTML (server-rendered via PHP)
    save: () => null,
});
