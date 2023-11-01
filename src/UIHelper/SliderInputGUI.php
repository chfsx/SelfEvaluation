<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\UIHelper;

use ilSubEnabledFormPropertyGUI;
use ilGlobalTemplateInterface;
use ilRepositoryObjectPlugin;
use ilTemplate;

class SliderInputGUI extends ilSubEnabledFormPropertyGUI
{
    public const PREFIX = 'slider_';
    /**
     * @var array
     */
    protected $values = [0, 1];
    /**
     * @var int
     */
    protected $min = 0;
    /**
     * @var int
     */
    protected $max = 0;
    /**
     * @var string
     */
    protected $unit = '%';
    /**
     * @var string
     */
    protected $ajax = '';
    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilRepositoryObjectPlugin
     */
    protected $plugin;

    public function __construct(
        ilGlobalTemplateInterface $tpl,
        ilRepositoryObjectPlugin $plugin,
        string $title,
        string $post_var,
        int $min,
        int $max,
        string $ajax_request = ''
    ) {
        parent::__construct($title, $post_var);
        $this->tpl = $tpl;
        $this->plugin = $plugin;
        $this->setMin($min);
        $this->setMax($max);
        $this->setAjax($ajax_request);
    }

    public function getHtml(): string
    {
        return $this->buildHTML();
    }

    private function buildHTML(): string
    {
        $this->tpl->addCss("./libs/bower/bower_components/jquery-ui/themes/base/jquery-ui.css");
        $this->tpl->addJavaScript("./libs/bower/bower_components/jquery-ui/jquery-ui.min.js");

        $tpl = $this->plugin->getTemplate('default/Feedback/tpl.slider_input.html');

        $values = $this->getValues();
        $tpl->setVariable('VAL_FROM', $values[0]);
        $tpl->setVariable('VAL_TO', $values[1]);
        $tpl->setVariable('MIN', $this->getMin());
        $tpl->setVariable('MAX', $this->getMax());
        $tpl->setVariable('POSTVAR', self::PREFIX . $this->getPostVar() . '');
        $tpl->setVariable('UNIT', $this->getUnit());
        if ($this->getAjax()) {
            $tpl->setVariable('AJAX', $this->getAjax());
            $tpl->setVariable('WARNING', $this->plugin->txt('warning_overlap'));
        }

        return $tpl->get();
    }

    public function insert(ilTemplate $a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_custom");
        $a_tpl->setVariable("CUSTOM_CONTENT", $this->getHtml());
        $a_tpl->parseCurrentBlock();
    }

    public function checkInput(): bool
    {
        global $lng;
        $_POST[$this->getPostVar()] = [
            $_POST[self::PREFIX . $this->getPostVar() . '_from'],
            $_POST[self::PREFIX . $this->getPostVar() . '_to']
        ];

        if ($this->getRequired() and
            trim($_POST[self::PREFIX . $this->getPostVar() . '_from']) == '' and
            trim($_POST[self::PREFIX . $this->getPostVar() . '_to']) == ''
        ) {
            $this->setAlert($lng->txt('msg_input_is_required'));

            return false;
        }

        return $this->checkSubItemsInput();
    }

    public function setValueByArray(array $array)
    {
        foreach ($this->getSubItems() as $item) {
            /**
             * @var SliderInputGUI $item
             */
            $item->setValueByArray($array);
        }
        $this->setValues((array)$array[$this->getPostVar()]);
    }

    public function setValues(array $values)
    {
        $this->values = $values;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setMax(int $max)
    {
        $this->max = $max;
    }

    public function getMax(): int
    {
        return $this->max;
    }

    public function setMin(int $min)
    {
        $this->min = $min;
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function setUnit(string $unit)
    {
        $this->unit = $unit;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setAjax(string $ajax)
    {
        $this->ajax = $ajax;
    }

    public function getAjax(): string
    {
        return $this->ajax;
    }
}
