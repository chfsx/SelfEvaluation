<?php

declare(strict_types=1);


use ilub\plugin\SelfEvaluation\Feedback\FeedbackChartGUI;
use ilub\plugin\SelfEvaluation\Identity\Identity;
use ilub\plugin\SelfEvaluation\Dataset\Dataset;
use ilub\plugin\SelfEvaluation\Dataset\DatasetTableGUI;
use ilub\plugin\SelfEvaluation\Dataset\DatasetCsvExport;

class DatasetGUI
{
    protected ilDBInterface $db;
    protected ilGlobalPageTemplate $tpl;
    protected ilCtrl $ctrl;
    protected ilObjSelfEvaluationGUI $parent;
    protected ilToolbarGUI $toolbar;
    protected ilAccessHandler $access;
    protected ilSelfEvaluationPlugin $plugin;
    protected Dataset $dataset;

    public function __construct(
        ilDBInterface $db,
        ilObjSelfEvaluationGUI $parent,
        ilGlobalPageTemplate $tpl,
        ilCtrl $ilCtrl,
        ilToolbarGUI $ilToolbar,
        ilAccessHandler $access,
        ilSelfEvaluationPlugin $plugin
    ) {
        $this->db = $db;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->parent = $parent;
        $this->toolbar = $ilToolbar;
        $this->plugin = $plugin;
        $this->access = $access;

        $this->dataset = new Dataset($this->db, $_GET['dataset_id'] ? $_GET['dataset_id'] : 0);
    }

    public function executeCommand()
    {
        $this->performCommand();
    }

    public function getStandardCommand(): string
    {
        return 'show';
    }

    public function performCommand()
    {
        $cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();

        switch ($cmd) {
            default:
                $this->$cmd();
                break;
        }
    }

    public function selectResult()
    {
        $this->ctrl->setParameter($this, 'dataset_id', $_POST['select_result']);
        $this->ctrl->redirect($this, 'listMyObjects');
    }

    public function index()
    {
        global $DIC;

        if ($this->access->checkAccess("write", "index", $this->parent->object->getRefId(), $this->plugin->getId())) {
            $this->toolbar->addButton(
                $this->plugin->txt('delete_all_datasets'),
                $this->ctrl->getLinkTargetByClass('DatasetGUI', 'confirmDeleteAll')
            );
            $this->toolbar->addButton(
                $this->plugin->txt('export_csv'),
                $this->ctrl->getLinkTargetByClass('DatasetGUI', 'exportCSV')
            );
            $table = new DatasetTableGUI($this->db, $this->ctrl, $this, 'index', $this->plugin, $this->parent->object->getId());
        } else {
            $id = Identity::_getInstanceForObjIdAndIdentifier($this->db, (int)$this->plugin->getId(), $DIC->user()->getId());
            if (!$id) {
                $id = Identity::_getNewInstanceForObjIdAndUserId($this->db, (int)$this->plugin->getId(), $DIC->user()->getId());
            }
            $table = new DatasetTableGUI($this->db, $this->ctrl, $this, 'index', $this->plugin, $this->parent->object->getId(), (string) $id->getIdentifier());
        }

        $this->tpl->setContent($table->getHTML());
    }

    public function show()
    {
        $content = $this->plugin->getTemplate('default/Dataset/tpl.dataset_presentation.html');
        $content->setVariable('INTRO_HEADER', $this->parent->object->getOutroTitle());
        $content->setVariable('INTRO_BODY', $this->parent->object->getOutro());
        $feedback = '';
        if ($this->parent->object->isAllowShowResults()) {
            $this->tpl->addJavaScript($this->plugin->getDirectory() . '/templates/js/bar_spider_chart_toggle.js');
            $charts = new FeedbackChartGUI($this->db, $this->tpl, $this->plugin, $this->toolbar, $this->parent->object);
            $feedback = $charts->getPresentationOfFeedback($this->dataset);
        }

        $this->tpl->setContent($content->get() . $feedback);
    }

    public function deleteDataset()
    {
        $this->confirmDelete([$this->dataset->getId()]);
    }

    public function deleteDatasets()
    {
        if(!is_array($_POST["id"])) {
            $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_FAILURE, $this->plugin->txt('no_dataset_selected'));
            $this->index();
            return;
        }
        $this->confirmDelete($_POST["id"]);
    }

    /**
     * @param array $ids
     */
    public function confirmDelete(array $ids = [])
    {
        $conf = new ilConfirmationGUI();
        $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_QUESTION, $this->plugin->txt('qst_delete_dataset'));
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setCancel($this->plugin->txt('cancel'), 'index');
        $conf->setConfirm($this->plugin->txt('delete_dataset'), 'delete');
        foreach ($ids as $id) {
            $dataset = new Dataset($this->db, $id);
            $identifier = new Identity($this->db, $dataset->getIdentifierId());
            $user = $identifier->getIdentifier();
            if ($identifier->getType() == $identifier::TYPE_LOGIN) {
                $user = (new ilObjUser($identifier->getIdentifier()))->getPublicName();
            }
            $conf->addItem('dataset_ids[]', $id, $user . " " . date('d.m.Y - H:i:s', $dataset->getCreationDate()));
        }
        $this->tpl->setContent($conf->getHTML());
    }

    public function delete()
    {
        $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->plugin->txt('msg_dataset_deleted'), true);
        foreach ($_POST['dataset_ids'] as $id) {
            $dataset = new Dataset($this->db, $id);
            $dataset->delete();
        }

        $this->ctrl->redirect($this, 'index');
    }

    public function confirmDeleteAll()
    {
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setCancel($this->plugin->txt('cancel'), 'index');
        $conf->setConfirm($this->plugin->txt('delete_all_datasets'), 'deleteAll');
        $conf->addItem('dataset_id', '', $this->plugin->txt('confirm_delete_all_datasets'));
        $this->tpl->setContent($conf->getHTML());
    }

    public function deleteAll()
    {
        Dataset::_deleteAllInstancesByObjectId($this->db, ilObject2::_lookupObjectId($_GET['ref_id']));
        $this->tpl->setOnScreenMessage(IlGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->plugin->txt('all_datasets_deleted'));
        $this->ctrl->redirect($this, 'index');
    }

    public function exportCsv()
    {
        $csvExport = new DatasetCsvExport($this->db, $this->plugin, ilObject2::_lookupObjectId($_GET['ref_id']));
        $csvExport->getCsvExport();
        exit;
    }
}
