<?php

namespace Cerad\Bundle\PersonBundle;

final class PersonEvents
{
    const FindPersonById          = 'CeradPersonFindPersonById';
    const FindPersonByGuid        = 'CeradPersonFindPersonByGuid';
    const FindPersonByFedKey      = 'CeradPersonFindPersonByFedKey';
    const FindPersonByProjectName = 'CeradPersonFindPersonByProjectName';
    
    const FindOfficialsByProject  = 'CeradPersonFindOfficialsByProject';
    
    const FindPersonPlanByProjectAndPersonGuid = 'CeradPersonFindPersonPlanByProjectAndPersonGuid';
    
    const FindPlanByProjectAndPerson = 'CeradPersonFindPlanByProjectAndPerson';

}
