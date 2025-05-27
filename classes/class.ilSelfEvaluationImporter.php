<?php

declare(strict_types=1);

class ilSelfEvaluationImporter extends ilXmlImporter
{
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $ref_id = false;
        foreach ($a_mapping->getMappingsOfEntity('Services/Container', 'objs') as $old => $new) {
            if (ilObject::_lookupType($new) === "xsev" && $a_id == $old) {
                $ref_array = ilObject::_getAllReferences($new);
                $ref_id = end($ref_array);
            }
        }

        $obj_self_eval = new ilObjSelfEvaluation((int) $ref_id);
        $obj_self_eval->fromXML($a_xml);
        $a_mapping->addMapping('Plugins/xsev', 'xsev', $a_id, (string) $obj_self_eval->getId());
    }
}
