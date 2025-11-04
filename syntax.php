<?php

/**
 * DokuWiki Plugin structsection (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Große <mic.grosse@googlemail.com>
 */

use dokuwiki\plugin\struct\meta\AccessTable;
use dokuwiki\plugin\struct\meta\Assignments;
use dokuwiki\plugin\struct\meta\StructException;

class syntax_plugin_structsection extends \DokuWiki_Syntax_Plugin
{
    protected $hasBeenRendered = false;

    private const XHTML_OPEN = '<div id="plugin__structsection_output">';
    private const XHTML_CLOSE = '</div>';

    /**
     * @return string Syntax mode type
     */
    public function getType()
    {
        return 'substition';
    }

    /**
     * @return string Paragraph type
     */
    public function getPType()
    {
        return 'block';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort()
    {
        return 155;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * We do not connect any pattern here, because the call to this plugin is not
     * triggered from syntax but our action component
     *
     * @asee action_plugin_structsection
     * @param string $mode Parser mode
     */
    public function connectTo($mode)
    {
    }

    /**
     * Handle matches of the struct syntax
     *
     * @param string $match The match of the syntax
     * @param int $state The state of the handler
     * @param int $pos The position in the document
     * @param \Doku_Handler $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, \Doku_Handler $handler)
    {
        // this is never called
        return array();
    }

    /**
     * Render schema data
     *
     * Currently completely renderer agnostic
     *
     * @param string $mode Renderer mode
     * @param \Doku_Renderer $R The renderer
     * @param array $handlerData The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, \Doku_Renderer $R, $handlerData)
    {
        global $ID;
        global $INFO;
        global $REV;
        if ($ID != $INFO['id']) {
            return true;
        }
        if (!$INFO['exists']) {
            return true;
        }
        if ($this->hasBeenRendered) {
            return true;
        }

        // do not render the output twice on the same page, e.g. when another page has been included
        $this->hasBeenRendered = true;
        try {
            $assignments = Assignments::getInstance();
        } catch (StructException $e) {
            return false;
        }
        $tables = $assignments->getPageAssignments($ID);
        if (!$tables) {
            return true;
        }

        $pos = $handlerData['pos'];
        if ($mode == 'xhtml') {
            $R->finishSectionEdit($pos - 1);
            $R->doc .= self::XHTML_OPEN;
        }


        $hasdata = false;
        foreach ($tables as $table) {
            try {
                $schemadata = AccessTable::getPageAccess($table, $ID, $REV);
            } catch (StructException $ignored) {
                continue; // no such schema at this revision
            }
            $schemadata->optionSkipEmpty(false);
            $data = $schemadata->getData();
            if (!count($data)) {
                continue;
            }
            $hasdata = true;

            foreach ($data as $field) {
                if (!is_a($field->getColumn()->getType(), \dokuwiki\plugin\structsection\types\Section::class)) {
                    continue;
                }
                $lvl = 2;
                $R->header($field->getColumn()->getTranslatedLabel(), $lvl, $pos);
                $pos += strlen($field->getColumn()->getTranslatedLabel());
                $R->section_open($lvl);
                if ($mode === 'xhtml') {
                    $structDataAttribute = 'data-struct="' . hsc($field->getColumn()->getFullQualifiedLabel()) . '"';
                    $R->doc = substr($R->doc, 0, -2) . ' ' . $structDataAttribute . '>' . "\n";
                }
                $field->render($R, $mode);
                $R->section_close();
            }
        }

        if ($mode == 'xhtml') {
            $R->finishSectionEdit($pos);
            $R->doc .= self::XHTML_CLOSE;
        }

        // if no data has been output, remove empty wrapper again
        if ($mode == 'xhtml' && !$hasdata) {
            $R->doc = substr($R->doc, 0, -1 * strlen(self::XHTML_OPEN . self::XHTML_CLOSE));
        }

        return true;
    }
}

// vim:ts=4:sw=4:et:
