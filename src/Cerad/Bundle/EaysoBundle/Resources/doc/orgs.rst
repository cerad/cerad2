Organization Documentation
==========================

AYSO Org Region
In theory an ayso volunteer belongs to one and only one region.

There are duplicate volunteer records which indicate that volunteer belongs to two or more regions.

This is a bit painful because we can't always just import the region.  
If a different region number exists then we need to leave it alone.

A person might also enter an incorrect region number so also need to flag those.

A person's region might also change over time.  The updated_on column might be used to track that.

For some things (like area referee administrator) it might be useful to have AREA records as well.

FedPerson
    FedPersonOrg
        FedPersonOrgJob (referee administrator,assignor etc) # Job is not yet implemented

---
For NFHS, referees can belong to multiple active organizations (Tennesse, Alabama etc).
They need to do individual recerts for each state before they can referee in that state.

---
For USSF, referees belong to one active state for their recert.
They can referee across state lines (and often do for tournaments).

State Referee Administrators need to verify a referee is in good standing.
Good standing is really a project(tournament or season) based flag
An updated mem_year suffices for the recert stuff though it might need to be manually added.

FedPersonOrg
  id
  fed_person_id 
  role          # Region, State
  primary       # Really need to allow multiple roles with a primary one

  org_key       # AYSOR0894

  mem_year            # MY2013
  mem_year_expires_on
  mem_year_first      # How long a user has been in the organization, assignor info
  mem_year_last       # Last time they did their yearly thing, probably redundant

  status
  verified # Not needed? All comes from imports or admins?


FedPersonOrg.mem_year
===================
For ayso a volunteer needs to update their volunteer form each year.
The form is processed and the import data is updated.
Nor real neeed for an expiration date.
Really up to the app to ensure the mem_year is acceptable?
With an expiration date then a HasExpired method could work based on current date or tournament date?

Basically the same for both USSF and NFHS.
The Walker spreadsheet will update mem_year.

---
PersonFed.mem_year

Having this means resyncing based on the imports.  