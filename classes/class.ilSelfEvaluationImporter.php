<?php

declare(strict_types=1);

class ilSelfEvaluationImporter extends ilXmlImporter
{
    /**
     * Import xml representation
     * @param string          $entity
     * @param string          $id
     * @param string          $xml
     * @param ilImportMapping $mapping
     * @return    int    $ref_id
     */
    public function importXmlRepresentation($entity, $id, $xml, $mapping): void
    {
        $ref_id = false;
        foreach ($mapping->getMappingsOfEntity('Services/Container', 'objs') as $old => $new) {
            if (ilObject::_lookupType($new) === "xsev" && $id == $old) {
                $ref_array = ilObject::_getAllReferences($new);
                $ref_id = end($ref_array);
            }
        }

        $obj_self_eval = new ilObjSelfEvaluation((int)$ref_id);
        $obj_self_eval->fromXML($xml);
        $mapping->addMapping('Plugins/xsev', 'xsev', $id, (string)$obj_self_eval->getId());
    }
}