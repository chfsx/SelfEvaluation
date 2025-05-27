<?php

declare(strict_types=1);

use ilub\plugin\SelfEvaluation\Block\BlockTableGUI;
use ilub\plugin\SelfEvaluation\Block\BlockFactory;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;

class ListBlocksGUI
{
    public function __construct(
        protected ilDBInterface $db,
        protected ilObjSelfEvaluationGUI $parent,
        protected ilGlobalTemplateInterface $tpl,
        protected ilCtrl $ctrl,
        protected ilToolbarGUI $toolbar,
        protected ilAccessHandler $access,
        protected ilSelfEvaluationPlugin $plugin,
        protected WrapperFactory $http,
        protected Factory $refinery
    ) {
    }

    /**
     * @throws ilObjectException
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $this->ctrl->saveParameter($this, 'block_id');
        $this->performCommand();
    }

    public function performCommand(): void
    {
        $cmd = $this->ctrl->getCmd() ?: $this->getStandardCommand();

        switch ($cmd) {
            case 'showContent':
            case 'saveSorting':
            case 'editOverall':
                if (!$this->access->checkAccess(
                    "write",
                    $cmd,
                    $this->parent->object->getRefId(),
                    $this->plugin->getId(),
                    $this->parent->object->getId()
                )) {
                    throw new ilObjectException($this->plugin->txt("permission_denied"));
                }
                $this->$cmd();
                break;
        }
    }

    public function getStandardCommand(): string
    {
        return 'showContent';
    }

    public function showContent(): void
    {
        $this->tpl->addJavaScript($this->plugin->getDirectory() . '/templates/js/sortable.js');
        $table = new BlockTableGUI($this->ctrl, $this->plugin, $this->parent, 'showContent');

        $this->ctrl->setParameterByClass(QuestionBlockGUI::class, 'block_id', null);
        $this->toolbar->addButton(
            $this->txt('add_new_question_block'),
            $this->ctrl->getLinkTargetByClass(QuestionBlockGUI::class, 'addBlock')
        );

        $this->ctrl->setParameterByClass(MetaBlockGUI::class, 'block_id', null);
        $this->toolbar->addButton(
            $this->txt('add_new_meta_block'),
            $this->ctrl->getLinkTargetByClass(MetaBlockGUI::class, 'addBlock')
        );

        $this->ctrl->setParameterByClass(FeedbackGUI::class, 'parent_overall', 1);
        $this->toolbar->addButton(
            $this->txt('edit_overal_feedback'),
            $this->ctrl->getLinkTargetByClass(FeedbackGUI::class, 'listObjects')
        );
        $this->ctrl->setParameterByClass(FeedbackGUI::class, 'parent_overall', 0);

        $factory = new BlockFactory($this->db, $this->getSelfEvalId());
        $blocks = $factory->getAllBlocks();

        $table_data = [];
        foreach ($blocks as $block) {
            $table_data[] = $block->getBlockTableRow($this->db, $this->ctrl, $this->plugin)->toArray();
        }

        $table->setData($table_data);

        $this->tpl->setContent($table->getHTML());
    }

    public function saveSorting(): void
    {
        $factory = new BlockFactory($this->db, $this->getSelfEvalId());
        $blocks = $factory->getAllBlocks();
        $positions = $this->http->post()->retrieve(
            'position',
            $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
        );
        foreach ($blocks as $block) {
            $position = (int) array_search($block->getPositionId(), $positions) + 1;
            if ($position !== 0) {
                $block->setPosition($position);
                $block->update();
            }
        }

        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->txt('sorting_saved'),
            true
        );
        $this->ctrl->redirect($this, 'showContent');
    }

    public function editOverall(): void
    {
        $this->tpl->setContent("hello World");
    }

    protected function getSelfEvalId(): int
    {
        return $this->parent->object->getId();
    }

    protected function txt(string $lng_var): string
    {
        return $this->plugin->txt($lng_var);
    }
}
