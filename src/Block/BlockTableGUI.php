<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Block;

use ilObjSelfEvaluationGUI;
use ilCtrl;
use ilSelfEvaluationPlugin;
use ilTable2GUI;
use ilAdvancedSelectionListGUI;

class BlockTableGUI extends ilTable2GUI
{
    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;

    public function __construct(
        ilCtrl $ilCtrl,
        ilSelfEvaluationPlugin $plugin,
        ilObjSelfEvaluationGUI $parent,
        $a_parent_cmd
    ) {

        $this->plugin = $plugin;
        $this->ctrl = $ilCtrl;
        $this->setId('');
        parent::__construct($parent, $a_parent_cmd);
        $this->setTitle($this->plugin->txt('block_table_title'));

        // Columns
        $this->addColumn('', '', '20px');
        $this->addColumn($this->plugin->txt('title'), '', 'auto');
        $this->addColumn($this->plugin->txt('abbreviation'), '', 'auto');
        $this->addColumn($this->plugin->txt('description'), '', 'auto');
        $this->addColumn($this->plugin->txt('count_questions'), '', 'auto');
        $this->addColumn($this->plugin->txt('count_feedbacks'), '', 'auto');
        $this->addColumn($this->plugin->txt('feedback_status'), '', 'auto');
        $this->addColumn($this->plugin->txt('actions'), '', 'auto');
        $this->setFormAction($ilCtrl->getFormActionByClass('ListBlocksGUI'));
        $this->addMultiCommand('saveSorting', $this->plugin->txt('save_sorting'));
        $this->setRowTemplate($this->plugin->getDirectory() . '/templates/default/Block/tpl.template_block_row.html');
    }

    public function fillRow(array $a_set): void
    {
        // Row
        $this->tpl->setVariable('ID', $a_set['position_id']);
        $this->tpl->setVariable('TITLE', $a_set['title']);
        $this->tpl->setVariable('EDIT_LINK', $a_set['edit_link']);
        $this->tpl->setVariable('ABBREVIATION', $a_set['abbreviation']);
        $this->tpl->setVariable('DESCRIPTION', $a_set['description']);
        if ($a_set['questions_link'] == '') {
            $this->tpl->setCurrentBlock('question_count');
            $this->tpl->setVariable('COUNT_QUESTIONS', $a_set['question_count']);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('question_count_with_link');
            $this->tpl->setVariable('QUESTIONS_LINK', $a_set['questions_link']);
            $this->tpl->setVariable('COUNT_QUESTIONS', $a_set['question_count']);
            $this->tpl->parseCurrentBlock();
        }
        if ($a_set['feedback_link'] == '') {
            $this->tpl->setCurrentBlock('feedback_count');
            $this->tpl->setVariable('COUNT_FEEDBACKS', $a_set['feedback_count']);
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock('status_img');
            $this->tpl->setVariable('FEEDBACK_STATUS', $a_set['status_img']);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('feedback_count_with_link');
            $this->tpl->setVariable('COUNT_FEEDBACKS', $a_set['feedback_count']);
            $this->tpl->setVariable('FEEDBACK_LINK', $a_set['feedback_link']);
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock('status_img_with_link');
            $this->tpl->setVariable('FEEDBACK_STATUS', $a_set['status_img']);
            $this->tpl->setVariable('FEEDBACK_LINK', $a_set['feedback_link']);
            $this->tpl->parseCurrentBlock();
        }

        $ac = new ilAdvancedSelectionListGUI();
        $ac->setId($a_set['position_id']);
        $ac->setListTitle($this->plugin->txt('actions'));
        /**
         * @var BlockTableAction[] $actions
         */
        $actions = unserialize($a_set['actions']);
        usort($actions, function (BlockTableAction $action_a, BlockTableAction $action_b) {
            return $action_a->getPosition() > $action_b->getPosition();
        });
        foreach ($actions as $action) {
            $ac->addItem($action->getTitle(), $action->getCmd(), $action->getLink());
        }
        $this->tpl->setVariable('ACTIONS', $ac->getHTML());
    }
}
