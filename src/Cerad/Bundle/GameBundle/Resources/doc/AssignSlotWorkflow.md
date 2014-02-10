
AssigneeWorkflow
  StateRequestedByAssignee => StateRequested, xfer  person, from StateOpen or StateIfNeededByAssignee
  StateIfNeededByAssignee  => StateIfNeeded,  xfer  person, from StateOpen  or StateRequestedByAssigneeXXX
  StateRemoveByAssignee    => StateOpen,      clear person, from StateRequestedByAssigneeXXX or StateIdNeededByAssigneeXXX
  StateDeclinedByAssignee  => StateOpen, clear person, notify assignor
  StateTurnbackByAssignee  => StateTurnbackRequested, keep person, notify assignor

AssignorWorkflow

Distingush between states and commands?

