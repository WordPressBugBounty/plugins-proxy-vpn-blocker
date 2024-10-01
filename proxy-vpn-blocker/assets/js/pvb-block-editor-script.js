(function (plugins, editor, components, data, i18n, element) {
    var registerPlugin = plugins.registerPlugin;
    var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
    var CheckboxControl = components.CheckboxControl;
    var withSelect = data.withSelect;
    var withDispatch = data.withDispatch;
    var el = element.createElement;
    var __ = i18n.__;

    // Sidebar option registration
    registerPlugin('proxy-vpn-blocker-post-options', {
        render: withSelect(function (select) {
            return {
                metaValue: select('core/editor').getEditedPostAttribute('meta')['_pvb_checkbox_block_on_post'],
            };
        })(withDispatch(function (dispatch, props) {
            return {
                setMetaValue: function (value) {
                    dispatch('core/editor').editPost({ meta: { '_pvb_checkbox_block_on_post': value } });
                },
            };
        })(function (props) {
            var metaValue = props.metaValue;
            var setMetaValue = props.setMetaValue;

            // Ensure that the initial state is set appropriately
            var isChecked = metaValue === true;

            return el(PluginDocumentSettingPanel, {
                name: 'proxy-vpn-blocker-post-options',
                title: 'Proxy & VPN Blocker',
                icon: 'shield',
            },
                el(CheckboxControl, {
                    label: __('Block on this Page/Post'),
                    checked: isChecked,
                    onChange: function (value) {
                        setMetaValue(value);
                    },
                })
            );
        })),
    });
})(window.wp.plugins, window.wp.editor, window.wp.components, window.wp.data, window.wp.i18n, window.wp.element);