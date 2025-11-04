jQuery(() => {
    /**
     * This removes unintended elements created by other plugins and broken for structsection
     *
     * Currently it removes the edit-button shown by edittable, which cannot work inside this plugin
     *
     * @returns {void}
     */
    function cleanOtherPlugins() {
        jQuery('#plugin__structsection_output').find('.editbutton_table').remove();
    }
    cleanOtherPlugins();

    jQuery('#plugin__structsection_output').on('submit', 'form.btn_secedit', function handleEdit(e) {
        e.preventDefault();
        e.stopPropagation();

        const $self = jQuery(this).parents('.section_highlight_wrapper').find('div[data-struct]');
        const pid = JSINFO.id;
        const field = $self.data('struct');
        const { rev } = JSINFO.plugin_structsection;
        const rid = 0;

        if (!pid) return;
        if (!field) return;

        // prepare the edit overlay
        const $div = jQuery('<div class="struct_inlineditor"><form></form><div class="err"></div></div>');
        const $form = $div.find('form');
        const $errors = $div.find('div.err').hide();
        const $save = jQuery('<button type="submit">Save</button>');
        const $cancel = jQuery('<button>Cancel</button>');
        $form.append(jQuery('<input type="hidden" name="pid">').val(pid));
        $form.append(jQuery('<input type="hidden" name="field">').val(field));
        $form.append(jQuery('<input type="hidden" name="rid">').val(rid));
        $form.append(jQuery('<input type="hidden" name="rev">').val(rev));
        $form.append('<input type="hidden" name="call" value="plugin_struct_inline_save">');
        $form.append(jQuery('<div class="ctl">').append($save).append($cancel));

        /**
         * load the editor
         */
        jQuery.post(
            `${DOKU_BASE}lib/exe/ajax.php`,
            {
                call: 'plugin_struct_inline_editor',
                pid,
                field,
                rid,
                rev,
            },
            (data) => {
                if (!data) return; // we're done

                $form.prepend(data);

                // show
                $self.closest('.dokuwiki').append($div);
                $div.position({
                    my: 'left top',
                    at: 'left top',
                    of: $self,
                });

                // attach entry handlers to the inline form
                // EntryEditor($form);

                // focus first input
                $form.find('input, textarea').first().focus();
            },
        );

        /**
         * Save the data, then close the form
         */
        $form.submit((submitEvent) => {
            submitEvent.preventDefault();
            jQuery.post(
                `${DOKU_BASE}lib/exe/ajax.php`,
                $form.serialize(),
            )
                .done((data) => {
                    const saved = JSON.parse(data);
                    $self.html(saved.value);
                    JSINFO.plugin_structsection.rev = saved.rev;
                    // save succeeded display new value and close editor
                    $div.remove();
                })
                .fail((data) => {
                    // something went wrong, display error
                    $errors.text(data.responseText).show();
                });
        });

        /**
         * Close the editor without saving
         */
        $cancel.click((clickEvent) => {
            // unlock page
            jQuery.post(
                `${DOKU_BASE}lib/exe/ajax.php`,
                {
                    call: 'plugin_struct_inline_cancel',
                    pid,
                },
            );

            clickEvent.preventDefault();
            $div.remove();
        });
    });
});
