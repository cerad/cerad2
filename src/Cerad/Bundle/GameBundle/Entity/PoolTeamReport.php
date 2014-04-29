<?php
namespace Cerad\Bundle\GameBundle\Doctrine\Entity;

/* =============================================
 * Value object
 * This summarizes the results for a team in a given pool
 */
class PoolTeamReport extends GameTeamReport
{
    protected $goalDifferential;
    
    protected $goalsScoredMax;
    protected $goalsAllowedMax;
    
    protected $gamesTotal;
    protected $gamesPlayed;
    protected $gamesWon;
    
    protected $winPercent;
    
    public function addPointsEarned($value) { $this->pointsEarned += $value; }
    public function addPointsMinus ($value) { $this->pointsMinus  += $value; }
    
    public function addGoalsScored ($value) { $this->goalsScored  += $value; }
    public function addGoalsAllowed($value) { $this->goalsAllowed += $value; }
    
    public function addGoalsScoredMax  ($value) { $this->goalsScoredMax   += $value; }
    public function addGoalsAllowedMax ($value) { $this->goalsAllowedMax  += $value; }
    public function addGoalDifferential($value) { $this->goalDifferential += $value; }
    
    public function addPlayerWarnings ($value) { $this->playerWarnings  += $value; }
    public function addCoachWarnings  ($value) { $this->coachWarnings   += $value; }
    public function addBenchWarnings  ($value) { $this->benchWarnings   += $value; }
    public function addSpecWarnings   ($value) { $this->specWarnings    += $value; }
    
    public function addPlayerEjections($value) { $this->playerEjections  += $value; }
    public function addCoachEjections ($value) { $this->coachEjections   += $value; }
    public function addBenchEjections ($value) { $this->benchEjections   += $value; }
    public function addSpecEjections  ($value) { $this->specEjections    += $value; }
    
    public function addSportsmanship  ($value) { $this->sportsmanship    += $value; }
    
    public function addGamesTotal ($value) { $this->gamesTotal  += $value; }
    public function addGamesPlayed($value) { $this->gamesPlayed += $value; }
    public function addGamesWon   ($value) { $this->gamesWon    += $value; }
    
    public function getGamesTotal () { return $this->gamesTotal;  }
    public function getGamesPlayed() { return $this->gamesPlayed; }
    public function getGamesWon   () { return $this->gamesWon;    }
    
    public function getGoalsScoredMax  () { return $this->goalsScoredMax ;  }
    public function getGoalsAllowedMax () { return $this->goalsAllowedMax;  }
    public function getGoalDifferential() { return $this->goalDifferential; }
    
    public function setWinPercent($value) { $this->winPercent = $value; }
    public function getWinPercent()       { return $this->winPercent; }

}
?>
