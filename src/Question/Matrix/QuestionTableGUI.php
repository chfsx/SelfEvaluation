<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Question\Matrix;

use ilTable2GUI;
use ilub\plugin\SelfEvaluation\Block\Block;
use ilSelfEvaluationPlugin;
use ilAdvancedSelectionListGUI;
use QuestionGUI;
use ilGlobalTemplateInterface;

class QuestionTableGUI extends ilTable2GUI
{
    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;

    /**
     * @var Block
     */
    protected $block;

    /**
     * @var bool
     */
    protected $sortable;



    public function __construct(QuestionGUI $a_parent_obj, ilSelfEvaluationPlugin $plugin, ilGlobalTemplateInterface $global_template, string $a_parent_cmd, Block $block, bool $sortable)
    {
        $this->setId('sev_feedbacks');
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->plugin = $plugin;
        $this->block = $block;
        $this->sortable = $sortable;

        $this->setTitle($block->getTitle() . ': ' . $this->plugin->txt('question_table_title'));
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->ctrl->setParameterByClass('QuestionGUI', 'question_id', null);
        $this->ctrl->setParameterByClass('QuestionGUI', 'block_id', $block->getId());
        $this->setRowTemplate($this->plugin->getDirectory() . '/templates/default/Question/tpl.template_question_row.html');
        $this->initColumns($global_template);
    }

    protected function initColumns(ilGlobalTemplateInterface $global_template)
    {
        if ($this->sortable) {
            $global_template->addJavaScript($this->plugin->getDirectory() . '/templates/js/sortable.js');
            $this->addColumn('', 'position', '20px');
            $this->addMultiCommand('saveSorting', $this->plugin->txt('save_sorting'));
        }

        $this->addColumn($this->plugin->txt('question_body'), $this->sortable ? 'question_body' : false, 'auto');
        $this->addColumn($this->plugin->txt('short_title'), $this->sortable ? 'title' : false, 'auto');
        $this->addColumn($this->plugin->txt('is_inverted'), $this->sortable ? 'is_inverse' : false, 'auto');
        $this->addColumn($this->plugin->txt('actions'), '', 'auto');
    }

    /**
     * @param array $a_set
     */
    public function fillRow($a_set): void
    {
        $this->ctrl->setParameterByClass('QuestionGUI', 'question_id', $a_set['id']);

        if ($this->sortable) {
            $this->tpl->setCurrentBlock("sortable");
            $this->tpl->setVariable('MOVE_IMG_SRC', $this->plugin->getDirectory()."/templates/images/move.png");
            $this->tpl->setVariable('ID', $a_set['id']);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable('TITLE', strip_tags($a_set['question_body']));
        $this->tpl->setVariable(
            'EDIT_LINK',
            $this->ctrl->getLinkTargetByClass('QuestionGUI', 'editQuestion')
        );
        $this->tpl->setVariable('BODY', $a_set['title'] ? $a_set['title'] :
            $this->plugin->txt('question') . ' ' . $this->block->getPosition() . '.' . $a_set['position']);
        $this->tpl->setVariable(
            'IS_INVERTED',
            $a_set['is_inverse'] ? $this->plugin->getDirectory().'/templates/images/icon_ok.svg' : $this->plugin->getDirectory().'/templates/images/empty.png'
        );
        // Actions
        $ac = new ilAdvancedSelectionListGUI();
        $ac->setId('question_' . $a_set['id']);
        $ac->addItem(
            $this->plugin->txt('edit_question'),
            'edit_question',
            $this->ctrl->getLinkTargetByClass('QuestionGUI', 'editQuestion')
        );
        $ac->addItem(
            $this->plugin->txt('delete_question'),
            'delete_question',
            $this->ctrl->getLinkTargetByClass('QuestionGUI', 'confirmDeleteQuestion')
        );
        $ac->setListTitle($this->plugin->txt('actions'));
        $this->tpl->setVariable('ACTIONS', $ac->getHTML());
    }
}
