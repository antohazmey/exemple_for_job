(function (richText, element, editor) {
var el = element.createElement,
    fragment = element.Fragment;

var registerFormatType = richText.registerFormatType,
    unregisterFormatType = richText.unregisterFormatType,
    toggleFormat = richText.toggleFormat;

var richTextToolbarButton = editor.RichTextToolbarButton,
    richTextShortcut = editor.RichTextShortcut;

var type = "core/mark";

unregisterFormatType(type);
registerFormatType(type, {
    title:      "Маркер",
    tagName:    "mark",
    className:  null,
    edit: function edit(props) {
        var isActive = props.isActive,
            value = props.value,
            onChange = props.onChange;

        var onToggle = function() {
            return onChange(toggleFormat(value, { type: type }));
        };

        return el(
            fragment,
            null,
            el(richTextShortcut, {
                type: "access",
                character: "x",
                onUse: onToggle
            }),
            el(richTextToolbarButton, {
                icon: "editor-mark",
                title: "Маркер",
                onClick: onToggle,
                isActive: isActive,
                shortcutType: "access",
                shortcutCharacter: "x"
            })
        );
    }
});

}(
    window.wp.richText,
    window.wp.element,
    window.wp.editor
));