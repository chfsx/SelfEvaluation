<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Question\Meta\Type;

use ilRadioOption;

class MetaTypeOption extends ilRadioOption
{
    public function __construct(string $info = '')
    {
        parent::__construct('', '', $info);
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;

        foreach ($this->getSubItems() as $sub_item) {
            $this->disable($sub_item, $disabled);
        }
    }

    /**
     * Disable items recursively
     * @param  $item
     * @param bool   $disabled
     */
    protected function disable($item, $disabled)
    {
        if (method_exists($item, 'getSubItems')) {
            foreach ($item->getSubItems() as $sub_item) {
                $this->disable($sub_item, $disabled);
            }
        }

        if (method_exists($item, 'setDisabled')) {
            $item->setDisabled($disabled);
        }
    }

}
