<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Block;

use ilCtrl;
use ilSelfEvaluationPlugin;
use iLub\Plugins\Mods\ModHelper\Translation\uiHookGuiModTranslatedString;

class BlockTableRow
{
    protected ilCtrl $ctrl;
    protected ilSelfEvaluationPlugin $plugin;
    protected int $block_id;
    protected string $title;
    protected string $abbreviation ='';
    protected string $description;
    protected int $question_count;
    protected int $feedback_count = 0;
    protected string $status_img;
    protected string $block_edit_link;
    protected string $questions_link;
    protected string $feedback_link = '';
    protected string $position_id;
    /**
     * @var BlockTableAction[]
     */
    protected array $actions;
    protected string $block_gui_class;

    public function __construct(
        ilCtrl $ilCtrl,
        ilSelfEvaluationPlugin $plugin,
        Block $block
    ) {
        $this->ctrl = $ilCtrl;
        $this->plugin = $plugin;
        $this->block_gui_class = (new \ReflectionClass($block))->getShortName() . 'GUI';

        $this->setBlockId($block->getId());
        $this->setPositionId($block->getPositionId());
        $this->setTitle($block->getTitle());
        $this->setDescription($block->getDescription());

        // actions
        $this->saveCtrlParameters();

        $edit_action = $this->getEditAction();
        $this->setBlockEditLink($edit_action->getLink());
        $this->addAction($edit_action);

        $duplicate_action = $this->getDuplicateAction();
        $this->addAction($duplicate_action);

        $delete_action = $this->getDeleteAction();
        $this->addAction($delete_action);
    }

    public function toArray(): array
    {
        $arr = [];
        $arr['block_id'] = $this->getBlockId();
        $arr['position_id'] = $this->getPositionId();
        $arr['title'] = $this->getTitle();
        $arr['description'] = $this->getDescription();
        $arr['abbreviation'] = $this->getAbbreviation();
        $arr['question_count'] = is_numeric($this->getQuestionCount()) ? $this->getQuestionCount() : 0;
        $arr['feedback_count'] = $this->getFeedbackCount();
        $arr['status_img'] = $this->getStatusImg();
        $arr['edit_link'] = $this->getBlockEditLink();
        $arr['questions_link'] = $this->getQuestionsLink();
        $arr['feedback_link'] = $this->getFeedbackLink();

        $arr['actions'] = serialize($this->getActions());

        return $arr;
    }

    public function setAbbreviation(string $abbreviation)
    {
        $this->abbreviation = $abbreviation;
    }

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setBlockEditLink(string $block_edit_link)
    {
        $this->block_edit_link = $block_edit_link;
    }

    public function getBlockEditLink(): string
    {
        return $this->block_edit_link;
    }

    public function setBlockId(int $block_id)
    {
        $this->block_id = $block_id;
    }

    public function getBlockId(): int
    {
        return $this->block_id;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setFeedbackCount(int $feedback_count)
    {
        $this->feedback_count = $feedback_count;
    }

    public function getFeedbackCount(): ?int
    {
        return $this->feedback_count;
    }

    public function setFeedbackLink(string $feedback_link)
    {
        $this->feedback_link = $feedback_link;
    }

    public function getFeedbackLink(): ?string
    {
        return $this->feedback_link;
    }

    public function setPositionId(string $position_id)
    {
        $this->position_id = $position_id;
    }

    public function getPositionId(): string
    {
        return $this->position_id;
    }

    public function setQuestionCount(int $question_count)
    {
        $this->question_count = $question_count;
    }

    public function getQuestionCount(): int
    {
        return $this->question_count;
    }

    public function setQuestionsLink(string $questions_link)
    {
        $this->questions_link = $questions_link;
    }

    public function getQuestionsLink(): string
    {
        return $this->questions_link;
    }

    public function setStatusImg(string $status_img)
    {
        $this->status_img = $status_img;
    }

    public function getStatusImg(): string
    {
        return $this->status_img;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param BlockTableAction[] $actions
     */
    public function setActions($actions)
    {
        $this->actions = $actions;
    }

    /**
     * @return BlockTableAction[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    public function addAction(BlockTableAction $action)
    {
        $this->actions[] = $action;
    }

    protected function saveCtrlParameters()
    {
        $this->ctrl->setParameterByClass('BlockGUI', 'block_id', $this->getBlockId());
    }

    protected function getEditAction(): BlockTableAction
    {
        $title = $this->plugin->txt('edit_block');
        $link = $this->ctrl->getLinkTargetByClass($this->block_gui_class, 'editBlock');
        $cmd = 'edit_block';
        $position = 3;
        return new  BlockTableAction($title, $cmd, $link, $position);
    }

    protected function getDeleteAction(): BlockTableAction
    {
        $title = $this->plugin->txt('delete_block');
        $link = $this->ctrl->getLinkTargetByClass($this->block_gui_class, 'deleteBlock');
        $cmd = 'delete_block';
        $position = 5;

        return new BlockTableAction($title, $cmd, $link, $position);
    }

    protected function getDuplicateAction(): BlockTableAction
    {
        $title = $this->plugin->txt('duplicate_block');
        $link = $this->ctrl->getLinkTargetByClass($this->block_gui_class, 'duplicateBlock');
        $cmd = 'duplicateBlock';
        $position = 4;

        return new BlockTableAction($title, $cmd, $link, $position);
    }
}
