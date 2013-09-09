<?php
namespace Cerad\Bundle\TournBundle\Controller\Tourn;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class TournIndexController extends MyBaseController
{
    public function indexAction(Request $request)
    {
        return $this->redirect('cerad_tourn_welcome');
        
    }
}
