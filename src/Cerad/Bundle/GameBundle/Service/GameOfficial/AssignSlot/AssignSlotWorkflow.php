<?php
namespace Cerad\Bundle\GameBundle\Service\GameOfficial\AssignSlot;

/* =========================================================
 * This could probably be encoded in a yaml file
 */
class AssignSlotWorkflow
{
    const StateOpen = 'Open';
    
    // Assignor Workflow
    const StatePending   = 'Pending';   // By assignor
    const StatePublished = 'Published'; // By assignor
    const StateNotified  = 'Notified';  // By assignor or system when user views assignment
    
    const StateAccepted  = 'Accepted';  // By user
    const StateRefused   = 'Refused';   // Bu user
    
    const StateTurnback          = 'Turnback';          // By user for previously accepted assignment
    const StateTurnbackApproved  = 'TurnbackApproved';  // By assignor - acknowledge turnback

    // Self Assign Workflow
    const StateRequested = 'Requested'; // By user for self assigning
    const StateIfNeeded  = 'IfNeeded';  // By user, will take assignment of needed
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
                return array(
                    self::StateOpen      => self::StateOpen,
                    self::StatePending   => self::StatePending,
                    self::StatePublished => self::StatePublished,
                    self::StateNotified  => self::StateNotified,
                    self::StateAccepted  => self::StateAccepted,
                    self::StateApproved  => self::StateApproved,
                );
                
            // Set by assignor
            case self::StateApproved:
                return array(
                    self::StateApproved  => self::StateApproved,
                    self::StateRemove    => 'Remove from game',
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
            case self::StateIfNeeded:
            case self::StateRejected:  // Should not occur
            case self::StateReview:
                 return array(
                    self::StateRequested => 'Requested by User',
                    self::StateIfNeeded  => 'If Needed',
                    self::StateApproved  => 'Approve Request',
                    self::StateRejected  => 'Reject Request',
                    self::StateReview    => 'Under Review',
                );               
        }
        // Oops
        return array ($state => $state);
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
                    self::StateIfNeeded  => 'If Needed',
                );
            case self::StateRequested:
                 return array(
                    self::StateRequested => 'Assignment Requested',
                    self::StateRemove    => 'Remove me from assignment',
                );
            case self::StateIfNeeded:
                 return array(
                    self::StateIfNeeded  => 'If Needed',
                    self::StateRemove    => 'Remove me from assignment',
                    self::StateRequested => 'Request Assignment',
                );
            case self::StateReview:
                 return array(
                    self::StateReview    => 'Assignment under Review',
                    self::StateRemove    => 'Remove me from assignment',
                );
             case self::StateAccepted:
                 return array(
                    self::StateAccepted => 'Assignment was Accepted',
                    self::StateTurnback => 'Turnback Assignment',
                );
             case self::StateApproved:
                 return array(
                    self::StateApproved => 'Assignment was Approved',
                    self::StateTurnback => 'Turnback Assignment',
                );
       }
       // Oops
       return array ($state => $state);
    }
}
?>
