Person Fed Cert Documentation
================================

id 
Autoinc id

fed_id
link to person_fed record
A cert belongs to one and only one fed record

role
Referee, SafeHaven, Coach, Assessor, Assignor
For a given fed is there will be at most one record for each role.
The record will contain the current information for that role.
So a Role of Referee with a Badge of Advanced indicated that the person_fed is currently an AYSO Advanced Referee.

role_date
Date first certified for the current role.  
In other words, attempts to show how long a person has been refereeing, assessing, assigning etc.
Useful for assignors

badge_current
Current badge for the referee
For new reocrds, the user can fill this in but it will not be considered as verified.

For AYSO imort, the current badge will only be updated if the imported value is higher than the current value.
This is because you cannot export the highest badge from eayso.

For USSF/NFHS the current badge will be set based on the import data since referees need to recert to maintain their badges.

badge_current_verified
Unverified badges will have a value of null
A value of 'eayso' will indicate that the badge was verfied via a eayso import
A value of 'walker' for Bill Walker's reports
A value with a username or possible guid or just admin will indicate manual verification

TODO: Could add more history (verified date, verified by, notes etc).
      hould be in it's own table.

badge_current_certified_on
Date of certification for the current badge.

badge_current_expires_on
Date certification will expire

badge_user
Filled in by the user when registering a new account.  
badge will intially be set to badge_user.
However, it's not uncommon (especially in AYSO) for a referee to think they are certified as one badge
when eayso shows them as having a different badge.  
The badge_user allows tracking this mismatch and can be used to encourage the user to complete their paperwork.

During eayso import, if the user badge is lower than the imported badge then the user_badge will be updated.
Otherwise, no updates.
Styling can be used to show a mismatch.  A filter can also be used.

badge_highest*
For USSF, referees need to recert evey year.  
If a grade 7 referee failes to recertas Grade 7 then their current badge
drops back to grade 8.
The badge_highest should show the highest badge the referee has attained.
Useful for assignors.

I't also a bit handy for NFHS where (in some states at least) getting above and maintaining the entry badge is difficult.

Not really applicable to AYSO as recerts are not required.

badge_highest_date
Date of certification for the highest badge.

----
Rethink the highest badge stuff.  Have a RefereeHighest role to track that stuff.

Go one more step and have a RefereeUser or RefereeUpgradingTo role?

RefereeOriginal role.

Have a sub role as well as a role
Referee
    Current
    Highest
    First
    User
    Upgrading

A RefereeUser record could be awkward.
Create during registration
If it exists then it needs to be checked during import.
Extra query.  Extra date stuff.
A badge_user woud be cleaner and faster.

The other sub roles could be created when I needed them.

Or have a person_fed_cert_history record?
The history makes ayso cleaner.
Use USSF data for loading.

id
fed_id
role
role_date
badge
badge_user = Set by user
date_certified  certified_on
date_expires    expires_on

upgrading = Set by user
status
verified
