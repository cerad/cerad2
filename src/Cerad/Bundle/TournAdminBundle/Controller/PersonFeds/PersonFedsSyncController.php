<?php
namespace Cerad\Bundle\TournAdminBundle\Controller\PersonFeds;

use Cerad\Bundle\TournAdminBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

class PersonFedsSyncController extends MyBaseController
{
    public function syncAction(Request $request)
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
        $tplData['form']    = $form->createView();
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
        $file = $model['attachment'];
        
        /* ===============================================================
         * TODO: Handle $file->isValid before calling sync
         * upload_max_filesize=50M in php.ini
         * memory_limit=512M
         */
      //echo sprintf("Max file size %d %d Valid: %d, Error: %d<br />\n",
      //  $file->getMaxFilesize(),$file->getClientSize(),$file->isValid(), $file->getError());
       
        $params['filepath'] = $file->getPathname();
        $params['basename'] = $file->getClientOriginalName();
        
        $syncService = $this->get('cerad_eayso.feds.sync');
        
        $results = $syncService->process($params);

        $model['results']  = $results;
        return $model;
    }
    public function createModel(Request $request)
    {   
        // Build the search parameter information
        $model = array();
        $model['attachment'] = null;
        $model['_route']     = $request->get('_route');
        return $model;
    }
    protected function createModelForm($model)
    {
        $builder = $this->createFormBuilder($model);
        
        $builder->setAction($this->generateUrl($model['_route']));
        $builder->setMethod('POST');
        
        $builder->add('attachment', 'file');
        
        $builder->add('sync', 'submit', array(
            'label' => 'Sync eAyso info',
            'attr'  => array('class' => 'sync'),
        ));        
        return $builder->getForm();
    }
}
