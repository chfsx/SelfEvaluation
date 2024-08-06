<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\UIHelper;

use ilRepositoryObjectPlugin;
use ilGlobalTemplateInterface;

class KnobGUI
{
    public const CAP_BUTT = '\'butt\'';
    public const CAP_ROUND = '\'round\'';
    public const CAP_GAUGE = '\'gauge\'';
    private static int $num = 1;
    /**
     * @var string
     */
    protected $html = '';
    /**
     * @var int
     */
    protected $value = 0;
    /**
     * @var int
     */
    protected $min = 0;
    /**
     * @var int
     */
    protected $max = 100;
    /**
     * @var array
     */
    protected $fg_color = [208, 232, 255];
    /**
     * @var array
     */
    protected $input_color = [208, 232, 255];
    /**
     * @var array
     */
    protected $bg_color = [240, 240, 240];
    /**
     * @var bool
     */
    protected $read_only = true;
    /**
     * @var int
     */
    protected $angle_offset = 0;
    /**
     * @var int
     */
    protected $angle_arc = 360;
    /**
     * @var bool
     */
    protected $stopper = true;
    /**
     * @var int
     */
    protected $thickness = 0.3;
    /**
     * @var string
     */
    protected $line_cap = self::CAP_BUTT;
    /**
     * @var $height
     */
    protected $height = 50;
    /**
     * @var bool
     */
    protected $display_input = true;
    /**
     * @var bool
     */
    protected $display_previous = false;

    public function render(ilGlobalTemplateInterface $tpl, ilRepositoryObjectPlugin $plugin): void
    {
        self::$num++;
        $tpl->addJavaScript($plugin->getDirectory() . '/templates/js/jquery.knob.js');
        $knob = $plugin->getTemplate('default/Form/tpl.knob.html', false, false);
        $knob->setVariable('ID', 'knob_' . self::getNum());
        $knob->setVariable('VALUE', $this->getValue());
        $knob->setVariable('MIN', $this->getMin());
        $knob->setVariable('MAX', $this->getMax());
        $knob->setVariable('READONLY', $this->getReadOnly() ? 'true' : 'false');
        $knob->setVariable('FGCOLOR', implode(', ', $this->getFgColor()));
        $knob->setVariable('INCOLOR', implode(', ', $this->getInputColor()));
        $knob->setVariable('BGCOLOR', implode(', ', $this->getBgColor()));
        $knob->setVariable('ANGLEOFFSET', $this->getAngleOffset());
        $knob->setVariable('ANGLEARC', $this->getAngleArc());
        $knob->setVariable('STOPPER', $this->getStopper() ? 'true' : 'false');
        $knob->setVariable('THICKNESS', $this->getThickness());
        $knob->setVariable('LINECAP', $this->getLineCap());
        $knob->setVariable('HEIGHT', $this->getHeight());
        $knob->setVariable('DISPLAYINPUT', $this->getDisplayInput() ? 'true' : 'false');
        $knob->setVariable('DISPLAYPREVIOUS', $this->getDisplayPrevious() ? 'true' : 'false');
        $this->setHtml($knob->get());
    }

    public function setHtml(string $html): void
    {
        $this->html = $html;
    }

    public function getHtml(ilGlobalTemplateInterface $tpl, ilRepositoryObjectPlugin $plugin): string
    {
        $this->render($tpl, $plugin);

        return $this->html;
    }

    /**
     * @param int $max
     */
    public function setMax($max): void
    {
        $this->max = $max;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param int $min
     */
    public function setMin($min): void
    {
        $this->min = $min;
    }

    /**
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param int $num
     */
    public static function setNum(int $num): void
    {
        self::$num = $num;
    }

    /**
     * @return int
     */
    public static function getNum(): int
    {
        return self::$num;
    }

    /**
     * @param int $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array $fg_color
     */
    public function setFgColor($fg_color): void
    {
        $this->fg_color = $fg_color;
    }

    /**
     * @return array
     */
    public function getFgColor()
    {
        return $this->fg_color;
    }

    /**
     * @param array $in_color
     */
    public function setInputColor($in_color): void
    {
        $this->input_color = $in_color;
    }

    /**
     * @return array
     */
    public function getInputColor()
    {
        return $this->input_color;
    }

    /**
     * @param boolean $read_only
     */
    public function setReadOnly($read_only): void
    {
        $this->read_only = $read_only;
    }

    /**
     * @return boolean
     */
    public function getReadOnly()
    {
        return $this->read_only;
    }

    /**
     * @param int $angle_arc
     */
    public function setAngleArc($angle_arc): void
    {
        $this->angle_arc = $angle_arc;
    }

    /**
     * @return int
     */
    public function getAngleArc()
    {
        return $this->angle_arc;
    }

    /**
     * @param int $angle_offset
     */
    public function setAngleOffset($angle_offset): void
    {
        $this->angle_offset = $angle_offset;
    }

    /**
     * @return int
     */
    public function getAngleOffset()
    {
        return $this->angle_offset;
    }

    /**
     * @param array $bg_color
     */
    public function setBgColor($bg_color): void
    {
        $this->bg_color = $bg_color;
    }

    /**
     * @return array
     */
    public function getBgColor()
    {
        return $this->bg_color;
    }

    /**
     * @param boolean $display_input
     */
    public function setDisplayInput($display_input): void
    {
        $this->display_input = $display_input;
    }

    /**
     * @return boolean
     */
    public function getDisplayInput()
    {
        return $this->display_input;
    }

    /**
     * @param boolean $display_previous
     */
    public function setDisplayPrevious($display_previous): void
    {
        $this->display_previous = $display_previous;
    }

    /**
     * @return boolean
     */
    public function getDisplayPrevious()
    {
        return $this->display_previous;
    }

    /**
     * @param string $line_cap
     */
    public function setLineCap($line_cap): void
    {
        $this->line_cap = $line_cap;
    }

    /**
     * @return string
     */
    public function getLineCap()
    {
        return $this->line_cap;
    }

    /**
     * @param boolean $stopper
     */
    public function setStopper($stopper): void
    {
        $this->stopper = $stopper;
    }

    /**
     * @return boolean
     */
    public function getStopper()
    {
        return $this->stopper;
    }

    /**
     * @param int $thickness
     */
    public function setThickness($thickness): void
    {
        $this->thickness = $thickness;
    }

    /**
     * @return int
     */
    public function getThickness()
    {
        return $this->thickness;
    }

    /**
     * @param int $height
     */
    public function setHeight($height): void
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }
}
