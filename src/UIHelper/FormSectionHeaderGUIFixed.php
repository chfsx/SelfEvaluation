<?php
namespace ilub\plugin\SelfEvaluation\UIHelper;

use ilFormSectionHeaderGUI;
use ilTemplate;

class FormSectionHeaderGUIFixed extends ilFormSectionHeaderGUI
{
    function insert($a_tpl = null)
    {
        /**
         * @var ilTemplate $a_tpl
         */
        $a_tpl->setCurrentBlock("header");
        $a_tpl->setVariable("TXT_TITLE", $this->getTitle());
        $a_tpl->setVariable("TXT_DESCRIPTION", $this->getInfo());
        if (isset($this->section_anchor)) {
            $a_tpl->setVariable('LABEL', $this->section_anchor);
        }

        $a_tpl->parseCurrentBlock();
    }

    public function getRequired() : bool
    {
        return true;
    }
}
