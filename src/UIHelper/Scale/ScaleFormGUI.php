<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\UIHelper\Scale;

use ilPropertyFormGUI;
use ilGlobalTemplateInterface;
use ilRepositoryObjectPlugin;
use ilDBInterface;
use ilFormSectionHeaderGUI;

/**
 * @ilCtrl_Calls      ilSelfEvaluationScaleGUI: ilObjSelfEvaluationGUI
 * @ilCtrl_IsCalledBy ilSelfEvaluationScaleGUI: ilCommonActionDispatcherGUI, ilObjSelfEvaluationGUI
 */
class ScaleFormGUI extends ilPropertyFormGUI
{
    public const FIELD_NAME = 'scale';

    protected Scale $scale;
    protected ilRepositoryObjectPlugin $plugin;

    protected ilGlobalTemplateInterface $tmpl;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var bool
     */
    protected $locked;

    /**
     * @var int
     */
    protected $parent_id;

    public function __construct(
        ilDBInterface $db,
        ilGlobalTemplateInterface $tmpl,
        \ilSelfEvaluationPlugin $plugin,
        $parent_obj_id,
        $locked = false
    ) {
        parent::__construct();

        $this->plugin = $plugin;
        $this->tmpl = $tmpl;
        $this->locked = $locked;
        $this->parent_id = $parent_obj_id;
        $this->db = $db;

        $this->scale = Scale::_getInstanceByObjId($db, $this->parent_id);
        $this->initForm();
        $this->tmpl->addJavaScript($this->plugin->getDirectory() . '/templates/sortable.js');
    }

    protected function initForm()
    {
        // Header
        $te = new ilFormSectionHeaderGUI();
        $te->setTitle($this->plugin->txt('scale_form'));
        $this->addItem($te);
        $te = new MultipleFieldInputGUI($this->plugin, $this->plugin->txt('scale'), 'scale', self::FIELD_NAME);
        $te->setPlaceholderValue($this->plugin->txt('multinput_value'));
        $te->setPlaceholderTitle($this->plugin->txt('multinput_title'));
        $te->setDescription($this->plugin->txt('multinput_description'));
        $te->setDisabled($this->locked);
        $this->addItem($te);
        // FillForm
        $this->fillForm();
    }

    /**
     * @return array
     */
    public function fillForm()
    {
        $array = [];
        foreach ($this->scale->getUnits() as $unit) {
            /**
             * @var $unit ScaleUnit
             */
            $array[$unit->getId()] = ['title' => $unit->getTitle(), 'value' => $unit->getValue()];
        }
        $array = [
            'scale' => $array,
        ];
        $this->setValuesByArray($array);

        return $array;
    }

    /**
     * @param ilPropertyFormGUI $form_gui
     * @return ilPropertyFormGUI
     */
    public function appendToForm(ilPropertyFormGUI $form_gui)
    {
        foreach ($this->getItems() as $item) {
            $form_gui->addItem($item);
        }

        return $form_gui;
    }

    public function updateObject()
    {
        $this->scale->update();
        if (!is_array($_POST[self::FIELD_NAME . '_new'])) {

            return;
        }
        $units = [];

        if (is_array($_POST[self::FIELD_NAME . '_position'])) {
            $positions = array_flip($_POST[self::FIELD_NAME . '_position']);
        }
        if (is_array($_POST[self::FIELD_NAME . '_new']['value'])) {
            foreach ($_POST[self::FIELD_NAME . '_new']['value'] as $k => $v) {
                if ($v !== false and $v !== null and $v !== '') {
                    $obj = new ScaleUnit($this->db);
                    $obj->setParentId($this->scale->getId());
                    $obj->setTitle($_POST['scale_new']['title'][$k]);
                    $obj->setValue((int)$v);
                    $obj->create();
                    $units[] = $obj;
                }
            }
        }
        if (is_array($_POST[self::FIELD_NAME . '_old']['value'])) {
            foreach ($_POST[self::FIELD_NAME . '_old']['value'] as $k => $v) {
                if ($v !== false and $v !== null and $v !== '') {
                    $obj = new ScaleUnit($this->db, str_replace('id_', '', (string)$k));
                    $obj->setTitle($_POST['scale_old']['title'][$k]);
                    $obj->setValue((int)$v);
                    $obj->setPosition($positions[str_replace('id_', '', (string)$k)]);
                    $obj->update();
                    $units[] = $obj;

                } else {
                    $obj = new ScaleUnit($this->db, str_replace('id_', '', $k));
                    $obj->delete();
                }
            }
        }
    }
}
