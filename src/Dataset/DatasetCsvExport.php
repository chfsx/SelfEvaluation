<?php
namespace ilub\plugin\SelfEvaluation\Dataset;

use ilub\plugin\SelfEvaluation\CsvExport\csvExport;
use ilub\plugin\SelfEvaluation\CsvExport\csvExportRow;
use ilub\plugin\SelfEvaluation\CsvExport\csvExportValue;

use ilSelfEvaluationPlugin;
use ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion;
use ilub\plugin\SelfEvaluation\Question\Matrix\Question;
use ilub\plugin\SelfEvaluation\CsvExport\csvExportColumn;
use ilDBInterface;
use ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock;
use ilub\plugin\SelfEvaluation\Block\Block;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaTypeMatrix;
use Exception;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaTypeSelect;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaTypeSingleChoice;
use ilub\plugin\SelfEvaluation\Identity\Identity;
use ilObjUser;

class DatasetCsvExport extends csvExport
{
    /**
     * @var int
     */
    protected $object_id = 0;

    /**
     * @var MetaQuestion[]
     */
    protected $meta_questions = [];
    /**
     * @var Question[]
     */
    protected $questions = [];
    /**
     * @var Dataset[]
     */
    protected $datasets = [];

    /**
     * @var string
     */
    protected $date_format = "Y-m-d H:i:s";

    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $pl;

    /**
     * @var ilDBInterface
     */
    protected $db;

    function __construct(ilDBInterface $db, ilSelfEvaluationPlugin $pl, $object_id = 0)
    {
        parent::__construct();
        $this->setObjectId($object_id);
        $this->pl = $pl;
        $this->db = $db;

    }

    public function getCsvExport(string $delimiter = ";", string $enclosure = '"')
    {
        $this->getData();
        $this->setColumns();
        $this->setRows();
        parent::getCsvExport($delimiter);
    }

    protected function getData()
    {
        $meta_blocks = MetaBlock::_getAllInstancesByParentId($this->db,$this->getObjectId());
        foreach ($meta_blocks as $meta_block) {
            $this->addMetaQuestions(MetaQuestion::_getAllInstancesForParentId($this->db, $meta_block->getId()));
        }
        $blocks = MetaBlock::_getAllInstancesByParentId($this->db,$this->getObjectId());
        foreach ($blocks as $block) {
            $this->addQuestions(Question::_getAllInstancesForParentId($this->db,$block->getId()));
        }

        $this->setDatasets(Dataset::_getAllInstancesByObjectId($this->db,$this->getObjectId()));
    }

    protected function setColumns()
    {
        $position = 0;
        $this->getTable()->addColumn(new csvExportColumn("identity", $this->pl->txt("identity"), -100));
        $this->getTable()->addColumn(new csvExportColumn("starting_date", $this->pl->txt("starting_date"), -90));
        $this->getTable()->addColumn(new csvExportColumn("ending_date", $this->pl->txt("ending_date"), -80));
        $this->getTable()->addColumn(new csvExportColumn("duration", $this->pl->txt("duration"), -70));
        $this->getTable()->addColumn(new csvExportColumn("average", $this->pl->txt("overview_statistics_median"), -60));
        $this->getTable()->addColumn(new csvExportColumn("max", $this->pl->txt("overview_statistics_max"), -50));
        $this->getTable()->addColumn(new csvExportColumn("min", $this->pl->txt("overview_statistics_min"), -40));
        $this->getTable()->addColumn(new csvExportColumn("variance", $this->pl->txt("overview_statistics_varianz"),
            -30));
        $this->getTable()->addColumn(new csvExportColumn("sd", $this->pl->txt("overview_statistics_standardabweichung"),
            -20));
        $this->getTable()->addColumn(new csvExportColumn("sd_per_block",
            $this->pl->txt("overview_statistics_standardabweichung_per_plock"), -10));

        $this->getTable()->setSortColumn("starting_date");

        foreach ($this->getMetaQuestions() as $meta_question) {

            if ($meta_question->getTypeId() == MetaTypeMatrix::TYPE_ID) {
                $questions = MetaTypeMatrix::getQuestionsFromArray(
                    $meta_question->getValues());
                foreach ($questions as $question) {
                    $this->getTable()->addColumn(
                        new csvExportColumn(
                            $question,
                            $question,
                            $position
                        ));
                    $this->getTable()->addColumn(
                        new csvExportColumn(
                            $question . " ID",
                            $question . " ID",
                            $position
                        ));
                    $position++;
                }

            } else {
                if ($meta_question->getShortTitle()) {
                    $column_name = $meta_question->getShortTitle();

                } else {
                    $column_name = $meta_question->getName();
                }
                $this->getTable()->addColumn(
                    new csvExportColumn(
                        $column_name,
                        $column_name,
                        $position
                    ));

                if ($meta_question->getTypeId() == MetaTypeSelect::TYPE_ID ||
                    $meta_question->getTypeId() == MetaTypeSingleChoice::TYPE_ID) {
                    $this->getTable()->addColumn(
                        new csvExportColumn(
                            $column_name . " ID",
                            $column_name . " ID",
                            $position
                        ));
                }
                $position++;
            }

        }

        foreach ($this->getQuestions() as $question) {
            $this->getTable()->addColumn(new csvExportColumn($this->getTitleForQuestion($question),
                $this->getTitleForQuestion($question), $position));
            $position++;
        }
    }

    protected function getIdentity(Dataset $dataset) : csvExportValue
    {
        $identifier = new Identity($this->db,$dataset->getIdentifierId());
        $id = $identifier->getIdentifier();
        if ($identifier->getType() == Identity::TYPE_LOGIN) {
            $username = ilObjUser::_lookupName($identifier->getIdentifier());
            $id = $username['login'];
        }

        return new csvExportValue("identity", $id);
    }

    protected function getDateValues(Dataset $dataset) : array
    {
        $meta_csv_values = [];

        try {
            $invalid = false;
            if ($dataset->getCreationDate()) {
                $meta_csv_values[] = new csvExportValue("starting_date",
                    date($this->getDateFormat(), $dataset->getCreationDate()));
            } else {
                $invalid = true;
                $meta_csv_values[] = new csvExportValue("starting_date", "Invalid");
            }
            if ($dataset->getSubmitDate()) {
                $meta_csv_values[] = new csvExportValue("ending_date",
                    date($this->getDateFormat(), $dataset->getSubmitDate()));
            } else {
                $invalid = true;
                $meta_csv_values[] = new csvExportValue("ending_date", "Invalid");
            }
            if ($invalid) {
                $meta_csv_values[] = new csvExportValue("duration", "Invalid");
            } else {
                $meta_csv_values[] = new csvExportValue("duration", $dataset->getDuration());
            }
        } catch (Exception $e) {
            $meta_csv_values[] = new csvExportValue("Error", "Invalid Date");
        }
        return $meta_csv_values;
    }

    protected function getStatisticValues(Dataset $dataset) : array
    {
        $meta_csv_values = [];
        /**
         * @var $max_block Block
         * @var $min_block Block
         */
        [$min_block,$min_percentage] = $dataset->getMinPercentageBlockAndMin();
        [$max_block,$max_percentage] = $dataset->getMaxPercentageBlockAndMax();
        $sd_per_block = $dataset->getPercentageStandardabweichungPerBlock();

        $statistics_sd_per_block = "";
        foreach ($sd_per_block as $key => $sd) {
            $statistics_sd_per_block .= $dataset->getBlockById($key)->getTitle() . ": " . $sd . "; ";
        }

        $meta_csv_values[] = new csvExportValue("mean", $dataset->getOverallPercentage());
        $meta_csv_values[] = new csvExportValue("max", $max_block->getTitle() . ": " .$max_percentage . "%");
        $meta_csv_values[] = new csvExportValue("min", $min_block->getTitle() . ": " . $min_percentage . "%");
        $meta_csv_values[] = new csvExportValue("percentage_variance", $dataset->getOverallPercentageVarianz());
        $meta_csv_values[] = new csvExportValue("percentage_sd", $dataset->getOverallPercentageStandardabweichung());
        $meta_csv_values[] = new csvExportValue("percentage_sd_per_block", $statistics_sd_per_block);

        return $meta_csv_values;
    }

    protected function getQuestionValues($row, Data $entry)
    {

        $column_name = $this->getTitleForQuestion($this->getQuestion($entry->getQuestionId()));

        $column_name = $this->generateUniqueName($row, $column_name);

        $value = $this->handledSkipped($entry->getValue());

        return new csvExportValue($column_name, $value);
    }

    protected function generateUniqueName(csvExportRow $row, $column_name)
    {
        while ($row->getColumns()->columnIdExists($column_name)) {
            $column_name = $column_name . "_duplicate";
        }
        return $column_name;
    }

    protected function handledSkipped($value)
    {
        if ($value == "ilsel_dummy") {
            $value = "übersprungen";
        }
        return $value;
    }

    protected function getMetaQuestionValues($row, Data $entry, MetaQuestion $meta_question) : array
    {
        $meta_csv_values = [];

        if ($meta_question->getTypeId() == MetaTypeMatrix::TYPE_ID) {

            $question_values = $meta_question->getValues();
            $questions = MetaTypeMatrix::getQuestionsFromArray($question_values);
            foreach ($questions as $key => $question) {
                $entry_keys = $entry->getValue();
                if (is_array($entry_keys) && array_key_exists($key, $entry_keys)) {
                    $entry_key = $entry_keys[$key];
                    $entry_content = $question_values[$entry_key];

                    $meta_csv_values[] = new csvExportValue($question, $entry_content);
                    $meta_csv_values[] = new csvExportValue($question . " ID",
                        str_replace("scale_", "", $entry_key));
                }
            }

        } else {

            if ($meta_question->getShortTitle()) {
                $column_name = $meta_question->getShortTitle();

            } else {
                $column_name = $meta_question->getName();
            }
            $column_name = $this->generateUniqueName($row, $column_name);
            $key = $this->handledSkipped($entry->getValue());

            if ($meta_question->getTypeId() == MetaTypeSelect::TYPE_ID ||
                $meta_question->getTypeId() == MetaTypeSingleChoice::TYPE_ID) {
                $question_values = $meta_question->getValues();
                if (is_array($question_values) && array_key_exists($key, $question_values)) {
                    $meta_csv_values[] = new csvExportValue($column_name . " ID", $key);
                    $entry_value = $question_values[$key];
                    $meta_csv_values[] = new csvExportValue($column_name, $entry_value);
                } else {
                    //legacy issue, get ID from time with none JSON-Save of values.
                    $value = "";
                    foreach ($question_values as $qkey => $qvalue) {
                        if ($qvalue == $key) {
                            $key = $qkey;
                            $value = $qvalue;
                            break;
                        }
                    }
                    $meta_csv_values[] = new csvExportValue($column_name, $value);
                    $meta_csv_values[] = new csvExportValue($column_name . " ID", $key);
                }
            } else {
                $meta_csv_values[] = new csvExportValue($column_name, $key);
            }
        }
        return $meta_csv_values;
    }

    protected function getMetaQuestionValueForSelections()
    {

    }

    protected function setRows()
    {
        foreach ($this->getDatasets() as $dataset) {
            $row = new csvExportRow();
            $row->addValue($this->getIdentity($dataset));
            $values = $this->getDateValues($dataset);
            foreach ($values as $value) {
                $row->addValue($value);
            }
            $values = $this->getStatisticValues($dataset);
            foreach ($values as $value) {
                $row->addValue($value);
            }
            $entries = Data::_getAllInstancesByDatasetId($this->db,$dataset->getId());
            foreach ($entries as $entry) {
                if ($this->getMetaQuestion($entry->getQuestionId())) {
                    $meta_question = $this->getMetaQuestion($entry->getQuestionId());
                    $values = $this->getMetaQuestionValues($row, $entry,
                        $meta_question);
                    foreach ($values as $value) {
                        $row->addValue($value);
                    }
                }
                else if($this->getQuestion($entry->getQuestionId())) {
                    $row->addValue($this->getQuestionValues($row, $entry));
                }
            }
            $this->getTable()->addRow($row);

        }
    }

    protected function getTitleForQuestion(Question $question) : string
    {
        $block = new Question($this->db, $question->getParentId());
        $title = $question->getTitle() ? $question->getTitle() : $this->pl->txt('question') . ' ' . $block->getPosition() . '.' . $question->getPosition();
        return $title;
    }

    public function setObjectId(int $object_id)
    {
        $this->object_id = $object_id;
    }

    public function getObjectId() : int
    {
        return $this->object_id;
    }

    /**
     * @param Dataset[] $datasets
     */
    public function setDatasets($datasets)
    {
        $this->datasets = $datasets;
    }

    /**
     * @return Dataset[]
     */
    public function getDatasets()
    {
        return $this->datasets;
    }

    /**
     * @param MetaQuestion[] $meta_questions
     */
    public function setMetaQuestions($meta_questions)
    {
        $this->meta_questions = $meta_questions;
    }

    /**
     * @param MetaQuestion[] $meta_questions
     */
    public function addMetaQuestions($meta_questions)
    {
        $this->meta_questions = $this->meta_questions + $meta_questions;
    }

    /**
     * @return MetaQuestion[]
     */
    public function getMetaQuestions()
    {
        return $this->meta_questions;
    }


    public function getMetaQuestion(int $id) : ?MetaQuestion
    {
        return $this->meta_questions[$id];
    }

    /**
     * @param Question[] $questions
     */
    public function setQuestions($questions)
    {
        $this->questions = $questions;
    }

    /**
     * @param Question[] $questions
     */
    public function addQuestions($questions)
    {
        $this->questions = $this->questions + $questions;
    }

    /**
     * @return Question[]
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    public function getQuestion(int $id) : ?Question
    {
        return $this->questions[$id];
    }

    /**
     * @param string $date_format
     */
    public function setDateFormat(string $date_format)
    {
        $this->date_format = $date_format;
    }

    /**
     * @return string
     */
    public function getDateFormat() : string
    {
        return $this->date_format;
    }

}