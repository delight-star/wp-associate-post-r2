const transforms = {
    from: [
        {
            type: "shortcode",
            tag: "wpap",
            attributes: {
                service: {
                    type: "string",
                    shortcode: attributes => attributes.named.service || "",
                },
                id: {
                    type: "string",
                    shortcode: attributes => attributes.named.id || "",
                },
                type: {
                    type: "string",
                    shortcode: attributes => attributes.named.type || "",
                },
                title: {
                    type: "string",
                    shortcode: attributes => attributes.named.title || "",
                },
                css_class: {
                    type: "string",
                    shortcode: attributes => attributes.named.css_class || "",
                },
                search: {
                    type: "string",
                    shortcode: attributes => attributes.named.search || "",
                }
            }
        }
    ]
};
export default transforms;