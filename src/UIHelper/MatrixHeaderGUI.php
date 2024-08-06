<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\UIHelper;

use ilSubEnabledFormPropertyGUI;
use ilRepositoryObjectPlugin;
use ilTemplate;
use ilUtil;

class MatrixHeaderGUI extends ilSubEnabledFormPropertyGUI
{
    public $parentgui;
    /**
     * @var string
     */
    protected $html = '';
    /**
     * @var array
     */
    protected $scale = [];
    /**
     * @var string
     */
    protected $block_info = '';
    protected \ilRepositoryObjectPlugin $plugin;

    public function __construct(ilRepositoryObjectPlugin $plugin, string $a_title = '', string $a_postvar = '')
    {
        parent::__construct($a_title, $a_postvar);
        $this->setType('matrix_header');
        $this->setPostvar('matrix_header');
        $this->plugin = $plugin;
    }

    public function getHtml(): string
    {
        $tpl = $this->plugin->getTemplate('default/Matrix/tpl.matrix_header.html');

        $width = floor(100 / count($this->getScale()));
        $even = false;
        foreach ($this->getScale() as $title) {
            if ($title == '' || $title == ' ') {
                $title = '&nbsp;';
            }
            $title = str_replace('  ', '&nbsp;', $title);

            $tpl->setCurrentBlock('item');
            $tpl->setVariable('NAME', $title);
            $tpl->setVariable('STYLE', $width . '%');
            $tpl->setVariable('CLASS', $even ? "ilUnitEven" : "ilUnitOdd");
            $even = !$even;
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    public function setValueByArray(array $a_values): void
    {
        foreach ($this->getSubItems() as $item) {
            /**
             * @var SliderInputGUI $item
             */
            $item->setValueByArray($a_values);
        }
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $a_tpl->setCurrentBlock('prop_custom');
        $a_tpl->setVariable('CUSTOM_CONTENT', $this->getHtml());
        $a_tpl->parseCurrentBlock();
    }

    public function checkInput(): bool
    {
        return $this->checkSubItemsInput();
    }

    /**
     * @param mixed $parentform
     */
    public function setParentform($parentform): void
    {
        $this->parentform = $parentform;
    }

    public function getParentform(): ?\ilPropertyFormGUI
    {
        return $this->parentform;
    }

    public function setParentgui($parentgui): void
    {
        $this->parentgui = $parentgui;
    }

    public function getParentgui()
    {
        return $this->parentgui;
    }

    public function setPostvar($postvar): void
    {
        $this->postvar = $postvar;
    }

    public function getPostvar(): string
    {
        return $this->postvar;
    }

    public function setScale(array $scale): void
    {
        $this->scale = $scale;
    }

    public function getScale(): array
    {
        return $this->scale;
    }

    public function setBlockInfo(string $block_info): void
    {
        $this->setTitle($block_info);
        $this->block_info = $block_info;
    }

    public function getBlockInfo(): string
    {
        return $this->block_info;
    }

    public function getRequired(): bool
    {
        return false;
    }
}
