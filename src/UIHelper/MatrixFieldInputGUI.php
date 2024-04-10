<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\UIHelper;

use ilSubEnabledFormPropertyGUI;
use ilRepositoryObjectPlugin;
use ilTemplate;

class MatrixFieldInputGUI extends ilSubEnabledFormPropertyGUI
{
    /**
     * @var string
     */
    protected $value = "";
    /**
     * @var array
     */
    protected $values;
    /**
     * @var array
     */
    protected $scale = [];
    /**
     * @var ilRepositoryObjectPlugin
     */
    protected $plugin;

    public function __construct(ilRepositoryObjectPlugin $plugin, $a_title = '', $a_postvar = '')
    {
        parent::__construct($a_title, $a_postvar);
        $this->setType('matrix_field');

        $this->plugin = $plugin;
    }

    public function getHtml(): string
    {
        return $this->buildHTML();
    }

    private function buildHTML(): string
    {
        $tpl = $this->plugin->getTemplate('default/Matrix/tpl.matrix_input.html');

        $even = false;
        $tpl->setVariable('ROW_NAME', $this->getPostVar());
        foreach ($this->getScale() as $value => $title) {
            $tpl->setCurrentBlock('item');
            if ($this->getValue() == $value and $this->getValue() !== null and $this->getValue() !== '') {
                $tpl->setVariable('SELECTED', 'checked="checked"');
            }
            $tpl->setVariable('CLASS', $even ? "ilUnitEven" : "ilUnitOdd");
            $even = !$even;
            $tpl->setVariable('VALUE', $value);
            $tpl->setVariable('NAME', $this->getPostVar());
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    public function insert(ilTemplate $a_tpl)
    {
        $a_tpl->setCurrentBlock('prop_custom');
        $a_tpl->setVariable('CUSTOM_CONTENT', $this->getHtml());
        $a_tpl->parseCurrentBlock();
    }

    public function setValueByArray($values)
    {
        if(array_key_exists($this->getPostVar(), $values)) {
            $this->setValue($values[$this->getPostVar()]);
            return;
        }
        try {
            list($matrix_key, $question_key) = explode("[", str_replace("]", "", $this->getPostVar()));
        }
        catch(\Exception $e){}

        if(array_key_exists($matrix_key, $values)) {
            $meta_question_values = $values[$matrix_key];
            if($meta_question_values[$question_key]) {
                $this->setValue($meta_question_values[$question_key]);
            }
        }
    }

    public function setScale(array $scale)
    {
        $this->scale = $scale;
    }

    public function getScale(): array
    {
        return $this->scale;
    }

    public function setValue(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValues(array $values)
    {
        $this->values = $values;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function checkInput(): bool
    {
        if ($this->getRequired()) {

            $post_var_parts = explode("[", str_replace("]", "", $this->getPostVar()));

            $value = $_POST;

            $pass = true;
            foreach ($post_var_parts as $part) {
                if (!$this->http->wrapper()->post()->has($part)) {
                    $pass = false;
                    $this->setAlert($this->plugin->txt('msg_input_is_required'));

                } else {
                    $value = $this->http->wrapper()->post()->retrieve($part, $this->refinery->kindlyTo()->string());
                }
            }

            if (!$pass || trim($value) == '') {
                $this->setAlert($this->plugin->txt('msg_input_is_required'));
                return false;
            }
        }

        return true;
    }
}
