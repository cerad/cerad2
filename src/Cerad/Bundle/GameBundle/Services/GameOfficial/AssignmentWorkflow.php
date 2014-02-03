<?php
namespace Cerad\Bundle\GameBundle\Services\GameOfficial;

class AssignmentWorkflow
{
    const StateOpen = 'Open';
    
    // Assignor Workflow
    const StatePending   = 'Pending';   // By assignor
    const StatePublished = 'Published'; // By assignor
    const StateNotified  = 'Notified';  // By assignor or system when user views assignment
    
    const StateAccepted  = 'Accepted';  // By user
    const StateRefused   = 'Refused';   // Bu user
    
    const StateTurnback  = 'Turnback';  // By user for previously accepted assignment

    // Self Assign Workflow
    const StateRequested = 'Requested'; // By user for self assigning
    const StateRemove    = 'Remove';    // By user to be removed
    
    const StateApproved  = 'Approved'; // By assignor
    const StateRejected  = 'Rejected'; // By assignor
    const StateReview    = 'Review';   // By assignor, thinking about it

    /* =======================================================
     * Kind of mixing up some presentation logic here
     * 
     * In theory we should have one case per state
     * Need to handle some game change logic
     */
    public function getStateOptionsForAssignorWorkflow($state)
    {
        switch($state)
        {
            // Set by assignor
            case self::StateOpen:
            case self::StatePending:
            case self::StatePublished:
            case self::StateAccepted:
            case self::StateApproved:
                return array(
                    self::StateOpen      => self::StateOpen,
                    self::StatePending   => self::StatePending,
                    self::StatePublished => self::StatePublished,
                    self::StateNotified  => self::StateNotified,
                    self::StateAccepted  => self::StateAccepted,
                    self::StateApproved  => self::StateApproved,
                );
            // Set by user
            case self::StateRemove:
            case self::StateRefused:
            case self::StateTurnback:
                 return array(
                    self::StateOpen     => self::StateOpen,
                    self::StateRemove   => self::StateRemove,
                    self::StateRefused  => self::StateRefused,
                    self::StateTurnback => self::StateTurnback,
                );
            // Requested by user, approved or rejected by assignor
            case self::StateRequested:
            case self::StateRejected:
            case self::StateReview:
                 return array(
                    self::StateOpen     => self::StateOpen,
                    self::StateApproved => self::StateApproved,
                    self::StateRejected => self::StateRejected,
                    self::StateReview   => self::StateReview,
                );               
        }
    }
    /* ====================================================
     * The assumption is that the user has already been checked
     * and is allowed to do these things
     */
    public function getStateOptionsForUserWorkflow($state)
    {
        switch($state)
        {
            case self::StateOpen:
                 return array(
                    self::StateOpen      => self::StateOpen,
                    self::StateRequested => 'Request Assignment',
                );
            case self::StateRequested:
                 return array(
                    self::StateRemove    => 'Remove me from assignment',
                    self::StateRequested => 'Assignment Requested',
                );
            case self::StateReview:
                 return array(
                    self::StateRemove    => 'Remove me from assignment',
                    self::StateRequested => 'Assignment under Review',
                );
             case self::StateAccepted:
                 return array(
                    self::StateTurnback => 'Turnback Assignment',
                    self::StateAccepted => 'Assignment was Accepted',
                );
             case self::StateApproved:
                 return array(
                    self::StateTurnback => 'Turnback Assignment',
                    self::StateAccepted => 'Assignment was Approved',
                );
       }
    }
}
?>
