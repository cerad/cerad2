The focus has been on ayso/ussf referee which only have one sport: soccer.

NFHS covers multiple sports

NFHS
    Soccer Referee
    Baseball Umpire
    Volleyball Judge

Same NFHS ID.

A person might recert for one but not the other.

fed_person_cert
    fed_key
    sport
    role
    mem_year

That in turn brings up org

NFHSC
    Alabama
        Soccer
            Referee
        Baseball
            Umpire
    Tennesse
        Volleyball
            Judge

AYSOV
    Region 894 - Primary
        Soccer
            Referee
            SafeHaven
            Assessor
            Coach
    Region 123 - Secondary
        Soccer
            Referee
            Instructor

USSFC
    Alabama - Primary (really should just be one)
        Soccer
            Referee
            Assignor
    Tennesse - In good standing
        Soccer
            Referee

fed_person
    idx
    fed_role
    fed_key

fed_person_org
    idx
    fed_person.idx
    is_primary
    org_key
    org_date

fed_person_org_cert
    idx
    fed_person_org.idx
    cert_sport
    cert_role
    cert_badge
    cert_date
    cert_year # membership year, year it's good for

fed_person_cert
    idx
    fed_person.idx

    cert_sport
    cert_role
    cert_badge
    cert_date
    cert_year # membership year, year it's good for

    org_key
    org_date

In the case where an ayso referee might be assigned to multiple regions the app admin would take care of picking the corect badge.
The is_primary is no longer really needed?

person_fed
person_fed_org
person_fed_org_cert

person -> person_fed -> fed_person

person.getCertAYSOReferee - Multiple regions messup

person.getCertUSSFReferee - Okay

person.getCertNFHSAlabamaSoccerReferee
person.getCertNFHSTennesseSoccerReferee

---------------------------
-- Works for all
-- Deals with dup eayso recods (multiple regions)
-- Instinct says this is best
fed_person
    fed_person_org
        fed_person_org_cert
        fed_person_org_job

-------------------------
-- Works for USSF
-- Works for NFHS, assume app logic for org state
-- Faster than three level approach
--
-- For AYSO, Have multiple region problems
             Have two different referee badges for two different regions
             is_primary would solve
             never having two regions would solve as well, extra query when syncing

fed_person
    fed_person_cert (org_key, mem_year)

-----------------------------
-- Does not work for NFHS for multiple states
-- Does not work for USSF for mem_year = referee,assignor
fed_person
    fed_person_org
    fed_person_cert 

Use person_fed = person_fed_cert in application database
Use fed_person ~ fed_person_org ~ fed_person_cert in fed database
