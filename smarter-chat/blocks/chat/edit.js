/**
 * The Smarter Chat block's editor: a placeholder in the content, and its settings in the sidebar. The block is
 * dynamic (see Smarter_Chat_Shortcode::render_block), so it saves nothing but its attributes.
 *
 * Plain JavaScript, with WordPress's globals, so that the plugin has no build. See doc/adr/005-placement.md.
 */
(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { createElement: el, Fragment } = wp.element;
  const { InspectorControls, useBlockProps } = wp.blockEditor;
  const { PanelBody, Placeholder, TextControl } = wp.components;
  const { __ } = wp.i18n;

  registerBlockType("smarter/chat", {
    edit({ attributes, setAttributes }) {
      const blockProps = useBlockProps();
      return el(
        Fragment,
        null,
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: __("Chatbot", "smarter-chat") },
            el(TextControl, {
              label: __("Chatbot url", "smarter-chat"),
              help: __("Empty: the chatbot in Settings → Smarter Chat.", "smarter-chat"),
              type: "url",
              value: attributes.chatbot,
              onChange: (chatbot) => setAttributes({ chatbot }),
            }),
            el(TextControl, {
              label: __("Height", "smarter-chat"),
              help: __("A css length, e.g. 600px or 70vh.", "smarter-chat"),
              value: attributes.height,
              onChange: (height) => setAttributes({ height }),
            }),
          ),
        ),
        el(
          "div",
          blockProps,
          el(Placeholder, {
            icon: "format-chat",
            label: __("Smarter Chat", "smarter-chat"),
            instructions: attributes.chatbot || __("The chatbot in Settings → Smarter Chat.", "smarter-chat"),
          }),
        ),
      );
    },
    save: () => null,
  });
})(window.wp);
