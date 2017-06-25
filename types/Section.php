<?php
namespace dokuwiki\plugin\structsection\types;

use dokuwiki\plugin\struct\types\TraitFilterPrefix;
use dokuwiki\plugin\struct\types\Wiki;

class Section extends Wiki {
    use TraitFilterPrefix;

    protected $config = array(
        'prefix' => '',
        'postfix' => '',
        'placeholder' => '', // ToDo: Make translatable
        'visibility' => [
            'ineditor' => true, // removing the inpage-key to prevent early rendering
        ]
    );

    /**
     * Use a text area for input
     *
     * @param string $name
     * @param string $rawvalue
     * @param string $htmlID
     *
     * @return string
     */
    public function valueEditor($name, $rawvalue, $htmlID) {
        $rawvalue = formText($rawvalue);
        $params = array(
            'name' => $name,
            'class' => 'struct_'.strtolower($this->getClass()),
            'id' => $htmlID,
            'placeholder' => $this->config['placeholder'],
        );
        $attributes = buildAttributes($params, true);

        return "<textarea $attributes>$rawvalue</textarea>";
    }
}
