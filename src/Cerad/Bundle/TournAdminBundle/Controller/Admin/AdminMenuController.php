<?php
namespace Cerad\Bundle\TournAdminBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class AdminMenuController extends MyBaseController
{
    public function showAction(Request $request)
    {
        if (!$this->hasRoleAdmin()) return $this->redirect('cerad_tourn_home');

        $tplData = array();
        $tplName = $request->get('_template');
        return $this->render($tplName, $tplData);
    }
}
