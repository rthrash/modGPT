Ext.onReady(function() {
    console.log('autosummary: Ext.onReady triggered.');

    // Delay execution by 500ms to allow ExtJS components to initialize.
    Ext.defer(function() {
        console.log('autosummary: Deferred function executing after 500ms.');

        // Locate the label element for the summary (introtext) field.
        // This assumes the label has an attribute for="modx-resource-introtext".
        var labelEl = Ext.select('label[for="modx-resource-introtext"]').first();
        if (!labelEl) {
            console.error('autosummary: Label element for "modx-resource-introtext" not found.');
            return;
        } else {
            console.log('autosummary: Label element found:', labelEl);
        }

        // Insert the magic wand emoji (🪄) immediately after the label element.
        labelEl.insertHtml('afterEnd', '<span id="autosummary-wand" style="cursor:pointer; margin-left:5px; vertical-align:middle; font-size:24px;">🪄</span>');
        console.log('autosummary: Wand icon inserted next to the label.');

        // Retrieve the newly inserted wand element.
        var wandEl = Ext.get('autosummary-wand');
        if (!wandEl) {
            console.error('autosummary: Failed to retrieve the wand element.');
            return;
        }
        console.log('autosummary: Wand element found:', wandEl);

        // Attach a click event handler to the wand icon.
        wandEl.on('click', function() {
            console.log('autosummary: Wand icon clicked.');
            Ext.Msg.wait('Generating summary...', 'Please wait');

            // Attempt to retrieve the resource ID component.
            var resourceIdCmp = Ext.getCmp('modx-resource-id');
            if (!resourceIdCmp) {
                console.error('autosummary: Resource ID component (modx-resource-id) not found.');
                Ext.Msg.alert('Error', 'Resource ID not found.');
                return;
            }
            var resourceId = resourceIdCmp.getValue();
            console.log('autosummary: Retrieved resource ID:', resourceId);

            // Determine the connector URL, with a fallback if not set.
            var connectorUrl = MODx.config.connector_url ? MODx.config.connector_url : '/connectors/index.php';
            console.log('autosummary: Using connector URL: ' + connectorUrl);

            // Make an AJAX request to our custom processor.
            Ext.Ajax.request({
                url: connectorUrl,
                params: {
                    action: 'autosummary/summarize',
                    id: resourceId
                },
                success: function(response) {
                    console.log('autosummary: AJAX request succeeded. Response:', response.responseText);
                    Ext.Msg.hide();
                    var res = Ext.decode(response.responseText);
                    if (res.success) {
                        console.log('autosummary: Received summary from processor:', res.summary);
                        // Set the generated summary into the introtext field.
                        var introTextField = Ext.getCmp('modx-resource-introtext');
                        if (introTextField) {
                            introTextField.setValue(res.summary);
                        } else {
                            console.error('autosummary: Introtext component not found on AJAX success.');
                        }
                    } else {
                        console.error('autosummary: Server returned an error:', res.message);
                        Ext.Msg.alert('Error', res.message);
                    }
                },
                failure: function(response) {
                    console.error('autosummary: AJAX request failed:', response);
                    Ext.Msg.hide();
                    Ext.Msg.alert('Error', 'An error occurred while generating the summary.');
                }
            });
        });
        console.log('autosummary: Wand click event attached.');
    }, 500); // 500ms delay
});