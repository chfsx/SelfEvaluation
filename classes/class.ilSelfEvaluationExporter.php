<?php
declare(strict_types=1);

class ilSelfEvaluationExporter extends ilXmlExporter
{

    public function init(): void
    {

    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        if ($type = ilObject::_lookupType((int)$a_id) != "xsev") {
            throw new Exception("Wrong type " . $type . " for selfevaluation export.");
        }

        $ref_array = ilObject::_getAllReferences((int) $a_id);
        $ref_id = end($ref_array);

        $obj_self_eval = new ilObjSelfEvaluation($ref_id);
        $dom = dom_import_simplexml($obj_self_eval->toXML());
        $xml_string = $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
        return $xml_string;
    }

    public function getValidSchemaVersions(string $a_entity): array
    {
        return [
            "5.3.0" => [
                "uses_dataset" => false,
                "min" => "5.3.0",
                "max" => ""
            ]
        ];
    }
}

