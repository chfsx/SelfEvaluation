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

    public function getPluginName(): string
    {
        return 'SelfEvaluation';
    }

    public function getConfigObject(): ilSelfEvaluationConfig
    {
        return new ilSelfEvaluationConfig($this->getConfigTableName());
    }

    public function getConfigTableName(): string
    {
        return 'rep_robj_xsev_c';
    }

    protected function uninstallCustom(): void
    {
    }

    public function allowCopy(): bool
    {
        return true;
    }
}
