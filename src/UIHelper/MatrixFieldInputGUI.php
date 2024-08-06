<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\UIHelper;

use ilSubEnabledFormPropertyGUI;
use ilRepositoryObjectPlugin;
use ilTemplate;
use ILIAS\Refinery\ConstraintViolationException;

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
    protected \ilRepositoryObjectPlugin $plugin;

    public function __construct(ilRepositoryObjectPlugin $plugin, string $a_title = '', string $a_postvar = '')
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
        foreach (array_keys($this->getScale()) as $value) {
            $tpl->setCurrentBlock('item');
            if ($this->getValue() == $value && $this->getValue() !== null && $this->getValue() !== '') {
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

    public function insert(ilTemplate $a_tpl): void
    {
        $a_tpl->setCurrentBlock('prop_custom');
        $a_tpl->setVariable('CUSTOM_CONTENT', $this->getHtml());
        $a_tpl->parseCurrentBlock();
    }

    public function setValueByArray(array $values): void
    {
        if(array_key_exists($this->getPostVar(), $values)) {
            $this->setValue($values[$this->getPostVar()]);
            return;
        }
        try {
            [$matrix_key, $question_key] = explode("[", str_replace("]", "", $this->getPostVar()));
        }
        catch(\Exception $e){}

        if(array_key_exists($matrix_key, $values)) {
            $meta_question_values = $values[$matrix_key];
            if(array_key_exists($question_key, $meta_question_values)) {
                $this->setValue($meta_question_values[$question_key]);
            }
        }
    }

    public function setScale(array $scale): void
    {
        $this->scale = $scale;
    }

    public function getScale(): array
    {
        return $this->scale;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValues(array $values): void
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
            if (!$this->http->wrapper()->post()->has($post_var_parts[0])) {
                $this->setAlert($this->plugin->txt('msg_input_is_required'));
                return false;
            }
            try {
                $value = $this->http->wrapper()->post()->retrieve(
                    $post_var_parts[0],
                    $this->refinery->kindlyTo()->string()
                );
            } catch (ConstraintViolationException $e) {
                $value = $this->http->wrapper()->post()->retrieve(
                    $post_var_parts[0],
                    $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
                );
            }
            if (is_array($value)) {
                if (!array_key_exists($post_var_parts[1], $value)) {
                    $this->setAlert($this->plugin->txt('msg_input_is_required'));
                    return false;
                }
            } elseif (trim($value) === '') {
                $this->setAlert($this->plugin->txt('msg_input_is_required'));
                return false;
            }
        }
        return true;
    }
}
