<?php
/* =========================================================
 * Focuses on calculating pool play results
 */
namespace Cerad\Bundle\GameBundle\Service\Results;

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
    
    // This if for total goal differential
    protected $maxGoalDifferentialPerGame = 3;
    
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
        // NO LIMIT ON GOALS ALLOWED IN THE RULES
        $ga1 = $team1->getGoalsAllowed();
        $ga2 = $team2->getGoalsAllowed();
        if ($ga1 < $ga2) return $w1;
        if ($ga1 > $ga2) return $w2;
        
        // Highest Goal Differential
        // MAX 3 per game
        $gd1 = $team1->getGoalDifferential();
        $gd2 = $team2->getGoalDifferential();
        if ($gd1 > $gd2) return $w1;
        if ($gd1 < $gd2) return $w2;
        
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
    /* =============================================================
     * Transfers data from game team to pool team
     * Summarizing the results
     */
    protected function calcPoolTeamPoints($poolTeamReport,$gameTeamReport)
    {   
        $poolTeamReport->addPointsEarned($gameTeamReport->getPointsEarned());   
        $poolTeamReport->addPointsMinus ($gameTeamReport->getPointsMinus());
        
        $goalsScored  = $gameTeamReport->getGoalsScored();
        $goalsAllowed = $gameTeamReport->getGoalsAllowed();
       
        $poolTeamReport->addGoalsScored ($goalsScored );
        $poolTeamReport->addGoalsAllowed($goalsAllowed);
        
        /* ================================================
         * Differential
         */
        $goalDifferential = $goalsScored - $goalsAllowed;

        // Max 3 per game
        if ($goalDifferential > $this->maxGoalDifferentialPerGame) 
        {
            $goalDifferential = $this->maxGoalDifferentialPerGame;
        }
        // Min -3 per game?
        if ($goalDifferential < ($this->maxGoalDifferentialPerGame * -1))
        {
            $goalDifferential = $this->maxGoalDifferentialPerGame * -1;
        }
        $poolTeamReport->addGoalDifferential($goalDifferential);
        
        // Conduct
        $poolTeamReport->addPlayerWarnings ($gameTeamReport->getPlayerWarnings ());
        $poolTeamReport->addPlayerEjections($gameTeamReport->getPlayerEjections());
        
        $poolTeamReport->addCoachWarnings ($gameTeamReport->getCoachWarnings ());
        $poolTeamReport->addCoachEjections($gameTeamReport->getCoachEjections());
        
        $poolTeamReport->addBenchWarnings ($gameTeamReport->getBenchWarnings ());
        $poolTeamReport->addBenchEjections($gameTeamReport->getBenchEjections());
        
        $poolTeamReport->addSpecWarnings ($gameTeamReport->getSpecWarnings ());
        $poolTeamReport->addSpecEjections($gameTeamReport->getSpecEjections());
        
        $poolTeamReport->addSportsmanship($gameTeamReport->getSportsmanship());
        
        // Missing from national?
        $poolTeamReport->addGamesTotal(1);
        
        if ($gameTeamReport->getGoalsScored() !== null)
        {
            // Track games played
            $poolTeamReport->addGamesPlayed(1);
            
            // Track games won
            if ($gameTeamReport->getGoalsScored() > $gameTeamReport->getGoalsAllowed()) $poolTeamReport->addGamesWon(1);
        }        
    }
}
?>
