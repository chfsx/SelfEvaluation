<#1>
<?php

$pl = new ilSelfEvaluationPlugin();
$conf = $pl->getConfigObject();
$conf->initDB();
?>
<#2>
<?php
$fields = [
    'id' => [
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ],
    'is_online' => [
        'type' => 'integer',
        'length' => 1,
    ],
    'evaluation_type' => [
        'type' => 'integer',
        'length' => 1,
    ],
    'sort_type' => [
        'type' => 'integer',
        'length' => 1,
    ],
    'display_type' => [
        'type' => 'integer',
        'length' => 1,
    ],
    'intro' => [
        'type' => 'clob',
    ],
    'outro' => [
        'type' => 'clob',
    ],
];
if (!$this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $this->db->createTable(ilObjSelfEvaluation::TABLE_NAME, $fields);
    $this->db->addPrimaryKey(ilObjSelfEvaluation::TABLE_NAME, ['id']);
}

?>
<#3>
<?php

$block = new \ilub\plugin\SelfEvaluation\UIHelper\Scale\ScaleUnit($this->db);
$block->initDB();

$block = new \ilub\plugin\SelfEvaluation\UIHelper\Scale\Scale($this->db);
$block->initDB();

$block = new \ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock($this->db);
$block->initDB();

$block = new \ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock($this->db);
$block->initDB();

$data = new \ilub\plugin\SelfEvaluation\Dataset\Data($this->db);
$data->initDB();

$dataset = new \ilub\plugin\SelfEvaluation\Dataset\Dataset($this->db);
$dataset->initDB();

$id = new \ilub\plugin\SelfEvaluation\Identity\Identity($this->db);
$id->initDB();

$id = new \ilub\plugin\SelfEvaluation\Feedback\Feedback($this->db);
$id->initDB();

$mq = new \ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion($this->db);
$mq->initDB();

$q = new \ilub\plugin\SelfEvaluation\Question\Matrix\Question($this->db);
$q->initDB();
?>
<#4>
<?php


if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 1,
    ];
    $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_questions', $field);

}

?>
<#5>
<?php


if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 1,
    ];
    $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs', $field);
    $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_charts', $field);
    $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview', $field);
    $this->db->dropTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_questions');

}

?>
<#6>
<?php


if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 1,
    ];
    $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_block_titles_sev', $field);
    $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_block_titles_fb', $field);
    $this->db->manipulate('UPDATE ' . ilObjSelfEvaluation::TABLE_NAME .
        ' SET `show_block_titles_sev` = 1, `show_block_titles_fb` = 1;');
}
?>
<#7>
<?php


if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 1,
    ];
    $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_block_desc_sev', $field);
    $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_block_desc_fb', $field);
    $this->db->manipulate('UPDATE ' . ilObjSelfEvaluation::TABLE_NAME .
        ' SET `show_block_desc_sev` = 1, `show_block_desc_fb` = 1;');
}
?>
<#8>
<?php


$block = new \ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock($this->db);
if (!$this->db->tableColumnExists($block::_getTableName(), 'abbreviation')) {
    $field = [
        'type' => 'text',
        'length' => 1024,
    ];
    $this->db->addTableColumn($block::_getTableName(), 'abbreviation', $field);
}
?>
<#9>
<?php

$pl = new ilSelfEvaluationPlugin();
if ($this->db->tableExists('robjselfevaluation_c') and !$this->db->tableExists($pl->getConfigTableName())) {
    $this->db->renameTable('robjselfevaluation_c', $pl->getConfigTableName());
}

if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'clob',
    ];
    $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'identity_selection_info', $field);
}
?>
<#10>
<?php


$block = new \ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock($this->db);
$block->initDB();

if (!$this->db->tableColumnExists(\ilub\plugin\SelfEvaluation\Dataset\Data::TABLE_NAME, 'question_type')) {
    $field = [
        'type' => 'text',
        'length' => 1024,
        'notnull' => true
    ];
    $this->db->addTableColumn(\ilub\plugin\SelfEvaluation\Dataset\Data::TABLE_NAME, 'question_type', $field);
    $this->db->manipulate('UPDATE ' . \ilub\plugin\SelfEvaluation\Dataset\Data::TABLE_NAME .
        ' SET `question_type` = ' . $this->db->quote(\ilub\plugin\SelfEvaluation\Dataset\Data::QUESTION_TYPE, 'text') . ';');
    $this->db->modifyTableColumn(\ilub\plugin\SelfEvaluation\Dataset\Data::TABLE_NAME, 'value', ['type' => 'clob']);
}
?>
<#11>
<?php


if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'sort_random_nr_items_block')) {
        $field = [
            'type' => 'integer',
            'length' => 4,
        ];
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'sort_random_nr_items_block', $field);
    }
}
?>
<#12>
<?php


if ($this->db->tableExists(\ilub\plugin\SelfEvaluation\Dataset\Data::TABLE_NAME)) {
    if (!$this->db->tableColumnExists(\ilub\plugin\SelfEvaluation\Dataset\Data::TABLE_NAME, 'creation_date')) {
        $field = [
            'type' => 'integer',
            'length' => 4
        ];
        $this->db->addTableColumn(\ilub\plugin\SelfEvaluation\Dataset\Data::TABLE_NAME, 'creation_date', $field);
    }
}
?>
<#13>
<?php




if (!$this->db->tableColumnExists(
    \ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME,
    'short_title'
)) {
    $field = [
        'type' => 'text',
        'length' => 1024,
        'notnull' => true
    ];
    $this->db->addTableColumn(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'short_title', $field);
}

?>
<#14>
<?php


if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_bar')) {
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_bar', $field);
    }
    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_spider')) {
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_spider', $field);
    }
    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_left_right')) {
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_left_right', $field);
    }

    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_chart_bar')) {
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_chart_bar', $field);
    }
    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_chart_spider')) {
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_chart_spider', $field);
    }
    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_chart_left_right')) {
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_chart_left_right', $field);
    }

    $this->db->manipulate('UPDATE ' . ilObjSelfEvaluation::TABLE_NAME .
        ' SET
			`show_fbs_overview_bar` = 1,
			`show_fbs_overview_spider` = 1,
			`show_fbs_overview_left_right` = 1,
			`show_fbs_chart_bar` = 1,
			`show_fbs_chart_spider` = 1,
			`show_fbs_chart_left_right` = 1
			;');

}
?>
<#15>
<?php

$this->db->modifyTableColumn(\ilub\plugin\SelfEvaluation\Feedback\Feedback::TABLE_NAME, 'feedback_text', [
    'type' => 'clob'
]);
?>
<#16>
<?php


if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'text',
        'length' => 1024,
        'notnull' => true
    ];
    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'outro_title')) {
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'outro_title', $field);
    }
}
?>
<#17>
<?php


if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'bar_show_label_as_percentage')) {
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'bar_show_label_as_percentage', $field);
    }
}
?>
<#18>
<?php


if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'clob'
    ];
    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'block_option_random_desc')) {
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'block_option_random_desc', $field);
    }
}
?>
<#19>
<?php

if ($this->db->tableExists(\ilub\plugin\SelfEvaluation\Feedback\Feedback::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    if (!$this->db->tableColumnExists(\ilub\plugin\SelfEvaluation\Feedback\Feedback::TABLE_NAME, 'parent_type_overall')) {
        $this->db->addTableColumn(\ilub\plugin\SelfEvaluation\Feedback\Feedback::TABLE_NAME, 'parent_type_overall', $field);
    }
}
?>
<#20>
<?php

if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_text')) {
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_text', $field);
    }
}
?>
<#21>
<?php
if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_statistics')) {
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'show_fbs_overview_statistics', $field);
    }
}
?>
<#22>
<?php

if ($this->db->tableExists(ilObjSelfEvaluation::TABLE_NAME)) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'identity_selection')) {
        $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'identity_selection', $field);
    }
}
?>
<#23>
<?php
$overall_feedbacks = \ilub\plugin\SelfEvaluation\Feedback\Feedback::_getAllInstances($this->db, true);
foreach ($overall_feedbacks as $overall_feedback) {
    if (ilObject::_lookupType($overall_feedback->getParentId(), true) == "xsev") {
        $self_eval = new ilObjSelfEvaluation($overall_feedback->getParentId());
        $obj_id = $self_eval->getId();
        $overall_feedback->setParentId($obj_id);
        $overall_feedback->update();
    } else {
        $overall_feedback->delete();
        throw new Exception("Step 23: Given ID is not ref-id of type xsev: " . $overall_feedback->getParentId() . " the Feedback has been deleted due to invalid data");
    }
}
?>
<#24>
<?php
if (!$this->db->indexExistsByFields('rep_robj_xsev_d', ['i2'])) {
    //$this->db->addIndex('rep_robj_xsev_d', ['dataset_id', 'question_id', 'question_type'], 'i2');
}
?>
<#25>
<?php
if (!$this->db->tableColumnExists(ilObjSelfEvaluation::TABLE_NAME, 'identity_selection')) {
    $this->db->addTableColumn(ilObjSelfEvaluation::TABLE_NAME, 'identity_selection', [
        'type' => 'integer',
        'length' => 1
    ]);
}
if ($this->db->tableColumnExists(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'container_id')) {
    $this->db->renameTableColumn(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'container_id', 'parent_id');
}
if ($this->db->tableColumnExists(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'field_name')) {
    $this->db->renameTableColumn(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'field_name', 'name');
}
if ($this->db->tableColumnExists(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'field_type')) {
    $this->db->renameTableColumn(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'field_type', 'type');
}
if ($this->db->tableColumnExists(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'field_values')) {
    $this->db->renameTableColumn(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'field_values', 'values');
}
if ($this->db->tableColumnExists(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'field_required')) {
    $this->db->renameTableColumn(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'field_required', 'required');
}
if ($this->db->tableColumnExists(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'type')) {
    $this->db->renameTableColumn(\ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion::TABLE_NAME, 'type', 'type_id');
}
?>
<#26>
<?php
if (!$this->db->tableColumnExists(\ilub\plugin\SelfEvaluation\Dataset\Dataset::TABLE_NAME, 'complete')) {
    $field = [
        'type' => 'integer',
        'length' => 4
    ];
    $this->db->addTableColumn(\ilub\plugin\SelfEvaluation\Dataset\Dataset::TABLE_NAME, 'complete', $field);
}
$this->db->query("UPDATE ".\ilub\plugin\SelfEvaluation\Dataset\Dataset::TABLE_NAME." SET complete = 1 ");
?>
