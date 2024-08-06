<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Player;

use ilPropertyFormGUI;
use ilub\plugin\SelfEvaluation\UIHelper\KnobGUI;
use ilGlobalTemplateInterface;
use ilRepositoryObjectPlugin;

class PlayerFormContainer extends ilPropertyFormGUI
{
    /**
     * @var array
     */
    protected $copy_of_buttons = [];

    /**
     * @var KnobGUI
     */
    protected $knob = null;

    /**
     * @var int
     */
    protected $question_field_size = 6;
    

    protected \ilRepositoryObjectPlugin $plugin;

    public function __construct(ilGlobalTemplateInterface $tpl, ilRepositoryObjectPlugin $plugin)
    {
        $this->global_tpl = $tpl;
        $this->plugin = $plugin;

        parent::__construct();
    }

    /**
     * @param        $a_cmd
     * @param        $a_text
     * @param string $a_id
     */
    public function addCommandButton(string $a_cmd, string $a_text, string $a_id = ''): void
    {

        $this->copy_of_buttons[] = ["cmd" => $a_cmd, "text" => $a_text];
        parent::addCommandButton($a_cmd, $a_text);
    }

    /**
     * Remove all command buttons
     */
    public function clearCommandButtons(): void
    {
        $this->copy_of_buttons = [];
        parent::clearCommandButtons();
    }

    public function addKnob($page, $last_page): void
    {
        $this->knob = new KnobGUI();
        $this->knob->setValue($page);
        $this->knob->setMax($last_page);

    }

    /**
     * Get Content.
     */
    public function getContent(): string
    {
        $this->tpl = $this->plugin->getTemplate('default/Player/tpl.player_form.html');
        $this->global_tpl->addCss($this->plugin->getStyleSheetLocation("css/player.css"));
        $this->global_tpl->addJavaScript("./Services/JavaScript/js/Basic.js");
        $this->global_tpl->addJavaScript("Services/Form/js/Form.js");
        $this->global_tpl->addJavascript("./Services/UIComponent/Tooltip/js/ilTooltip.js");
        $this->global_tpl->addJavascript($this->plugin->getDirectory()."/templates/js/scale_units.js");
        $this->global_tpl->addJavascript($this->plugin->getDirectory()."/templates/js/jquery.knob.js");
        $this->global_tpl->addOnLoadCode('il.Tooltip.init();', 3);

        $required_text = false;

        foreach ($this->getItems() as $item) {
            /**
             * @var $item \ilFormPropertyGUI
             */
            if ($item->getType() == "section_header" && $this->knob) {
                $this->tpl->setVariable("PROGRESS_KNOB", $this->knob->getHtml($this->global_tpl, $this->plugin));
            }
            if ($item->getType() != "hidden") {
                $this->tpl->setVariable("RATING_SIZE", 12 - $this->getQuestionFieldSize());
                $this->tpl->setVariable("QUESTION_SIZE", $this->getQuestionFieldSize());
                $this->tpl->setVariable("RATING_SIZE_COMMAND", 12 - $this->getQuestionFieldSize());
                $this->tpl->setVariable("QUESTION_SIZE_COMMAND", $this->getQuestionFieldSize());
                $this->tpl->setVariable("TYPE", $item->getType());

                $this->insertItem($item);
            }

            if ($item->getRequired()) {
                $required_text = true;
            }
        }

        if ($required_text && $this->getMode() === "std") {
            $this->tpl->setCurrentBlock("required_text");
            $this->tpl->setVariable("TXT_REQUIRED", $this->plugin->txt("required_field"));
            $this->tpl->parseCurrentBlock();
        }

        /** command buttons**/
        foreach ($this->copy_of_buttons as $button) {
            $this->tpl->setCurrentBlock("cmd");
            $this->tpl->setVariable("CMD", $button["cmd"]);
            $this->tpl->setVariable("CMD_TXT", $button["text"]);
            $this->tpl->parseCurrentBlock();
        }

        // hidden properties
        $hidden_fields = false;
        foreach ($this->getItems() as $item) {
            if ($item->getType() == "hidden") {
                /**
                 * @var $item \ilTextInputGUI
                 */
                $item->insert($this->tpl);
                $hidden_fields = true;
            }
        }

        if ($required_text || $hidden_fields) {
            $this->tpl->setCurrentBlock("commands");
            $this->tpl->parseCurrentBlock();
        }

        return $this->tpl->get();
    }

    /**
     * @param int $question_field_size
     */
    public function setQuestionFieldSize($question_field_size): void
    {
        $this->question_field_size = $question_field_size;
    }

    /**
     * @return int
     */
    public function getQuestionFieldSize()
    {
        return $this->question_field_size;
    }

}
