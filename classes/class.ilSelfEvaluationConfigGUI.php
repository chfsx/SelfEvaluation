<?php

declare(strict_types=1);

/**
 * @ilCtrl_IsCalledBy ilSelfEvaluationConfigGUI: ilObjComponentSettingsGUI
 */
class ilSelfEvaluationConfigGUI extends ilPluginConfigGUI
{
    public const TYPE_TEXT = 'ilTextInputGUI';
    public const TYPE_RTE_TEXT_AREA = 'ilTextAreaInputGUI';
    public const TYPE_CHECKBOX = 'ilCheckboxInputGUI';
    protected \ilSelfEvaluationConfig $object;
    /**
     * @var array
     */
    protected $fields = [];
    /**
     * @var string
     */
    protected $table_name = '';
    /**
     * @var ilPropertyFormGUI
     */
    protected $form;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilTemplate
     */
    protected $tpl;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    protected \ilSelfEvaluationPlugin $plugin;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();

        $this->plugin = new ilSelfEvaluationPlugin();

        $this->object = new ilSelfEvaluationConfig($this->plugin->getConfigTableName());
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $this->fields = [
            'identity_selection' => [
                'type' => self::TYPE_RTE_TEXT_AREA,
                'info' => true,
                'subelements' => null
            ]
        ];

        return $this->fields;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * @return ilSelfEvaluationConfig
     */
    public function getObject(): \ilSelfEvaluationConfig
    {
        return $this->object;
    }

    /**
     * @param $cmd
     */
    public function performCommand($cmd): void
    {
        switch ($cmd) {
            case 'configure':
            case 'save':
            case 'svn':
                $this->$cmd();
                break;
        }
    }

    public function configure(): void
    {
        $this->initConfigurationForm();
        $this->getValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function getValues(): void
    {
        $values = [];
        foreach ($this->getFields() as $key => $item) {
            $values[$key] = $this->object->getValue($key);
            if (is_array($item['subelements'])) {
                foreach (array_keys($item['subelements']) as $subkey) {
                    $values[$key . '_' . $subkey] = $this->object->getValue($key . '_' . $subkey);
                }
            }
        }
        $this->form->setValuesByArray($values);
    }

    /**
     * @return ilPropertyFormGUI
     */
    public function initConfigurationForm()
    {
        $this->form = new ilPropertyFormGUI();
        foreach ($this->getFields() as $key => $item) {
            /** @var ilFormPropertyGUI $field */
            $field = new $item['type']($this->plugin->txt($key), $key);
            if ($item['type'] === self::TYPE_RTE_TEXT_AREA) {
                /** @var ilTextAreaInputGUI $field */
                $field->setUseRte(true);
                /* A hack to use RTE in places without ref_ids is to set set the object id to '1' and the
                 * object type to 'tst'. Then ilWebAccessChecker only verifies that the user has read access to the repository.
                 */
                $field->setRTESupport(1, 'tst', '', null, false, '3.4.7');
                $field->setRteTagSet('extended_img');
            }
            if ($item['info']) {
                $field->setInfo($this->plugin->txt($key . '_info'));
            }
            if (is_array($item['subelements'])) {
                /** @var ilSubEnabledFormPropertyGUI $field */
                foreach ($item['subelements'] as $subkey => $subitem) {
                    $subfield = new $subitem['type']($this->plugin->txt($key . '_' . $subkey), $key . '_' . $subkey);
                    if ($subitem['info']) {
                        /** @var ilFormPropertyGUI $subfield */
                        $subfield->setInfo($this->plugin->txt($key . '_info'));
                    }
                    $field->addSubItem($subfield);
                }
            }
            $this->form->addItem($field);
        }
        $this->form->addCommandButton('save', $this->plugin->txt('save'));
        $this->form->setTitle($this->plugin->txt('configuration'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        return $this->form;
    }

    public function save(): void
    {
        $this->initConfigurationForm();
        if ($this->form->checkInput()) {
            foreach ($this->getFields() as $key => $item) {
                $this->object->setValue($key, $this->form->getInput($key));
                if (is_array($item['subelements'])) {
                    foreach (array_keys($item['subelements']) as $subkey) {
                        $this->object->setValue($key . '_' . $subkey, $this->form->getInput($key . '_' . $subkey));
                    }
                }
            }
            $this->tpl->setOnScreenMessage(
                IlGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $this->plugin->txt('conf_saved')
            );
            $this->ctrl->redirect($this, 'configure');
        } else {
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHtml());
        }
    }
}
