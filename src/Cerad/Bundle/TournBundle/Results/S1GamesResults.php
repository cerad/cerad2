<?php
/* =========================================================
 * Focuses on calculating pool play results
 */
namespace Cerad\Bundle\TournBundle\Results;

class S1GamesResults extends AbstractResults
{
    /* ==========================================================
     * For calculating points
     */
    protected $pointsEarnedForWin      = 6;
    protected $pointsEarnedForTie      = 3;
    protected $pointsEarnedForLoss     = 0;
    protected $pointsEarnedForShutout  = 1;
    protected $pointsEarnedForGoalsMax = 3;
    
    protected $pointsMinusForPlayerWarning  = 0;
    protected $pointsMinusForCoachWarning   = 0;
    protected $pointsMinusForBenchWarning   = 0;
    protected $pointsMinusForSpecWarning    = 0;
    
    protected $pointsMinusForPlayerEjection = 1;
    protected $pointsMinusForCoachEjection  = 1;
    protected $pointsMinusForBenchEjection  = 1;
    protected $pointsMinusForSpecEjection   = 1;
    
    // This is for the pool play results
    protected $maxGoalsScoredPerGame  =  3;
    protected $maxGoalsAllowedPerGame = 99; // Used for goal differential
    
    /* =====================================================
     * Standings sort based on PoolTeamReports
     */
    protected function compareTeamStandings($team1,$team2)
    {   
        $w1 = -1; // team1 wins over team2
        $w2 =  1; // team2 wins over team1
        
        // Points earned
        $pe1 = $team1->getPointsEarned();
        $pe2 = $team2->getPointsEarned();
        if ($pe1 > $pe2) return $w1;
        if ($pe1 < $pe2) return $w2;
        
        // Head to head
        $compare = $this->compareHeadToHead($team1,$team2);
        if ($compare) return $compare;
        
        // Fewest sendoffs
        $te1 = $team1->getTotalEjections();
        $te2 = $team2->getTotalEjections();
        if ($te1 < $te2) return $w1;
        if ($te1 > $te2) return $w2;
        
        // Fewest Goals Allowed
        $ga1 = $team1->getGoalsAllowedMax();
        $ga2 = $team2->getGoalsAllowedMax();
        if ($ga1 < $ga2) return $w1;
        if ($ga1 > $ga2) return $w2;
        
        // Goal differential
        $gd1 = $team1->getGoalsScoredMax() - $team1->getGoalsAllowed();
        $gd2 = $team2->getGoalsScoredMax() - $team2->getGoalsAllowed();
        if ($gd1 < $gd2) return $w1;
        if ($gd1 > $gd2) return $w2;
        
        // Best sportsmanship
        $sp1 = $team1->getSportsmanship();
        $sp2 = $team2->getSportsmanship();
        if ($sp1 > $sp2) return $w1;
        if ($sp1 < $sp2) return $w2;
         
        // WPF?
        
        // Coin toss
        // Make sure order never changes
        $group1 = $team1->getTeam()->getGroup();
        $group2 = $team2->getTeam()->getGroup();
        
        if ($group1 < $group2) return $w1;
        if ($group1 > $group2) return $w2;
         
        // Should not happen
        return 0;
    }

}
?>
