<?php

namespace Cerad\Bundle\GameBundle\Service\Results;

/* =============================================
 * Value object
 * This summarizes the results for a team in a given pool
 */
class PoolTeamReport
{
    protected $pointsEarned = 0;
    protected $pointsMinus  = 0;
    
    protected $goalDifferential;
    
    protected $goalsScored  = 0;
    protected $goalsAllowed = 0;
    
    protected $goalsScoredMax  = 0;
    protected $goalsAllowedMax = 0;
    
    protected $gamesTotal  = 0;
    protected $gamesPlayed = 0;
    protected $gamesWon    = 0;
    
    protected $winPercent  = 0.000;
    
    protected $playerWarnings  = 0;
    protected $playerEjections = 0;
    
    protected $coachWarnings  = 0;
    protected $coachEjections = 0;
    
    protected $benchWarnings  = 0;
    protected $benchEjections = 0;
    
    protected $specWarnings   = 0;
    protected $specEjections  = 0;
 
    protected $injuries = 0;
    
    protected $team;
    
    public function getPointsEarned() { return $this->pointsEarned; }
    public function getPointsMinus()  { return $this->pointsMinus;  }
    
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
    
    public function getPlayerWarnings () { return $this->playerWarnings; }
    public function getCoachWarnings  () { return $this->coachWarnings;  }
    public function getBenchWarnings  () { return $this->benchWarnings;  }
    public function getSpecWarnings   () { return $this->specWarnings;   }
    
    public function addPlayerEjections($value) { $this->playerEjections  += $value; }
    public function addCoachEjections ($value) { $this->coachEjections   += $value; }
    public function addBenchEjections ($value) { $this->benchEjections   += $value; }
    public function addSpecEjections  ($value) { $this->specEjections    += $value; }
    
    public function getPlayerEjections() { return $this->playerEjections; }
    public function getCoachEjections () { return $this->coachEjections;  }
    public function getBenchEjections () { return $this->benchEjections;  }
    public function getSpecEjections  () { return $this->specEjections;   }
    
    public function addSportsmanship($value) { $this->sportsmanship += $value; }
    public function getSportsmanship()       { return $this->sportsmanship;    }
    
    public function addGamesTotal ($value) { $this->gamesTotal  += $value; }
    public function addGamesPlayed($value) { $this->gamesPlayed += $value; }
    public function addGamesWon   ($value) { $this->gamesWon    += $value; }
    
    public function getGamesTotal () { return $this->gamesTotal;  }
    public function getGamesPlayed() { return $this->gamesPlayed; }
    public function getGamesWon   () { return $this->gamesWon;    }
    
    public function getGoalsScored     () { return $this->goalsScored ;     }
    public function getGoalsAllowed    () { return $this->goalsAllowed;     }
    public function getGoalsScoredMax  () { return $this->goalsScoredMax;   }
    public function getGoalsAllowedMax () { return $this->goalsAllowedMax;  }
    public function getGoalDifferential() { return $this->goalDifferential; }
    
    public function setWinPercent($value) { $this->winPercent = $value; }
    public function getWinPercent()       { return $this->winPercent; }
    
    public function getTotalEjections()
    {
        return $this->playerEjections +
               $this->benchEjections  +
               $this->coachEjections  +
               $this->specEjections;
    }
    public function setTeam($team) { $this->team = $team; }
    public function getTeam()      { return $this->team;  }

    
}
?>
