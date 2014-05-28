<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Results\Poolplay\Show;

use Cerad\Bundle\CoreBundle\Action\ActionController;

use Symfony\Component\HttpFoundation\Request;
//  Symfony\Component\HttpFoundation\RedirectResponse;

class ResultsPoolplayShowController extends ActionController
{
    public function action(Request $request, ResultsPoolplayShowModel $model)
    {
        /* ===================================================
         * No form means there is really nothing to do here
         * Let the view do the rendering
         */
        return;
    }
}
