<?php
/**
 * DokuWiki Plugin structsection (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Große <mic.grosse@googlemail.com>
 */

class action_plugin_structsection extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PARSER_HANDLER_DONE', 'AFTER', $this, 'handle_parser_done');
        $controller->register_hook('PLUGIN_STRUCT_TYPECLASS_INIT', 'BEFORE', $this, 'handle_init');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_parser_done(Doku_Event $event, $param) {
        /** @var helper_plugin_struct $struct */
        $struct = plugin_load('helper', 'struct');
        if (!$struct) {
            return;
        }

        global $ACT;

        if (act_clean($ACT) != 'show') {
            dbglog($ACT, __FILE__ .': '.__LINE__);
            return;
        }

        $last = end($event->data->calls);
        $pos = $last[2];

        $event->data->calls[] = array(
            'plugin',
            array(
                'structsection', array('pos' => $pos), DOKU_LEXER_SPECIAL, '',
            ),
            $pos,
        );
    }

    public function handle_init(Doku_Event &$event, $param) {
        $event->data['Section'] = 'dokuwiki\\plugin\\structsection\\types\\Section';
    }

}

// vim:ts=4:sw=4:et:
