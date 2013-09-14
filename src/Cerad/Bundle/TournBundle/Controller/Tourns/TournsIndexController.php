<?php
namespace Cerad\Bundle\TournBundle\Controller\Tourns;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class TournsIndexController extends MyBaseController
{
    public function indexAction(Request $request, $slug)
    {
        return $this->redirect('cerad_tourns_welcome', array('slug' => $slug));
    }
}
