<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Block;

use ilDBInterface;
use ilGlobalTemplateInterface;
use ilCtrl;
use ilSelfEvaluationPlugin;
use ilPropertyFormGUI;
use ilObjSelfEvaluationGUI;
use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock;
use ilTextInputGUI;
use ilTextAreaInputGUI;
use ilUtil;
use ilConfirmationGUI;
use ilAccessHandler;
use ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock;

abstract class BlockGUI
{
    protected ilPropertyFormGUI $form;

    protected ilDBInterface $db;

    protected ilGlobalTemplateInterface $tpl;

    protected ilCtrl $ctrl;

    protected ilObjSelfEvaluationGUI $parent;

    protected ilAccessHandler $access;

    protected ilSelfEvaluationPlugin $plugin;

    protected QuestionBlock|Metablock $object;

    public function __construct(
        ilDBInterface $db,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilAccessHandler $access,
        ilSelfEvaluationPlugin $plugin,
        ilObjSelfEvaluationGUI $parent
    ) {
        $this->db = $db;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->access = $access;
        $this->plugin = $plugin;
        $this->parent = $parent;
    }

    public function executeCommand()
    {
        $this->ctrl->saveParameter($this, 'block_id');
        $this->performCommand();
    }

    public function getStandardCommand(): string
    {
        return 'addBlock';
    }

    protected function performCommand()
    {
        $cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();

        switch ($cmd) {
            case 'addBlock':
            case 'createObject':
            case 'editBlock':
            case 'updateObject':
            case 'deleteBlock':
            case 'deleteObject':
            case 'duplicateBlock':
                if (!$this->checkAccess("write", $cmd)) {
                    throw new \ilObjectException($this->plugin->txt("permission_denied"));
                }
                $this->$cmd();
                break;
            case 'cancel':
                if (!$this->checkAccess("read", $cmd)) {
                    throw new \ilObjectException($this->plugin->txt("permission_denied"));
                }
                $this->$cmd();
                break;
        }
    }

    protected function checkAccess($permission, $cmd)
    {
        return $this->access->checkAccess($permission, $cmd, $this->parent->getRefId(), $this->plugin->getId());
    }

    protected function addBlock()
    {
        $this->initForm();
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function cancel()
    {
        $this->ctrl->redirectByClass('ListBlocksGUI', 'showContent');
    }

    protected function initForm(string $mode = 'create')
    {
        $this->form = new  ilPropertyFormGUI();
        $this->form->setTitle($this->plugin->txt($mode . '_block'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->addCommandButton($mode . 'Object', $this->plugin->txt($mode . '_block_button'));
        $this->form->addCommandButton('cancel', $this->plugin->txt('cancel'));

        $te = new ilTextInputGUI($this->plugin->txt('title'), 'title');
        $te->setRequired(true);
        $this->form->addItem($te);
        $te = new ilTextAreaInputGUI($this->plugin->txt('description'), 'description');
        $this->form->addItem($te);
    }

    /**
     * Create a new block object
     */
    protected function createObject()
    {
        $this->initForm();
        if ($this->form->checkInput()) {
            $this->setObjectValuesByPost();
            $this->object->create();
            $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->plugin->txt('msg_block_created'), true);
            $this->cancel();
        }
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Create a new block object
     */
    protected function duplicateBlock()
    {
        $this->object->cloneTo($this->object->getParentId());
        $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->plugin->txt('msg_block_duplicated'), true);
        $this->cancel();
    }

    /**
     * Show the edit block GUI
     */
    protected function editBlock()
    {
        $this->initForm('update');
        $values = $this->getObjectValuesAsArray();
        $this->form->setValuesByArray($values);
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function getObjectValuesAsArray()
    {
        $values['title'] = $this->object->getTitle();
        $values['description'] = $this->object->getDescription();

        return $values;
    }

    /**
     * Update a block object
     */
    protected function updateObject()
    {
        $this->initForm('update');
        $this->form->setValuesByPost();
        if ($this->form->checkInput()) {
            $this->setObjectValuesByPost();
            $this->object->update();
            $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->plugin->txt('msg_block_updated'), true);
            $this->cancel();
        }
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function deleteBlock()
    {
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setCancel($this->plugin->txt('cancel'), 'cancel');
        $conf->setConfirm($this->plugin->txt('delete_block'), 'deleteObject');
        $conf->addItem('block_id', (string) $this->object->getId(), $this->object->getTitle());
        $conf->setHeaderText($this->plugin->txt('qst_delete_block'));
        $this->tpl->setContent($conf->getHTML());
    }

    protected function deleteObject()
    {
        $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->plugin->txt('msg_block_deleted'), true);
        $this->object->delete();
        $this->cancel();
    }

    protected function setObjectValuesByPost()
    {
        $this->object->setParentId($this->object->getParentId());
        $this->object->setTitle($this->form->getInput('title'));
        $this->object->setDescription($this->form->getInput('description'));
    }
}
