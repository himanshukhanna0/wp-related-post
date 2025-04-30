// block.js
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { PanelBody, TextControl } = wp.components;
const { createElement: el } = wp.element;

registerBlockType('rpb/related-posts', {
    title: 'Related Posts',
    icon: 'admin-post',
    category: 'widgets',
    attributes: {
        title: {
            type: 'string',
            default: '',
        },
    },
    edit: ({ attributes, setAttributes }) => {
        return el('div', {},
            el(InspectorControls, {},
                el(PanelBody, { title: 'Settings' },
                    el(TextControl, {
                        label: 'Title',
                        value: attributes.title,
                        onChange: (val) => setAttributes({ title: val }),
                    })
                )
            ),
            el('p', {}, 'This block shows 3 related posts on the frontend.')
        );
    },
    save: () => null, // Server-rendered via PHP
});
