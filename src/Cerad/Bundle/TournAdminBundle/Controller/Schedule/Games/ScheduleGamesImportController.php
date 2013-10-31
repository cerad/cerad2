<?php
namespace Cerad\Bundle\TournAdminBundle\Controller\Schedule\Games;

use Cerad\Bundle\TournAdminBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

class ScheduleGamesImportController extends MyBaseController
{
    public function importAction(Request $request)
    {       
        // The search model
        $model = $this->createModel($request);
        
        // Simple custom form
        $form = $this->createModelForm($model);
        
        $form->handleRequest($request);

        if ($form->isValid()) // GET Request
        {   
            $modelx = $form->getData();
            
            $model = $this->processModel($modelx);
        }
        $results = isset($model['results']) ? $model['results']: null;
        
        // Render
        $tplData = array();
        $tplData['form'] = $form->createView();
        $tplData['results'] = $results;
        return $this->render($request->get('_template'),$tplData);
    }
    /* ========================================================
     * Eventually want to move the file someplace safe and redirect
     * Then allow for mulitple import/processing passes
     * 
     * But for now, just process the silly thing
     */
    public function processModel($model)
    {
      //$file->move($dir, $file->getClientOriginalName());
        
        $file = $model['attachment'];
        
      //echo sprintf("Max file size %d %d Valid: %d, Error: %d<br />\n",
      //    $file->getMaxFilesize(),$file->getClientSize(),$file->isValid(), $file->getError());
        
        $importFilePath = $file->getPathname();
        $clientFileName = $file->getClientOriginalName();
        
        $params['project']  = $model['project'];
        $params['filepath'] = $importFilePath;
        $params['basename'] = $clientFileName;
        
        $importService = $this->get('cerad_tourn.schedule_games.import_xls');
        
        $results = $importService->import($params);

        $model['results']  = $results;
        return $model;
    }
    public function createModel(Request $request)
    {   
        // Build the search parameter information
        $model = array();
        $model['project'] = $this->getProject();
        $model['attachment'] = null;
        
        return $model;
    }
    protected function createModelForm($model)
    {
        $builder = $this->createFormBuilder($model);
        
        // TODO: Figure out how to get this from the request object
        $builder->setAction($this->generateUrl('cerad_tourn_admin_schedule_games_import'));
        $builder->setMethod('POST');
        
        $builder->add('attachment', 'file');
        
        $builder->add('import', 'submit', array(
            'label' => 'Import Schedule Games',
            'attr' => array('class' => 'import'),
        ));        
        return $builder->getForm();
    }
}
