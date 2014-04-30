<?php
/* =========================================================
 * Focuses on calculating pool play results
 */
namespace Cerad\Bundle\GameBundle\Service\Results;

class NatGamesResults extends AbstractResults
{
    protected $pointsEarnedForWin     = 6;
    protected $pointsEarnedForTie     = 3;
    protected $pointsEarnedForLoss    = 0;
    protected $pointsEarnedForShutout = 0;
    
    protected $pointsEarnedForGoalsMax = 3;
    
    protected $pointsMinusForPlayerEjection = 1;
    protected $pointsMinusForCoachEjection  = 1;
    protected $pointsMinusForBenchEjection  = 1;
      
    public function calcPointsEarnedForTeam($team1,$team2)
    {   
        // Make scores are set
        $team1Goals = $team1->getGoalsScored();
        $team2Goals = $team2->getGoalsScored();
        if (($team1Goals === null) || ($team2Goals === null)) 
        {
            $team1->clear();
            $team2->clear();
            return;
        }
        $team1->setGoalsAllowed($team2Goals);
        $team2->setGoalsAllowed($team1Goals);
   
        $pointsEarned = 0;
        
        if ($team1Goals  > $team2Goals) $pointsEarned += $this->pointsEarnedForWin;
        if ($team1Goals == $team2Goals) $pointsEarned += $this->pointsEarnedForTie;
        if ($team1Goals  < $team2Goals) $pointsEarned += $this->pointsEarnedForLoss;
        
        if ($team2Goals == 0) $pointsEarned += $this->pointsEarnedForShutout;
        
        // Winning team gets goal differential
        if ($team1Goals  > $team2Goals)
        {
            $goalDiff = $team1Goals  - $team2Goals;
            if ($goalDiff > $this->pointsEarnedForGoalsMax) $goalDiff = $this->pointsEarnedForGoalsMax;
            $pointsEarned += $goalDiff;
        }
      
        $fudgeFactor   = $team1->getFudgeFactor();
        $pointsEarned += $fudgeFactor;
        
        $pointsMinus = 0;
        $pointsMinus  += ($team1->getPlayerEjections()* $this->pointsMinusForPlayerEjection);
        $pointsMinus  += ($team1->getCoachEjections() * $this->pointsMinusForCoachEjection);
        $pointsMinus  += ($team1->getBenchEjections() * $this->pointsMinusForBenchEjection);
             
        $pointsEarned -= $pointsMinus;
        
        // Save
        $team1->setPointsMinus ($pointsMinus);
        $team1->setPointsEarned($pointsEarned); // Just as an error check
        
        return;     
    }

}
?>
