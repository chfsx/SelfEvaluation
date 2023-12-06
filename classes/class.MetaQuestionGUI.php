<?php

declare(strict_types=1);

use ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaTypeOption;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaQuestionType;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaTypeFactory;
use ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion;
use ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestionTableGUI;
use ilub\plugin\SelfEvaluation\Question\BaseQuestionGUI;

class MetaQuestionGUI extends BaseQuestionGUI
{
    /**
     * @var MetaBlock
     */
    protected $block;

    /**
     * @var MetaQuestionType[]
     */
    protected $types;

    /**
     * @var MetaQuestion
     */
    protected $question;

    /**
     * @var bool
     */
    protected $enable_sorting = true;


    protected function createTableGUI(): ilTable2GUI
    {
        return new MetaQuestionTableGUI($this, $this->plugin, $this->tpl, 'showContent', $this->getTypes(), $this->hasSorting(), $this->block);
    }

    protected function initQuestionForm(string $mode = 'create')
    {
        parent::initQuestionForm($mode);

        $na = new ilTextInputGUI($this->plugin->txt('question'), 'question');
        $na->setSize(32);
        $na->setMaxLength(255);
        $na->setRequired(true);
        $this->form->addItem($na);

        $na = new ilTextInputGUI($this->plugin->txt('short_title'), 'short_title');
        $na->setSize(32);
        $na->setMaxLength(255);
        $na->setRequired(true);
        $this->form->addItem($na);

        $ty = new ilRadioGroupInputGUI($this->plugin->txt('type'), 'type');
        $ty->setRequired(true);
        $this->form->addItem($ty);

        foreach ($this->getTypes() as $type) {
            $option = $type->getValueDefinitionInputGUI($this->plugin, new MetaTypeOption());
            /**
             * @var MetaTypeOption $option
             */
            $option->setTitle($this->plugin->txt($type->getTypeName()));
            $option->setValue((string)$type->getId());
            $ty->addOption($option);
        }

        $re = new ilCheckboxInputGUI($this->plugin->txt('required_field'), 'required');
        $re->setValue('1');
        $this->form->addItem($re);
    }

    protected function setQuestionFormValues()
    {
        $item = $this->form->getItemByPostVar('question');
        /**
         * @var ilTextInputGUI $item
         */
        $item->setValue($this->question->getName());
        $item = $this->form->getItemByPostVar('short_title');
        $item->setValue($this->question->getShortTitle());
        $item = $this->form->getItemByPostVar('type');
        $item->setValue((string)$this->question->getTypeId());
        $item = $this->form->getItemByPostVar('required');
        /**
         * @var ilCheckboxInputGUI $item
         */
        $item->setChecked((bool)$this->question->isRequired());

        /** @var ilRadioGroupInputGUI $group */
        $group = $this->form->getItemByPostVar('type');
        $option = $this->getValueDefinitionInputGuiByTypeId($group, (int)$this->question->getTypeId());
        $type = $this->getTypes()[$this->question->getTypeId()];
        $type->setValues($option, $this->question->getValues());
    }

    protected function getValueDefinitionInputGuiByTypeId(ilRadioGroupInputGUI $group, int $type_id): ?MetaTypeOption
    {
        $options = $group->getOptions();
        if (is_array($options)) {
            /** @var MetaTypeOption[] $options */
            foreach ($options as $option) {
                if ($option->getValue() == $type_id) {
                    return $option;
                }
            }
        }
        return null;
    }

    protected function createQuestionSetFields()
    {
        $this->question->setName($this->form->getInput('question'));
        $this->question->setShortTitle($this->form->getInput('short_title'));
        $this->question->setTypeId((int)$this->form->getInput('type'));
        $this->question->setValues($this->getFormValuesByTypeId((int)$this->form->getInput('type')));
        $this->question->enableRequired((bool)$this->form->getInput('required'));
    }

    protected function getFormValuesByTypeId(int $type_id): array
    {
        $type = $this->getTypes()[$type_id];

        if (!$type instanceof MetaQuestionType) {

            return [];
        }

        $post_values = $type->getValues($this->form);
        if (!is_array($post_values)) {

            return [];
        }

        $values = [];
        foreach ($post_values as $key => $value) {
            $value = trim(ilUtil::stripSlashes($value));
            if (strlen($value)) {
                $values[$key] = $value;
            }
        }

        return $values;

    }

    protected function saveRequired()
    {
        foreach (MetaQuestion::_getAllInstancesForParentId($this->db, $this->block->getId()) as $question) {
            $question->enableRequired(isset($_POST['required'][$question->getId()]));
            $question->update();
        }

        $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->plugin->txt('msg_question_updated'), true);
        $this->cancel();
    }

    /**
     * @return MetaQuestionType[]
     */
    public function getTypes(): array
    {
        return (new MetaTypeFactory())->getTypes();
    }


}
