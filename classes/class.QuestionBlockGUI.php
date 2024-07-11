<?php

declare(strict_types=1);
use ilub\plugin\SelfEvaluation\Block\BlockGUI;
use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock;

class QuestionBlockGUI extends BlockGUI
{
    /**
     * @var \ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock|\ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock
     */
    protected $object;

    public function __construct(
        ilDBInterface $db,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilAccessHandler $access,
        ilSelfEvaluationPlugin $plugin,
        ilObjSelfEvaluationGUI $parent
    ) {
        parent::__construct($db, $tpl, $ilCtrl, $access, $plugin, $parent);
        if ($parent->http->query()->has('block_id')){
            $this->object = new QuestionBlock($this->db, $parent->http->query()->retrieve('block_id', $parent->refinery->kindlyTo()->int()));
        }
        else {
            $this->object = new QuestionBlock($this->db);
        }
        $this->object->setParentId($this->parent->getObjId());
    }

    public function initForm(string $mode = 'create')
    {
        parent::initForm($mode);

        $te = new ilTextInputGUI($this->plugin->txt('abbreviation'), 'abbreviation');
        $te->setInfo($this->plugin->txt("block_abbreviation_info"));
        $te->setMaxLength(8);
        $this->form->addItem($te);
    }

    protected function setObjectValuesByPost()
    {
        parent::setObjectValuesByPost();
        $this->object->setAbbreviation($this->form->getInput('abbreviation'));
    }

    protected function getObjectValuesAsArray(): array
    {
        $values = ['abbreviation' => $this->object->getAbbreviation()];

        return array_merge(parent::getObjectValuesAsArray(), $values);
    }
}
