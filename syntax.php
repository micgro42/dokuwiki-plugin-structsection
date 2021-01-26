<?php

declare(strict_types=1);

use dokuwiki\plugin\struct\meta\{AccessTable, Assignments, StructException, Value};
use dokuwiki\plugin\structsection\types\Section;

/**
 * DokuWiki Plugin structsection (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Große <mic.grosse@googlemail.com>
 */
final class syntax_plugin_structsection extends \DokuWiki_Syntax_Plugin
{

    protected $hasBeenRendered = false;

    private const XHTML_OPEN = '<div id="plugin__structsection_output">';
    private const XHTML_CLOSE = '</div>';

    /**
     * @return string Syntax mode type
     */
    public function getType(): string
    {
        return 'substition';
    }

    /**
     * @return string Paragraph type
     */
    public function getPType(): string
    {
        return 'block';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort(): int
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
    public function connectTo($mode): void
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
    public function handle($match, $state, $pos, \Doku_Handler $handler): array
    {
        // this is never called
        return [];
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
    public function render($mode, \Doku_Renderer $R, $handlerData): bool
    {
        global $ID;
        global $INFO;
        global $REV;
        if ($handlerData['id'] !== $ID) {
            return true;
        }
        if (!$INFO['exists']) {
            return true;
        }

        $assignments = Assignments::getInstance();
        $tablesAssignedToThisPage = $assignments->getPageAssignments($ID);
        if (!$tablesAssignedToThisPage) {
            return true;
        }

        $pos = $handlerData['pos'];
        if ($mode == 'xhtml') {
            $R->finishSectionEdit($pos - 1); // FIXME: shouldn't this be $R->startSectionEdit ?
            $R->doc .= self::XHTML_OPEN;
        }

        $hasRenderedSomething = $this->renderTables($R, $mode, $tablesAssignedToThisPage, $ID, $REV, $pos);

        if ($mode == 'xhtml') {
            $R->finishSectionEdit($pos);
            $R->doc .= self::XHTML_CLOSE;
        }

        // if no data has been output, remove empty wrapper again
        if ($mode == 'xhtml' && !$hasRenderedSomething) {
            $R->doc = substr($R->doc, 0, -1 * strlen(self::XHTML_OPEN . self::XHTML_CLOSE));
        }

        return true;
    }

    private function renderTables($R, $mode, $tables, $ID, $REV, $pos): bool
    {
        $hasRenderedSomething = false;
        foreach ($tables as $table) {
            $data = $this->getTableData($table, $ID, $REV);
            if ($data === null) {
                continue;
            }

            foreach ($data as $field) {
                if (!is_a($field->getColumn()->getType(), Section::class)) {
                    continue;
                }
                $hasRenderedSomething = true;
                $this->renderSection($R, $field, $mode, $pos);
                $pos += strlen($field->getColumn()->getTranslatedLabel());
            }
        }
        return $hasRenderedSomething;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @return Value[]|null
     */
    private function getTableData(string $tablename, string $ID, int $REV)
    {
        try {
            $schemadata = AccessTable::getPageAccess($tablename, $ID, $REV);
        } catch (StructException $ignored) {
            return null; // no such schema at this revision
        }
        $schemadata->optionSkipEmpty(false);
        $data = $schemadata->getData();
        if (!count($data)) {
            return null;
        }
        return $data;
    }

    private function renderSection(\Doku_Renderer $R, Value $field, string $mode, int $pos): void
    {
        $lvl = 2;
        $R->header($field->getColumn()->getTranslatedLabel(), $lvl, $pos);
        $R->section_open($lvl);
        if ($mode === 'xhtml') {
            $structDataAttribute = 'data-struct="' . hsc($field->getColumn()->getFullQualifiedLabel()) . '"';
            $R->doc = substr($R->doc, 0, -2) . ' ' . $structDataAttribute . '>' . "\n";
        }
        $field->render($R, $mode);
        $R->section_close();
    }
}

// vim:ts=4:sw=4:et:
