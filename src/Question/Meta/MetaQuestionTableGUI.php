<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Question\Meta;

use ilTable2GUI;
use ilSelfEvaluationPlugin;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaQuestionType;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaTypeFactory;
use ilAdvancedSelectionListGUI;
use MetaQuestionGUI;
use ilGlobalTemplateInterface;
use ilub\plugin\SelfEvaluation\Block\Block;

class MetaQuestionTableGUI extends ilTable2GUI
{
    /**
     * @var MetaQuestionType[]
     */
    protected $types;
    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;
    /**
     * @var bool
     */
    protected $sortable;

    /**
     * MetaQuestionTableGUI constructor.
     * @param MetaQuestionGUI           $a_parent_obj
     * @param ilSelfEvaluationPlugin    $plugin
     * @param ilGlobalTemplateInterface $global_template
     * @param string                    $a_parent_cmd
     * @param array                     $types
     * @param bool                      $sortable
     */
    public function __construct(MetaQuestionGUI $a_parent_obj, ilSelfEvaluationPlugin $plugin, ilGlobalTemplateInterface $global_template, string $a_parent_cmd, array $types, bool $sortable, Block $block)
    {
        $this->types = $types;
        $this->sortable = $sortable;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->plugin = $plugin;
        $this->sortable = $sortable;
        $this->types = $types;

        $this->setTitle($block->getTitle() . ': ' . $this->plugin->txt('question_table_title'));

        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $this->getParentCmd()));

        $this->addCommandButton('saveRequired', $this->lng->txt('save'));

        $this->enable('sort');
        $this->enable('header');
        $this->enable('numinfo');

        $this->setRowTemplate($this->plugin->getDirectory().'/templates/default/Question/tpl.template_meta_question_row.html');

        $this->initColumns($global_template);
    }

    protected function initColumns(ilGlobalTemplateInterface $global_template)
    {
        if ($this->sortable) {
            $global_template->addJavaScript($this->plugin->getDirectory() . '/templates/js/sortable.js');
            $this->addColumn('', 'position', '20px');
            $this->addMultiCommand('saveSorting', $this->plugin->txt('save_sorting'));
        } else {
            $this->setDefaultOrderField('name');
            $this->setDefaultOrderDirection('asc');
        }

        $this->addColumn($this->plugin->txt('question'), $this->sortable ? 'question' : false, 'auto');
        $this->addColumn($this->plugin->txt('short_title'), $this->sortable ? 'short_title' : false, 'auto');
        $this->addColumn($this->plugin->txt('type'), $this->sortable ? 'type' : false, 'auto');
        $this->addColumn($this->plugin->txt('required_field'), $this->sortable ? 'required_field' : false, 'auto');
        $this->addColumn($this->plugin->txt('actions'), $this->sortable ? 'actions' : false, 'auto');
    }

    /**
     * Fill row
     * @param array $row
     */
    public function fillRow(array $row): void
    {
        $this->ctrl->setParameter($this->getParentObject(), 'question_id', $row['id']);

        if ($this->sortable) {
            $this->tpl->setCurrentBlock('sortable');
            $this->tpl->setVariable('MOVE_IMG_SRC', $this->plugin->getDirectory()."/templates/images/move.png");
            $this->tpl->setVariable('ID', $row['id']);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable('VAL_ID', $row['id']);
        $this->tpl->setVariable(
            'EDIT_LINK',
            $this->ctrl->getLinkTarget($this->getParentObject(), 'editQuestion')
        );
        $this->tpl->setVariable('VAL_NAME', $row['name']);
        $this->tpl->setVariable('VAL_SHORT_TITLE', $row['short_title']);
        $type_factory = new MetaTypeFactory();
        $this->tpl->setVariable('VAL_TYPE', $this->plugin->txt($type_factory->getTypeByTypeId($row['type_id'])->getTypeName()));

        $this->tpl->setVariable('REQUIRED_CHECKED', $row['required'] ? 'checked="checked"' : '');

        // actions
        $ac = new ilAdvancedSelectionListGUI();
        $ac->setId((string)$row['id']);
        $ac->setListTitle($this->lng->txt('actions'));

        $edit_link = $this->ctrl->getLinkTarget($this->getParentObject(), 'editQuestion');
        $ac->addItem($this->lng->txt('edit'), 'edit_field', $edit_link);

        $delete_link = $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmDeleteQuestion');
        $ac->addItem($this->lng->txt('delete'), 'delete_field', $delete_link);

        $this->tpl->setVariable('ACTIONS', $ac->getHTML());
    }
}
