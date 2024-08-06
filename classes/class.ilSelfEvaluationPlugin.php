<?php

declare(strict_types=1);

class ilSelfEvaluationPlugin extends ilRepositoryObjectPlugin
{
    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();

        parent::__construct($this->db, $DIC["component.repository"], 'xsev');
    }

    /**
     * @return string
     */
    public function getPluginName(): string
    {
        return 'SelfEvaluation';
    }

    /**
     * @return ilSelfEvaluationConfig
     */
    public function getConfigObject(): \ilSelfEvaluationConfig
    {
        return new ilSelfEvaluationConfig($this->getConfigTableName());
    }

    /**
     * @return string
     */
    public function getConfigTableName(): string
    {
        return 'rep_robj_xsev_c';
    }

    protected function uninstallCustom(): void
    {
        return;
    }

    /**
     * decides if this repository plugin can be copied
     * @return bool
     */
    public function allowCopy(): bool
    {
        return true;
    }
}
