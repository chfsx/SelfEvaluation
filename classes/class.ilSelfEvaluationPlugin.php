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

    /**
     * @description This is the easiest way to fix all locations which use a template.
     * And this method most likely will work in ILIAS 9 and 10.
     */
    public function getTemplate(string $a_template, bool $a_par1 = true, bool $a_par2 = true): ilTemplate
    {
        // remove the string 'default/' from the template path, otherwise it will be doubled
        $a_template = str_replace('default/', '', $a_template);

        return new ilTemplate(
            $a_template,
            $a_par1,
            $a_par2,
            __DIR__ . '/../'
        );
    }

    public function getRelativeDirectory(): string
    {
        $ansolute_path = realpath(__DIR__ . '/../');
        // cut everything before /Customizing/
        $pos = strpos($ansolute_path, '/Customizing/');
        if ($pos !== false) {
            return '.' . substr($ansolute_path, $pos);
        }
        return '';
    }

    public function getStyleSheetLocation(string $a_css_file): string
    {
        return $this->getRelativeDirectory() . '/templates/' . $a_css_file;
    }

    /**
     * @deprecated the core method is not working ATM.
     */
    public static function _getIcon(string $a_type): string
    {
        return 'Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/images/icon_xsev.svg';
    }


}
