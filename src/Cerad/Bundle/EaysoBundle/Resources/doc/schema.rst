person
    idx    # The x just means it's autoincrement
    guid   # Generated on person create
    name   # changable by user
    email  # changable by user
    dob    # fed importable
    gender # fed importable

person_fed
    idx
    person_idx
    person_verified # Person Fed link has been verified

    fed_role_key    # AYSOVolunteer,AYSOPlayer,USSFContractor,NFHSContractor

    fed_key         # Unique or null, may or may not exist in fed database yet
                    # Verification is done by looking it up

    referee_badge          # User created, Sync and verified
    referee_badge_verified # If eayso is not available
    referee_badge_user     # User updatable
    referee_upgrading      # User updatable
    referee_experience     # User updateable

    assessor_badge

    mem_year # MY2013, synced and verified

    org_key          # AYSOR0894, primary org key, Set by user
                     # Might be different because of eayso issues?
                     # TODO: Make project specific?
                     # Will not be adjusted by import

    org_key_is_primary # If true, must match primary key in fed database
                       # Setting to false means skip the check

    safe_haven       # Or equivalent, synced and verified

    status

=========================================================================
= Only created and updated via imports
= Hence the data should always be accurate
= Hence no verifications

fed_person
    idx
    fed_role_key # AYSOV
    fed_key      # AYSOV12341234, Unique

    name_full    # These are used for validation
    email
    phone
    dob
    gender

    status

    
fed_person_cert
    idx
    fed_person_idx

    role           # One and only one per person, Referee, SafeHaven, Assignor
    role_date      # First certied in the role

    badge          # National etc
    badge_date     # When certified for that badge
    status

fed_person_org
    idx
    fed_person_idx
    role           # Region, State
    role_date      # First joined the organization
    is_primary     # Allow multiple roles but only one primary per role
                   # This is the only thing we might need to perserve if the fed database is dropped
                   # Otherwise, first come first serve

    org_key        # AYSOR0894

    mem_year       # MY2013
    status

TODO
fed_person_cert_history  # Track upgrades
fed_person_org_job       # Referee Administrator etc
fed_person_org_background_check
