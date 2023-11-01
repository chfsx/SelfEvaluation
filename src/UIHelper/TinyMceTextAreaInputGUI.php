<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\UIHelper;

use ilTextAreaInputGUI;

class TinyMceTextAreaInputGUI extends ilTextAreaInputGUI
{
    public function __construct(int $ref_id, string $plugin_id, $a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setUseRte(true);
        $this->setRTESupport($ref_id, $plugin_id, '', null, false);
        $this->setRteTagSet('full');
        $this->disableButtons([
            'charmap',
            'undo',
            'redo',
            'justifyleft',
            'justifycenter',
            'justifyright',
            'justifyfull',
            'anchor',
            'fullscreen',
            'cut',
            'copy',
            'paste',
            'pastetext',
            'pasteword',
            'imgupload',
            'ilimgupload']);
    }
}
