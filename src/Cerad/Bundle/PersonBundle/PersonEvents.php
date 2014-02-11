<?php

namespace Cerad\Bundle\PersonBundle;

final class PersonEvents
{
    const FindPersonByGuid        = 'CeradPersonFindPersonByGuid';
    const FindPersonByFedKey      = 'CeradPersonFindPersonByFedKey';
    const FindPersonByProjectName = 'CeradPersonFindPersonByProjectName';
    
    const FindOfficialsByProject  = 'CeradPersonFindOfficialsByProject';
    
    const FindPersonPlanByProjectAndPersonGuid = 'CeradPersonFindPersonPlanByProjectAndPersonGuid';
}
