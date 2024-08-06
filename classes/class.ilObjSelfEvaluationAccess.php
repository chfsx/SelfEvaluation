<?php

declare(strict_types=1);

class ilObjSelfEvaluationAccess extends ilObjectPluginAccess
{
    public function _checkAccess(string $a_cmd, string $a_permission, int $a_ref_id, int $a_obj_id, ?int $a_user_id = null): bool
    {
        if ($a_user_id == '') {
            $a_user_id = $this->user->getId();
        }

        switch ($a_permission) {
            case 'read':
            case 'visible':
                $object = new ilObjSelfEvaluation($a_ref_id);
                if (!$object->isOnline() && !$this->access->checkAccessOfUser($a_user_id, 'write', '', $a_ref_id)
                ) {
                    return false;
                }
                break;
        }
        return true;
    }
}
