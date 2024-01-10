<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Question;

use ilub\plugin\SelfEvaluation\Block\Block;
use ilSelfEvaluationPlugin;
use ilPropertyFormGUI;
use ilCtrl;
use ilGlobalTemplateInterface;
use ilToolbarGUI;
use ilObjSelfEvaluationGUI;
use ilAccessHandler;
use ilDBInterface;
use ilTable2GUI;
use ilUtil;
use ilConfirmationGUI;

abstract class BaseQuestionGUI
{
    public const MODE_CREATE = 1;
    public const MODE_UPDATE = 2;

    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;

    /**
     * @var Block
     */
    protected $block;

    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilObjSelfEvaluationGUI
     */
    protected $parent;

    /**
     * @var ilAccessHandler
     */
    protected $access;
    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var Question
     */
    protected $question;

    /**
     * @var bool
     */
    protected $enable_sorting = true;

    public function __construct(
        ilDBInterface $db,
        ilObjSelfEvaluationGUI $parent,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilToolbarGUI $ilToolbar,
        ilAccessHandler $access,
        ilSelfEvaluationPlugin $plugin,
        Block $block,
        Question $question
    ) {
        $this->tpl = $tpl;
        $this->toolbar = $ilToolbar;
        $this->ctrl = $ilCtrl;
        $this->parent = $parent;
        $this->plugin = $plugin;
        $this->access = $access;
        $this->db = $db;
        $this->block = $block;
        $this->question = $question;

    }

    public function executeCommand()
    {
        $this->ctrl->saveParameter($this, 'block_id');
        $this->performCommand();
    }

    public function performCommand()
    {
        $cmd = $this->ctrl->getCmd();

        if (!$this->access->checkAccess(
            "write",
            $cmd,
            $this->parent->object->getRefId(),
            $this->plugin->getId(),
            $this->parent->object->getId()
        )) {
            throw new \ilObjectException($this->plugin->txt("permission_denied"));
        }

        switch ($cmd) {
            case 'showContent':
            case 'cancel':
            case 'addQuestion':
            case 'saveSorting':
            case 'createQuestion':
            case 'saveRequired':
            case 'editQuestion':
            case 'updateQuestion':
            case 'confirmDeleteQuestion':
            case 'deleteQuestion':
                $this->$cmd();
                break;
            default:
                $this->showContent();
                break;
        }

    }

    protected function showContent()
    {
        $this->toolbar->addButton(
            '<b>&lt;&lt; ' . $this->plugin->txt('back_to_blocks') . '</b>',
            $this->ctrl->getLinkTargetByClass('ListBlocksGUI', 'showContent')
        );
        $this->toolbar->addButton($this->plugin->txt("add_question"), $this->ctrl->getLinkTarget($this, 'addQuestion'));

        $table = $this->createTableGUI();
        $table->setData($this->question::_getAllInstancesForParentIdAsArray($this->db, $this->block->getId()));
        $this->tpl->setContent($table->getHTML());
        $table->setTitle($this->block->getTitle() . ': ' . $this->plugin->txt('question_table_title'));
    }

    abstract protected function createTableGUI(): ilTable2GUI;

    public function cancel()
    {
        $this->ctrl->setParameterByClass(static::class, 'question_id', null);
        $this->ctrl->redirectByClass(static::class);
    }

    protected function saveSorting()
    {

        if($this->parent->http->post()->has('position')) {
            $post_array = $this->parent->http->post()->retrieve('position',
                $this->parent->refinery->kindlyTo()->listOf($this->parent->refinery->kindlyTo()->int()));

            foreach ($post_array as $position => $question_id) {
                $this->question->setId((int) $question_id);
                $this->question->read();
                $this->question->setPosition($position + 1);
                $this->question->update();
            }
        }
        $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->plugin->txt("sorting_saved"), true);
        $this->ctrl->redirect($this, 'showContent');
    }


    public function addQuestion()
    {
        $this->initQuestionForm();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function editQuestion()
    {
        $this->ctrl->saveParameter($this, 'question_id');
        $this->initQuestionForm('update');
        $this->setQuestionFormValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    abstract protected function setQuestionFormValues();


    protected function initQuestionForm(string $mode = 'create')
    {
        $this->form = new  ilPropertyFormGUI();
        $this->form->setTitle($this->plugin->txt($mode . '_question'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->addCommandButton($mode . 'Question', $this->plugin->txt($mode . '_question_button'));
        $this->form->addCommandButton('cancel', $this->plugin->txt('cancel'));

    }

    protected function createQuestion()
    {
        $this->updateQuestion("create");
    }

    protected function updateQuestion(string $mode = "update")
    {
        if($mode == "update") {
            $this->ctrl->saveParameter($this, 'question_id');
        }
        $this->initQuestionForm($mode);
        $this->form->setValuesByPost();

        if ($this->form->checkInput()) {
            $this->createQuestionSetFields();
            $this->question->setParentId($this->block->getId());
            $this->question->update();
            $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->plugin->txt('msg_question_updated'), true);
            $this->cancel();
        }

        $this->tpl->setContent($this->form->getHTML());
    }

    abstract protected function createQuestionSetFields();

    public function confirmDeleteQuestion()
    {
        $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_QUESTION, $this->plugin->txt('qst_delete_question'));
        $conf = new ilConfirmationGUI();
        $conf->setHeaderText($this->plugin->txt('qst_delete_question'));
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setCancel($this->plugin->txt('cancel'), 'cancel');
        $conf->setConfirm($this->plugin->txt('delete_question'), 'deleteQuestion');
        $title = $this->question->getTitle();
        if($title == "") {
            $title = $this->plugin->txt('question') . ' ' . $this->block->getPosition() . '.' . $this->question->getPosition();
        }

        $conf->addItem('question_id', (string)$this->question->getId(), $title);
        $this->tpl->setContent($conf->getHTML());
    }

    public function deleteQuestion()
    {
        $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->plugin->txt('msg_question_deleted'), true);
        $this->question->delete();
        $this->cancel();
    }
    public function enableSorting(bool $enable_sorting)
    {
        $this->enable_sorting = $enable_sorting;
    }

    public function hasSorting(): bool
    {
        return $this->enable_sorting;
    }
}
