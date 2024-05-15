<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Dataset;

use ilTable2GUI;
use ilSelfEvaluationPlugin;
use ilDBInterface;
use ilCtrl;
use ilub\plugin\SelfEvaluation\Identity\Identity;
use ilObjUser;
use ilAdvancedSelectionListGUI;
use DatasetGUI;

class DatasetTableGUI extends ilTable2GUI
{
    protected ilSelfEvaluationPlugin $plugin;
    protected ilDBInterface $db;
    protected ilCtrl $ctrl;

    public function __construct(
        ilDBInterface $db,
        ilCtrl $ilCtrl,
        DatasetGUI $a_parent_obj,
        string $a_parent_cmd,
        ilSelfEvaluationPlugin $plugin,
        int $obj_id = 0,
        string $identifier = ""
    ) {
        $this->plugin = $plugin;
        $this->ctrl = $ilCtrl;
        $this->db = $db;

        $this->setId('');
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($this->plugin->txt('dataset_table_title'));
        //
        // Columns
        $this->addColumn("", "", "1");
        $this->addColumn($this->plugin->txt('identity_type'), '', '100px');
        $this->addColumn($this->plugin->txt('date'), '', 'auto');
        $this->addColumn($this->plugin->txt('identity'), '', 'auto');
        $this->addColumn($this->plugin->txt('complete'), '', 'auto');
        //$this->addColumn($this->plugin->txt('average_all'), false, 'auto');
        $this->addColumn($this->plugin->txt('actions'), '', 'auto');
        $this->ctrl->setParameterByClass('DatasetGUI', 'dataset_id', null);
        $this->setFormAction($this->ctrl->getFormActionByClass('DatasetGUI'));
        $this->setRowTemplate($this->plugin->getDirectory() . '/templates/default/Dataset/tpl.template_dataset_row.html');
        $this->addMultiCommand("deleteDatasets", $this->plugin->txt("delete_dataset"));

        if ($identifier != "") {
            $this->setData(Dataset::_getAllInstancesByObjectId($this->db, $obj_id, true, $identifier));
        } else {
            $this->setData(Dataset::_getAllInstancesByObjectId($this->db, $obj_id, true));
        }
    }

    public function fillRow(array $a_set): void
    {
        $obj = new Dataset($this->db, (int)$a_set['id']);
        $identifier = new Identity($this->db, $obj->getIdentifierId());
        $this->ctrl->setParameterByClass('DatasetGUI', 'dataset_id', $obj->getId());
        // Row
        $this->tpl->setVariable("ID", $obj->getId());
        $this->tpl->setVariable(
            'COMPLETE',
            $obj->isComplete() ? $this->plugin->getDirectory().'/templates/images/icon_ok.svg' : $this->plugin->getDirectory().'/templates/images/empty.png'
        );
        $this->tpl->setVariable('DATE', date('d.m.Y - H:i:s', $obj->getCreationDate()));
        $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTargetByClass('DatasetGUI', 'show'));
        switch ($identifier->getType()) {
            case Identity::TYPE_EXTERNAL:
                $this->tpl->setVariable('TYPE', $this->plugin->txt('identity_type_'
                    . Identity::TYPE_EXTERNAL));
                $this->tpl->setVariable('IDENTITY', $identifier->getIdentifier());
                break;
            case Identity::TYPE_LOGIN:
                $this->tpl->setVariable('TYPE', $this->plugin->txt('identity_type_'
                    . Identity::TYPE_LOGIN));
                $username = ilObjUser::_lookupName((int)$identifier->getIdentifier());
                $this->tpl->setVariable('IDENTITY', $username['login']);
                break;
        }
        //$this->tpl->setVariable('PERCENTAGE', $obj->getOverallPercentage());
        $this->tpl->setVariable('ID', $obj->getId());
        // Actions
        $ac = new ilAdvancedSelectionListGUI();
        $ac->setId('dataset_' . $obj->getId());
        $ac->addItem(
            $this->plugin->txt('show_feedback'),
            'show_dataset',
            $this->ctrl->getLinkTargetByClass('DatasetGUI', 'show')

        );
        $ac->addItem(
            $this->plugin->txt('delete_dataset'),
            'delete_dataset',
            $this->ctrl->getLinkTargetByClass('DatasetGUI', 'deleteDataset')
        );
        $ac->setListTitle($this->plugin->txt('actions'));
        //
        $this->ctrl->setParameterByClass('DatasetGUI', 'dataset_id', 0);
        $this->tpl->setVariable('ACTIONS', $ac->getHTML());
    }
}
