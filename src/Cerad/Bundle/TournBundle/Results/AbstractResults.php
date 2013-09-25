<?php
/* =========================================================
 * Focuses on calculating pool play results
 * 
 * 1. Calc points earned for game
 * 2. Calc points earned for pool team
 * 3. Apply tie breaking rules
 */
namespace Cerad\Bundle\TournBundle\Results;

class AbstractResults
{
    /* ==========================================================
     * For calculating points
     */
    protected $pointsEarnedForWin     = 6;
    protected $pointsEarnedForTie     = 3;
    protected $pointsEarnedForLoss    = 0;
    protected $pointsEarnedForShutout = 0;
    
    protected $pointsEarnedForGoalsMax = 3;
    
    protected $pointsMinusForPlayerWarning  = 0;
    protected $pointsMinusForCoachWarning   = 0;
    protected $pointsMinusForSpecWarning    = 0;
    
    protected $pointsMinusForPlayerEjection = 2;
    protected $pointsMinusForCoachEjection  = 3;
    protected $pointsMinusForSpecEjection   = 0;
    
    // This if for the pool play results
    protected $maxGoalsScoredPerGame  = 3;
    protected $maxGoalsAllowedPerGame = 5;
    
    /* =========================================================
     * Called by game report controller
     * Game is for future use, maybe not all games count?
     * Maybe also for forfeits?
     */
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
   
        $pointsMinus  = 0;
        $pointsEarned = 0;
        
        if ($team1Goals  > $team2Goals) $pointsEarned += $this->pointsEarnedForWin;
        if ($team1Goals == $team2Goals) $pointsEarned += $this->pointsEarnedForTie;
        if ($team1Goals  < $team2Goals) $pointsEarned += $this->pointsEarnedForLoss;
        
        if ($team2Goals == 0) $pointsEarned += $this->pointsEarnedForShutout;
        
        $maxGoals = $team1Goals;
        if ($maxGoals > $this->pointsEarnedForGoalsMax) $maxGoals = $this->pointsEarnedForGoalsMax;
        $pointsEarned += $maxGoals;
      
        $fudgeFactor   = $team1->getFudgeFactor();
        $pointsEarned += $fudgeFactor;
         
        $pointsMinus  += ($team1->getPlayerWarnings()* $this->pointsMinusForPlayerWarning);
        $pointsMinus  += ($team1->getCoachWarnings() * $this->pointsMinusForCoachWarning);
        $pointsMinus  += ($team1->getSpecWarnings()  * $this->pointsMinusForSpecWarning);
        
        $pointsMinus  += ($team1->getPlayerEjections()* $this->pointsMinusForPlayerEjection);
        $pointsMinus  += ($team1->getCoachEjections() * $this->pointsMinusForCoachEjection);
        $pointsMinus  += ($team1->getSpecEjections()  * $this->pointsMinusForSpecEjection);
             
        $pointsEarned -= $pointsMinus;
              
        // Sportsmanship?
        
        // Save
        $team1->setPointsMinus ($pointsMinus);
        $team1->setPointsEarned($pointsEarned);
        
        return;     
    }
    /* ===========================================================
     * Internal routine to allow recalculating points earned if needed
     */
    protected function calcPointsEarnedForGame($game)
    {
        $homeTeamReport = $game->getHomeTeam()->getReport();
        $awayTeamReport = $game->getAwayTeam()->getReport();
        
        $this->calcPointsEarnedForTeam($homeTeamReport,$awayTeamReport);
        $this->calcPointsEarnedForTeam($awayTeamReport,$homeTeamReport);
    }
    /* =============================================================
     * Transfers data from game team to pool team
     * Summarizing the results
     */
    protected function calcPoolTeamPoints($poolTeamReport,$gameTeamReport)
    {   
        $poolTeamReport->addPointsEarned($gameTeamReport->getPointsEarned());   
        $poolTeamReport->addPointsMinus ($gameTeamReport->getPointsMinus());
        
        $poolTeamReport->addGoalsScored ($gameTeamReport->getGoalsScored());
        $poolTeamReport->addGoalsAllowed($gameTeamReport->getGoalsAllowed());
        
        /* =======================================================
         * Tie breaking rule for goals allowed
         */
        $goalsScored = $gameTeamReport->getGoalsScored();
        if ($goalsScored > $this->maxGoalsScoredPerGame) $goalsScored = $this->maxGoalsScoredPerGame;
        $poolTeamReport->addGoalsScoredMax($goalsScored);
        
        $goalsAllowed = $gameTeamReport->getGoalsAllowed();
        if ($goalsAllowed > $this->maxGoalsAllowedPerGame) $goalsAllowed = $this->maxGoalsAllowedPerGame;
        $poolTeamReport->addGoalsAllowedMax($goalsAllowed);
        
        // Conduct
        $poolTeamReport->addPlayerWarnings ($gameTeamReport->getPlayerWarnings());
        $poolTeamReport->addPlayerEjections($gameTeamReport->getPlayerEjections());
        
        $poolTeamReport->addCoachEjections($gameTeamReport->getCoachEjections());
        $poolTeamReport->addSpecEjections ($gameTeamReport->getSpecEjections ());
        
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
        
        /* ===========================================================
         * Keep for now as documentation
         */
        // WPF
        if ($poolTeamReport->getGamesPlayed())
        {
            // The 6 comes from the six soccer fest points
            $spf = 0; // 6;
            $wpf = $poolTeamReport->getPointsEarned() / (($poolTeamReport->getGamesPlayed() * 10) + $spf);
            
            $winPercent = sprintf('%.3f',$wpf);
        }
        else $winPercent = null;
        
        $poolTeamReport->setWinPercent($winPercent);
    }
    /* =====================================================
     * The extraction portion
     */
    protected $pools;
    
    protected function getPoolTeamReport($pool,$team)
    {
        $group = $team->getGroup();
        
        if (isset($this->pools[$pool]['teams'][$group])) return $this->pools[$pool]['teams'][$group];
        
        $report = $team->createPoolTeamReport();
        
        $report->setTeam($team);
        
        $this->pools[$pool]['teams'][$group] = $report;
        
        return $report;
    }
    /* ===========================================================
     * Processing one game here
     */
    protected function processPoolGame($game,$pool,$poolFilter)
    {
        // Hack to filter non pool games
        if (strpos($pool,' PP ') === false) return;
        
        // Never have a filter for now
        if ($poolFilter && $poolFilter != substr($pool,8,1)) return;

        $this->pools[$pool]['games'][$game->getId()] = $game;
        
        $homeGameTeam = $game->getHomeTeam();
        $awayGameTeam = $game->getAwayTeam();
        
        $homeGameTeamReport = $homeGameTeam->getReport();
        $awayGameTeamReport = $awayGameTeam->getReport();
        
        $homePoolTeamReport = $this->getPoolTeamReport($pool,$homeGameTeam);
        $awayPoolTeamReport = $this->getPoolTeamReport($pool,$awayGameTeam);
        
        // Summarizes results from individual games
        $this->calcPoolTeamPoints($homePoolTeamReport,$homeGameTeamReport);
        $this->calcPoolTeamPoints($awayPoolTeamReport,$awayGameTeamReport);
    }
    /* ================================================================
     * Given a list of games, pull the pool information from them
     */
    public function getPools($games, $poolFilter = null)
    {
        $this->games = $games;
        $this->pools = array();
        
        foreach($games as $game)
        {
            // Recalc? Only if formula changes
            //$this->calcPointsEarnedForGame($game);
            
            // 
            $pool = $game->getGroup();
            
            $this->processPoolGame($game,$pool,$poolFilter);
        } // die();
        
        $pools = $this->pools;
        
        ksort($pools);
        
        
        // Sort the teams by standing within each pool
        foreach($pools as $poolKey => $pool)
        {
            // Used for head to head
            $this->poolGames = $pools[$poolKey]['games'];
            
            // The teamReports
            $teams = $pool['teams'];
            
            //sort
            usort($teams,array($this,'compareTeamStandings'));
            
            $pools[$poolKey]['teams'] = $teams;
        }
        return $pools;
    }
    /* =====================================================
     * Standings sort based on PoolTeamReports
     */
    protected function compareTeamStandings($team1,$team2)
    {   
        // Points earned
        $pe1 = $team1->getPointsEarned();
        $pe2 = $team2->getPointsEarned();
        if ($pe1 < $pe2) return  1;
        if ($pe1 > $pe2) return -1;
        
        // Head to head
        $compare = $this->compareHeadToHead($team1,$team2);
        if ($compare) return $compare;
        
        // Games won
        $gw1 = $team1->getGamesWon();
        $gw2 = $team2->getGamesWon();
        if ($gw1 < $gw2) return  1;
        if ($gw1 > $gw2) return -1;
        
        // Sportsmanship deductions
        $pm1 = $team1->getPointsMinus();
        $pm2 = $team2->getPointsMinus();
        if ($pm1 < $pm2) return -1;
        if ($pm1 > $pm2) return  1;
         
        // Goals Allowed
        $ga1 = $team1->getGoalsAllowedMax();
        $ga2 = $team2->getGoalsAllowedMax();
        if ($ga1 < $ga2) return -1;
        if ($ga1 > $ga2) return  1;
        
        // Goal differential
        $gd1 = $team1->getGoalsScoredMax() - $team1->getGoalsAllowed();
        $gd2 = $team2->getGoalsScoredMax() - $team2->getGoalsAllowed();
        if ($gd1 < $gd2) return -1;
        if ($gd1 > $gd2) return  1;
        
        // Just the key
        $key1 = $team1->getTeam()->getName();
        $key2 = $team2->getTeam()->getName();
        
        if ($key1 < $key2) return -1;
        if ($key1 > $key2) return  1;
         
        return 0;
    }
    /* ===============================================
     * Assume for now they only play once
     * Though do have one division with dups
     */
    protected function compareHeadToHead($team1,$team2)
    {
        $team1Wins = 0;
        $team2Wins = 0;
        
        foreach($this->poolGames as $game)
        {
            // Should probably use group and not name
            $homeTeamName = $game->getHomeTeam()->getName();
            $awayTeamName = $game->getAwayTeam()->getName();
            
            $team1Name = $team1->getTeam()->getName();
            $team2Name = $team2->getTeam()->getName();
            
            if ($homeTeamName == $team1Name && ($awayTeamName == $team2Name))
            {
                $score1 = $game->getHomeTeam()->getReport()->getGoalsScored();
                $score2 = $game->getAwayTeam()->getReport()->getGoalsScored();
                if ($score1 > $score2) $team1Wins++;
                if ($score1 < $score2) $team2Wins++;
            }
            if ($homeTeamName == $team2Name && ($awayTeamName == $team1Name))
            {
                $score2 = $game->getHomeTeam()->getReport()->getGoalsScored();
                $score1 = $game->getAwayTeam()->getReport()->getGoalsScored();
                if ($score1 > $score2) $team1Wins++;
                if ($score1 < $score2) $team2Wins++;
            }
        }
        if ($team1Wins < $team2Wins) return  1;
        if ($team1Wins > $team2Wins) return -1;
        return 0;
    }
}
?>
