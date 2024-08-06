<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Feedback;

use ilTable2GUI;
use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlockInterface;
use ilRepositoryObjectPlugin;
use ilAdvancedSelectionListGUI;
use ilDBInterface;
use FeedbackGUI;

class FeedbackTableGUI extends ilTable2GUI
{
    /**
     * @var ilRepositoryObjectPlugin;
     */
    protected \ilRepositoryObjectPlugin $plugin;

    /**
     * @var
     */
    protected \ilDBInterface $db;

    public function __construct(
        ilDBInterface $db,
        FeedbackGUI $a_parent_obj,
        ilRepositoryObjectPlugin $plugin,
        string $a_parent_cmd,
        QuestionBlockInterface $block,
        bool $is_ovarall = false
    ) {
        $this->db = $db;
        $this->plugin = $plugin;

        $this->setId('');

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($block->getTitle() . ': ' . $this->plugin->txt('feedback_table_title'));
        $this->addColumn("", "", "1");
        $this->addColumn($this->plugin->txt('fb_title'), 'title', 'auto');
        $this->addColumn($this->plugin->txt('fb_body'), 'feedback_text', 'auto');
        $this->addColumn($this->plugin->txt('fb_start'), 'start_value', 'auto');
        $this->addColumn($this->plugin->txt('fb_end'), 'end_value', 'auto');
        $this->addColumn($this->plugin->txt('actions'));

        $this->ctrl->setParameter($this->parent_obj, 'feedback_id', null);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->addMultiCommand("deleteFeedbacks", $this->plugin->txt("delete_feedback"));

        $this->setRowTemplate($this->plugin->getDirectory() . '/templates/default/Feedback/tpl.template_feedback_row.html');
        $this->setData(Feedback::_getAllInstancesForParentId(
            $this->db,
            $a_parent_obj->getBlock()->getId(),
            true,
            $is_ovarall
        ));
    }

    public function fillRow($a_set): void
    {
        $obj = new Feedback($this->db, $a_set['id']);
        $this->tpl->setVariable("ID", $obj->getId());
        $this->tpl->setVariable('TITLE', $obj->getTitle());
        $this->tpl->setVariable('BODY', strip_tags($obj->getFeedbackText()));
        $start_sign = "> ";
        if($obj->getStartValue() == "0") {
            $start_sign = "> ";
        } elseif($obj->getStartValue() == "100") {
            $start_sign = "= ";
        }
        $this->tpl->setVariable('START', $start_sign . $obj->getStartValue() . '%');
        $this->tpl->setVariable('END', '<= ' . $obj->getEndValue() . '%');
        // Actions
        $ac = new ilAdvancedSelectionListGUI();
        $this->ctrl->setParameter($this->parent_obj, 'feedback_id', $obj->getId());
        $ac->setId('fb_' . $obj->getId());
        $ac->addItem(
            $this->plugin->txt('edit_feedback'),
            'edit_feedback',
            $this->ctrl->getLinkTarget($this->parent_obj, 'editFeedback')
        );
        $ac->addItem(
            $this->plugin->txt('delete_feedback'),
            'delete_feedback',
            $this->ctrl->getLinkTarget($this->parent_obj, 'deleteFeedback')
        );
        $ac->setListTitle($this->plugin->txt('actions'));
        //
        $this->tpl->setVariable('ACTIONS', $ac->getHTML());
    }
}
