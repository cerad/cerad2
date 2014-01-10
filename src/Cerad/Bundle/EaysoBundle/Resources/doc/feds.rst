Person Fed Documentation
============================

Person Object
id
guid
name
dob
gender

The guid connects with account object as well as the game_official assignment.

PersonFed
id
person_id
role       AYSOV
fed_key    AYSOV12341234
status
verified
certs
orgs

During account creation a use enters
fed_key
referee badge
referee upgrading
ayso region
email

If the fed_key does not currently exist, a person_fed record is created

If the fed_key does exist then the newly created person is linked to it.
There is currently no security checking done so a user could connect to someone elses record.
The email could be used as a security check,

A person_fed_cert Referee record will also be created with referee_badge and referee_upgrading.
Referee_badge will actually be stored as referee_user since the user will sometimes enter an incorrect badge.

A person_fed_org record will be created using the ayso region number.
The region number might be incorrect so it needs to be verified during a syn operation.

It;s tempting to want to move the fed stuff into their own namespace

Person - PersonFed - Fed

PersonFed
    idx
    person_id
    fed_key - Unique
    status
    verified

FedPerson
   idx
   role
   fed_key Unique
   status verified
   verified

   FedPersonCerts
   FedPersonOrgs

Having a Fed context implies that much of seldom changing cert information 
could be in an individual database and shared bewteen different applications.

Currently, an eayso cert import updates person.gender. person.dob is not available.

---
Assume have eayso specific schema.

eayso_volunteer
    eayso_volunteer_cert
    eayso_person_org (really don't need this as a person should only belong to one region)

    eayso_player  More for future use - might just be an attribute (player id)

Be nice if we could completely blow away the eayso database.

That implies rebuilding completely from eayso imports.

There can be a lag between the import and wanting to get player in the system.
This implies the need to allow editing the database which can make restarting from stratch difficult.

Have a perserve attribute.

PersonFed
    person_id
    person_verified # Implies that the connection between the person and the fed record is okay

    fed_role_key

    fed_key          # Unique, can be null
    fed_key_verified # Implies that a fed_person record exists
                     # Not really needed since always just do a query?

    official_badge          # If a fed_key has been found, then badge will always be verified?
    official_badge_verified # Again, could just do a query?
    official_badge_user
    official_upgrading

    org_key
    org_key_verified # Need this because the eayso region might be different
                     # Used to flag records which require manual verification
                     # Also be used to adjust someone's region to a fake region?

    mem_year         # Probably going too far, allows independence from fed/eayso bundle

The person fed record would contain everything normally neeed to assign a referee.

Also allow logging in using aysoid as username.

A query to FedPerson would be needed for additional information.

Generally focuse on just one Fed (say AYSO) but having more would be nice.

Allow fed_key to be null which allows adding a record without mucking with the fed schema.

Allows multiple people to share same aysoid during account creation?
Don't really like that.  
Trigger:
    Setting person_verified back to false which in turn blocks login.
    Telling people an account already exists.

FedBundle
    Should only know about the FedStuff.
    But allow overriding for PersonBundle ?

======================================================================
# Only have PersonFed record
# Simple and fast, works AYSOV,USSFC,NFHSC(Single state)

idx
person_idx
person_fed_verified

fed_role
fed_key
fed_key_verified

org_key
org_key_verified

mem_year

referee_badge
referee_badge_user
referee_badge_verified
referee_experience
referee_upgrading

assessor_badge
safehaven_badge

name_full
email
phone
dob
gender
address_city
address_state

status

================================
person_fed
    idx
    person_idx
    fed_role
    fed_key
    org_key
    mem_year
    referee_badge
    status
