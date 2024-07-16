<?php

declare(strict_types=1);
use ilub\plugin\SelfEvaluation\Block\BlockGUI;
use ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock;

class MetaBlockGUI extends BlockGUI
{
    /**
     * @var MetaBlock
     */
    protected $object;

    public function __construct(
        ilDBInterface $db,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilAccessHandler $access,
        ilSelfEvaluationPlugin $plugin,
        ilObjSelfEvaluationGUI $parent
    ) {
        parent::__construct($db, $tpl, $ilCtrl, $access, $plugin, $parent);
        if ($parent->http->query()->has('block_id')) {
            $this->object = new MetaBlock($this->db,
                $parent->http->query()->retrieve('block_id', $parent->refinery->kindlyTo()->int()));
        }
        else {
            $this->object = new MetaBlock($this->db);
        }
        $this->object->setParentId($this->parent->getObjId());
    }
}
